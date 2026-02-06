<?php
/**
 * File Path: cron/scout_failures.php
 * Description: High-reliability scraper that populates the Strategic Intelligence library.
 */

require_once __DIR__ . '/../config.php';

function scout() {
    $pdo = getDbConnection();
    echo "[Scout] Initializing Professional Intelligence Gathering...\n";

    // 1. Broad Search queries for Algolia (HN)
    $queries = [
        'business failure case study',
        'strategic blunder analysis',
        'failed acquisition post mortem',
        'CEO mistake lessons',
        'why [Company] failed',
        'corporate bankruptcy post-mortem'
    ];

    $ingested = 0;

    foreach ($queries as $q) {
        $url = "https://hn.algolia.com/api/v1/search?query=" . urlencode($q) . "&tags=story&hitsPerPage=20";
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($ch);
        $data = json_decode($res, true);
        curl_close($ch);

        if (empty($data['hits'])) continue;

        foreach ($data['hits'] as $hit) {
            $sourceUrl = $hit['url'] ?? "https://news.ycombinator.com/item?id=" . $hit['objectID'];
            
            // Dedupe
            $check = $pdo->prepare("SELECT id FROM external_startup_failures WHERE source_url = ?");
            $check->execute([$sourceUrl]);
            if ($check->fetch()) continue;

            echo "[Scout] Extracting from: " . $hit['title'] . "\n";
            
            // Fetch content
            $content = @file_get_contents($sourceUrl);
            if (!$content) continue;
            $cleanText = mb_strimwidth(strip_tags($content), 0, 15000);

            // 2. Deep Strategic Analysis via Gemini
            $analysis = analyzeWithAI($cleanText);

            if ($analysis && !empty($analysis['company_name'])) {
                $stmt = $pdo->prepare("
                    INSERT INTO external_startup_failures 
                    (company_name, industry, decision_type, logic_used, failure_reason, mitigation_strategy, source_url, tags)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $analysis['company_name'],
                    $analysis['industry'],
                    $analysis['decision_type'],
                    $analysis['logic_used'],
                    $analysis['failure_reason'],
                    $analysis['mitigation_strategy'],
                    $sourceUrl,
                    $analysis['tags']
                ]);
                $ingested++;
            }
        }
    }
    echo "[Scout] Ingested $ingested real-world strategic patterns.\n";
}

function analyzeWithAI($text) {
    $apiKey = GEMINI_API_KEY;
    $url = "https://generativelanguage.googleapis.com/v1beta/models/" . GEMINI_MODEL . ":generateContent?key=" . $apiKey;

    $prompt = "Analyze this business failure narrative. Extract:
    1. company_name: Entity name.
    2. industry: Sector.
    3. decision_type: Category of blunder (M&A, Pricing, Hiring).
    4. logic_used: Their internal rationale.
    5. failure_reason: Clinical reason for failure.
    6. mitigation_strategy: 2-3 specific actions that would have prevented this.
    7. tags: Comma separated high-level keywords.

    TEXT:
    {$text}

    Return RAW JSON only.";

    $payload = [
        "contents" => [["parts" => [["text" => $prompt]]]],
        "generationConfig" => ["responseMimeType" => "application/json"]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $res = curl_exec($ch);
    $data = json_decode($res, true);
    curl_close($ch);

    return json_decode($data['candidates'][0]['content']['parts'][0]['text'] ?? '{}', true);
}

scout();
