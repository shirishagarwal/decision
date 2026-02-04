<?php
// api/ai-strategy.php
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/RecommendationEngine.php';
require_once __DIR__ . '/../lib/IntelligentDecisionEngine.php';

requireOrgAccess(); // cite: lib/auth.php
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$orgId = $_SESSION['current_org_id'];

try {
    // 1. Internal Analysis: "What did we do last time?"
    $internal = new RecommendationEngine($orgId, $_SESSION['user_id']);
    $internalResults = $internal->generateRecommendations($data); // cite: lib/RecommendationEngine.php

    // 2. External Analysis: "What do the 2,000 failure patterns say?"
    $external = new IntelligentDecisionEngine($orgId);
    $externalResults = $external->generateIntelligentRecommendation($data); // cite: lib/IntelligentDecisionEngine.php

    echo json_encode([
        'success' => true,
        'internal' => $internalResults,
        'external' => $externalResults,
        'category' => $data['category'] ?? 'General'
    ]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
