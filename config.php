<?php
/**
 * DecisionVault - Configuration
 * Uses credentials from env.php
 */

// Load environment variables from env.php
require_once __DIR__ . '/env.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================
// ENVIRONMENT CONFIGURATION
// ============================================

// Set to 'production' when live, 'development' for local testing
define('ENVIRONMENT', getenv('ENVIRONMENT') ?: 'production');

// ============================================
// ERROR HANDLING
// ============================================

if (ENVIRONMENT === 'development') {
    // Show all errors in development
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
} else {
    // Hide errors in production, log them instead
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    ini_set('log_errors', 1);
    
    // Create logs directory if it doesn't exist
    $logsDir = __DIR__ . '/logs';
    if (!is_dir($logsDir)) {
        @mkdir($logsDir, 0755, true);
    }
    
    ini_set('error_log', $logsDir . '/php-errors.log');
}

// Custom error handler
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    // Log the error
    $log = sprintf(
        "[%s] Error %d: %s in %s on line %d\n",
        date('Y-m-d H:i:s'),
        $errno,
        $errstr,
        $errfile,
        $errline
    );
    
    @error_log($log, 3, __DIR__ . '/logs/app-errors.log');
    
    // In production, redirect to error page for critical errors
    if (ENVIRONMENT === 'production' && !headers_sent()) {
        if ($errno === E_ERROR || $errno === E_CORE_ERROR || $errno === E_COMPILE_ERROR) {
            header('Location: /error-500');
            exit;
        }
    }
    
    return false;
});

// Exception handler
set_exception_handler(function($exception) {
    // Log the exception
    $log = sprintf(
        "[%s] Exception: %s in %s on line %d\nStack trace:\n%s\n",
        date('Y-m-d H:i:s'),
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine(),
        $exception->getTraceAsString()
    );
    
    @error_log($log, 3, __DIR__ . '/logs/app-errors.log');
    
    // In production, show friendly error page
    if (ENVIRONMENT === 'production' && !headers_sent()) {
        http_response_code(500);
        if (file_exists(__DIR__ . '/error-500.php')) {
            require __DIR__ . '/error-500.php';
        } else {
            echo '<h1>Something went wrong</h1><p>We\'re working on it.</p>';
        }
        exit;
    }
});

// ============================================
// SITE CONFIGURATION
// ============================================

define('SITE_NAME', 'DecisionVault');
define('SITE_URL', getenv('SITE_URL') ?: 'https://offduties.com');
define('SITE_EMAIL', getenv('SITE_EMAIL') ?: 'hello@offduties.com');
define('SUPPORT_EMAIL', getenv('SUPPORT_EMAIL') ?: 'support@offduties.com');

// ============================================
// SECURITY CONFIGURATION
// ============================================

// Session security
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', ENVIRONMENT === 'production' ? 1 : 0);
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.use_strict_mode', 1);

// CSRF Protection
function generateCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// ============================================
// AUTHENTICATION HELPERS
// ============================================

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: /login');
        exit;
    }
}

function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    static $user = null;
    
    if ($user === null) {
        try {
            $pdo = getDbConnection();
            $stmt = $pdo->prepare("
                SELECT id, name, email, created_at, 
                       onboarding_completed, 
                       organization_id
                FROM users 
                WHERE id = ?
            ");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
            
            // If onboarding_completed column doesn't exist, default to false
            if (!isset($user['onboarding_completed'])) {
                $user['onboarding_completed'] = false;
            }
        } catch (Exception $e) {
            error_log('Error fetching current user: ' . $e->getMessage());
            return null;
        }
    }
    
    return $user;
}

function logout() {
    $_SESSION = array();
    
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    session_destroy();
    header('Location: /');
    exit;
}

// ============================================
// UTILITY FUNCTIONS
// ============================================

function sanitizeOutput($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

function formatDate($date) {
    return date('M j, Y', strtotime($date));
}

function formatDateTime($datetime) {
    return date('M j, Y g:i A', strtotime($datetime));
}

function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) {
        return 'just now';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return formatDate($datetime);
    }
}

// ============================================
// DATABASE MIGRATION CHECK
// ============================================

function ensureOnboardingColumn() {
    static $checked = false;
    
    if ($checked) {
        return;
    }
    
    try {
        $pdo = getDbConnection();
        
        // Check if onboarding_completed column exists
        $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'onboarding_completed'");
        if ($stmt->rowCount() === 0) {
            // Add the column
            $pdo->exec("
                ALTER TABLE users 
                ADD COLUMN onboarding_completed BOOLEAN DEFAULT FALSE,
                ADD COLUMN onboarding_completed_at TIMESTAMP NULL
            ");
            error_log('[Migration] Added onboarding_completed column to users table');
        }
        
        $checked = true;
    } catch (Exception $e) {
        error_log('[Migration Error] Could not add onboarding column: ' . $e->getMessage());
    }
}

// Run migration check (only in development or via manual trigger)
if (ENVIRONMENT === 'development') {
    ensureOnboardingColumn();
}


// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

