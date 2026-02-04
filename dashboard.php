<?php
/**
 * File Path: dashboard.php
 * Description: Restores Workspace/Settings context and provides a professional entry point.
 */
require_once __DIR__ . '/config.php';
requireLogin();

$user = getCurrentUser();
$pdo = getDbConnection();

// Fetch Organization & Workspaces [cite: 34]
$stmt = $pdo->prepare("
    SELECT o.* FROM organizations o 
    JOIN organization_members om ON o.id = om.organization_id 
    WHERE om.user_id = ? LIMIT 1
");
$stmt->execute([$user['id']]);
$org = $stmt->fetch();

// Redirect to onboarding if no org exists
if (!$org) { header('Location: onboarding/create-organization.php'); exit; }

// Fetch Recent Decisions
$stmt = $pdo->prepare("SELECT * FROM decisions WHERE organization_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$org['id']]);
$recentDecisions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Dashboard | <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap'); body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-50 min-h-screen">
    <nav class="bg-white border-b border-gray-200 p-4 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-6">
                <span class="font-black text-xl tracking-tighter">DECISION<span class="text-indigo-600">VAULT</span></span>
                <div class="h-6 w-px bg-gray-200"></div>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-indigo-600 rounded flex items-center justify-center text-white font-bold text-xs"><?php echo substr($org['name'], 0, 1); ?></div>
                    <span class="font-bold text-sm text-gray-700"><?php echo htmlspecialchars($org['name']); ?></span>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <a href="/organization-settings.php" class="text-gray-400 hover:text-indigo-600 text-sm font-bold uppercase tracking-widest">Settings</a>
                <img src="<?php echo $user['avatar_url']; ?>" class="w-10 h-10 rounded-full border-2 border-white shadow-sm">
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto py-12 px-6">
        <header class="flex justify-between items-end mb-12">
            <div>
                <h1 class="text-4xl font-black text-gray-900">Workspace</h1>
                <p class="text-gray-500">Your organization's strategic intelligence nerve center.</p>
            </div>
            <a href="create-decision.php" class="bg-indigo-600 text-white px-8 py-4 rounded-2xl font-black text-lg shadow-xl shadow-indigo-100 hover:scale-105 transition-all">
                + New Decision
            </a>
        </header>

        <div class="grid lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-8">
                <section class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-50 flex justify-between items-center">
                        <h2 class="font-black text-gray-400 uppercase text-xs tracking-widest">Recent Strategy Logs</h2>
                        <a href="#" class="text-indigo-600 text-xs font-bold">View All</a>
                    </div>
                    <div class="divide-y divide-gray-50">
                        <?php if (empty($recentDecisions)): ?>
                            <div class="p-12 text-center">
                                <p class="text-gray-400 mb-4 font-medium">Your vault is empty.</p>
                                <a href="create-decision.php" class="text-indigo-600 font-bold">Document your first decision →</a>
                            </div>
                        <?php else: ?>
                            <?php foreach($recentDecisions as $d): ?>
                                <a href="decision.php?id=<?php echo $d['id']; ?>" class="block p-6 hover:bg-gray-50 transition-colors">
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <div class="font-bold text-gray-900"><?php echo htmlspecialchars($d['title']); ?></div>
                                            <div class="text-xs text-gray-400 mt-1"><?php echo $d['status']; ?> • <?php echo date('M d, Y', strtotime($d['created_at'])); ?></div>
                                        </div>
                                        <span class="text-indigo-600 text-xl">→</span>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>
            </div>

            <!-- Sidebar -->
            <aside class="space-y-8">
                <div class="bg-indigo-600 rounded-3xl p-8 text-white shadow-xl shadow-indigo-200">
                    <div class="text-[10px] font-black uppercase tracking-widest opacity-60 mb-1">Organization IQ</div>
                    <div class="text-5xl font-black mb-4">84</div>
                    <p class="text-xs text-indigo-100 leading-relaxed">Your accuracy is in the <strong>Top 12%</strong> for startups in your sector. Keep documenting to increase intelligence.</p>
                </div>

                <div class="bg-white rounded-3xl p-8 border border-gray-100 shadow-sm">
                    <h3 class="font-black text-gray-400 uppercase text-xs tracking-widest mb-6">Next Actions</h3>
                    <div class="space-y-4">
                        <div class="flex gap-4 items-start">
                            <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center text-amber-600 font-bold text-xs">!</div>
                            <div>
                                <div class="text-sm font-bold text-gray-900">Review Pricing Pivot</div>
                                <div class="text-xs text-gray-400 mt-1">Outcome review due in 2 days</div>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </main>
</body>
</html>
