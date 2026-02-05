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
 * Updated to use the 'search' endpoint for better relevance and higher hit counts.
 */
function discoverLinks() {
    // Search back 100 days to ensure we have a deep pool of data to draw from
    $timestampLimit = time() - (100 * 24 * 60 * 60);
    
    // Expanded keyword list to catch institutional and corporate strategic blunders
    $queries = [
        'strategic failure OR "business collapse"',
        '"bad decision" OR "failed acquisition" OR "acquisition disaster"',
        '"why we failed" OR "post mortem" OR "lessons learned"',
        '"bankruptcy" OR "liquidation" OR "insolvency"',
        '"product failure" OR "market exit" OR "failed launch"',
        '"CEO mistake" OR "leadership failure" OR "boardroom conflict"',
        '"pivot failed" OR "scaling too fast" OR "burn rate crisis"'
    ];

    $allHits = [];
    foreach ($queries as $q) {
        $encodedQuery = urlencode($q);
        // Using 'search' instead of 'search_by_date' for better ranking of high-quality discussions
        // hitsPerPage=100 to cast a wider net
        $url = "https://hn.algolia.com/api/v1/search?query={$encodedQuery}&tags=story&numericFilters=created_at_i>{$timestampLimit}&hitsPerPage=100";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_USERAGENT, 'DecisionVaultDiscovery/2.0');
        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($status === 200) {
            $data = json_decode($response, true);
            if (!empty($data['hits'])) {
                $allHits = array_merge($allHits, $data['hits']);
            }
        }
    }

    // Deduplicate hits by URL (primary) or ObjectID (secondary)
    $uniqueHits = [];
    foreach ($allHits as $hit) {
        $key = !empty($hit['url']) ? $hit['url'] : $hit['objectID'];
        // Prioritize stories with more comments as they usually have better post-mortem analysis
        if (!isset($uniqueHits[$key]) || $hit['num_comments'] > $uniqueHits[$key]['num_comments']) {
            $uniqueHits[$key] = $hit;
        }
    }

    // Sort by number of comments to process high-signal stories first
    usort($uniqueHits, function($a, $b) {
        return ($b['num_comments'] ?? 0) <=> ($a['num_comments'] ?? 0);
    });

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
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
    $content = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($status !== 200 || !$content) return null;

    // Remove scripts, styles, nav, and footer tags to isolate the core strategic narrative
    $content = preg_replace('/<(script|style|nav|footer|header)\b[^>]*>(.*?)<\/\1>/is', "", $content);
    
    // Extract text and trim to a reasonable size for the AI context window
    $text = strip_tags($content);
    $text = preg_replace('/\s+/', ' ', $text); // Clean up whitespace
    return mb_strimwidth(trim($text), 0, 18000, "...");
}

/**
 * 3. Strategic Analysis via Gemini
 */
function analyzeWithAI($text) {
    $apiKey = GEMINI_API_KEY;
    $model = GEMINI_MODEL;
    $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

    $prompt = "You are a world-class Business Historian and Strategic Analyst. Analyze the following text which details a business failure, corporate blunder, or strategic mistake.
    
    Extract and structure the core 'Strategic DNA' into a RAW JSON object:
    1. 'company_name': The name of the organization.
    2. 'industry': Broad sector (e.g., Tech, Manufacturing, Finance).
    3. 'decision_type': The specific strategic move (e.g., Aggressive Expansion, M&A, Cost-Cutting, Cultural Transformation).
    4. 'logic_used': The original 'Good Intentions' or rationale behind the decision.
    5. 'failure_reason': The root cause of the collapse (e.g., Market Misread, Internal Friction, Technological Debt).
    6. 'red_flags': A list of 3-5 early indicators that suggested this path was compromised.
    7. 'scale': The magnitude of the failure (Minor, Moderate, Catastrophic, Existential).

    Text Content:
    {$text}

    Return ONLY the JSON object. Do not include markdown tags.";

    $payload = [
        "contents" => [["parts" => [["text" => $prompt]]]],
        "generationConfig" => ["responseMimeType" => "application/json"]
    ];

    // Implement Exponential Backoff for API reliability
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
echo "[Scout] Initializing broad strategic discovery (100-day window)...\n";
$hits = discoverLinks();
$pdo = getDbConnection();
$ingestedCount = 0;

if (empty($hits)) {
    echo "[Scout] No failure signals found. Check API connectivity or query parameters.\n";
    exit;
}

echo "[Scout] Found " . count($hits) . " potential signals. Beginning extraction...\n";

foreach ($hits as $hit) {
    // Only process the top 20 high-signal stories per run to avoid rate limits
    if ($ingestedCount >= 20) break;

    $sourceUrl = !empty($hit['url']) ? $hit['url'] : "https://news.ycombinator.com/item?id={$hit['objectID']}";
    
    // Check if we've already secured this pattern
    $check = $pdo->prepare("SELECT id FROM external_startup_failures WHERE source_url = ?");
    $check->execute([$sourceUrl]);
    if ($check->fetch()) {
        continue;
    }

    echo "[Scout] Analyzing: " . ($hit['title'] ?? $sourceUrl) . " (" . ($hit['num_comments'] ?? 0) . " comments)\n";
    
    $rawText = fetchUrlContent($sourceUrl);
    
    // If external content is blocked or empty, try the HN comment thread itself as it often contains the autopsy
    if (!$rawText || strlen($rawText) < 500) {
        $hnThreadUrl = "https://news.ycombinator.com/item?id={$hit['objectID']}";
        $rawText = fetchUrlContent($hnThreadUrl);
    }

    if (!$rawText || strlen($rawText) < 500) {
        echo "[Scout] Skip: Insufficient narrative depth.\n";
        continue;
    }

    $analysis = analyzeWithAI($rawText);
    
    if ($analysis && !empty($analysis['company_name'])) {
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
            echo "[Scout] Pattern Secured: " . $analysis['company_name'] . " [" . $analysis['industry'] . "]\n";
        } catch (Exception $e) {
            echo "[Error] DB Write Error: " . $e->getMessage() . "\n";
        }
    }
}

echo "[Scout] Cycle complete. " . $ingestedCount . " strategic patterns added to the Moat.\n";
