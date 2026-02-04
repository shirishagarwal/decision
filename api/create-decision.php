<?php
/**
 * File Path: api/create-decision.php
 * Description: Processes the strategic decision form submission.
 */
require_once __DIR__ . '/../config.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (empty($input['title'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Decision title is required']);
    exit;
}

$pdo = getDbConnection();
$userId = $_SESSION['user_id'];
$orgId = $_SESSION['current_org_id'];

try {
    $pdo->beginTransaction();

    // 1. Insert Decision
    $stmt = $pdo->prepare("INSERT INTO decisions (organization_id, title, problem_statement, created_by) VALUES (?, ?, ?, ?)");
    $stmt->execute([$orgId, $input['title'], $input['problem'], $userId]);
    $decisionId = $pdo->lastInsertId();

    // 2. Insert Options (if any)
    if (!empty($input['options'])) {
        $stmt = $pdo->prepare("INSERT INTO decision_options (decision_id, name, description) VALUES (?, ?, ?)");
        foreach ($input['options'] as $opt) {
            if (empty($opt['name'])) continue;
            $stmt->execute([$decisionId, $opt['name'], $opt['description']]);
        }
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'decision_id' => $decisionId]);

} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save decision: ' . $e->getMessage()]);
}
