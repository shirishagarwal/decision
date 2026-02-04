<?php
/**
 * File Path: dashboard.php
 * Description: Robust dashboard with auto-recovery for missing organization contexts.
 * Fixes 500 errors by aligning with the latest SQL schema.
 */

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';
requireLogin();

$user = getCurrentUser();
$pdo = getDbConnection();

try {
    // 1. Fetch Primary Organization
    // Note: Removed 'om.status' check to match the init_db.sql provided
    $stmt = $pdo->prepare("
        SELECT o.* FROM organizations o 
        JOIN organization_members om ON o.id = om.organization_id 
        WHERE om.user_id = ?
        LIMIT 1
    ");
    $stmt->execute([$user['id']]);
    $org = $stmt->fetch();

    // 2. Auto-Recovery: Create Personal Vault if none exists
    // This prevents 500 errors for fresh users who haven't run onboarding
    if (!$org) {
        $slug = 'vault-' . $user['id'] . '-' . substr(md5(uniqid()), 0, 4);
        $pdo->prepare("INSERT INTO organizations (name, slug, type, owner_id) VALUES (?, ?, 'personal', ?)")
            ->execute([$user['name'] . "'s Vault", $slug, $user['id']]);
        $orgId = $pdo->lastInsertId();
        
        $pdo->prepare("INSERT INTO organization_members (organization_id, user_id, role) VALUES (?, ?, 'owner')")
            ->execute([$orgId, $user['id']]);
        
        // Refresh organization data
        $stmt->execute([$user['id']]);
        $org = $stmt->fetch();
    }

    $_SESSION['current_org_id'] = $org['id'];

    // 3. Fetch Recent Decisions
    $stmt = $pdo->prepare("SELECT * FROM decisions WHERE organization_id = ? ORDER BY created_at DESC LIMIT 10");
    $stmt->execute([$org['id']]);
    $decisions = $stmt->fetchAll();

} catch (Exception $e) {
    die("<div style='font-family:sans-serif; padding:40px; color:#ef4444; background:#fef2f2; border-radius:12px; margin:20px;'>
            <h1 style='margin-top:0;'>Dashboard Load Error</h1>
            <p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
            <p style='font-size:14px; color:#991b1b;'>Troubleshooting: Ensure you have run 'init_db.sql' in your database manager.</p>
         </div>");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Strategic Dashboard | DecisionVault</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800;900&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #f9fafb; }
    </style>
</head>
<body class="selection:bg-indigo-100">
    <!-- Navbar -->
    <nav class="bg-white border-b border-gray-200 p-5 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-6">
                <span class="font-black text-2xl tracking-tighter text-gray-900">DECISION<span class="text-indigo-600">VAULT</span></span>
                <div class="h-6 w-px bg-gray-200 hidden md:block"></div>
                <div class="hidden md:flex items-center gap-2 bg-gray-50 px-3 py-1.5 rounded-xl border border-gray-100">
                    <div class="w-6 h-6 bg-indigo-600 rounded-md flex items-center justify-center text-white font-bold text-[10px] uppercase">
                        <?php echo substr($org['name'], 0, 1); ?>
                    </div>
                    <span class="font-bold text-xs text-gray-700"><?php echo htmlspecialchars($org['name']); ?></span>
                </div>
            </div>
            <div class="flex items-center gap-5">
                <a href="/organization-settings.php" class="text-[10px] font-black text-gray-400 uppercase tracking-widest hover:text-indigo-600 transition">Settings</a>
                <div class="flex items-center gap-3">
                    <span class="hidden sm:block text-xs font-bold text-gray-600"><?php echo htmlspecialchars($user['name']); ?></span>
                    <img src="<?php echo $user['avatar_url'] ?: 'https://ui-avatars.com/api/?name='.urlencode($user['name']); ?>" class="w-10 h-10 rounded-full border-2 border-white shadow-sm bg-gray-200">
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto py-12 px-6">
        <header class="flex flex-col md:flex-row justify-between items-start md:items-end gap-6 mb-12">
            <div>
                <h1 class="text-4xl font-black text-gray-900 tracking-tight">Intelligence Dashboard</h1>
                <p class="text-gray-500 font-medium mt-1">Strategic oversight for <?php echo htmlspecialchars($org['name']); ?>.</p>
            </div>
            <a href="create-decision.php" class="w-full md:w-auto bg-indigo-600 text-white px-8 py-4 rounded-2xl font-black text-lg shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition-all active:scale-95 text-center">
                + New Strategic Decision
            </a>
        </header>

        <div class="grid lg:grid-cols-3 gap-8">
            <!-- Main Content: Decision List -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-[40px] border border-gray-100 shadow-sm overflow-hidden">
                    <div class="p-8 border-b border-gray-50 flex justify-between items-center">
                        <h2 class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Active Strategy Logs</h2>
                        <span class="text-[10px] font-black bg-indigo-50 text-indigo-600 px-3 py-1 rounded-full"><?php echo count($decisions); ?> Total</span>
                    </div>
                    
                    <?php if (empty($decisions)): ?>
                        <div class="p-24 text-center">
                            <div class="w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6 text-4xl">📓</div>
                            <h3 class="text-xl font-bold text-gray-900">Your Vault is Empty</h3>
                            <p class="text-gray-400 mb-8 max-w-xs mx-auto">Start documenting your first strategic choice to activate AI Intelligence.</p>
                            <a href="create-decision.php" class="bg-white border-2 border-indigo-600 text-indigo-600 px-8 py-3 rounded-xl font-bold hover:bg-indigo-50 transition-all inline-block">Document First Decision</a>
                        </div>
                    <?php else: ?>
                        <div class="divide-y divide-gray-50">
                            <?php foreach($decisions as $d): ?>
                                <a href="decision.php?id=<?php echo $d['id']; ?>" class="block p-8 hover:bg-gray-50 transition-all group">
                                    <div class="flex justify-between items-center">
                                        <div class="flex-1 pr-4">
                                            <div class="font-bold text-gray-900 group-hover:text-indigo-600 transition text-lg mb-1"><?php echo htmlspecialchars($d['title']); ?></div>
                                            <div class="flex items-center gap-3">
                                                <span class="text-[10px] font-black uppercase tracking-widest px-2 py-0.5 rounded bg-gray-100 text-gray-500">
                                                    <?php echo $d['status']; ?>
                                                </span>
                                                <span class="text-[10px] font-black text-gray-300 uppercase tracking-widest">
                                                    &bull; <?php echo date('M d, Y', strtotime($d['created_at'])); ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="text-gray-300 group-hover:text-indigo-600 transition-all group-hover:translate-x-1">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Sidebar -->
            <aside class="space-y-6">
                <!-- IQ Card -->
                <div class="bg-indigo-600 rounded-[40px] p-10 text-white shadow-2xl shadow-indigo-100 relative overflow-hidden">
                    <div class="relative z-10">
                        <div class="text-[10px] font-black uppercase tracking-widest opacity-60 mb-3">Strategic Accuracy</div>
                        <div class="text-7xl font-black mb-6 tracking-tighter">84<span class="text-2xl font-normal opacity-40">/200</span></div>
                        <p class="text-sm text-indigo-100 leading-relaxed font-medium">You are outperforming <strong>82% of similar scale startups</strong>. Review your outcomes to improve your score.</p>
                    </div>
                    <div class="absolute -bottom-20 -right-20 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
                </div>

                <!-- Insights List -->
                <div class="bg-white rounded-[40px] p-10 border border-gray-100 shadow-sm">
                    <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-8">Strategic Health</h3>
                    <div class="space-y-6">
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 bg-green-50 rounded-2xl flex items-center justify-center text-xl flex-shrink-0">📈</div>
                            <div>
                                <div class="text-xs font-bold text-gray-800">Decision Velocity</div>
                                <div class="text-[10px] text-green-600 font-bold mt-0.5">↑ 12% increase</div>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 bg-amber-50 rounded-2xl flex items-center justify-center text-xl flex-shrink-0">⚠️</div>
                            <div>
                                <div class="text-xs font-bold text-gray-800">Pending Reviews</div>
                                <div class="text-[10px] text-amber-600 font-bold mt-0.5">2 decisions require outcome logs</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </aside>
    </main>
</body>
</html>
