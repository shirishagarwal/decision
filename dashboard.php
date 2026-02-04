<?php
require_once __DIR__ . '/lib/auth.php';
requireOrgAccess();

$user = getCurrentUser(); //cite: 4
$pdo = getDbConnection(); //cite: 6
$orgId = $_SESSION['current_org_id'];
$workspaceId = getActiveWorkspaceId();

// 1. Fetch Basic Stats
$stmt = $pdo->prepare("SELECT COUNT(*) FROM decisions WHERE workspace_id = ?");
$stmt->execute([$workspaceId]);
$totalDecisions = $stmt->fetchColumn();

// 2. Fetch Recent Decisions
$stmt = $pdo->prepare("
    SELECT * FROM decisions 
    WHERE workspace_id = ? 
    ORDER BY created_at DESC LIMIT 5
");
$stmt->execute([$workspaceId]);
$recentDecisions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Dashboard | <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <nav class="bg-white border-b p-4 flex justify-between items-center">
        <h1 class="text-xl font-bold"><?php echo APP_NAME; ?></h1>
        <div class="flex gap-4 items-center">
            <span class="text-sm text-gray-500"><?php echo $user['name']; ?></span>
            <a href="/auth/logout.php" class="text-sm text-red-600">Logout</a>
        </div>
    </nav>

    <main class="max-w-6xl mx-auto p-8">
        <header class="flex justify-between items-end mb-12">
            <div>
                <h2 class="text-3xl font-black">Your Decisions</h2>
                <p class="text-gray-500">You have documented <?php echo $totalDecisions; ?> strategic choices.</p>
            </div>
            <a href="/ai-assistant.php" class="bg-indigo-600 text-white px-6 py-3 rounded-xl font-bold shadow-lg shadow-indigo-200 hover:scale-105 transition">
                + New AI Decision
            </a>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
            <div class="bg-white p-6 rounded-2xl border shadow-sm">
                <div class="text-gray-400 text-sm font-bold uppercase">Decision IQ</div>
                <div class="text-4xl font-black text-indigo-600">--</div>
            </div>
            <div class="bg-white p-6 rounded-2xl border shadow-sm">
                <div class="text-gray-400 text-sm font-bold uppercase">Accuracy</div>
                <div class="text-4xl font-black text-green-600">0%</div>
            </div>
            <div class="bg-white p-6 rounded-2xl border shadow-sm">
                <div class="text-gray-400 text-sm font-bold uppercase">Reviews Due</div>
                <div class="text-4xl font-black text-orange-500">0</div>
            </div>
        </div>

        <section class="bg-white rounded-3xl border shadow-sm overflow-hidden">
            <div class="p-6 border-b bg-gray-50/50">
                <h3 class="font-bold">Recent History</h3>
            </div>
            <?php if (empty($recentDecisions)): ?>
                <div class="p-12 text-center text-gray-500">No decisions yet. Start your first one with AI!</div>
            <?php else: ?>
                <?php foreach($recentDecisions as $d): ?>
                    <div class="p-6 border-b last:border-0 hover:bg-gray-50 cursor-pointer flex justify-between items-center">
                        <div>
                            <div class="font-bold text-lg"><?php echo htmlspecialchars($d['title']); ?></div>
                            <div class="text-sm text-gray-400"><?php echo $d['category']; ?> • <?php echo $d['status']; ?></div>
                        </div>
                        <span class="text-gray-300">→</span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
