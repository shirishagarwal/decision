<?php
/**
 * File Path: config.php
 * Description: Global application settings and session management.
 */

require_once __DIR__ . '/env.php';
require_once __DIR__ . '/lib/db.php';

// Set session security BEFORE starting session
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', 'Lax');
    session_start();
}

define('APP_NAME', 'DecisionVault');

/**
 * Check if the current user is authenticated.
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Redirect user to landing page if not logged in.
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /index.php');
        exit;
    }
}

/**
 * Fetch full user record from database based on session.
 */
function getCurrentUser() {
    if (!isLoggedIn()) return null;
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

/**
 * Helper to return standardized JSON responses.
 */
function jsonResponse($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
