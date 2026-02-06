<?php
/**
 * File Path: api/ai-strategy.php
 * Description: The Core Intelligence Brain.
 * Hardened to return structured data for high-fidelity UI features.
 * Supports Governance, Risk Gaps, Strategic Options, and Impact Analysis.
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

$prompt = "You are a Senior Strategic Advisor and Chief Strategy Officer for a global enterprise. 
You are architecting a high-stakes executive decision.

PROJECT_TITLE: '{$title}'
EXECUTIVE_SUMMARY: '{$problem}'
GOVERNANCE_STAKEHOLDERS: " . json_encode($stakeholders) . "
CURRENT_ORGANIZATIONAL_DATA: " . json_encode($contextData) . "
INTEGRATED_DATA_STREAMS: " . json_encode($activeConnectors) . "
HISTORICAL_RISK_BENCHMARKS: " . json_encode($externalPatterns) . "

TASK:
1. IDENTIFY 'context_gaps': List 3-4 critical data points required to reach a 95% confidence threshold. Include 'label', 'key', 'reason', and 'suggested_connector'.
2. GENERATE 3 'strategic_options': Professional strategic paths. Include:
   - 'name', 'description'
   - 'confidence_interval' (e.g., '85-90%')
   - 'expected_value' (e.g., 'Estimated ROI: +$2.4M ARR')
   - 'risk_score' (1-10)
   - 'pattern_match' (Reference a relevant industry case study or historical pattern)
3. PROVIDE 'counterfactual_analysis': A rigorous assessment of the risks associated with maintaining the Status Quo (Inaction).
4. PROVIDE 'industry_benchmark': e.g., '72% of organizations in this sector prioritize this path'.

REQUIRED OUTPUT FORMAT:
Return ONLY a raw JSON object with these keys: 'context_gaps', 'strategic_options', 'counterfactual_analysis', 'industry_benchmark'.
Maintain a formal, clinical, and data-driven tone.
Do not include markdown code blocks.";

$apiKey = GEMINI_API_KEY;
$url = "https://generativelanguage.googleapis.com/v1beta/models/" . GEMINI_MODEL . ":generateContent?key=" . $apiKey;

$payload = [
    "contents" => [["parts" => [["text" => $prompt]]]],
    "generationConfig" => [
        "responseMimeType" => "application/json",
        "temperature" => 0.3
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

if (is_string($aiData)) {
    $aiData = json_decode($aiData, true);
}

echo json_encode([
    'success' => true,
    'gaps' => $aiData['context_gaps'] ?? [],
    'options' => $aiData['strategic_options'] ?? [],
    'counterfactual' => $aiData['counterfactual_analysis'] ?? 'Insufficient data for risk-of-inaction analysis.',
    'benchmark' => $aiData['industry_benchmark'] ?? 'Benchmarking sectoral data...'
]);
