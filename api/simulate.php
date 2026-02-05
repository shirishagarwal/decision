<?php
/**
 * File Path: api/simulate.php
 * Description: The backend engine for the "Aggressive Stress Test."
 */

require_once __DIR__ . '/../config.php';
requireLogin();

header('Content-Type: application/json');

$decisionId = $_GET['id'] ?? null;
if (!$decisionId) {
    echo json_encode(['error' => 'Decision ID missing.']);
    exit;
}

$pdo = getDbConnection();

// 1. Fetch Decision Data
$stmt = $pdo->prepare("SELECT d.*, GROUP_CONCAT(do.name SEPARATOR ', ') as options_list FROM decisions d LEFT JOIN decision_options do ON d.id = do.decision_id WHERE d.id = ? GROUP BY d.id");
$stmt->execute([$decisionId]);
$decision = $stmt->fetch();

if (!$decision) {
    echo json_encode(['error' => 'Decision not found.']);
    exit;
}

// 2. Prompt Gemini for the Stress Test
$prompt = "Act as a 'Chief Disaster Officer' for a startup. Perform a brutal Pre-Mortem Stress Test.
Decision: '{$decision['title']}'.
Context: '{$decision['problem_statement']}'.
Options Considered: '{$decision['options_list']}'.

ASSUME IT IS 12 MONTHS LATER AND THIS DECISION HAS FAILED COMPLETELY.
Provide a JSON object with these exact keys:
'day30': Subtle early warning signs that were ignored.
'day90': The specific point where drift became a crisis.
'day365': The final autopsy of the collapse.
'mitigation': One specific 'fail-safe' action to implement NOW.

Return ONLY raw JSON text.";

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
$simData = json_decode($rawText, true);

// 3. Store results for persistence
$stmt = $pdo->prepare("
    INSERT INTO decision_simulations (decision_id, day30, day90, day365, mitigation_plan)
    VALUES (?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE day30=VALUES(day30), day90=VALUES(day90), day365=VALUES(day365), mitigation_plan=VALUES(mitigation_plan)
");
$stmt->execute([
    $decisionId,
    $simData['day30'] ?? 'Unknown early flags',
    $simData['day90'] ?? 'Unknown drift point',
    $simData['day365'] ?? 'Catastrophic failure',
    $simData['mitigation'] ?? 'No mitigation found'
]);

echo json_encode($simData);
