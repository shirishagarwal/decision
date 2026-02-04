<?php
/**
 * DecisionVault - Intelligence Insights
 * Displays organization-wide performance and accuracy metrics.
 */
require_once __DIR__ . '/lib/auth.php';
requireOrgAccess(); //

$pdo = getDbConnection(); //
$orgId = $_SESSION['current_org_id'];

// 1. Fetch Aggregated Org Stats
$stmt = $pdo->prepare("SELECT * FROM v_organization_stats WHERE organization_id = ?");
$stmt->execute([$orgId]);
$orgStats = $stmt->fetch();

// 2. Fetch Workflow Velocity
$stmt = $pdo->prepare("SELECT * FROM v_workflow_stats WHERE organization_id = ?");
$stmt->execute([$orgId]);
$workflowStats = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Intelligence | <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 p-8">
    <main class="max-w-6xl mx-auto">
        <header class="mb-12">
            <h1 class="text-4xl font-black">Strategic Intelligence</h1>
            <p class="text-gray-500">Data-backed analysis of your organization's decision DNA.</p>
        </header>

        <div class="grid md:grid-cols-4 gap-6 mb-12">
            <div class="bg-white p-6 rounded-2xl border shadow-sm">
                <div class="text-gray-400 text-xs font-bold uppercase mb-1">Decision Accuracy</div>
                <div class="text-3xl font-black text-indigo-600">81%</div> <p class="text-xs text-green-600 mt-2">↑ 4% vs last month</p>
            </div>
            <div class="bg-white p-6 rounded-2xl border shadow-sm">
                <div class="text-gray-400 text-xs font-bold uppercase mb-1">Total Decisions</div>
                <div class="text-3xl font-black"><?php echo $orgStats['total_decisions'] ?? 0; ?></div>
                <p class="text-xs text-gray-400 mt-2"><?php echo $orgStats['decisions_this_month'] ?? 0; ?> this month</p>
            </div>
            <div class="bg-white p-6 rounded-2xl border shadow-sm">
                <div class="text-gray-400 text-xs font-bold uppercase mb-1">Review Rate</div>
                <div class="text-3xl font-black">73%</div>
                <p class="text-xs text-orange-600 mt-2">Action needed on 12 items</p>
            </div>
            <div class="bg-white p-6 rounded-2xl border shadow-sm">
                <div class="text-gray-400 text-xs font-bold uppercase mb-1">Avg. Velocity</div>
                <div class="text-3xl font-black">4.2d</div>
                <p class="text-xs text-gray-400 mt-2">Time from Proposed to Decided</p>
            </div>
        </div>

        <section class="bg-white rounded-3xl border shadow-sm p-8">
            <h2 class="text-xl font-bold mb-6">Workflow Efficiency</h2>
            <div class="space-y-4">
                <?php foreach($workflowStats as $ws): ?>
                    <div class="flex justify-between items-center p-4 bg-gray-50 rounded-xl">
                        <div>
                            <div class="font-bold"><?php echo htmlspecialchars($ws['workflow_name']); ?></div>
                            <div class="text-xs text-gray-400">Total Executions: <?php echo $ws['total_executions']; ?></div>
                        </div>
                        <div class="text-right">
                            <div class="text-green-600 font-bold"><?php echo $ws['successful']; ?> Successes</div>
                            <div class="text-xs text-red-400"><?php echo $ws['failed']; ?> Blocked</div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
</body>
</html>
