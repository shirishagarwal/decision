<?php
/**
 * DecisionVault - AI Chat Engine
 * Interfaces with Google Gemini to discover problems and generate options.
 */

require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

// 1. Authenticate Request
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$userMessage = $data['message'] ?? '';
$step = $data['step'] ?? 'situation'; // 'situation' or 'options'

if (empty($userMessage)) {
    echo json_encode(['error' => 'No message provided']);
    exit;
}

// 2. Build the Strategic System Prompt
$systemPrompts = [
    'situation' => "You are a world-class strategic consultant known for being brutally honest. 
    Your goal is to tear apart the user's assumptions. Don't be nice.
    Ask 2-3 aggressive questions that challenge the user's logic. 
    Focus on:
    1. Opportunity Cost: What are they NOT doing because of this?
    2. Over-optimism: Why is their timeline/budget likely 50% too low?
    3. Ego: Is this a 'vanity' decision or a survival decision?",
    
    'options' => "Generate 3-4 distinct strategic options. For each, you MUST include a 'Failure Mode' section.
    Tell the user exactly how this specific option will fail based on historical data. 
    Use phrases like: 'This is high-risk because...', 'Most founders fail here because...', or 'The data suggests this is a mistake.'
    Format the final output as a JSON array."
];

$prompt = $systemPrompts[$step];

// 3. Prepare the Gemini API Call
$url = "https://generativelanguage.googleapis.com/v1beta/models/" . GEMINI_MODEL . ":generateContent?key=" . GEMINI_API_KEY;

$payload = [
    "contents" => [
        ["parts" => [["text" => $prompt . "\n\nUser Situation: " . $userMessage]]]
    ],
    "generationConfig" => [
        "temperature" => 0.7,
        "maxOutputTokens" => 1024
    ]
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    echo json_encode(['error' => 'AI Service unavailable', 'details' => json_decode($response)]);
    exit;
}

$result = json_decode($response, true);
$aiText = $result['candidates'][0]['content']['parts'][0]['text'] ?? "I couldn't process that. Try again.";

echo json_encode([
    'success' => true,
    'response' => $aiText,
    'step' => $step
]);
