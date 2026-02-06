<?php
/**
 * File Path: api/ai-strategy.php
 * Description: The Core Intelligence Brain.
 * Hardened to return structured data for high-fidelity UI features.
 * Supports Stakeholders, Gaps, Options, and Counterfactuals.
 */

require_once __DIR__ . '/../config.php';
requireLogin();

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$title = $input['title'] ?? '';
$problem = $input['problem_statement'] ?? '';
$contextData = $input['context_data'] ?? [];
$activeConnectors = $input['active_connectors'] ?? [];
$stakeholders = $input['stakeholders'] ?? [];
$forceOptions = $input['force_options'] ?? false;

if (empty($title)) {
    echo json_encode(['success' => false, 'error' => 'Title is required.']);
    exit;
}

$pdo = getDbConnection();

// Fetch failure library for pattern matching
$stmt = $pdo->prepare("SELECT company_name, decision_type, failure_reason FROM external_startup_failures LIMIT 10");
$stmt->execute();
$externalPatterns = $stmt->fetchAll(PDO::FETCH_ASSOC);

$prompt = "You are a 'Chief Strategy Officer' for an elite organization. 
A critical decision is being architectures.

PROJECT_TITLE: '{$title}'
CORE_PROBLEM: '{$problem}'
STAKEHOLDERS: " . json_encode($stakeholders) . "
EXISTING_DATA: " . json_encode($contextData) . "
ACTIVE_CONNECTORS: " . json_encode($activeConnectors) . "
FAILURE_BENCHMARKS: " . json_encode($externalPatterns) . "

TASK:
1. IDENTIFY 'context_gaps': List 3-4 data points missing for a 95% confidence score. Include 'label', 'key', 'reason', and 'suggested_connector'.
2. GENERATE 3 'strategic_options': High-fidelity paths. Include:
   - 'name', 'description'
   - 'confidence_interval' (e.g., '85-90%')
   - 'expected_value' (e.g., '+$2M ARR impact')
   - 'risk_score' (1-10)
   - 'pattern_match' (Reference a real company or industry failure pattern)
3. PROVIDE 'counterfactual_analysis': A brutal look at the cost of doing NOTHING (Status Quo).
4. PROVIDE 'industry_benchmark': e.g., '72% of SaaS firms at this stage chose Path A'.

REQUIRED OUTPUT FORMAT:
Return ONLY a raw JSON object with these keys: 'context_gaps', 'strategic_options', 'counterfactual_analysis', 'industry_benchmark'.
If data is low, provide SPECULATIVE options rather than empty arrays.
Do not include markdown code blocks.";

$apiKey = GEMINI_API_KEY;
$url = "https://generativelanguage.googleapis.com/v1beta/models/" . GEMINI_MODEL . ":generateContent?key=" . $apiKey;

$payload = [
    "contents" => [["parts" => [["text" => $prompt]]]],
    "generationConfig" => [
        "responseMimeType" => "application/json",
        "temperature" => 0.4
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

// Clean potential JSON wrappers if they exist
if (is_string($aiData)) {
    $aiData = json_decode($aiData, true);
}

echo json_encode([
    'success' => true,
    'gaps' => $aiData['context_gaps'] ?? [],
    'options' => $aiData['strategic_options'] ?? [],
    'counterfactual' => $aiData['counterfactual_analysis'] ?? 'Limited data for counterfactual analysis.',
    'benchmark' => $aiData['industry_benchmark'] ?? 'Aggregating sectoral data...'
]);
