<?php
/**
 * File Path: api/delete-decision.php
 * Description: Securely deletes a strategic log and its associated data.
 */
ob_start();
require_once __DIR__ . '/../config.php';
requireLogin();

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$decisionId = $input['id'] ?? null;

if (!$decisionId) {
    ob_end_clean();
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Decision ID required.']);
    exit;
}

$pdo = getDbConnection();
$orgId = $_SESSION['current_org_id'];

try {
    // Verify ownership before deleting
    $stmt = $pdo->prepare("DELETE FROM decisions WHERE id = ? AND organization_id = ?");
    $stmt->execute([$decisionId, $orgId]);

    if ($stmt->rowCount() === 0) {
        throw new Exception("Decision not found or unauthorized.");
    }

    ob_end_clean();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
