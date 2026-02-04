<?php
// Installation Test Script
// Visit this BEFORE attempting Google login
// Access: https://yourdomain.com/test-install.php
// DELETE after successful installation!

$errors = [];
$warnings = [];
$success = [];

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>DecisionVault Installation Test</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 40px auto; padding: 20px; }
        h1 { color: #4F46E5; }
        .test { padding: 15px; margin: 10px 0; border-radius: 8px; border-left: 5px solid; }
        .success { background: #D1FAE5; border-color: #10B981; }
        .warning { background: #FEF3C7; border-color: #F59E0B; }
        .error { background: #FEE2E2; border-color: #EF4444; }
        .code { background: #F3F4F6; padding: 10px; border-radius: 4px; font-family: monospace; margin: 10px 0; }
        pre { margin: 0; }
        .status { font-weight: bold; }
    </style>
</head>
<body>
    <h1>🔍 DecisionVault Installation Test</h1>
    <p>This script checks if your installation is configured correctly.</p>
    <hr>
";

// Test 1: PHP Version
echo "<h2>1. PHP Version</h2>";
$phpVersion = phpversion();
if (version_compare($phpVersion, '7.4.0', '>=')) {
    echo "<div class='test success'>✅ PHP version $phpVersion is supported</div>";
    $success[] = "PHP version OK";
} else {
    echo "<div class='test error'>❌ PHP version $phpVersion is too old. Need 7.4+</div>";
    $errors[] = "PHP version too old";
}

// Test 2: Required Extensions
echo "<h2>2. Required PHP Extensions</h2>";
$requiredExtensions = ['pdo', 'pdo_mysql', 'curl', 'json', 'mbstring', 'session'];
foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<div class='test success'>✅ $ext extension loaded</div>";
        $success[] = "$ext OK";
    } else {
        echo "<div class='test error'>❌ $ext extension NOT loaded</div>";
        $errors[] = "$ext missing";
    }
}

// Test 3: Config File
echo "<h2>3. Configuration File</h2>";
if (file_exists(__DIR__ . '/config.php')) {
    echo "<div class='test success'>✅ config.php exists</div>";
    $success[] = "config.php found";
    
    require_once __DIR__ . '/config.php';
    
    // Check if constants are defined
    $requiredConstants = ['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS', 'GOOGLE_CLIENT_ID', 'GOOGLE_CLIENT_SECRET', 'GEMINI_API_KEY', 'APP_URL'];
    foreach ($requiredConstants as $const) {
        if (defined($const) && constant($const) !== 'your_database_name' && constant($const) !== 'your-google-client-id.apps.googleusercontent.com' && constant($const) !== 'your-gemini-api-key-here') {
            echo "<div class='test success'>✅ $const is configured</div>";
            $success[] = "$const configured";
        } else {
            echo "<div class='test error'>❌ $const is NOT configured or still has default value</div>";
            $errors[] = "$const not configured";
        }
    }
} else {
    echo "<div class='test error'>❌ config.php NOT found</div>";
    $errors[] = "config.php missing";
}

// Test 4: Database Connection
echo "<h2>4. Database Connection</h2>";
try {
    $pdo = getDbConnection();
    echo "<div class='test success'>✅ Database connection successful</div>";
    $success[] = "Database connected";
    
    // Check if tables exist
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $requiredTables = ['users', 'workspaces', 'decisions', 'workspace_members', 'options', 'tags'];
    $missingTables = array_diff($requiredTables, $tables);
    
    if (empty($missingTables)) {
        echo "<div class='test success'>✅ All required database tables exist (" . count($tables) . " tables found)</div>";
        $success[] = "Database tables OK";
    } else {
        echo "<div class='test error'>❌ Missing tables: " . implode(', ', $missingTables) . "</div>";
        echo "<div class='code'>Run database.sql in phpMyAdmin to create missing tables</div>";
        $errors[] = "Missing database tables";
    }
} catch (Exception $e) {
    echo "<div class='test error'>❌ Database connection failed: " . htmlspecialchars($e->getMessage()) . "</div>";
    echo "<div class='code'>Check DB_HOST, DB_NAME, DB_USER, and DB_PASS in config.php</div>";
    $errors[] = "Database connection failed";
}

// Test 5: Sessions
echo "<h2>5. Session Functionality</h2>";
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "<div class='test success'>✅ Session started successfully</div>";
    $success[] = "Session started";
    
    echo "<div class='code'>Session ID: " . session_id() . "</div>";
    echo "<div class='code'>Session Save Path: " . session_save_path() . "</div>";
    
    // Test session write
    $_SESSION['test_install'] = time();
    if (isset($_SESSION['test_install'])) {
        echo "<div class='test success'>✅ Session write/read working</div>";
        $success[] = "Session read/write OK";
    } else {
        echo "<div class='test error'>❌ Session write/read failed</div>";
        $errors[] = "Session not working";
    }
    
    // Check if sessions directory exists and is writable
    $sessionsPath = __DIR__ . '/sessions';
    if (file_exists($sessionsPath)) {
        if (is_writable($sessionsPath)) {
            echo "<div class='test success'>✅ Sessions directory exists and is writable</div>";
            $success[] = "Sessions directory OK";
        } else {
            echo "<div class='test warning'>⚠️ Sessions directory exists but is NOT writable</div>";
            echo "<div class='code'>chmod 755 sessions</div>";
            $warnings[] = "Sessions directory not writable";
        }
    } else {
        echo "<div class='test warning'>⚠️ Sessions directory will be created automatically</div>";
    }
} else {
    echo "<div class='test error'>❌ Session NOT started</div>";
    $errors[] = "Session not started";
}

// Test 6: Google OAuth Configuration
echo "<h2>6. Google OAuth Configuration</h2>";
if (defined('GOOGLE_CLIENT_ID') && GOOGLE_CLIENT_ID !== 'your-google-client-id.apps.googleusercontent.com') {
    if (strpos(GOOGLE_CLIENT_ID, '.apps.googleusercontent.com') !== false) {
        echo "<div class='test success'>✅ Google Client ID looks valid</div>";
        $success[] = "Google Client ID OK";
    } else {
        echo "<div class='test warning'>⚠️ Google Client ID may be incorrect (should end with .apps.googleusercontent.com)</div>";
        $warnings[] = "Google Client ID format issue";
    }
} else {
    echo "<div class='test error'>❌ Google Client ID not configured</div>";
    $errors[] = "Google Client ID missing";
}

if (defined('GOOGLE_REDIRECT_URI')) {
    $redirectUri = GOOGLE_REDIRECT_URI;
    if (strpos($redirectUri, 'https://') === 0 && strpos($redirectUri, '/auth/callback.php') !== false) {
        echo "<div class='test success'>✅ Google Redirect URI looks valid</div>";
        echo "<div class='code'>$redirectUri</div>";
        $success[] = "Redirect URI OK";
    } else {
        echo "<div class='test warning'>⚠️ Redirect URI should start with https:// and end with /auth/callback.php</div>";
        echo "<div class='code'>Current: $redirectUri</div>";
        $warnings[] = "Redirect URI format issue";
    }
}

// Test 7: Gemini API
echo "<h2>7. Google Gemini API Configuration</h2>";
if (defined('GEMINI_API_KEY') && GEMINI_API_KEY !== 'your-gemini-api-key-here') {
    echo "<div class='test success'>✅ Gemini API key is configured</div>";
    $success[] = "Gemini API key set";
    
    // Try a test API call (optional)
    if (function_exists('curl_init')) {
        echo "<div class='test success'>✅ cURL is available for API calls</div>";
        $success[] = "cURL available";
    } else {
        echo "<div class='test error'>❌ cURL is NOT available (required for Gemini API)</div>";
        $errors[] = "cURL missing";
    }
} else {
    echo "<div class='test error'>❌ Gemini API key not configured</div>";
    $errors[] = "Gemini API key missing";
}

// Test 8: File Permissions
echo "<h2>8. File Permissions</h2>";
$checkDirs = [
    'sessions' => __DIR__ . '/sessions',
    'logs' => __DIR__ . '/logs'
];

foreach ($checkDirs as $name => $path) {
    if (file_exists($path)) {
        if (is_writable($path)) {
            echo "<div class='test success'>✅ $name directory is writable</div>";
        } else {
            echo "<div class='test warning'>⚠️ $name directory exists but not writable (chmod 755 needed)</div>";
            $warnings[] = "$name not writable";
        }
    } else {
        echo "<div class='test warning'>⚠️ $name directory doesn't exist (will be created automatically)</div>";
    }
}

// Final Summary
echo "<hr><h2>📊 Summary</h2>";
echo "<div class='test success'><strong>✅ Passed:</strong> " . count($success) . " tests</div>";
if (count($warnings) > 0) {
    echo "<div class='test warning'><strong>⚠️ Warnings:</strong> " . count($warnings) . " items need attention</div>";
}
if (count($errors) > 0) {
    echo "<div class='test error'><strong>❌ Errors:</strong> " . count($errors) . " critical issues found</div>";
}

// Next Steps
echo "<hr><h2>🎯 Next Steps</h2>";
if (count($errors) === 0) {
    echo "<div class='test success'>";
    echo "<h3>✅ Installation looks good!</h3>";
    echo "<p>You can now try logging in with Google.</p>";
    echo "<ol>";
    echo "<li>Visit <a href='index.php'>index.php</a></li>";
    echo "<li>Click 'Continue with Google'</li>";
    echo "<li>Complete authorization</li>";
    echo "<li>You should be redirected to the dashboard</li>";
    echo "</ol>";
    echo "<p><strong>⚠️ DELETE this file (test-install.php) after successful login!</strong></p>";
    echo "</div>";
} else {
    echo "<div class='test error'>";
    echo "<h3>❌ Fix these issues first:</h3>";
    echo "<ul>";
    foreach ($errors as $error) {
        echo "<li>$error</li>";
    }
    echo "</ul>";
    echo "<p>Check the detailed results above for solutions.</p>";
    echo "</div>";
}

echo "<hr>";
echo "<p style='text-align: center; color: #6B7280; font-size: 12px;'>DecisionVault Installation Test | " . date('Y-m-d H:i:s') . "</p>";
echo "</body></html>";
?>
