<?php
/**
 * File Path: api/ai-strategy.php
 * Description: The intelligence engine of DecisionVault.
 * Combines internal failure data with Gemini AI to suggest strategic options.
 */

require_once __DIR__ . '/../config.php';
requireLogin();

header('Content-Type: application/json');

// 1. Parse Input
$input = json_decode(file_get_contents('php://input'), true);
$title = $input['title'] ?? '';
$problem = $input['problem_statement'] ?? '';

if (empty($title)) {
    echo json_encode(['success' => false, 'error' => 'Title is required for analysis.']);
    exit;
}

$pdo = getDbConnection();

// 2. Fetch Internal Risks (The "Moat")
// We look for historical failure patterns that match the user's decision type
$stmt = $pdo->prepare("
    SELECT company_name, industry, failure_reason 
    FROM external_startup_failures 
    WHERE industry = (SELECT industry FROM organizations WHERE id = ? LIMIT 1)
    OR failure_reason LIKE ? 
    OR decision_type LIKE ? 
    LIMIT 3
");
$stmt->execute([$_SESSION['current_org_id'], "%$title%", "%$title%"]);
$internalRisks = $stmt->fetchAll();

// 3. Call Gemini for Strategic Options
$prompt = "You are a 'Chief Strategy Officer' for a high-growth startup. 
Analyze this decision: '{$title}'. 
Context: '{$problem}'.

Generate 3 distinct strategic options. For each option, provide:
1. A concise name.
2. A detailed description of the path and why it works.
3. A 'Failure Mode' (how this specific path typically fails).
4. A confidence score (0-100).

YOU MUST RETURN ONLY A RAW JSON OBJECT with the key 'suggested_options' containing an array of these objects. 
DO NOT include markdown formatting like ```json. Just raw text.";

$apiKey = ""; // Provided by environment
$url = "[https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-preview-09-2025:generateContent?key=](https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-preview-09-2025:generateContent?key=)" . $apiKey;

$payload = [
    "contents" => [[
        "parts" => [["text" => $prompt]]
    ]],
    "generationConfig" => [
        "responseMimeType" => "application/json"
    ]
];

// Helper for Exponential Backoff
function callGemini($url, $payload, $retries = 5) {
    for ($i = 0; $i < $retries; $i++) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) return $response;
        
        // Wait: 1s, 2s, 4s, 8s, 16s
        sleep(pow(2, $i));
    }
    return null;
}

$response = callGemini($url, $payload);
$suggestedOptions = [];

if ($response) {
    $result = json_decode($response, true);
    $rawText = $result['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
    
    // Aggressive cleaning of potential markdown
    $cleanJson = preg_replace('/^.*?({.*}).*?$/s', '$1', trim($rawText));
    $aiData = json_decode($cleanJson, true);
    $suggestedOptions = $aiData['suggested_options'] ?? [];
}

// 4. Return Unified Intelligence
echo json_encode([
    'success' => true,
    'external' => [
        'suggested_options' => $suggestedOptions
    ],
    'internal' => [
        'risks' => $internalRisks
    ]
]);
