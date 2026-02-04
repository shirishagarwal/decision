<?php
// Quick Error Checker
// Visit this to see what's causing the 500 error
// Access: https://yourdomain.com/check-error.php

ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Checking Installation...</h1>";
echo "<style>body{font-family:monospace;padding:20px;} .error{color:red;} .success{color:green;}</style>";

// Check 1: Does env.php exist?
echo "<h2>1. Checking for env.php</h2>";
if (file_exists(__DIR__ . '/env.php')) {
    echo "<p class='success'>✅ env.php EXISTS</p>";
    
    // Check 2: Can we include it?
    echo "<h2>2. Loading env.php</h2>";
    try {
        require_once __DIR__ . '/env.php';
        echo "<p class='success'>✅ env.php loaded successfully</p>";
        
        // Check 3: Are constants defined?
        echo "<h2>3. Checking Required Constants</h2>";
        $required = ['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS', 'GOOGLE_CLIENT_ID', 'GOOGLE_CLIENT_SECRET', 'GEMINI_API_KEY', 'APP_URL', 'APP_ENV'];
        
        foreach ($required as $const) {
            if (defined($const)) {
                $value = constant($const);
                // Hide passwords
                if (in_array($const, ['DB_PASS', 'GOOGLE_CLIENT_SECRET', 'GEMINI_API_KEY'])) {
                    $display = str_repeat('*', strlen($value));
                } else {
                    $display = $value;
                }
                echo "<p class='success'>✅ $const = $display</p>";
            } else {
                echo "<p class='error'>❌ $const is NOT defined</p>";
            }
        }
        
        // Check 4: Can we load config.php?
        echo "<h2>4. Loading config.php</h2>";
        require_once __DIR__ . '/config.php';
        echo "<p class='success'>✅ config.php loaded successfully</p>";
        
        // Check 5: Test database connection
        echo "<h2>5. Testing Database Connection</h2>";
        try {
            $pdo = getDbConnection();
            echo "<p class='success'>✅ Database connected!</p>";
        } catch (Exception $e) {
            echo "<p class='error'>❌ Database error: " . $e->getMessage() . "</p>";
        }
        
        echo "<hr><h2>✅ Everything looks good!</h2>";
        echo "<p>If you're seeing this, your installation is working. Try visiting <a href='index.php'>index.php</a></p>";
        
    } catch (Exception $e) {
        echo "<p class='error'>❌ ERROR loading env.php: " . $e->getMessage() . "</p>";
        echo "<p>Line: " . $e->getLine() . "</p>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
    
} else {
    echo "<p class='error'>❌ env.php NOT FOUND</p>";
    echo "<h3>How to fix:</h3>";
    echo "<ol>";
    echo "<li>Create a file named <code>env.php</code> in the same directory as this file</li>";
    echo "<li>Copy the contents from <code>env.example.php</code></li>";
    echo "<li>Edit the values with your actual credentials</li>";
    echo "<li>Upload to your server</li>";
    echo "<li>Refresh this page</li>";
    echo "</ol>";
    
    // Show what directory we're in
    echo "<p>Current directory: <code>" . __DIR__ . "</code></p>";
    echo "<p>Looking for: <code>" . __DIR__ . "/env.php</code></p>";
    
    // List files in directory
    echo "<h3>Files in current directory:</h3>";
    echo "<ul>";
    $files = scandir(__DIR__);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            echo "<li>$file</li>";
        }
    }
    echo "</ul>";
}

echo "<hr>";
echo "<p><strong>Remember to DELETE this file (check-error.php) after fixing the issue!</strong></p>";
?>