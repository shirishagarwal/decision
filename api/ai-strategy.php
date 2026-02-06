<?php
/**
 * File Path: api/ai-strategy.php
 * Description: The Core Intelligence Brain. Now supports:
 * - Risk Quantification (Dollar Values)
 * - Counterfactual Analysis
 * - Pattern Recognition (Real-world failures)
 * - Industry Benchmarking
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
    jsonResponse(['success' => false, 'error' => 'Title is required.'], 400);
}

$pdo = getDbConnection();
$orgId = $_SESSION['current_org_id'];

// 1. Fetch Real-World Failure Patterns for Similarity Matching
// We use the 'Strategic Scout' data to warn the user.
$stmt = $pdo->prepare("SELECT company_name, decision_type, failure_reason FROM external_startup_failures LIMIT 5");
$stmt->execute();
$externalPatterns = $stmt->fetchAll();

// 2. The "Killer Feature" Prompt
$prompt = "You are a 'Chief Strategy Officer' for an elite venture-backed organization.
Title: '{$title}'
Context: '{$problem}'
Organization Data: " . json_encode($contextData) . "
Active Data Streams: " . json_encode($activeConnectors) . "
Historical Failure Library: " . json_encode($externalPatterns) . "

TASK: 
1. IDENTIFY 'CONTEXT_GAPS': What metrics are missing for a 95% confidence score?
2. GENERATE 3 'STRATEGIC_OPTIONS'. For each option, include:
   - 'name' and 'description'
   - 'confidence_interval': (e.g., '82% - 88%')
   - 'expected_value': Assign a hypothetical dollar impact based on context (e.g., '+$1.2M MRR opportunity')
   - 'risk_score': (1-10)
   - 'pattern_match': Mention a real-world company (from the library or your knowledge) that used this logic.
3. PROVIDE A 'COUNTERFACTUAL_ANALYSIS': If the user does NOTHING (Status Quo), what is the quantified decay of their current position over 12 months?
4. INDUSTRY BENCHMARK: 'X% of [Industry] companies in similar positions chose this path'.

RETURN ONLY RAW JSON.";

$apiKey = GEMINI_API_KEY;
$url = "https://generativelanguage.googleapis.com/v1beta/models/" . GEMINI_MODEL . ":generateContent?key=" . $apiKey;

$payload = [
    "contents" => [["parts" => [["text" => $prompt]]]],
    "generationConfig" => [
        "responseMimeType" => "application/json",
        "temperature" => 0.2 // Lower temp for strategic precision
    ]
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
