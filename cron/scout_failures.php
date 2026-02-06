<?php
/**
 * File Path: cron/scout_failures.php
 * Description: Advanced High-Density Strategic Scout.
 * Features:
 * - Broadened search for Operational Inefficiency & Opportunity Cost.
 * - Quantified impact extraction (Waste/Time).
 * - Multi-stage search pagination.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(0);

require_once __DIR__ . '/../config.php';

/**
 * Robust HTTP client to bypass bot-detection
 */
function fetchUrlContent($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) DecisionVaultIntelligence/2.0');
    $content = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ($httpCode === 200) ? $content : null;
}

/**
 * Advanced AI Strategic Synthesis
 * Maps failure narratives to the high-density schema.
 */
function analyzeWithGemini($text) {
    if (empty($text)) return null;

    $apiKey = GEMINI_API_KEY;
    $url = "https://generativelanguage.googleapis.com/v1beta/models/" . GEMINI_MODEL . ":generateContent?key=" . $apiKey;

    // Use a larger context window for complex operational stories
    $cleanText = mb_strimwidth(strip_tags($text), 0, 12000);

    $prompt = "Act as an Enterprise Forensic Strategist. Analyze the following narrative regarding a business event (failure, inefficiency, or missed opportunity).
    Extract the intelligence into a RAW JSON object with these exact keys:
    'company_name', 
    'industry', 
    'intelligence_type' (Select one: 'Total Collapse', 'Operational Inefficiency', 'Strategic Blunder', 'Opportunity Cost', 'Market Displacement'),
    'decision_type' (e.g. 'Failed ERP Implementation', 'Delayed Market Entry', 'Pricing Miscalculation'), 
    'logic_used' (the original executive rationale), 
    'failure_reason' (clinical root cause),
    'mitigation_strategy' (prevention playbook),
    'estimated_capital_waste' (e.g. '$10M+', 'Unknown'),
    'estimated_time_loss' (e.g. '24 Months', '3 Quarters'),
    'tags' (comma separated keywords),
    'strategic_severity' (1-10 scale).

    TEXT:
    {$cleanText}

    Return ONLY raw JSON. Do not include markdown formatting or explanations.";

    $payload = [
        "contents" => [["parts" => [["text" => $prompt]]]],
        "generationConfig" => ["responseMimeType" => "application/json", "temperature" => 0.2]
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
    
    // Sanitize JSON response
    $jsonString = preg_replace('/^```json\s*|```$/m', '', $rawResponse);
    return json_decode(trim($jsonString), true);
}

function runScout() {
    $pdo = getDbConnection();
    echo "\n[SYSTEM] INITIALIZING: High-Density Strategic Scout v3.0\n";

    // BROADENED SEARCH HORIZON
    $queries = [
        'failed enterprise software implementation case study',
        'strategic opportunity cost business example',
        'operational inefficiency post-mortem',
        'why [Company] acquisition failed',
        'technical debt bankruptcy story',
        'corporate blunder analysis',
        'missed market signal retrospective'
    ];

    $totalIngested = 0;

    foreach ($queries as $q) {
        echo "[SEARCH] Querying global archives for signals: '$q'...\n";
        
        // Loop through multiple pages for higher yield
        for ($page = 0; $page < 3; $page++) {
            $searchUrl = "https://hn.algolia.com/api/v1/search?query=" . urlencode($q) . "&tags=story&page=$page&hitsPerPage=20";
            $searchRes = fetchUrlContent($searchUrl);
            $data = json_decode($searchRes, true);

            if (empty($data['hits'])) break;

            foreach ($data['hits'] as $hit) {
                $sourceUrl = $hit['url'] ?? "https://news.ycombinator.com/item?id=" . $hit['objectID'];
                
                // Duplicate check
                $check = $pdo->prepare("SELECT id FROM external_startup_failures WHERE source_url = ?");
                $check->execute([$sourceUrl]);
                if ($check->fetch()) continue;

                echo "      - Processing Potential Signal: " . mb_strimwidth($hit['title'], 0, 40) . "...\n";
                $articleHtml = fetchUrlContent($sourceUrl);
                if (!$articleHtml || strlen($articleHtml) < 500) continue; // Skip thin content

                echo "      - Synthesizing Strategic Pattern...\n";
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
                            85 // Static confidence base for Gemini 2.5
                        ]);
                        $totalIngested++;
                        echo "      [+] Pattern Secured: " . $analysis['company_name'] . " (" . $analysis['intelligence_type'] . ")\n";
                    } catch (Exception $e) {
                        echo "      [!] DB Write Failure: " . $e->getMessage() . "\n";
                    }
                }
            }
            // Polite delay between pages
            sleep(1);
        }
    }

    echo "\n[SUCCESS] Scout sequence complete. $totalIngested high-density patterns secured in the library.\n\n";
}

runScout();
