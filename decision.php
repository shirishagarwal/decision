<?php
/**
 * File Path: decision.php
 * Description: The primary view for a strategic decision.
 * Displays the problem, chosen/proposed options, and the simulation interface.
 */
require_once __DIR__ . '/config.php';
requireLogin();

$decisionId = $_GET['id'] ?? null;
$pdo = getDbConnection();

// Fetch Decision details with creator info
$stmt = $pdo->prepare("
    SELECT d.*, u.name as creator_name, u.avatar_url 
    FROM decisions d 
    JOIN users u ON d.created_by = u.id 
    WHERE d.id = ?
");
$stmt->execute([$decisionId]);
$decision = $stmt->fetch();

if (!$decision) {
    die("<div style='font-family:sans-serif;padding:50px;text-align:center;'>
            <h1 style='color:#ef4444;'>Decision Not Found</h1>
            <p>This strategic log may have been moved or deleted.</p>
            <a href='/dashboard.php' style='color:#4f46e5;font-weight:bold;'>Return to Dashboard</a>
         </div>");
}

// Fetch Options (AI + Manual)
$stmt = $pdo->prepare("SELECT * FROM decision_options WHERE decision_id = ? ORDER BY created_at ASC");
$stmt->execute([$decisionId]);
$options = $stmt->fetchAll();

// Fetch Simulation results if they exist
$stmt = $pdo->prepare("SELECT * FROM decision_simulations WHERE decision_id = ? LIMIT 1");
$stmt->execute([$decisionId]);
$simulation = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($decision['title']); ?> | DecisionVault</title>
    <script src="[https://cdn.tailwindcss.com](https://cdn.tailwindcss.com)"></script>
    <style>
        @import url('[https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap](https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap)');
        body { font-family: 'Inter', sans-serif; background-color: #f9fafb; }
        .decision-card { box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03); }
    </style>
</head>
<body class="selection:bg-indigo-100">

    <nav class="bg-white border-b border-gray-200 p-5 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <a href="/dashboard.php" class="flex items-center gap-2 text-gray-500 hover:text-indigo-600 transition font-bold text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Back to Dashboard
            </a>
            <div class="flex items-center gap-3">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Status: <?php echo $decision['status']; ?></span>
                <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto py-12 px-6">
        <div class="grid lg:grid-cols-3 gap-12">
            
            <!-- Left: Decision Details & Options -->
            <div class="lg:col-span-2 space-y-12">
                <header>
                    <div class="flex items-center gap-3 mb-6">
                        <span class="px-3 py-1 bg-indigo-50 text-indigo-600 text-[10px] font-black uppercase tracking-widest rounded-full border border-indigo-100">
                            <?php echo htmlspecialchars($decision['category'] ?: 'Strategic'); ?>
                        </span>
                        <span class="text-[10px] font-black text-gray-300 uppercase tracking-widest">
                            Recorded <?php echo date('M d, Y', strtotime($decision['created_at'])); ?>
                        </span>
                    </div>
                    <h1 class="text-5xl font-black text-gray-900 tracking-tight mb-6">
                        <?php echo htmlspecialchars($decision['title']); ?>
                    </h1>
                    <div class="p-8 bg-white rounded-[32px] border border-gray-100 decision-card">
                        <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-4">Problem Statement</h3>
                        <p class="text-lg text-gray-700 leading-relaxed font-medium">
                            <?php echo nl2br(htmlspecialchars($decision['problem_statement'])); ?>
                        </p>
                    </div>
                </header>

                <section>
                    <h2 class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-8">Strategic Options Evaluated</h2>
                    <div class="space-y-6">
                        <?php foreach($options as $opt): ?>
                            <div class="p-8 bg-white border border-gray-100 rounded-[32px] decision-card group hover:border-indigo-200 transition-colors">
                                <div class="flex justify-between items-start mb-4">
                                    <h3 class="text-xl font-bold text-gray-900 group-hover:text-indigo-600 transition">
                                        <?php echo htmlspecialchars($opt['name']); ?>
                                    </h3>
                                    <?php if($opt['is_ai_suggested']): ?>
                                        <span class="bg-indigo-600 text-white text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-widest">
                                            AI Suggested
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-gray-500 leading-relaxed text-sm">
                                    <?php echo nl2br(htmlspecialchars($opt['description'])); ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            </div>

            <!-- Right: Simulations & Metadata -->
            <aside class="space-y-8">
                <!-- User Context -->
                <div class="bg-white p-8 rounded-[32px] border border-gray-100 decision-card">
                    <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-6">Strategist</h3>
                    <div class="flex items-center gap-4">
                        <img src="<?php echo $decision['avatar_url']; ?>" class="w-12 h-12 rounded-full border-2 border-white shadow-sm">
                        <div>
                            <div class="font-bold text-gray-900"><?php echo htmlspecialchars($decision['creator_name']); ?></div>
                            <div class="text-xs text-gray-400 font-medium">Decision Owner</div>
                        </div>
                    </div>
                </div>

                <!-- Simulation Component -->
                <div class="sticky top-32">
                    <?php
                    // Make the ID available for the JS in simulator-ui.php
                    $decisionId = $decision['id'];
                    include __DIR__ . '/components/simulator-ui.php';
                    ?>
                </div>
            </aside>
        </div>
    </main>

</body>
</html>
