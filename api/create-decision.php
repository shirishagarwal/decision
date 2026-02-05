<?php
/**
 * File Path: api/create-decision.php
 * Description: Backend for saving decisions. Uses output buffering to prevent malformed JSON.
 */
ob_start(); // Prevent any accidental output/warnings from breaking the JSON
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

try {
    // Verify Organization
    $stmt = $pdo->prepare("
        SELECT o.id FROM organizations o 
        JOIN organization_members om ON o.id = om.organization_id 
        WHERE om.user_id = ? 
        LIMIT 1
    ");
    $stmt->execute([$userId]);
    $org = $stmt->fetch();

    if (!$org) {
        ob_end_clean();
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Organization not found. Refresh dashboard.']);
        exit;
    }

    $orgId = $org['id'];
    $_SESSION['current_org_id'] = $orgId;

    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        INSERT INTO decisions (organization_id, title, problem_statement, created_by, status) 
        VALUES (?, ?, ?, ?, 'Proposed')
    ");
    $stmt->execute([$orgId, $input['title'], $input['problem'] ?? '', $userId]);
    $decisionId = $pdo->lastInsertId();

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
