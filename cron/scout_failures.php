<?php
/**
 * File Path: cron/scout_failures.php
 * Description: High-reliability Intelligence Scout.
 * Features: cURL-based scraping, AI pattern synthesis, and robust error handling.
 */

// Enable error reporting for terminal debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(0); // Allow the script to run indefinitely for deep scraping

require_once __DIR__ . '/../config.php';

/**
 * Robust HTTP client to bypass bot-detection
 */
function fetchUrlContent($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) DecisionVaultIntelligence/1.0');
    $content = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ($httpCode === 200) ? $content : null;
}

/**
 * Cleans and extracts strategic logic using Gemini
 */
function analyzeWithGemini($text) {
    if (empty($text)) return null;

    $apiKey = GEMINI_API_KEY;
    $url = "https://generativelanguage.googleapis.com/v1beta/models/" . GEMINI_MODEL . ":generateContent?key=" . $apiKey;

    // Limit text to ~8k characters to prevent payload issues
    $cleanText = mb_strimwidth(strip_tags($text), 0, 8000);

    $prompt = "Act as a Forensic Strategy Consultant. Analyze this business failure story.
    Extract the following into a RAW JSON object:
    'company_name', 'industry', 'decision_type' (e.g. Hiring, Expansion, Pricing), 
    'logic_used' (their original rationale), 
    'failure_reason' (root cause of collapse),
    'mitigation_strategy' (what specific fail-safe would have saved them).

    TEXT:
    {$cleanText}

    Return ONLY raw JSON. No markdown blocks.";

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
    curl_close($ch);

    $data = json_decode($res, true);
    $rawResponse = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
    
    if (!$rawResponse) return null;

    // Clean any markdown formatting if the AI ignores the 'raw' instruction
    $jsonString = preg_replace('/^```json\s*|```$/m', '', $rawResponse);
    return json_decode(trim($jsonString), true);
}

function runScout() {
    $pdo = getDbConnection();
    echo "\n[1/4] INITIALIZING: Strategic Intelligence Scout v2.5\n";

    $queries = [
        'business failure analysis',
        'startup post-mortem',
        'failed strategic expansion',
        'corporate bankruptcy lessons'
    ];

    $totalIngested = 0;

    foreach ($queries as $q) {
        echo "[2/4] SEARCHING: Querying global signals for '$q'...\n";
        
        $searchUrl = "https://hn.algolia.com/api/v1/search?query=" . urlencode($q) . "&tags=story&hitsPerPage=10";
        $searchRes = fetchUrlContent($searchUrl);
        $data = json_decode($searchRes, true);

        if (empty($data['hits'])) {
            echo "      ! No signals found for this query.\n";
            continue;
        }

        foreach ($data['hits'] as $hit) {
            $sourceUrl = $hit['url'] ?? "https://news.ycombinator.com/item?id=" . $hit['objectID'];
            
            // Check for existing records
            $check = $pdo->prepare("SELECT id FROM external_startup_failures WHERE source_url = ?");
            $check->execute([$sourceUrl]);
            if ($check->fetch()) {
                echo "      - Skipping existing pattern: " . $hit['title'] . "\n";
                continue;
            }

            echo "[3/4] EXTRACTING: " . mb_strimwidth($hit['title'], 0, 50) . "...\n";
            $articleHtml = fetchUrlContent($sourceUrl);
            
            if (!$articleHtml) {
                echo "      ! Failed to retrieve article content.\n";
                continue;
            }

            echo "[4/4] ANALYZING: Reverse-engineering strategic failure via AI...\n";
            $analysis = analyzeWithGemini($articleHtml);

            if ($analysis && !empty($analysis['company_name'])) {
                try {
                    $stmt = $pdo->prepare("
                        INSERT INTO external_startup_failures 
                        (company_name, industry, decision_type, logic_used, failure_reason, source_url)
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $analysis['company_name'],
                        $analysis['industry'] ?? 'General',
                        $analysis['decision_type'] ?? 'Strategic',
                        $analysis['logic_used'] ?? 'Undocumented',
                        $analysis['failure_reason'] ?? 'Unspecified',
                        $sourceUrl
                    ]);
                    $totalIngested++;
                    echo "      + Pattern Secured: " . $analysis['company_name'] . "\n";
                } catch (Exception $e) {
                    echo "      ! Database Write Error: " . $e->getMessage() . "\n";
                }
            } else {
                echo "      ! Analysis returned insufficient logical data.\n";
            }
        }
    }

    echo "\n[SUCCESS] Scout sequence complete. $totalIngested new strategic patterns architected.\n\n";
}

// Run the script
runScout();
