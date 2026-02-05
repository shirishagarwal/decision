<?php
/**
 * File Path: dashboard.php
 * Description: Dashboard with "Session Resync" to fix post-wipe integrity errors.
 */
require_once __DIR__ . '/config.php';
requireLogin();

$user = getCurrentUser();
$pdo = getDbConnection();

try {
    // 1. Fetch Organization for the current user
    $stmt = $pdo->prepare("
        SELECT o.* FROM organizations o 
        JOIN organization_members om ON o.id = om.organization_id 
        WHERE om.user_id = ?
        LIMIT 1
    ");
    $stmt->execute([$user['id']]);
    $org = $stmt->fetch();

    // 2. AUTO-RECOVERY: If the database was wiped, recreate the user's vault automatically
    if (!$org) {
        $slug = 'vault-' . $user['id'] . '-' . substr(md5(uniqid()), 0, 4);
        $pdo->prepare("INSERT INTO organizations (name, slug, type, owner_id) VALUES (?, ?, 'personal', ?)")
            ->execute([$user['name'] . "'s Vault", $slug, $user['id']]);
        $orgId = $pdo->lastInsertId();
        
        $pdo->prepare("INSERT INTO organization_members (organization_id, user_id, role) VALUES (?, ?, 'owner')")
            ->execute([$orgId, $user['id']]);
        
        $stmt->execute([$user['id']]);
        $org = $stmt->fetch();
    }

    // 3. SESSION RESYNC: Ensure the session always has the correct DB ID
    $_SESSION['current_org_id'] = $org['id'];

    // 4. Fetch Decisions
    $stmt = $pdo->prepare("SELECT * FROM decisions WHERE organization_id = ? ORDER BY created_at DESC");
    $stmt->execute([$org['id']]);
    $decisions = $stmt->fetchAll();

} catch (Exception $e) {
    die("Intelligence Hub Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Strategic Hub | DecisionVault</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap'); body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-50 p-8">
    <div class="max-w-7xl mx-auto">
        <header class="flex justify-between items-center mb-12">
            <div>
                <h1 class="text-4xl font-black text-gray-900">Workspace</h1>
                <p class="text-gray-500">Managing intelligence for <?php echo htmlspecialchars($org['name']); ?></p>
            </div>
            <a href="create-decision.php" class="bg-indigo-600 text-white px-8 py-4 rounded-2xl font-black shadow-xl">
                + New Decision
            </a>
        </header>

        <div class="bg-white rounded-[40px] border border-gray-100 shadow-sm overflow-hidden">
            <div class="p-8 border-b border-gray-50 font-black text-xs text-gray-400 uppercase tracking-widest">
                Strategic Logs
            </div>
            <div class="divide-y divide-gray-50">
                <?php if (empty($decisions)): ?>
                    <div class="p-20 text-center text-gray-400">No decisions recorded yet.</div>
                <?php else: ?>
                    <?php foreach($decisions as $d): ?>
                        <a href="decision.php?id=<?php echo $d['id']; ?>" class="block p-8 hover:bg-gray-50 transition">
                            <div class="font-bold text-lg"><?php echo htmlspecialchars($d['title']); ?></div>
                            <div class="text-xs text-gray-400 mt-1 uppercase font-black tracking-widest"><?php echo $d['status']; ?> &bull; <?php echo date('M d, Y', strtotime($d['created_at'])); ?></div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
