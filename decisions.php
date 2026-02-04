<?php
/**
 * All Decisions - Working Version
 */

require_once __DIR__ . '/config.php';
requireLogin();

$user = getCurrentUser();
$pdo = getDbConnection();

// Get workspace
$stmt = $pdo->prepare("
    SELECT w.* FROM workspaces w
    LEFT JOIN workspace_members wm ON w.id = wm.workspace_id
    WHERE wm.user_id = ? OR w.owner_id = ?
    LIMIT 1
");
$stmt->execute([$user['id'], $user['id']]);
$workspace = $stmt->fetch();

if (!$workspace) {
    $stmt = $pdo->prepare("INSERT INTO workspaces (name, type, is_default, owner_id, created_at, updated_at) VALUES ('My Workspace', 'personal', 1, ?, NOW(), NOW())");
    $stmt->execute([$user['id']]);
    $workspaceId = $pdo->lastInsertId();
    $stmt = $pdo->prepare("INSERT INTO workspace_members (workspace_id, user_id, role, joined_at) VALUES (?, ?, 'owner', NOW())");
    $stmt->execute([$workspaceId, $user['id']]);
    $stmt = $pdo->prepare("SELECT * FROM workspaces WHERE id = ?");
    $stmt->execute([$workspaceId]);
    $workspace = $stmt->fetch();
}

// Get decisions
$stmt = $pdo->prepare("
    SELECT d.*, u.name as creator_name
    FROM decisions d
    LEFT JOIN users u ON d.created_by = u.id
    WHERE d.workspace_id = ?
    ORDER BY d.created_at DESC
");
$stmt->execute([$workspace['id']]);
$decisions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Decisions | DecisionVault</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50">
    
    <nav class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <a href="/dashboard" class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-gradient-to-br from-purple-600 to-pink-600 rounded-lg flex items-center justify-center">
                        <span class="text-white font-bold text-sm">D</span>
                    </div>
                    <span class="text-xl font-bold text-gray-900">DecisionVault</span>
                </a>
                
                <div class="flex items-center gap-4">
                    <a href="/dashboard" class="text-gray-600 hover:text-gray-900">Dashboard</a>
                    <a href="/create-decision" class="px-4 py-2 bg-purple-600 text-white rounded-lg font-semibold hover:bg-purple-700">New Decision</a>
                    <a href="/logout" class="text-gray-600 hover:text-gray-900">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <div class="mb-8">
            <h1 class="text-3xl font-black text-gray-900">All Decisions</h1>
            <p class="text-gray-600"><?php echo count($decisions); ?> total decisions</p>
        </div>

        <?php if (empty($decisions)): ?>
            <div class="bg-white rounded-xl p-12 text-center">
                <div class="text-6xl mb-4">📋</div>
                <h3 class="text-xl font-bold mb-2">No decisions yet</h3>
                <p class="text-gray-600 mb-6">Create your first decision</p>
                <a href="/create-decision" class="inline-block bg-purple-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-purple-700">Create Decision</a>
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($decisions as $d): ?>
                <a href="/decision/<?php echo $d['id']; ?>" class="block bg-white rounded-xl p-6 hover:shadow-md transition-all border border-gray-200">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="flex items-center gap-2 mb-2">
                                <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-semibold"><?php echo $d['status']; ?></span>
                                <?php if ($d['category']): ?>
                                <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded text-xs"><?php echo $d['category']; ?></span>
                                <?php endif; ?>
                            </div>
                            <h3 class="font-bold text-lg mb-2"><?php echo htmlspecialchars($d['title']); ?></h3>
                            <?php if ($d['description']): ?>
                            <p class="text-sm text-gray-600"><?php echo htmlspecialchars(substr($d['description'], 0, 150)); ?>...</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>
