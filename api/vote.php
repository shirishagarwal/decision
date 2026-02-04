<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(['error' => 'Unauthorized'], 401);
}

$user = getCurrentUser();
$pdo = getDbConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Cast vote
    $data = json_decode(file_get_contents('php://input'), true);
    
    $decisionId = $data['decision_id'] ?? null;
    $optionId = $data['option_id'] ?? null;
    $reasoning = $data['reasoning'] ?? '';
    
    if (!$decisionId || !$optionId) {
        jsonResponse(['error' => 'Missing required fields'], 400);
    }
    
    // Verify access
    $stmt = $pdo->prepare("
        SELECT d.* FROM decisions d
        INNER JOIN workspace_members wm ON d.workspace_id = wm.workspace_id
        WHERE d.id = ? AND wm.user_id = ?
    ");
    $stmt->execute([$decisionId, $user['id']]);
    $decision = $stmt->fetch();
    
    if (!$decision) {
        jsonResponse(['error' => 'Access denied'], 403);
    }
    
    try {
        // Insert or update vote
        $stmt = $pdo->prepare("
            INSERT INTO decision_votes (decision_id, user_id, option_id, reasoning, voted_at)
            VALUES (?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE option_id = ?, reasoning = ?, voted_at = NOW()
        ");
        $stmt->execute([$decisionId, $user['id'], $optionId, $reasoning, $optionId, $reasoning]);
        
        jsonResponse(['success' => true, 'message' => 'Vote recorded']);
    } catch (Exception $e) {
        error_log("Vote error: " . $e->getMessage());
        jsonResponse(['error' => 'Failed to record vote'], 500);
    }
    
} else {
    // GET: Fetch votes for a decision
    $decisionId = $_GET['decision_id'] ?? null;
    
    if (!$decisionId) {
        jsonResponse(['error' => 'Decision ID required'], 400);
    }
    
    // Get all votes with user info
    $stmt = $pdo->prepare("
        SELECT dv.*, u.name as voter_name, u.avatar_url, o.name as option_name
        FROM decision_votes dv
        INNER JOIN users u ON dv.user_id = u.id
        INNER JOIN options o ON dv.option_id = o.id
        WHERE dv.decision_id = ?
        ORDER BY dv.voted_at DESC
    ");
    $stmt->execute([$decisionId]);
    $votes = $stmt->fetchAll();
    
    // Get vote counts per option
    $stmt = $pdo->prepare("
        SELECT option_id, COUNT(*) as vote_count
        FROM decision_votes
        WHERE decision_id = ?
        GROUP BY option_id
    ");
    $stmt->execute([$decisionId]);
    $voteCounts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    // Get user's vote
    $stmt = $pdo->prepare("SELECT * FROM decision_votes WHERE decision_id = ? AND user_id = ?");
    $stmt->execute([$decisionId, $user['id']]);
    $userVote = $stmt->fetch();
    
    jsonResponse([
        'success' => true,
        'votes' => $votes,
        'vote_counts' => $voteCounts,
        'user_vote' => $userVote
    ]);
}
