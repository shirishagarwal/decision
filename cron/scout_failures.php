<?php
/**
 * File Path: cron/scout_failures.php
 * Description: Automated daily scout that searches Hacker News for new startup post-mortems
 * and ingests them into the Intelligence Moat.
 */

require_once __DIR__ . '/../config.php';

// 1. Search Hacker News for "Post Mortem" or "Shutting Down" in the last 24 hours
function discoverLinks() {
    $yesterday = time() - (24 * 60 * 60);
    $query = urlencode('startup post mortem OR "why we shut down"');
    $url = "https://hn.algolia.com/api/v1/search_by_date?query={$query}&tags=story&numericFilters=created_at_i>{$yesterday}";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);
    return $data['hits'] ?? [];
}

// 2. Extract clean text from a URL (Simple helper)
function fetchUrlContent($url) {
    if (empty($url)) return null;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'DecisionVaultBot/1.0 (Strategic Intelligence Scout)');
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $content = curl_exec($ch);
    curl_close($ch);
    
    // Strip tags and excessive whitespace to save on AI tokens
    return mb_strimwidth(strip_tags($content), 0, 10000, "...");
}

// 3. Process with Gemini (Exponential Backoff implemented)
function analyzeWithAI($text) {
    $apiKey = ""; // Provided by environment at runtime
    $url = "https://generativelanguage.googleapis.com/v1beta/models/" . GEMINI_MODEL . ":generateContent?key=" . $apiKey;

    $prompt = "Analyze this text from a startup failure post-mortem. 
    Extract data in RAW JSON:
    1. 'company_name'
    2. 'industry'
    3. 'decision_type' (e.g. Expansion, Pricing, Pivot)
    4. 'logic_used' (Original rationale)
    5. 'failure_reason' (Why it collapsed)
    6. 'red_flags' (List of early signs)

    Text: {$text}";

    $payload = [
        "contents" => [["parts" => [["text" => $prompt]]]],
        "generationConfig" => ["responseMimeType" => "application/json"]
    ];

    for ($i = 0; $i < 5; $i++) {
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
            return json_decode($data['candidates'][0]['content']['parts'][0]['text'], true);
        }
        sleep(pow(2, $i)); // 1s, 2s, 4s, 8s, 16s
    }
    return null;
}

// MAIN EXECUTION
$hits = discoverLinks();
$pdo = getDbConnection();
$ingestedCount = 0;

foreach ($hits as $hit) {
    $sourceUrl = $hit['url'] ?? "https://news.ycombinator.com/item?id={$hit['objectID']}";
    
    // Deduplication: Don't process the same link twice
    $check = $pdo->prepare("SELECT id FROM external_startup_failures WHERE source_url = ?");
    $check->execute([$sourceUrl]);
    if ($check->fetch()) continue;

    echo "Processing: " . ($hit['title'] ?? $sourceUrl) . "\n";
    
    $rawText = fetchUrlContent($sourceUrl);
    if (!$rawText || strlen($rawText) < 200) continue;

    $analysis = analyzeWithAI($rawText);
    if ($analysis && isset($analysis['company_name'])) {
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
    }
}

echo "Scout complete. Ingested {$ingestedCount} new strategic patterns.\n";
