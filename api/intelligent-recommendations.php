<?php
/**
 * Intelligent Recommendations API
 * 
 * Endpoint: POST /api/intelligent-recommendations.php
 * 
 * Returns AI-generated option suggestions based on:
 * - External startup failure data
 * - Industry benchmarks
 * - User's own decision history
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/IntelligentDecisionEngine.php';

header('Content-Type: application/json');

// Verify authentication
$user = authenticateRequest();
if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get request data
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['decision'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing decision data']);
    exit;
}

try {
    // Initialize intelligent engine
    $engine = new IntelligentDecisionEngine($user['organization_id']);
    
    // Generate intelligent recommendations
    $recommendation = $engine->generateIntelligentRecommendation($input['decision']);
    
    // Return success
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'recommendation' => $recommendation,
        'generated_at' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    error_log("Intelligent recommendation error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to generate recommendation',
        'message' => $e->getMessage()
    ]);
}