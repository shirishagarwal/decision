<?php
/**
 * API: Mark Onboarding as Complete
 * POST /api/complete-onboarding.php
 */

require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

// Must be logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    $userId = $_SESSION['user_id'];
    $pdo = getDbConnection();
    
    // Update user to mark onboarding as complete
    $stmt = $pdo->prepare("
        UPDATE users 
        SET onboarding_completed = TRUE,
            onboarding_completed_at = NOW()
        WHERE id = ?
    ");
    
    $stmt->execute([$userId]);
    
    // Also update session
    $_SESSION['onboarding_completed'] = true;
    
    echo json_encode([
        'success' => true,
        'message' => 'Onboarding completed'
    ]);
    
} catch (Exception $e) {
    error_log("Error completing onboarding: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to complete onboarding',
        'message' => $e->getMessage()
    ]);
}