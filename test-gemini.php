<?php
require_once __DIR__ . '/config.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Gemini API Test</title>
    <style>
        body { font-family: monospace; padding: 20px; max-width: 800px; margin: 0 auto; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        pre { background: #f5f5f5; padding: 10px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>🔍 Gemini API Configuration Test</h1>
    
    <?php
    echo "<h2>1. Checking Configuration</h2>";
    
    // Check if API key is set
    if (!defined('GEMINI_API_KEY')) {
        echo "<p class='error'>❌ GEMINI_API_KEY is not defined in env.php</p>";
        echo "<p>Fix: Add this to env.php: <code>define('GEMINI_API_KEY', 'your-key-here');</code></p>";
        exit;
    }
    
    $apiKey = GEMINI_API_KEY;
    $apiKeyLength = strlen($apiKey);
    $apiKeyPreview = substr($apiKey, 0, 10) . '...' . substr($apiKey, -5);
    
    echo "<p class='success'>✅ GEMINI_API_KEY is set</p>";
    echo "<p class='info'>Key length: $apiKeyLength characters</p>";
    echo "<p class='info'>Key preview: $apiKeyPreview</p>";
    
    if ($apiKeyLength < 20) {
        echo "<p class='error'>⚠️ API key seems too short. Valid keys are usually 39+ characters.</p>";
    }
    
    // Check model
    if (!defined('GEMINI_MODEL')) {
        echo "<p class='error'>❌ GEMINI_MODEL is not defined</p>";
        exit;
    }
    
    echo "<p class='success'>✅ Model: " . GEMINI_MODEL . "</p>";
    
    echo "<h2>2. Testing API Connection</h2>";
    echo "<p>Sending test request to Gemini...</p>";
    
    $geminiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/' . GEMINI_MODEL . ':generateContent?key=' . $apiKey;
    
    $requestBody = [
        'contents' => [
            [
                'role' => 'user',
                'parts' => [['text' => 'Say "Hello, API is working!" and nothing else.']]
            ]
        ],
        'generationConfig' => [
            'temperature' => 0.7,
            'maxOutputTokens' => 100,
        ]
    ];
    
    $ch = curl_init($geminiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestBody));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "<h3>Response Details:</h3>";
    echo "<p><strong>HTTP Status Code:</strong> $httpCode</p>";
    
    if ($httpCode === 200) {
        echo "<p class='success'>✅ API Connection Successful!</p>";
        
        $geminiResponse = json_decode($response, true);
        
        if (isset($geminiResponse['candidates'][0]['content']['parts'][0]['text'])) {
            $aiMessage = $geminiResponse['candidates'][0]['content']['parts'][0]['text'];
            echo "<p class='success'>✅ AI Response: <strong>$aiMessage</strong></p>";
            echo "<h2>✅ Everything is Working!</h2>";
            echo "<p>Your Gemini API is configured correctly and responding.</p>";
        } else {
            echo "<p class='error'>❌ Unexpected response structure</p>";
            echo "<pre>" . htmlspecialchars(json_encode($geminiResponse, JSON_PRETTY_PRINT)) . "</pre>";
        }
    } else {
        echo "<p class='error'>❌ API Request Failed</p>";
        
        $errorData = json_decode($response, true);
        
        if (isset($errorData['error'])) {
            echo "<h3>Error Details:</h3>";
            echo "<p><strong>Message:</strong> " . htmlspecialchars($errorData['error']['message'] ?? 'Unknown') . "</p>";
            echo "<p><strong>Status:</strong> " . htmlspecialchars($errorData['error']['status'] ?? 'Unknown') . "</p>";
            
            echo "<h3>Common Fixes:</h3>";
            
            if ($httpCode === 400) {
                echo "<p class='error'>⚠️ Bad Request - Check your API key and model name</p>";
                echo "<ul>";
                echo "<li>Verify API key is correct (get new one from <a href='https://makersuite.google.com/app/apikey' target='_blank'>Google AI Studio</a>)</li>";
                echo "<li>Check GEMINI_MODEL is correct (should be 'gemini-1.5-flash' or 'gemini-1.5-pro')</li>";
                echo "</ul>";
            } elseif ($httpCode === 403) {
                echo "<p class='error'>⚠️ Forbidden - API key invalid or not authorized</p>";
                echo "<ul>";
                echo "<li>Get a new API key from <a href='https://makersuite.google.com/app/apikey' target='_blank'>Google AI Studio</a></li>";
                echo "<li>Make sure the API key is enabled for Gemini API</li>";
                echo "<li>Check if you have quota remaining</li>";
                echo "</ul>";
            } elseif ($httpCode === 429) {
                echo "<p class='error'>⚠️ Rate Limit Exceeded</p>";
                echo "<ul>";
                echo "<li>You've made too many requests</li>";
                echo "<li>Wait a few minutes and try again</li>";
                echo "<li>Free tier: 15 requests/minute</li>";
                echo "</ul>";
            }
        }
        
        echo "<h3>Raw Response:</h3>";
        echo "<pre>" . htmlspecialchars($response) . "</pre>";
    }
    ?>
    
    <hr>
    <h2>📝 Next Steps:</h2>
    <ol>
        <li>If you see "Everything is Working!" above, your AI generation should work</li>
        <li>If you see errors, follow the fix suggestions above</li>
        <li>After fixing, delete this test file for security</li>
    </ol>
    
    <p><strong>Delete this file after testing:</strong> <code>test-gemini.php</code></p>
</body>
</html>