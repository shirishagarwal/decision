<?php
/**
 * File Path: cron/scout_failures.php
 * Description: High-Velocity Strategic Scout v4.1.
 * Fixes:
 * - Implemented mandatory exponential backoff for Gemini API calls.
 * - Added robust error handling to prevent 503 Gateway Timeouts.
 * - Optimized memory management inside high-volume loops.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(0);
ignore_user_abort(true);

require_once __DIR__ . '/../config.php';

/**
 * Robust HTTP client to bypass bot-detection
 */
function fetchUrlContent($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20); // Reduced timeout to prevent hanging
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) DecisionVaultIntelligence/4.1');
    $content = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ($httpCode === 200) ? $content : null;
}

/**
 * Advanced AI Strategic Synthesis with Exponential Backoff
 */
function analyzeWithGemini($text) {
    if (empty($text) || strlen($text) < 800) return null;

    $apiKey = GEMINI_API_KEY;
    $url = "https://generativelanguage.googleapis.com/v1beta/models/" . GEMINI_MODEL . ":generateContent?key=" . $apiKey;

    $cleanText = mb_strimwidth(strip_tags($text), 0, 12000);

    $prompt = "Act as an Enterprise Forensic Strategist. Analyze the following narrative regarding a business event (failure, inefficiency, or missed opportunity).
    Extract the intelligence into a RAW JSON object with these exact keys:
    'company_name', 
    'industry', 
    'intelligence_type' (Select one: 'Total Collapse', 'Operational Inefficiency', 'Strategic Blunder', 'Opportunity Cost', 'Market Displacement'),
    'decision_type' (e.g. 'Failed ERP Implementation', 'Delayed Market Entry', 'Pricing Miscalculation', 'M&A Blunder'), 
    'logic_used' (the original executive rationale), 
    'failure_reason' (clinical root cause),
    'mitigation_strategy' (prevention playbook),
    'estimated_capital_waste' (e.g. '$10M+', 'Unknown'),
    'estimated_time_loss' (e.g. '24 Months', '3 Quarters'),
    'tags' (comma separated keywords),
    'strategic_severity' (1-10 scale).

    TEXT:
    {$cleanText}

    Return ONLY raw JSON. No markdown formatting.";

    $payload = [
        "contents" => [["parts" => [["text" => $prompt]]]],
        "generationConfig" => ["responseMimeType" => "application/json", "temperature" => 0.1]
    ];

    // Implementation of mandatory exponential backoff
    $maxRetries = 5;
    $retryDelay = 1; // Seconds

    for ($i = 0; $i < $maxRetries; $i++) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $data = json_decode($res, true);
            $rawResponse = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
            if (!$rawResponse) return null;
            $jsonString = preg_replace('/^```json\s*|```$/m', '', $rawResponse);
            return json_decode(trim($jsonString), true);
        }

        // Wait before retrying (1s, 2s, 4s, 8s, 16s)
        sleep($retryDelay);
        $retryDelay *= 2;
    }

    return null; // All retries failed
}

function runScout() {
    $pdo = getDbConnection();
    echo "\n[SYSTEM] INITIALIZING: High-Velocity Strategic Scout v4.1\n";
    echo "[INFO] Running with Exponential Backoff and Memory Protection.\n";

    $queries = [
        'failed enterprise software implementation story',
        'ERP migration failure case study',
        'why the digital transformation failed',
        'cloud migration budget overrun',
        'technical debt bankruptcy analysis',
        'redundant software stack waste',
        'merger failure post-mortem',
        'failed acquisition rationale',
        'strategic pivot disaster',
        'missed market signal retrospective',
        'why [Company] failed to innovate',
        'over-leveraged buyouts gone wrong',
        'product launch failure analysis',
        'why we killed the feature retrospective',
        'pricing model change churn case study',
        'international expansion blunder story',
        'regional market exit retrospective',
        'bad hire executive failure',
        'governance collapse story',
        'corporate board blunder analysis',
        'startup founder conflict post-mortem',
        'startup post-mortem archive',
        'lessons from business bankruptcy',
        'why my company shut down',
        'strategic error examples',
        'opportunity cost business retrospective'
    ];

    $totalIngested = 0;
    $maxBatchSize = 150; // Process 150 per run if via web to avoid 503, increase for CLI

    foreach ($queries as $q) {
        echo "\n[SEARCH] Query: '$q'\n";
        
        for ($page = 0; $page < 5; $page++) {
            $searchUrl = "https://hn.algolia.com/api/v1/search?query=" . urlencode($q) . "&tags=story&page=$page&hitsPerPage=20";
            $searchRes = fetchUrlContent($searchUrl);
            $data = json_decode($searchRes, true);

            if (empty($data['hits'])) break;

            foreach ($data['hits'] as $hit) {
                if ($totalIngested >= $maxBatchSize) break 3;

                $sourceUrl = $hit['url'] ?? "https://news.ycombinator.com/item?id=" . $hit['objectID'];
                
                $check = $pdo->prepare("SELECT id FROM external_startup_failures WHERE source_url = ?");
                $check->execute([$sourceUrl]);
                if ($check->fetch()) continue;

                echo "      - Analyzing: " . mb_strimwidth($hit['title'], 0, 50) . "...\n";
                $articleHtml = fetchUrlContent($sourceUrl);
                
                if (!$articleHtml || strlen($articleHtml) < 1000) continue;

                $analysis = analyzeWithGemini($articleHtml);

                if ($analysis && !empty($analysis['company_name'])) {
                    try {
                        $stmt = $pdo->prepare("
                            INSERT INTO external_startup_failures 
                            (source_url, company_name, industry, intelligence_type, decision_type, logic_used, failure_reason, mitigation_strategy, estimated_capital_waste, estimated_time_loss, tags, strategic_severity, confidence_score)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                        ");
                        $stmt->execute([
                            $sourceUrl,
                            $analysis['company_name'],
                            $analysis['industry'] ?? 'General',
                            $analysis['intelligence_type'] ?? 'Operational Inefficiency',
                            $analysis['decision_type'] ?? 'Strategic',
                            $analysis['logic_used'] ?? 'Undocumented',
                            $analysis['failure_reason'] ?? 'Unspecified',
                            $analysis['mitigation_strategy'] ?? null,
                            $analysis['estimated_capital_waste'] ?? 'Unknown',
                            $analysis['estimated_time_loss'] ?? 'Unknown',
                            $analysis['tags'] ?? '',
                            $analysis['strategic_severity'] ?? 5,
                            90
                        ]);
                        $totalIngested++;
                        echo "      [+] Secured: " . $analysis['company_name'] . " (Total: $totalIngested)\n";
                    } catch (Exception $e) {
                        echo "      [!] DB Error: " . $e->getMessage() . "\n";
                    }
                }
                
                // Clear memory
                unset($articleHtml, $analysis);
                usleep(300000); // 0.3s polite delay
            }
        }
    }

    echo "\n[SUCCESS] Scout sequence complete. $totalIngested high-density patterns secured.\n\n";
}

runScout();
