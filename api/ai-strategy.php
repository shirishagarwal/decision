<?php
/**
 * File Path: api/ai-strategy.php
 * Description: The Core Intelligence Brain.
 * Hardened to return structured data for high-fidelity UI features.
 */

require_once __DIR__ . '/../config.php';
requireLogin();

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$title = $input['title'] ?? '';
$problem = $input['problem_statement'] ?? '';
$contextData = $input['context_data'] ?? [];
$activeConnectors = $input['active_connectors'] ?? [];
$forceOptions = $input['force_options'] ?? false;

if (empty($title)) {
    echo json_encode(['success' => false, 'error' => 'Title is required.']);
    exit;
}

$pdo = getDbConnection();

// Fetch failure library for context
$stmt = $pdo->prepare("SELECT company_name, decision_type, failure_reason FROM external_startup_failures LIMIT 5");
$stmt->execute();
$externalPatterns = $stmt->fetchAll(PDO::FETCH_ASSOC);

$prompt = "You are a 'Chief Strategy Officer' for an elite venture-backed organization.
Title: '{$title}'
Context: '{$problem}'
Organization Data (provided by user): " . json_encode($contextData) . "
Active Data Connectors: " . json_encode($activeConnectors) . "
Real-World Failures to consider: " . json_encode($externalPatterns) . "

TASK: 
1. IDENTIFY 'CONTEXT_GAPS': What metrics are missing for a 95% confidence score?
2. GENERATE 3 'STRATEGIC_OPTIONS'. For each option, include:
   - 'name' and 'description'
   - 'confidence_interval': (e.g., '82% - 88%')
   - 'expected_value': A hypothetical dollar/impact value (e.g., '+$400k ARR')
   - 'risk_score': (1-10)
   - 'pattern_match': Mention a real-world company/industry example.
3. PROVIDE A 'COUNTERFACTUAL_ANALYSIS': If the user does NOTHING (Status Quo), what is the quantified decay of their current position?
4. INDUSTRY BENCHMARK: 'X% of [Industry] companies in similar positions chose this path'.

YOU MUST RETURN ONLY A RAW JSON OBJECT with keys: 'context_gaps', 'strategic_options', 'counterfactual_analysis', 'industry_benchmark'.
Return only raw JSON text.";

$apiKey = GEMINI_API_KEY;
$url = "https://generativelanguage.googleapis.com/v1beta/models/" . GEMINI_MODEL . ":generateContent?key=" . $apiKey;

$payload = [
    "contents" => [["parts" => [["text" => $prompt]]]],
    "generationConfig" => ["responseMimeType" => "application/json"]
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);
$rawText = $result['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
$aiData = json_decode($rawText, true);

echo json_encode([
    'success' => true,
    'gaps' => $aiData['context_gaps'] ?? [],
    'options' => $aiData['strategic_options'] ?? [],
    'counterfactual' => $aiData['counterfactual_analysis'] ?? null,
    'benchmark' => $aiData['industry_benchmark'] ?? null
]);
