<?php
/**
 * File Path: cron/scout_failures.php
 * Description: Automated daily scout that searches for strategic failures,
 * bad corporate decisions, and business post-mortems across multiple industries.
 */

require_once __DIR__ . '/../config.php';

/**
 * 1. Broad Discovery Engine
 * Searches Hacker News (via Algolia) for failure-related discussions.
 */
function discoverLinks() {
    // Search back 100 days to ensure a deep pool of data
    $timestampLimit = time() - (100 * 24 * 60 * 60);
    
    // Broadened keyword list.
    $queries = [
        'strategic failure',
        'business collapse',
        'failed acquisition',
        'why we failed',
        'startup post mortem',
        'lessons learned failure',
        'bankruptcy liquidation',
        'product failure exit',
        'scaling too fast crisis'
    ];

    $allHits = [];
    foreach ($queries as $q) {
        // Properly encode all parameters to avoid 400 Bad Request errors
        $params = [
            'query' => $q,
            'tags' => '(story,show_hn,ask_hn)',
            'numericFilters' => 'created_at_i>' . $timestampLimit,
            'hitsPerPage' => 50
        ];
        
        $url = "https://hn.algolia.com/api/v1/search?" . http_build_query($params);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_USERAGENT, 'DecisionVaultDiscovery/2.6');
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($status !== 200) {
            echo "[Debug] API Error on query '{$q}': Status {$status}. {$error}\n";
            if ($status === 400) {
                echo "[Debug] Response: " . $response . "\n";
            }
            continue;
        }

        $data = json_decode($response, true);
        if (!empty($data['hits'])) {
            $allHits = array_merge($allHits, $data['hits']);
        }
    }

    // Deduplicate hits by URL or ObjectID
    $uniqueHits = [];
    foreach ($allHits as $hit) {
        $key = !empty($hit['url']) ? $hit['url'] : $hit['objectID'];
        if (!isset($uniqueHits[$key]) || ($hit['num_comments'] ?? 0) > ($uniqueHits[$key]['num_comments'] ?? 0)) {
            $uniqueHits[$key] = $hit;
        }
    }

    // Sort by signal strength (number of comments)
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
    curl_setopt($ch, CURLOPT_USERAGENT, 'DecisionVaultBot/2.5 (Strategic Intelligence Scout)');
    curl_setopt($ch, CURLOPT_TIMEOUT, 25);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Handle SSL certificate issues on some blogs
    
    $content = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($status !== 200 || !$content) return null;

    // Remove noise (scripts, styles, nav, etc.)
    $content = preg_replace('/<(script|style|nav|footer|header|aside)\b[^>]*>(.*?)<\/\1>/is', "", $content);
    
    // Extract text and clean whitespace
    $text = strip_tags($content);
    $text = preg_replace('/\s+/', ' ', $text);
    return mb_strimwidth(trim($text), 0, 20000, "...");
}

/**
 * 3. Strategic Analysis via Gemini
 */
function analyzeWithAI($text) {
    $apiKey = GEMINI_API_KEY;
    $model = GEMINI_MODEL;
    $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

    $prompt = "You are a world-class Business Historian and Strategic Analyst. Analyze the following text detailing a business failure or strategic mistake.
    
    Extract the 'Strategic DNA' into a RAW JSON object:
    1. 'company_name': Name of organization.
    2. 'industry': Broad sector.
    3. 'decision_type': Move type (e.g. Pivot, Expansion, M&A).
    4. 'logic_used': The original 'Good Intentions'.
    5. 'failure_reason': Root cause of collapse.
    6. 'red_flags': 3-5 early indicators.
    7. 'scale': Magnitude (Minor, Moderate, Catastrophic, Existential).

    Text Content:
    {$text}

    Return ONLY the JSON object. No markdown tags.";

    $payload = [
        "contents" => [["parts" => [["text" => $prompt]]]],
        "generationConfig" => ["responseMimeType" => "application/json"]
    ];

    // Exponential Backoff for API reliability
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
        
        if ($status === 429 || $status >= 500) {
            sleep($delay);
            continue;
        }
        break; // Break on other errors (401, 404, etc.)
    }
    return null;
}

// --- MAIN EXECUTION ---
echo "[Scout] Initializing broad strategic discovery (100-day window)...\n";

$hits = discoverLinks();
$pdo = getDbConnection();
$ingestedCount = 0;

if (empty($hits)) {
    echo "[Scout] No failure signals found. Check network connectivity or query strings.\n";
    exit;
}

echo "[Scout] Found " . count($hits) . " potential signals. Beginning extraction...\n";

foreach ($hits as $hit) {
    // Limit processing to prevent timeouts or hitting AI rate limits too hard
    if ($ingestedCount >= 15) break;

    $sourceUrl = !empty($hit['url']) ? $hit['url'] : "https://news.ycombinator.com/item?id={$hit['objectID']}";
    
    // Deduplication check
    $check = $pdo->prepare("SELECT id FROM external_startup_failures WHERE source_url = ?");
    $check->execute([$sourceUrl]);
    if ($check->fetch()) {
        continue;
    }

    echo "[Scout] Analyzing: " . ($hit['title'] ?? $sourceUrl) . " (" . ($hit['num_comments'] ?? 0) . " comments)\n";
    
    $rawText = fetchUrlContent($sourceUrl);
    
    // Fallback: If external content is poor, scrape the HN thread comments for the "community autopsy"
    if (!$rawText || strlen($rawText) < 800) {
        $hnThreadUrl = "https://news.ycombinator.com/item?id={$hit['objectID']}";
        $rawText = fetchUrlContent($hnThreadUrl);
    }

    if (!$rawText || strlen($rawText) < 800) {
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
            echo "[Scout] Pattern Secured: " . $analysis['company_name'] . "\n";
        } catch (Exception $e) {
            echo "[Error] DB Write Error: " . $e->getMessage() . "\n";
        }
    }
}

echo "[Scout] Cycle complete. " . $ingestedCount . " strategic patterns added to the Moat.\n";
