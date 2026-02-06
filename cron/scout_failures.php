<?php
/**
 * File Path: cron/scout_failures.php
 * Description: High-Velocity Strategic Scout v4.0.
 * Features:
 * - Aggressive multi-query horizon (25+ queries).
 * - Deep pagination (up to 10 pages per query).
 * - Targets Operational Waste, M&A blunders, and Technical Debt.
 * - Built for high-volume ingestion (Target: 1,000+ records).
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(0); // Essential for long-running ingestion sessions
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
    curl_setopt($ch, CURLOPT_TIMEOUT, 25);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) DecisionVaultIntelligence/4.0');
    $content = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ($httpCode === 200) ? $content : null;
}

/**
 * Advanced AI Strategic Synthesis
 */
function analyzeWithGemini($text) {
    if (empty($text) || strlen($text) < 500) return null;

    $apiKey = GEMINI_API_KEY;
    $url = "https://generativelanguage.googleapis.com/v1beta/models/" . GEMINI_MODEL . ":generateContent?key=" . $apiKey;

    // Focused context window to extract deep logic from operational stories
    $cleanText = mb_strimwidth(strip_tags($text), 0, 15000);

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

    Return ONLY raw JSON. No markdown.";

    $payload = [
        "contents" => [["parts" => [["text" => $prompt]]]],
        "generationConfig" => ["responseMimeType" => "application/json", "temperature" => 0.1]
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
    echo "\n[SYSTEM] INITIALIZING: High-Velocity Strategic Scout v4.0\n";
    echo "[INFO] Targeting 1,000+ records across global archives.\n";

    // EXPANDED SEARCH HORIZON (Targets failures, inefficiencies, and blunders)
    $queries = [
        // Operational & IT Waste
        'failed enterprise software implementation story',
        'ERP migration failure case study',
        'why the digital transformation failed',
        'cloud migration budget overrun',
        'technical debt bankruptcy analysis',
        'redundant software stack waste',
        
        // Strategic & M&A
        'merger failure post-mortem',
        'failed acquisition rationale',
        'strategic pivot disaster',
        'missed market signal retrospective',
        'why [Company] failed to innovate',
        'over-leveraged buyouts gone wrong',
        
        // Product & Market
        'product launch failure analysis',
        'why we killed the feature retrospective',
        'pricing model change churn case study',
        'international expansion blunder story',
        'regional market exit retrospective',
        
        // Governance & Hiring
        'bad hire executive failure',
        'governance collapse story',
        'corporate board blunder analysis',
        'startup founder conflict post-mortem',
        
        // General Failure Archives
        'startup post-mortem archive',
        'lessons from business bankruptcy',
        'why my company shut down',
        'strategic error examples',
        'opportunity cost business retrospective'
    ];

    $totalIngested = 0;

    foreach ($queries as $q) {
        echo "\n[SEARCH] Signal Query: '$q'\n";
        
        // Deep Pagination: Loop through up to 10 pages per query for higher yield
        for ($page = 0; $page < 10; $page++) {
            echo "      - Paging: Offset $page\n";
            $searchUrl = "https://hn.algolia.com/api/v1/search?query=" . urlencode($q) . "&tags=story&page=$page&hitsPerPage=30";
            $searchRes = fetchUrlContent($searchUrl);
            $data = json_decode($searchRes, true);

            if (empty($data['hits'])) {
                echo "      ! End of signals for this query.\n";
                break;
            }

            foreach ($data['hits'] as $hit) {
                $sourceUrl = $hit['url'] ?? "https://news.ycombinator.com/item?id=" . $hit['objectID'];
                
                // Duplicate check
                $check = $pdo->prepare("SELECT id FROM external_startup_failures WHERE source_url = ?");
                $check->execute([$sourceUrl]);
                if ($check->fetch()) continue;

                // Title check: Skip low-value content (jobs, ask hn without story, etc)
                if (stripos($hit['title'], 'Ask HN:') !== false || stripos($hit['title'], 'Show HN:') !== false) {
                    // Only skip if the link isn't external
                    if (!isset($hit['url'])) continue;
                }

                echo "      - Processing: " . mb_strimwidth($hit['title'], 0, 60) . "...\n";
                $articleHtml = fetchUrlContent($sourceUrl);
                
                // Only process substantial narratives
                if (!$articleHtml || strlen($articleHtml) < 1000) {
                    echo "        ! Content too thin. Skipping.\n";
                    continue;
                }

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
                            90 // High confidence base for v4.0
                        ]);
                        $totalIngested++;
                        echo "      [+] Pattern Secured: " . $analysis['company_name'] . " ($totalIngested total)\n";
                    } catch (Exception $e) {
                        echo "      [!] DB Error: " . $e->getMessage() . "\n";
                    }
                } else {
                    echo "        ! Analysis rejected or insufficient logic found.\n";
                }
                
                // Small delay to keep Gemini API healthy
                usleep(500000); // 0.5s
            }
            
            // Safety break: if we've already secured enough in one session, stop to review
            if ($totalIngested >= 1000) break 2;
        }
    }

    echo "\n[SUCCESS] Scout sequence complete. $totalIngested high-density patterns secured.\n\n";
}

runScout();
