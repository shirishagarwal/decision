<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/RecommendationEngine.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(['error' => 'Unauthorized'], 401);
}

$user = getCurrentUser();
$orgId = $_SESSION['current_org_id'] ?? null;

if (!$orgId) {
    jsonResponse(['error' => 'Organization ID required'], 400);
}

// GET RECOMMENDATIONS
if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'GET') {
    $decisionData = [];
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $decisionData = json_decode(file_get_contents('php://input'), true);
    } else {
        // GET parameters
        $decisionData = [
            'title' => $_GET['title'] ?? '',
            'problem_statement' => $_GET['problem'] ?? '',
            'category' => $_GET['category'] ?? '',
            'options' => json_decode($_GET['options'] ?? '[]', true)
        ];
    }
    
    try {
        $engine = new RecommendationEngine($orgId, $user['id']);
        $recommendations = $engine->generateRecommendations($decisionData);
        
        jsonResponse([
            'success' => true,
            'recommendations' => $recommendations
        ]);
        
    } catch (Exception $e) {
        error_log("Recommendation engine error: " . $e->getMessage());
        jsonResponse(['error' => 'Failed to generate recommendations'], 500);
    }
}