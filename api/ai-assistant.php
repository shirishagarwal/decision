<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/DecisionAssistant.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(['error' => 'Unauthorized'], 401);
}

$user = getCurrentUser();
$orgId = $_SESSION['current_org_id'] ?? null;

$assistant = new DecisionAssistant($user['id'], $orgId);

// GET WARNINGS for a new decision
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'warnings') {
    $decisionData = [
        'title' => $_GET['title'] ?? '',
        'problem_statement' => $_GET['problem_statement'] ?? '',
        'category' => $_GET['category'] ?? '',
        'deadline' => $_GET['deadline'] ?? null,
        'options' => json_decode($_GET['options'] ?? '[]', true)
    ];
    
    $warnings = $assistant->generateWarnings($decisionData);
    
    jsonResponse([
        'success' => true,
        'warnings' => $warnings
    ]);
}

// GET TIMELINE ANALYSIS
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'timeline') {
    $analysis = $assistant->analyzeTimelineAccuracy();
    
    jsonResponse([
        'success' => true,
        'analysis' => $analysis
    ]);
}

// GET CATEGORY ANALYSIS
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'categories') {
    $analysis = $assistant->analyzeAccuracyByCategory();
    
    jsonResponse([
        'success' => true,
        'analysis' => $analysis
    ]);
}

// GET SIMILAR DECISIONS
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'similar') {
    $title = $_GET['title'] ?? '';
    $problem = $_GET['problem'] ?? '';
    
    $similar = $assistant->findSimilarDecisions($title, $problem, 5);
    
    jsonResponse([
        'success' => true,
        'similar_decisions' => $similar
    ]);
}

// GET LEADERBOARD
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'leaderboard') {
    $leaderboard = $assistant->getDecisionMakerLeaderboard();
    
    jsonResponse([
        'success' => true,
        'leaderboard' => $leaderboard
    ]);
}

// GET ORGANIZATION PATTERNS
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'patterns') {
    $patterns = $assistant->detectOrganizationPatterns();
    
    jsonResponse([
        'success' => true,
        'patterns' => $patterns
    ]);
}

// Default: return overview
jsonResponse([
    'success' => true,
    'available_actions' => [
        'warnings' => 'Get AI warnings for decision data',
        'timeline' => 'Analyze timeline estimation accuracy',
        'categories' => 'Analyze accuracy by category',
        'similar' => 'Find similar past decisions',
        'leaderboard' => 'Get decision maker rankings',
        'patterns' => 'Detect organization patterns'
    ]
]);