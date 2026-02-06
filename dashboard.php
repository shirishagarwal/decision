<?php
/**
 * File Path: dashboard.php
 * Description: Refined Executive Dashboard for Institutional Intelligence.
 * Professionalized: Reduced font scaling, formal terminology, and data-density.
 * Fixed: Robust error handling for metrics to prevent 500 errors.
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/Intelligence.php';
requireLogin();

$user = getCurrentUser();
$orgId = $_SESSION['current_org_id'] ?? null;

// Redirect to onboarding if no organization context exists
if (!$orgId) {
    header('Location: /onboarding/create-organization.php');
    exit;
}

$pdo = getDbConnection();

try {
    // 1. Fetch Organization Context
    $stmt = $pdo->prepare("SELECT name FROM organizations WHERE id = ?");
    $stmt->execute([$orgId]);
    $org = $stmt->fetch();

    // 2. Synthesize High-Level Metrics
    $maturityIndex = Intelligence::calculateDMI($pdo, $orgId);
    $scoutedCount = Intelligence::getScoutedPatternCount($pdo);
    $sectorRank = Intelligence::getSectorRanking($maturityIndex);

    // 3. Aggregate Institutional Memory
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM decisions WHERE organization_id = ?");
    $stmt->execute([$orgId]);
    $totalArtifacts = (int)$stmt->fetchColumn();

    // 4. Retrieve Recent Strategic Logs
    $stmt = $pdo->prepare("
        SELECT id, title, category, status, created_at 
        FROM decisions 
        WHERE organization_id = ? 
        ORDER BY created_at DESC 
        LIMIT 10
    ");
    $stmt->execute([$orgId]);
    $recentDecisions = $stmt->fetchAll();

} catch (Exception $e) {
    // Fail-safe for corporate environments: Show error in UI rather than 500
    $dbError = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Executive Hub | DecisionVault</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; color: #1e293b; }
        .stat-card { background: white; border: 1px solid #e2e8f0; border-radius: 0.75rem; padding: 1.25rem; transition: all 0.2s; }
        .stat-card:hover { border-color: #cbd5e1; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        .metric-value { font-size: 1.875rem; font-weight: 800; color: #0f172a; line-height: 1; }
        .label-caps { font-size: 0.65rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; }
    </style>
</head>
<body class="min-h-screen flex flex-col">
    <?php include __DIR__ . '/includes/header.php'; ?>

    <main class="max-w-7xl mx-auto py-12 px-6 w-full flex-grow">
        <?php if (isset($dbError)): ?>
            <div class="mb-8 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-xs font-bold">
                System Alert: Database synchronization failed. Metrics may be temporarily unavailable.
            </div>
        <?php endif; ?>

        <header class="flex flex-col md:flex-row justify-between items-start md:items-end gap-6 mb-12">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Executive Dashboard</h1>
                <p class="text-sm text-slate-500 mt-1">Institutional Memory for <span class="font-semibold text-indigo-600"><?php echo htmlspecialchars($org['name'] ?? "Corporate Vault"); ?></span></p>
            </div>
            <div class="flex gap-3">
                <a href="/marketplace.php" class="px-4 py-2 border border-slate-200 rounded-lg font-bold text-[11px] uppercase tracking-wider text-slate-500 hover:bg-white transition">Templates</a>
                <a href="create-decision.php" class="px-5 py-2.5 bg-indigo-600 text-white rounded-lg font-bold text-[11px] uppercase tracking-wider shadow-sm hover:bg-indigo-700 transition">
                    + Capture Artifact
                </a>
            </div>
        </header>

        <!-- Strategic KPI Grid -->
        <div class="grid md:grid-cols-4 gap-6 mb-12">
            <div class="stat-card border-l-4 border-l-indigo-600">
                <div class="label-caps mb-3">Decision Maturity Index</div>
                <div class="flex items-baseline gap-2">
                    <span class="metric-value"><?php echo $maturityIndex; ?></span>
                    <span class="text-xs font-bold text-slate-400">/ 200</span>
                </div>
                <div class="mt-3 inline-block bg-indigo-50 text-indigo-600 text-[10px] font-bold px-2 py-0.5 rounded-md">
                    <?php echo $sectorRank; ?>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="label-caps mb-3">Institutional Repository</div>
                <div class="metric-value"><?php echo $totalArtifacts; ?></div>
                <div class="mt-2 text-[10px] font-bold text-slate-400 uppercase">Logged Decision Logs</div>
            </div>

            <div class="stat-card">
                <div class="label-caps mb-3">Risk Simulations</div>
                <div class="metric-value">24</div>
                <div class="mt-2 text-[10px] font-bold text-emerald-600 uppercase">System: Operational</div>
            </div>

            <div class="stat-card bg-slate-900 border-none shadow-xl">
                <div class="text-[10px] font-bold text-indigo-400 uppercase tracking-widest mb-3">Strategic Intelligence</div>
                <div class="text-2xl font-extrabold text-white">
                    <?php
                        if ($scoutedCount >= 1000) {
                            echo number_format($scoutedCount / 1000, 1) . 'k+';
                        } else {
                            echo number_format($scoutedCount);
                        }
                    ?>
                </div>
                <div class="mt-2 text-[10px] font-medium text-slate-400">Verified Global Failure Patterns</div>
            </div>
        </div>

        <!-- Strategic Logs Table -->
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                <h2 class="text-xs font-bold text-slate-900 uppercase tracking-widest">Recent Strategic Artifacts</h2>
                <button class="text-[10px] font-bold text-indigo-600 hover:text-indigo-800 uppercase tracking-tighter transition">Access Full Repository</button>
            </div>
            <div class="divide-y divide-slate-100">
                <?php if (empty($recentDecisions)): ?>
                    <div class="p-16 text-center text-xs text-slate-400 font-medium">No strategic artifacts recorded. Initialize a capture to begin building organizational intelligence.</div>
                <?php else: ?>
                    <?php foreach($recentDecisions as $d): ?>
                        <a href="decision.php?id=<?php echo $d['id']; ?>" class="flex items-center justify-between px-6 py-4 hover:bg-slate-50 transition group">
                            <div class="flex items-center gap-4">
                                <div class="w-8 h-8 rounded bg-slate-100 flex items-center justify-center font-bold text-slate-400 text-[10px]">
                                    <?php echo date('d', strtotime($d['created_at'])); ?>
                                </div>
                                <div>
                                    <div class="text-[13px] font-bold text-slate-900 group-hover:text-indigo-600 transition"><?php echo htmlspecialchars($d['title']); ?></div>
                                    <div class="text-[9px] font-medium text-slate-400 uppercase mt-0.5">
                                        <?php echo htmlspecialchars($d['category'] ?: 'Governance'); ?> &bull; <?php echo date('M Y', strtotime($d['created_at'])); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-8">
                                <div class="text-right hidden sm:block">
                                    <div class="text-[8px] font-bold text-slate-400 uppercase tracking-widest">Compliance Status</div>
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
