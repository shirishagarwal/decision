<?php
/**
 * DecisionVault - Voting API
 * Handles casting and retrieving votes for decision options.
 */

require_once __DIR__ . '/../lib/auth.php';
requireOrgAccess(); //

header('Content-Type: application/json');

$pdo = getDbConnection(); //
$userId = $_SESSION['user_id'];

// 1. Handling POST - Cast or Update a Vote
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $decisionId = $data['decision_id'] ?? null;
    $optionId = $data['option_id'] ?? null;
    $reasoning = $data['reasoning'] ?? '';

    if (!$decisionId || !$optionId) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing decision or option ID']);
        exit;
    }

    try {
        //
        $stmt = $pdo->prepare("
            INSERT INTO decision_votes (decision_id, user_id, option_id, reasoning, voted_at)
            VALUES (?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE option_id = ?, reasoning = ?, voted_at = NOW()
        ");
        $stmt->execute([$decisionId, $userId, $optionId, $reasoning, $optionId, $reasoning]);

        echo json_encode(['success' => true, 'message' => 'Vote recorded']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to record vote']);
    }
    exit;
}

// 2. Handling GET - Fetch Votes for a Decision
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $decisionId = $_GET['decision_id'] ?? null;

    if (!$decisionId) {
        http_response_code(400);
        echo json_encode(['error' => 'Decision ID required']);
        exit;
    }

    // Get counts per option
    $stmt = $pdo->prepare("
        SELECT option_id, COUNT(*) as vote_count
        FROM decision_votes
        WHERE decision_id = ?
        GROUP BY option_id
    ");
    $stmt->execute([$decisionId]);
    $counts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // Get details of who voted for what
    $stmt = $pdo->prepare("
        SELECT v.option_id, u.name as voter_name, u.avatar_url
        FROM decision_votes v
        INNER JOIN users u ON v.user_id = u.id
        WHERE v.decision_id = ?
    ");
    $stmt->execute([$decisionId]);
    $voters = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'vote_counts' => $counts,
        'voters' => $voters
    ]);
    exit;
}
