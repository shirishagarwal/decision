<?php
/**
 * File Path: cron/scout_failures.php
 * Description: Automated daily scout that searches for strategic failures,
 * bad corporate decisions, and business post-mortems across multiple industries.
 */

require_once __DIR__ . '/../config.php';

/**
 * 1. Broad Discovery Engine
 * We search Hacker News for a wider array of failure-related keywords.
 */
function discoverLinks() {
    $yesterday = time() - (100 * 24 * 60 * 60);
    
    // Expanded keyword list to catch non-startup corporate failures
    $queries = [
        'corporate failure OR "strategic mistake"',
        '"bad decision" OR "failed acquisition"',
        'post mortem OR "why it failed"',
        '"business lesson" OR "product failure"',
        '"bankruptcy" OR "pivot failure"'
    ];

    $allHits = [];
    foreach ($queries as $q) {
        $encodedQuery = urlencode($q);
        $url = "https://hn.algolia.com/api/v1/search_by_date?query={$encodedQuery}&tags=story&numericFilters=created_at_i>{$yesterday}";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        if (!empty($data['hits'])) {
            $allHits = array_merge($allHits, $data['hits']);
        }
    }

    // Deduplicate hits by URL or ID
    $uniqueHits = [];
    foreach ($allHits as $hit) {
        $key = $hit['url'] ?? $hit['objectID'];
        $uniqueHits[$key] = $hit;
    }

    return array_values($uniqueHits);
}

/**
 * 2. Extract clean text from the target source
 */
function fetchUrlContent($url) {
    if (empty($url)) return null;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'DecisionVaultBot/2.0 (Strategic Intelligence Scout)');
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $content = curl_exec($ch);
    curl_close($ch);
    
    if (!$content) return null;

    // Remove scripts, styles, and tags to isolate the narrative logic
    $text = preg_replace('/<(script|style)\b[^>]*>(.*?)<\/\1>/is', "", $content);
    return mb_strimwidth(strip_tags($text), 0, 15000, "...");
}

/**
 * 3. Strategic Analysis via Gemini
 */
function analyzeWithAI($text) {
    // API Key is provided by the execution environment at runtime
    const apiKey = "";
    const model = "gemini-2.5-flash-preview-09-2025";
    const url = `https://generativelanguage.googleapis.com/v1beta/models/${model}:generateContent?key=${apiKey}`;

    $prompt = "Analyze this business text which describes a strategic failure, a bad corporate decision, or a company collapse.
    
    Extract the following data into a RAW JSON object:
    1. 'company_name': The entity that made the decision.
    2. 'industry': The sector (e.g., Automotive, Retail, Tech, Pharma).
    3. 'decision_type': Categorize the move (e.g., M&A, Pricing Change, Product Launch, Cultural Shift).
    4. 'logic_used': The reasoning the company had at the time (the 'Why' behind the mistake).
    5. 'failure_reason': The fundamental reason it didn't work (The 'Why' it broke).
    6. 'red_flags': A list of 3-5 subtle signs that indicated this would fail early on.
    7. 'scale': Estimate the impact (e.g., 'Minor', 'Moderate', 'Catastrophic', 'Existential').

    Text Content:
    {$text}

    Return ONLY the JSON object. Do not include markdown code blocks.";

    $payload = [
        "contents" => [["parts" => [["text" => $prompt]]]],
        "generationConfig" => ["responseMimeType" => "application/json"]
    ];

    // Implement Exponential Backoff (as per platform rules)
    $delays = [1, 2, 4, 8, 16];
    foreach ($delays as $delay) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $res = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($status === 200) {
            $data = json_decode($res, true);
            $rawJson = $data['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
            return json_decode($rawJson, true);
        }
        sleep($delay);
    }
    return null;
}

// --- MAIN EXECUTION ---
echo "[Scout] Initializing strategic discovery...\n";
$hits = discoverLinks();
$pdo = getDbConnection();
$ingestedCount = 0;

if (empty($hits)) {
    echo "[Scout] No new public failure signals identified in the last 24h cycle.\n";
    exit;
}

foreach ($hits as $hit) {
    $sourceUrl = $hit['url'] ?? "https://news.ycombinator.com/item?id={$hit['objectID']}";
    
    // Deduplication
    $check = $pdo->prepare("SELECT id FROM external_startup_failures WHERE source_url = ?");
    $check->execute([$sourceUrl]);
    if ($check->fetch()) continue;

    echo "[Scout] Analyzing: " . ($hit['title'] ?? $sourceUrl) . "\n";
    
    $rawText = fetchUrlContent($sourceUrl);
    if (!$rawText || strlen($rawText) < 500) {
        echo "[Scout] Insufficient content found at source. Skipping.\n";
        continue;
    }

    $analysis = analyzeWithAI($rawText);
    
    if ($analysis && isset($analysis['company_name'])) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO external_startup_failures 
                (source_url, company_name, industry, decision_type, logic_used, failure_reason, red_flags)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $sourceUrl,
                $analysis['company_name'],
                $analysis['industry'],
                $analysis['decision_type'],
                $analysis['logic_used'],
                $analysis['failure_reason'],
                json_encode($analysis['red_flags'])
            ]);
            $ingestedCount++;
            echo "[Scout] Pattern Secured: " . $analysis['company_name'] . " (" . $analysis['decision_type'] . ")\n";
        } catch (Exception $e) {
            echo "[Error] Database write failed: " . $e->getMessage() . "\n";
        }
    }
}

echo "[Scout] Cycle complete. Ingested {$ingestedCount} new strategic patterns into the Moat.\n";
