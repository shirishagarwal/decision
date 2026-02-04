<?php
// api/proactive-intel.php - Aggressive Version
require_once __DIR__ . '/../config.php';
requireLogin();

header('Content-Type: application/json');

$title = $_GET['q'] ?? '';
$problem = $_GET['p'] ?? '';
$user = getCurrentUser();
$pdo = getDbConnection();

// 1. Broad Search for Failure Patterns
// We use a broad LIKE to catch even loose industry similarities
$stmt = $pdo->prepare("
    SELECT company_name, failure_reason, decision_type 
    FROM external_startup_failures 
    WHERE industry = (SELECT industry FROM organizations WHERE id = ?) 
    OR failure_reason LIKE ? OR decision_type LIKE ?
    LIMIT 4
");
$stmt->execute([
    $_SESSION['current_org_id'],
    '%' . $title . '%',
    '%' . $title . '%'
]);
$failureRisks = $stmt->fetchAll();

// 2. Identify "Internal Blind Spots"
// Find past decisions the user made that turned out "Worse" or "Much Worse"
$stmt = $pdo->prepare("
    SELECT title, actual_outcome FROM decisions 
    WHERE created_by = ? AND review_rating IN ('worse', 'much_worse')
    AND (title LIKE ? OR category = (
        SELECT category FROM decisions WHERE title = ? LIMIT 1
    ))
    LIMIT 2
");
$stmt->execute([$user['id'], '%' . $title . '%', $title]);
$internalBlindSpots = $stmt->fetchAll();

// 3. The "Aggressive AI Summary"
// This provides a one-sentence "Brutally Honest" warning
$warning = "Warning: You are entering a high-failure zone.";
if (count($failureRisks) > 0) {
    $warning = "Warning: Similar strategies killed " . $failureRisks[0]['company_name'] . " because of " . $failureRisks[0]['failure_reason'];
}

echo json_encode([
    'aggressive_warning' => $warning,
    'external_risks' => $failureRisks,
    'internal_mistakes' => $internalBlindSpots,
    'risk_level' => count($failureRisks) > 1 ? 'CRITICAL' : 'ELEVATED'
]);
