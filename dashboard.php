<?php
/**
 * File Path: dashboard.php
 * Description: Updated to reflect real intelligence counts and maturity index.
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/Intelligence.php';
requireLogin();

$orgId = $_SESSION['current_org_id'];
$pdo = getDbConnection();

// Fetch REAL stats
$artifactsCount = (int)$pdo->prepare("SELECT COUNT(*) FROM decisions WHERE organization_id = ?");
$artifactsCount->execute([$orgId]);
$totalArtifacts = $artifactsCount->fetchColumn();

$scoutedCount = Intelligence::getScoutedPatternCount();
$maturityIndex = Intelligence::calculateDMI($orgId);
$rank = Intelligence::getSectorRanking($maturityIndex);

// Recent Decisions
$stmt = $pdo->prepare("SELECT * FROM decisions WHERE organization_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$orgId]);
$recentDecisions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Executive Hub | DecisionVault</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap');
        body { font-family: 'Inter', sans-serif; background: #f8fafc; }
        .executive-card { background: white; border: 1px solid #e2e8f0; border-radius: 1rem; padding: 1.5rem; }
    </style>
</head>
<body class="min-h-screen">
    <?php include __DIR__ . '/includes/header.php'; ?>

    <main class="max-w-7xl mx-auto py-12 px-6">
        <header class="flex justify-between items-center mb-10">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Executive Dashboard</h1>
                <p class="text-sm text-slate-500">System State: Active Monitoring</p>
            </div>
            <a href="create-decision.php" class="bg-indigo-600 text-white px-6 py-2.5 rounded-lg font-bold text-sm shadow-lg hover:bg-indigo-700 transition">
                + Log Decision
            </a>
        </header>

        <div class="grid md:grid-cols-4 gap-6 mb-12">
            <!-- REAL MATURITY INDEX -->
            <div class="executive-card border-l-4 border-indigo-600 shadow-sm">
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Decision Maturity Index</div>
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold text-slate-900"><?php echo $maturityIndex; ?></span>
                    <span class="text-xs font-bold text-slate-400">/ 200</span>
                </div>
                <div class="mt-2 inline-block bg-indigo-50 text-indigo-600 text-[10px] font-bold px-2 py-0.5 rounded-full">
                    <?php echo $rank; ?> Sector Ranking
                </div>
            </div>

            <div class="executive-card">
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Institutional Memory</div>
                <div class="text-3xl font-extrabold text-slate-900"><?php echo $totalArtifacts; ?></div>
                <div class="text-[10px] font-bold text-slate-400 uppercase mt-1">Logged Artifacts</div>
            </div>

            <div class="executive-card">
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Active Risk Simulations</div>
                <div class="text-3xl font-extrabold text-slate-900">24</div>
                <div class="text-[10px] font-bold text-emerald-600 uppercase mt-1">Status: Operational</div>
            </div>

            <!-- REAL SCOUTED COUNT -->
            <div class="executive-card bg-slate-900 border-none shadow-xl">
                <div class="text-[10px] font-bold text-indigo-300 uppercase tracking-widest mb-2">Strategic Intelligence</div>
                <div class="text-3xl font-extrabold text-white">
                    <?php echo number_format($scoutedCount / 1000, 1); ?>k+
                </div>
                <div class="text-[10px] font-medium text-slate-400 mt-1">Failure Patterns Benchmarked</div>
            </div>
        </div>

        <!-- Recent Logs -->
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-200">
                <h2 class="text-xs font-black text-slate-900 uppercase tracking-widest">Recent Strategic Artifacts</h2>
            </div>
            <div class="divide-y divide-slate-100">
                <?php foreach($recentDecisions as $d): ?>
                <a href="decision.php?id=<?php echo $d['id']; ?>" class="flex items-center justify-between p-6 hover:bg-slate-50 transition">
                    <div>
                        <div class="text-sm font-bold text-slate-900"><?php echo htmlspecialchars($d['title']); ?></div>
                        <div class="text-[10px] text-slate-400 uppercase mt-1">Status: <?php echo $d['status']; ?></div>
                    </div>
                    <Icon name="chevron-right" className="text-slate-300" />
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
</body>
</html>
