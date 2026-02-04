<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(['error' => 'Unauthorized'], 401);
}

$user = getCurrentUser();
$pdo = getDbConnection();
$workspaceId = $_GET['workspace_id'] ?? null;

if (!$workspaceId) {
    jsonResponse(['error' => 'Workspace ID required'], 400);
}

// Verify access
$stmt = $pdo->prepare("
    SELECT 1 FROM workspace_members 
    WHERE workspace_id = ? AND user_id = ?
");
$stmt->execute([$workspaceId, $user['id']]);

if (!$stmt->fetch()) {
    jsonResponse(['error' => 'Access denied'], 403);
}

// Get statistics
$stats = [];

// Total decisions
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM decisions WHERE workspace_id = ?");
$stmt->execute([$workspaceId]);
$stats['total'] = $stmt->fetch()['count'];

// Implemented decisions
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM decisions WHERE workspace_id = ? AND status = 'Implemented'");
$stmt->execute([$workspaceId]);
$stats['implemented'] = $stmt->fetch()['count'];

// Average days to decide
$stmt = $pdo->prepare("
    SELECT AVG(DATEDIFF(decided_at, created_at)) as avg_days 
    FROM decisions 
    WHERE workspace_id = ? AND decided_at IS NOT NULL
");
$stmt->execute([$workspaceId]);
$result = $stmt->fetch();
$stats['avgDays'] = round($result['avg_days'] ?? 0, 1);

// Team members count
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM workspace_members WHERE workspace_id = ?");
$stmt->execute([$workspaceId]);
$stats['members'] = $stmt->fetch()['count'];

jsonResponse(['stats' => $stats]);
