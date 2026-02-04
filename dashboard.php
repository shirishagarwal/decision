<?php
/**
 * File Path: dashboard.php
 * Description: Robust dashboard that fixes the 500 error and auto-recovers user context.
 */
require_once __DIR__ . '/config.php';
requireLogin();

$user = getCurrentUser();
$pdo = getDbConnection();

try {
    // 1. Identify current organization
    $stmt = $pdo->prepare("
        SELECT o.* FROM organizations o 
        JOIN organization_members om ON o.id = om.organization_id 
        WHERE om.user_id = ?
        LIMIT 1
    ");
    $stmt->execute([$user['id']]);
    $org = $stmt->fetch();

    // 2. Auto-fix: Create vault if user is somehow without an org
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

    $_SESSION['current_org_id'] = $org['id'];

    // 3. Fetch Recent Decisions (Matches organization_id in SQL)
    $stmt = $pdo->prepare("SELECT * FROM decisions WHERE organization_id = ? ORDER BY created_at DESC LIMIT 10");
    $stmt->execute([$org['id']]);
    $decisions = $stmt->fetchAll();

} catch (Exception $e) {
    die("Database Error: " . htmlspecialchars($e->getMessage()));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Dashboard | DecisionVault</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap'); body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-50">
    <nav class="bg-white border-b border-gray-200 p-5 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-4">
                <span class="font-black text-2xl tracking-tighter text-gray-900">DECISION<span class="text-indigo-600">VAULT</span></span>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-xs font-bold text-gray-500 bg-gray-100 px-3 py-1 rounded-full"><?php echo htmlspecialchars($org['name']); ?></span>
                <img src="<?php echo $user['avatar_url']; ?>" class="w-10 h-10 rounded-full border">
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto py-12 px-6">
        <div class="flex justify-between items-end mb-12">
            <div>
                <h1 class="text-4xl font-black text-gray-900">Intelligence Hub</h1>
                <p class="text-gray-500">Documenting the strategic path for your organization.</p>
            </div>
            <a href="create-decision.php" class="bg-indigo-600 text-white px-8 py-4 rounded-2xl font-black shadow-xl hover:scale-105 transition-all">
                + New Decision
            </a>
        </div>

        <div class="bg-white rounded-[40px] border border-gray-100 shadow-sm overflow-hidden">
            <div class="p-8 border-b border-gray-50 flex justify-between items-center">
                <h2 class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Strategy Logs</h2>
                <span class="text-[10px] font-black bg-indigo-50 text-indigo-600 px-3 py-1 rounded-full"><?php echo count($decisions); ?> ACTIVE</span>
            </div>
            <div class="divide-y divide-gray-50">
                <?php if (empty($decisions)): ?>
                    <div class="p-20 text-center text-gray-400">Your vault is empty. Create your first decision to start.</div>
                <?php else: ?>
                    <?php foreach($decisions as $d): ?>
                        <a href="decision.php?id=<?php echo $d['id']; ?>" class="block p-8 hover:bg-gray-50 transition">
                            <div class="flex justify-between items-center">
                                <div>
                                    <div class="font-bold text-lg text-gray-900"><?php echo htmlspecialchars($d['title']); ?></div>
                                    <div class="text-[10px] font-black text-gray-300 uppercase mt-1"><?php echo $d['status']; ?> &bull; <?php echo date('M d, Y', strtotime($d['created_at'])); ?></div>
                                </div>
                                <div class="text-indigo-600">→</div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>
