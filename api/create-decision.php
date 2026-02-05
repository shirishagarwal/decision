<?php
/**
 * File Path: api/create-decision.php
 * Description: Robust backend for saving and updating decisions.
 * Supports both 'create' and 'edit' modes.
 */
ob_start();
require_once __DIR__ . '/../config.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_end_clean();
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || empty($input['title'])) {
    ob_end_clean();
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Title is required.']);
    exit;
}

$pdo = getDbConnection();
$userId = $_SESSION['user_id'];
$orgId = $_SESSION['current_org_id'];
$mode = $input['mode'] ?? 'create';
$decisionId = $input['id'] ?? null;

try {
    $pdo->beginTransaction();

    if ($mode === 'edit' && $decisionId) {
        // 1. UPDATE MODE - Verify ownership first
        $stmt = $pdo->prepare("SELECT id FROM decisions WHERE id = ? AND organization_id = ?");
        $stmt->execute([$decisionId, $orgId]);
        if (!$stmt->fetch()) {
            throw new Exception("Unauthorized or decision not found.");
        }

        // Update main decision record
        $stmt = $pdo->prepare("
            UPDATE decisions 
            SET title = ?, problem_statement = ? 
            WHERE id = ?
        ");
        $stmt->execute([$input['title'], $input['problem'] ?? '', $decisionId]);

        // Refresh options (Delete existing and re-insert new ones to sync)
        $pdo->prepare("DELETE FROM decision_options WHERE decision_id = ?")->execute([$decisionId]);
    } else {
        // 2. CREATE MODE
        $stmt = $pdo->prepare("
            INSERT INTO decisions (organization_id, title, problem_statement, created_by, status) 
            VALUES (?, ?, ?, ?, 'Proposed')
        ");
        $stmt->execute([$orgId, $input['title'], $input['problem'] ?? '', $userId]);
        $decisionId = $pdo->lastInsertId();
    }

    // Insert Options (Shared for both modes)
    if (!empty($input['options']) && is_array($input['options'])) {
        $optStmt = $pdo->prepare("INSERT INTO decision_options (decision_id, name, description, is_ai_suggested) VALUES (?, ?, ?, ?)");
        foreach ($input['options'] as $option) {
            if (!empty($option['name'])) {
                $optStmt->execute([
                    $decisionId,
                    $option['name'],
                    $option['description'] ?? '',
                    (isset($option['isAiGenerated']) && $option['isAiGenerated']) ? 1 : 0
                ]);
            }
        }
    }

    $pdo->commit();
    ob_end_clean();
    echo json_encode(['success' => true, 'decision_id' => $decisionId]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
