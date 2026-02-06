<?php
/**
 * File Path: dashboard.php
 * Description: Professional Executive Dashboard.
 * Fixed: Fatal 500 error in DB query logic.
 * Design: High-fidelity Corporate UI (Inter font, optimized density).
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/Intelligence.php';
requireLogin();

$user = getCurrentUser();
$orgId = $_SESSION['current_org_id'];
$pdo = getDbConnection();

// 1. Fetch Organization Context
$stmt = $pdo->prepare("SELECT name FROM organizations WHERE id = ?");
$stmt->execute([$orgId]);
$org = $stmt->fetch();

// 2. Fetch Live Metrics (Fixed Syntax)
$maturityIndex = Intelligence::calculateDMI($pdo, $orgId);
$scoutedCount = Intelligence::getScoutedPatternCount($pdo);
$sectorRank = Intelligence::getSectorRanking($maturityIndex);

// 3. Fetch Institutional Memory Count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM decisions WHERE organization_id = ?");
$stmt->execute([$orgId]);
$totalArtifacts = (int)$stmt->fetchColumn();

// 4. Fetch Recent Logs for the table
$stmt = $pdo->prepare("SELECT * FROM decisions WHERE organization_id = ? ORDER BY created_at DESC LIMIT 10");
$stmt->execute([$orgId]);
$recentDecisions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Executive Dashboard | DecisionVault</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; color: #1e293b; }
        .executive-card { background: white; border: 1px solid #e2e8f0; border-radius: 1rem; padding: 1.5rem; }
        .metric-label { font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; }
    </style>
</head>
<body class="min-h-screen flex flex-col">
    <?php include __DIR__ . '/includes/header.php'; ?>

    <main class="max-w-7xl mx-auto py-10 px-6 w-full flex-grow">
        <header class="flex flex-col md:flex-row justify-between items-start md:items-end gap-6 mb-10">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Executive Dashboard</h1>
                <p class="text-sm text-slate-500 mt-1">Institutional Memory for <span class="font-semibold text-indigo-600"><?php echo htmlspecialchars($org['name'] ?? "Your Vault"); ?></span></p>
            </div>
            <div class="flex gap-3">
                <a href="create-decision.php" class="px-5 py-2.5 bg-indigo-600 text-white rounded-lg font-bold text-xs uppercase tracking-wider shadow-md hover:bg-indigo-700 transition">
                    + Log Strategic Artifact
                </a>
            </div>
        </header>

        <!-- KPI Grid -->
        <div class="grid md:grid-cols-4 gap-6 mb-10">
            <div class="executive-card border-l-4 border-l-indigo-600 shadow-sm">
                <div class="metric-label mb-2">Decision Maturity Index</div>
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold text-slate-900"><?php echo $maturityIndex; ?></span>
                    <span class="text-xs font-bold text-slate-400">/ 200</span>
                </div>
                <div class="mt-2 inline-block bg-indigo-50 text-indigo-600 text-[10px] font-bold px-2 py-0.5 rounded-full">
                    <?php echo $sectorRank; ?> Sector Ranking
                </div>
            </div>
            
            <div class="executive-card shadow-sm">
                <div class="metric-label mb-2">Institutional Memory</div>
                <div class="text-3xl font-extrabold text-slate-900"><?php echo $totalArtifacts; ?></div>
                <div class="mt-1 text-[10px] font-bold text-slate-400 uppercase">Logged Artifacts</div>
            </div>

            <div class="executive-card shadow-sm">
                <div class="metric-label mb-2">Risk Simulations</div>
                <div class="text-3xl font-extrabold text-slate-900">24</div>
                <div class="mt-1 text-[10px] font-bold text-emerald-600 uppercase">System Active</div>
            </div>

            <div class="executive-card bg-slate-900 border-none shadow-xl">
                <div class="text-[10px] font-bold text-indigo-300 uppercase tracking-widest mb-2">Strategic Intelligence</div>
                <div class="text-3xl font-extrabold text-white">
                    <?php echo number_format($scoutedCount / 1000, 1); ?>k+
                </div>
                <div class="mt-1 text-[10px] font-medium text-slate-400">Benchmarked Failure Patterns</div>
            </div>
        </div>

        <!-- Recent Logs Table -->
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                <h2 class="text-xs font-bold text-slate-900 uppercase tracking-widest">Recent Strategic Logs</h2>
                <button class="text-[10px] font-bold text-indigo-600 hover:underline uppercase">View Repository</button>
            </div>
            <div class="divide-y divide-slate-100">
                <?php if (empty($recentDecisions)): ?>
                    <div class="p-16 text-center text-sm text-slate-400 font-medium">No decisions recorded. Initialize your first strategic artifact to begin.</div>
                <?php else: ?>
                    <?php foreach($recentDecisions as $d): ?>
                        <a href="decision.php?id=<?php echo $d['id']; ?>" class="flex items-center justify-between px-6 py-4 hover:bg-slate-50 transition group">
                            <div class="flex items-center gap-4">
                                <div class="w-8 h-8 rounded bg-slate-100 flex items-center justify-center font-bold text-slate-400 text-[10px]">
                                    <?php echo date('d', strtotime($d['created_at'])); ?>
                                </div>
                                <div>
                                    <div class="text-sm font-bold text-slate-900 group-hover:text-indigo-600 transition"><?php echo htmlspecialchars($d['title']); ?></div>
                                    <div class="text-[10px] font-medium text-slate-400 uppercase mt-0.5">
                                        <?php echo htmlspecialchars($d['category'] ?: 'Governance'); ?> &bull; <?php echo date('M Y', strtotime($d['created_at'])); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-8">
                                <div class="text-right hidden sm:block">
                                    <div class="text-[8px] font-bold text-slate-400 uppercase tracking-widest">Status</div>
                                    <div class="text-[10px] font-bold text-slate-600"><?php echo $d['status']; ?></div>
                                </div>
                                <div class="text-slate-300 group-hover:text-indigo-600 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
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
