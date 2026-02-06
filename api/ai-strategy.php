<?php
/**
 * File Path: api/ai-strategy.php
 * Updated: Now performs a 'Context Gap Analysis' to identify what data is needed
 * for high-confidence recommendations.
 */

require_once __DIR__ . '/../config.php';
requireLogin();

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$title = $input['title'] ?? '';
$problem = $input['problem_statement'] ?? '';
$currentContext = $input['context_data'] ?? []; // User-provided or connector-provided data

if (empty($title)) {
    echo json_encode(['success' => false, 'error' => 'Title is required.']);
    exit;
}

$pdo = getDbConnection();
$orgId = $_SESSION['current_org_id'];

// 1. Fetch existing Organization Meta to provide baseline to AI
$stmt = $pdo->prepare("SELECT meta_key, meta_value FROM organization_meta WHERE organization_id = ?");
$stmt->execute([$orgId]);
$meta = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// 2. The Context-Aware Prompt
$prompt = "You are a 'Chief Strategy Officer'. 
Current Company State (from connectors): " . json_encode($meta) . "
User Query: '{$title}'
User Context: '{$problem}'
Additional User Data: " . json_encode($currentContext) . "

TASK: 
1. Identify 'CONTEXT_GAPS': What specific metrics or data points are MISSING to give a 90%+ confidence recommendation? (e.g. 'Customer Acquisition Cost', 'Runway in months').
2. Identify 'CONNECTORS': Which external tool would provide this data? (Stripe, HubSpot, LinkedIn, etc.)
3. If enough data exists, provide 2 'STRATEGIC_OPTIONS'.

YOU MUST RETURN ONLY A RAW JSON OBJECT with:
{
  'context_gaps': [{'label': string, 'key': string, 'reason': string, 'suggested_connector': string}],
  'suggested_options': [{'name': string, 'description': string, 'confidence': number}]
}
Return only raw JSON.";

$apiKey = GEMINI_API_KEY;
$url = "https://generativelanguage.googleapis.com/v1beta/models/" . GEMINI_MODEL . ":generateContent?key=" . $apiKey;

$payload = [
    "contents" => [["parts" => [["text" => $prompt]]]],
    "generationConfig" => ["responseMimeType" => "application/json"]
];

// Helper call function with backoff (simplified for brevity)
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
    'options' => $aiData['suggested_options'] ?? []
]);
