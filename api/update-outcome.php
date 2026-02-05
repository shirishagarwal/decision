<?php
/**
 * File Path: api/update-outcome.php
 * Description: Updates a decision with its actual outcome and rating.
 * Part of the "Learning Loop" that feeds the Strategic Moat IQ.
 */

ob_start(); // Prevent accidental warnings or whitespace from breaking JSON output
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
$decisionId = $input['decision_id'] ?? null;
$rating = $input['rating'] ?? null;
$outcome = $input['outcome'] ?? '';

// 1. Validation
$allowedRatings = ['much_worse', 'worse', 'as_expected', 'better', 'much_better'];

if (!$decisionId || !$rating) {
    ob_end_clean();
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Decision ID and Rating are required.']);
    exit;
}

if (!in_array($rating, $allowedRatings)) {
    ob_end_clean();
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid strategic rating provided.']);
    exit;
}

$pdo = getDbConnection();
$userId = $_SESSION['user_id'];
$orgId = $_SESSION['current_org_id'];

try {
    // 2. Security Check: Ensure the decision belongs to the user's active organization
    $checkStmt = $pdo->prepare("SELECT id FROM decisions WHERE id = ? AND organization_id = ?");
    $checkStmt->execute([$decisionId, $orgId]);
    if (!$checkStmt->fetch()) {
        ob_end_clean();
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Unauthorized or decision not found.']);
        exit;
    }

    // 3. Update the Strategic Log
    // This transition from 'Proposed' to 'Implemented' triggers the IQ boost in Intelligence::calculateIQ
    $stmt = $pdo->prepare("
        UPDATE decisions 
        SET status = 'Implemented', 
            review_rating = ?, 
            actual_outcome = ?, 
            review_date = CURRENT_DATE 
        WHERE id = ? AND organization_id = ?
    ");
    
    $stmt->execute([
        $rating,
        $outcome,
        $decisionId,
        $orgId
    ]);

    ob_end_clean();
    echo json_encode([
        'success' => true,
        'message' => 'Strategic loop closed. Moat IQ updated.'
    ]);

} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database failure: ' . $e->getMessage()
    ]);
}
