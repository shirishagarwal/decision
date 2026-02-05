<?php
/**
 * File Path: dashboard.php
 * Description: Dashboard with "Session Resync" and global include pattern.
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

    // 2. AUTO-RECOVERY: Ensure org exists
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

    // 3. SESSION RESYNC
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Strategic Hub | DecisionVault</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap'); body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-50 flex flex-col min-h-screen">
    
    <!-- Global Header -->
    <?php include __DIR__ . '/includes/header.php'; ?>

    <main class="max-w-7xl mx-auto py-12 px-6 flex-grow w-full">
        <header class="flex flex-col md:flex-row justify-between items-start md:items-end gap-6 mb-12">
            <div>
                <h1 class="text-4xl font-black text-gray-900 tracking-tight leading-none mb-2">Vault</h1>
                <p class="text-gray-500 font-medium">Strategic intelligence recorded for <?php echo htmlspecialchars($org['name']); ?>.</p>
            </div>
            <a href="create-decision.php" class="bg-indigo-600 text-white px-8 py-4 rounded-2xl font-black text-lg shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition-all active:scale-95 text-center">
                + New Decision
            </a>
        </header>

        <div class="grid lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-8">
                <div class="bg-white rounded-[40px] border border-gray-100 shadow-sm overflow-hidden">
                    <div class="p-8 border-b border-gray-50 font-black text-[10px] text-gray-400 uppercase tracking-widest flex justify-between">
                        <span>Active Strategy Logs</span>
                        <span><?php echo count($decisions); ?> Total</span>
                    </div>
                    <div class="divide-y divide-gray-50">
                        <?php if (empty($decisions)): ?>
                            <div class="p-24 text-center">
                                <div class="text-4xl mb-4">📓</div>
                                <h3 class="text-lg font-bold text-gray-900 mb-2">No decisions recorded</h3>
                                <p class="text-sm text-gray-400 mb-8 max-w-xs mx-auto">Build your strategic moat by documenting your first high-stakes decision.</p>
                                <a href="create-decision.php" class="text-indigo-600 font-bold hover:underline">Start Recording →</a>
                            </div>
                        <?php else: ?>
                            <?php foreach($decisions as $d): ?>
                                <a href="decision.php?id=<?php echo $d['id']; ?>" class="block p-8 hover:bg-gray-50 transition-all group">
                                    <div class="flex justify-between items-center">
                                        <div class="flex-1">
                                            <div class="font-bold text-lg text-gray-900 group-hover:text-indigo-600 transition mb-1"><?php echo htmlspecialchars($d['title']); ?></div>
                                            <div class="flex items-center gap-3">
                                                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest"><?php echo $d['status']; ?></span>
                                                <span class="text-[10px] font-black text-gray-200 uppercase tracking-widest">&bull;</span>
                                                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest"><?php echo date('M d, Y', strtotime($d['created_at'])); ?></span>
                                            </div>
                                        </div>
                                        <div class="text-gray-300 group-hover:text-indigo-600 group-hover:translate-x-1 transition-all">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Dashboard Sidebar -->
            <aside class="space-y-6">
                <div class="bg-indigo-600 rounded-[40px] p-10 text-white shadow-2xl shadow-indigo-100 relative overflow-hidden">
                    <div class="relative z-10">
                        <div class="text-[10px] font-black uppercase tracking-widest opacity-60 mb-3">Strategic Moat IQ</div>
                        <div class="text-6xl font-black mb-6 tracking-tighter">84<span class="text-2xl font-normal opacity-40">/200</span></div>
                        <p class="text-sm text-indigo-100 leading-relaxed font-medium">Your accuracy is in the <strong>Top 12%</strong> for startups in your sector.</p>
                    </div>
                    <div class="absolute -bottom-20 -right-20 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
                </div>

                <div class="bg-white rounded-[40px] p-8 border border-gray-100 shadow-sm">
                    <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-6">Upcoming Reviews</h3>
                    <div class="space-y-4">
                        <p class="text-xs text-gray-500 font-medium italic">No outcomes due for review this week. Decision velocity is stable.</p>
                    </div>
                </div>
            </aside>
        </div>
    </main>

    <!-- Global Footer -->
    <?php include __DIR__ . '/includes/footer.php'; ?>

</body>
</html>
