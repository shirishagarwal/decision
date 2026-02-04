<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(['error' => 'Unauthorized'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

$data = json_decode(file_get_contents('php://input'), true);
$userMessage = $data['message'] ?? '';
$conversationHistory = $data['history'] ?? [];
$step = $data['step'] ?? 'situation';

if (empty($userMessage)) {
    jsonResponse(['error' => 'Message required'], 400);
}

// Build system prompt based on step
$systemPrompts = [
    'situation' => "You are an expert decision-making assistant. Your role is to help users identify the CORE problem they need to solve. Ask clarifying questions to understand:
1. What's the real underlying issue?
2. What are the constraints (budget, time, resources)?
3. Who is affected by this decision?
4. What's the desired outcome?

After understanding the situation, synthesize a clear, specific problem statement with context and constraints. Be conversational and empathetic.",
    
    'options' => "You are an expert at generating comprehensive decision options. Based on the problem provided, generate 4-5 distinct, viable options. For each option provide:
1. Clear name
2. Brief summary
3. Estimated cost (if applicable)
4. 5-7 specific pros
5. 5-7 specific cons
6. Feasibility assessment (Very High, High, Medium, Low)
7. Your AI insight/recommendation

Be specific and practical. Consider creative alternatives the user might not have thought of. Format your response as JSON.",
    
    'analyze' => "You are an expert decision analyst. Help the user compare their selected options and make a final recommendation based on their specific constraints and priorities. Be clear about trade-offs."
];

$systemPrompt = $systemPrompts[$step] ?? $systemPrompts['situation'];

// Build conversation for Gemini
$contents = [];

// Add system instructions in the first user message
$contents[] = [
    'role' => 'user',
    'parts' => [['text' => $systemPrompt]]
];
$contents[] = [
    'role' => 'model',
    'parts' => [['text' => 'I understand. I will help you with your decision-making process.']]
];

// Add conversation history
foreach ($conversationHistory as $msg) {
    $contents[] = [
        'role' => $msg['role'] === 'user' ? 'user' : 'model',
        'parts' => [['text' => $msg['content']]]
    ];
}

// Add current user message
$contents[] = [
    'role' => 'user',
    'parts' => [['text' => $userMessage]]
];

// Call Gemini API
$geminiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/' . GEMINI_MODEL . ':generateContent?key=' . GEMINI_API_KEY;

$requestBody = [
    'contents' => $contents,
    'generationConfig' => [
        'temperature' => 0.7,
        'topK' => 40,
        'topP' => 0.95,
        'maxOutputTokens' => 4096,
    ]
];

$ch = curl_init($geminiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestBody));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    $errorDetails = json_decode($response, true);
    error_log("Gemini API error (HTTP $httpCode): " . $response);
    
    // Return more helpful error message
    $errorMsg = 'AI service error';
    if (isset($errorDetails['error']['message'])) {
        $errorMsg = $errorDetails['error']['message'];
    }
    
    jsonResponse([
        'error' => $errorMsg,
        'details' => 'HTTP ' . $httpCode,
        'hint' => $httpCode === 400 ? 'Check API key and model name' : 
                  ($httpCode === 403 ? 'API key invalid or quota exceeded' : 
                  ($httpCode === 429 ? 'Rate limit exceeded' : 'Service unavailable'))
    ], 500);
}

$geminiResponse = json_decode($response, true);

if (!isset($geminiResponse['candidates'][0]['content']['parts'][0]['text'])) {
    jsonResponse(['error' => 'Invalid response from AI service'], 500);
}

$aiMessage = $geminiResponse['candidates'][0]['content']['parts'][0]['text'];

// If generating options, try to parse JSON
$parsedOptions = null;
if ($step === 'options' && strpos($aiMessage, '{') !== false) {
    // Try to extract JSON from the response
    preg_match('/\{[\s\S]*\}|\[[\s\S]*\]/', $aiMessage, $matches);
    if (!empty($matches)) {
        $parsedOptions = json_decode($matches[0], true);
    }
}

jsonResponse([
    'success' => true,
    'message' => $aiMessage,
    'parsed_options' => $parsedOptions,
    'step' => $step
]);