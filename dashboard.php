<?php
/**
 * File Path: dashboard.php
 * Description: The high-fidelity command center for Strategic Intelligence.
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/Intelligence.php';
requireLogin();

$user = getCurrentUser();
$orgId = $_SESSION['current_org_id'];
$pdo = getDbConnection();

// 1. Fetch Organization Data
$stmt = $pdo->prepare("SELECT * FROM organizations WHERE id = ?");
$stmt->execute([$orgId]);
$org = $stmt->fetch();

// 2. Calculate Intelligence Metrics
$iqScore = Intelligence::calculateIQ($orgId);
$percentile = Intelligence::getPercentile($iqScore);

// 3. Fetch Recent Strategic Logs
$stmt = $pdo->prepare("
    SELECT * FROM decisions 
    WHERE organization_id = ? 
    ORDER BY created_at DESC 
    LIMIT 10
");
$stmt->execute([$orgId]);
$decisions = $stmt->fetchAll();

// 4. Calculate Velocity (Last 30 days count)
$stmt = $pdo->prepare("SELECT COUNT(*) FROM decisions WHERE organization_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)");
$stmt->execute([$orgId]);
$velocityCount = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Intelligence Hub | <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #fcfcfd; }
        .glass-card { background: white; border: 1px solid #f1f3f5; box-shadow: 0 10px 30px -5px rgba(0,0,0,0.04); border-radius: 2.5rem; }
    </style>
</head>
<body class="min-h-screen flex flex-col">
    
    <?php include __DIR__ . '/includes/header.php'; ?>

    <main class="max-w-7xl mx-auto py-12 px-6 flex-grow w-full">
        <!-- Hero Metrics -->
        <header class="flex flex-col lg:flex-row justify-between items-start lg:items-end gap-8 mb-16">
            <div class="space-y-2">
                <div class="flex items-center gap-2 mb-2">
                    <span class="w-2 h-2 rounded-full bg-indigo-600 animate-pulse"></span>
                    <span class="text-[10px] font-black uppercase tracking-[0.2em] text-indigo-600">Strategic Command</span>
                </div>
                <h1 class="text-5xl font-black tracking-tighter text-slate-900">Intelligence Hub</h1>
                <p class="text-slate-500 font-medium text-lg">Managing the moat for <span class="text-indigo-600 font-bold"><?php echo htmlspecialchars($org['name']); ?></span>.</p>
            </div>
            
            <div class="flex gap-4 w-full lg:w-auto">
                <a href="/marketplace.php" class="flex-1 lg:flex-none text-center px-8 py-4 border-2 border-slate-100 rounded-2xl font-black text-sm uppercase tracking-widest text-slate-400 hover:text-indigo-600 hover:border-indigo-100 transition-all">
                    Browse Patterns
                </a>
                <a href="create-decision.php" class="flex-1 lg:flex-none text-center bg-indigo-600 text-white px-10 py-4 rounded-2xl font-black text-sm uppercase tracking-widest shadow-2xl shadow-indigo-200 hover:bg-indigo-700 transition-all">
                    + Record Decision
                </a>
            </div>
        </header>

        <!-- Stats Grid -->
        <div class="grid lg:grid-cols-4 gap-8 mb-16">
            <!-- Moat IQ Score -->
            <div class="lg:col-span-2 glass-card p-10 relative overflow-hidden group">
                <div class="relative z-10">
                    <div class="flex justify-between items-start mb-10">
                        <div class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Moat IQ Score</div>
                        <div class="bg-indigo-50 text-indigo-600 text-[10px] font-black px-3 py-1 rounded-full"><?php echo $percentile; ?></div>
                    </div>
                    <div class="flex items-baseline gap-2 mb-4">
                        <span class="text-8xl font-black tracking-tighter text-slate-900"><?php echo $iqScore; ?></span>
                        <span class="text-2xl font-bold text-slate-300">/ 200</span>
                    </div>
                    <p class="text-slate-500 font-medium leading-relaxed max-w-sm">
                        Your strategic documentation rigor is currently in the <span class="text-indigo-600 font-bold"><?php echo $percentile; ?></span> of startups in your sector.
                    </p>
                </div>
                <div class="absolute -right-20 -bottom-20 w-64 h-64 bg-indigo-50 rounded-full blur-3xl opacity-50 group-hover:scale-110 transition-transform duration-700"></div>
            </div>

            <!-- Velocity -->
            <div class="glass-card p-10 flex flex-col justify-between">
                <div class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4">Strategic Velocity</div>
                <div>
                    <div class="text-5xl font-black text-slate-900 mb-2"><?php echo $velocityCount; ?></div>
                    <p class="text-xs font-bold text-emerald-500 uppercase tracking-widest">Decisions / 30 Days</p>
                </div>
                <div class="mt-8 h-12 w-full bg-slate-50 rounded-xl overflow-hidden flex items-end gap-1 p-1">
                    <?php for($i=0; $i<12; $i++): ?>
                        <div class="bg-indigo-200 w-full rounded-sm" style="height: <?php echo rand(20, 100); ?>%"></div>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- Pattern Library -->
            <div class="glass-card p-10 flex flex-col justify-between bg-slate-900 text-white border-none">
                <div class="text-[10px] font-black text-indigo-400 uppercase tracking-[0.2em] mb-4">External Intelligence</div>
                <div>
                    <div class="text-5xl font-black mb-2">2,042</div>
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-widest">Failure Patterns Tracked</p>
                </div>
                <p class="text-xs text-slate-400 font-medium mt-6 italic">
                    Cross-referencing your logic against the "Strategic Moat" database...
                </p>
            </div>
        </div>

        <!-- Recent Logs -->
        <div class="space-y-8">
            <h2 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Active Strategic Logs</h2>
            <div class="glass-card overflow-hidden">
                <div class="divide-y divide-slate-50">
                    <?php if (empty($decisions)): ?>
                        <div class="p-20 text-center">
                            <p class="text-slate-400 font-medium">No decisions recorded yet. Start building your moat.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach($decisions as $d): ?>
                            <a href="decision.php?id=<?php echo $d['id']; ?>" class="flex items-center justify-between p-10 hover:bg-slate-50/50 transition-all group">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <span class="text-[10px] font-black px-2 py-0.5 bg-slate-100 text-slate-500 rounded uppercase"><?php echo htmlspecialchars($d['category'] ?: 'Strategy'); ?></span>
                                        <span class="text-[10px] font-black text-slate-300 uppercase tracking-widest"><?php echo date('M d, Y', strtotime($d['created_at'])); ?></span>
                                    </div>
                                    <div class="text-2xl font-black text-slate-900 group-hover:text-indigo-600 transition tracking-tight">
                                        <?php echo htmlspecialchars($d['title']); ?>
                                    </div>
                                </div>
                                <div class="flex items-center gap-8">
                                    <div class="hidden md:block text-right">
                                        <div class="text-[10px] font-black text-slate-300 uppercase tracking-widest mb-1">Status</div>
                                        <div class="text-xs font-bold text-slate-900"><?php echo $d['status']; ?></div>
                                    </div>
                                    <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center border border-slate-100 shadow-sm group-hover:border-indigo-200 group-hover:text-indigo-600 transition-all transform group-hover:translate-x-1">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
