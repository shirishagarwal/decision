<?php
/**
 * File Path: dashboard.php
 * Description: Professionalized Command Center for Strategic Intelligence.
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/Intelligence.php';
requireLogin();

$user = getCurrentUser();
$orgId = $_SESSION['current_org_id'];
$pdo = getDbConnection();

$stmt = $pdo->prepare("SELECT * FROM organizations WHERE id = ?");
$stmt->execute([$orgId]);
$org = $stmt->fetch();

$iqScore = Intelligence::calculateIQ($orgId);
$percentile = Intelligence::getPercentile($iqScore);

$stmt = $pdo->prepare("SELECT * FROM decisions WHERE organization_id = ? ORDER BY created_at DESC LIMIT 15");
$stmt->execute([$orgId]);
$decisions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Intelligence Hub | <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; color: #334155; }
        .stat-card { background: white; border: 1px solid #e2e8f0; border-radius: 1rem; padding: 1.5rem; }
    </style>
</head>
<body class="min-h-screen flex flex-col">
    <?php include __DIR__ . '/includes/header.php'; ?>

    <main class="max-w-7xl mx-auto py-10 px-6 w-full flex-grow">
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-10">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Executive Dashboard</h1>
                <p class="text-sm text-slate-500 mt-1">Institutional Memory for <span class="font-semibold text-indigo-600"><?php echo htmlspecialchars($org['name']); ?></span></p>
            </div>
            <div class="flex gap-3">
                <a href="/marketplace.php" class="px-5 py-2.5 bg-white border border-slate-200 rounded-lg font-bold text-xs uppercase tracking-wider text-slate-600 hover:bg-slate-50 transition">Templates</a>
                <a href="create-decision.php" class="px-5 py-2.5 bg-indigo-600 text-white rounded-lg font-bold text-xs uppercase tracking-wider shadow-sm hover:bg-indigo-700 transition">+ Log Decision</a>
            </div>
        </header>

        <div class="grid md:grid-cols-4 gap-6 mb-10">
            <div class="stat-card border-l-4 border-l-indigo-600 shadow-sm">
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Decision Maturity Index</div>
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl font-black text-slate-900"><?php echo $iqScore; ?></span>
                    <span class="text-xs font-bold text-slate-400">/ 200</span>
                </div>
                <div class="mt-2 text-[10px] font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full inline-block"><?php echo $percentile; ?> Sector Ranking</div>
            </div>
            
            <div class="stat-card shadow-sm">
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Institutional Memory</div>
                <div class="text-3xl font-black text-slate-900"><?php echo count($decisions); ?></div>
                <div class="mt-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Logged Artifacts</div>
            </div>

            <div class="stat-card shadow-sm">
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Risk Simulations</div>
                <div class="text-3xl font-black text-slate-900">24</div>
                <div class="mt-2 text-[10px] font-bold text-emerald-600 uppercase tracking-widest">Active Monitoring</div>
            </div>

            <div class="stat-card bg-slate-900 border-none shadow-xl">
                <div class="text-[10px] font-bold text-indigo-400 uppercase tracking-widest mb-2">Strategic Intelligence</div>
                <div class="text-3xl font-black text-white">2.0k+</div>
                <div class="mt-2 text-[10px] font-medium text-slate-400">Failure Patterns Benchmarked</div>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                <h2 class="text-xs font-black text-slate-900 uppercase tracking-widest">Recent Strategic Logs</h2>
                <button class="text-[10px] font-bold text-indigo-600 hover:underline uppercase">View All Logs</button>
            </div>
            <div class="divide-y divide-slate-100">
                <?php if (empty($decisions)): ?>
                    <div class="p-20 text-center text-sm text-slate-400 font-medium">No decisions recorded. Initialize your first strategic artifact to begin.</div>
                <?php else: ?>
                    <?php foreach($decisions as $d): ?>
                        <a href="decision.php?id=<?php echo $d['id']; ?>" class="flex items-center justify-between px-6 py-5 hover:bg-slate-50 transition group">
                            <div class="flex items-center gap-6">
                                <div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center font-bold text-slate-400 text-xs">
                                    <?php echo date('d', strtotime($d['created_at'])); ?>
                                </div>
                                <div>
                                    <div class="text-sm font-bold text-slate-900 group-hover:text-indigo-600 transition"><?php echo htmlspecialchars($d['title']); ?></div>
                                    <div class="text-[10px] font-medium text-slate-400 uppercase tracking-tight mt-0.5">
                                        <?php echo htmlspecialchars($d['category'] ?: 'Strategy'); ?> &bull; Created <?php echo date('M Y', strtotime($d['created_at'])); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-10">
                                <div class="text-right hidden sm:block">
                                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Status</div>
                                    <div class="text-xs font-bold text-slate-700"><?php echo $d['status']; ?></div>
                                </div>
                                <div class="text-slate-300 group-hover:text-indigo-600 group-hover:translate-x-1 transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
