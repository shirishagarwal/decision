<?php
/**
 * File Path: decision.php
 * Description: The primary high-fidelity view for a strategic decision.
 * Includes problem context, options list, and the Strategic Stress Test (Pre-Mortem) engine.
 */
require_once __DIR__ . '/config.php';
requireLogin();

$decisionId = $_GET['id'] ?? null;
$pdo = getDbConnection();

// Fetch Decision details with creator info
try {
    $stmt = $pdo->prepare("
        SELECT d.*, u.name as creator_name, u.avatar_url 
        FROM decisions d 
        JOIN users u ON d.created_by = u.id 
        WHERE d.id = ?
    ");
    $stmt->execute([$decisionId]);
    $decision = $stmt->fetch();

    if (!$decision) {
        header('Location: /dashboard.php');
        exit;
    }

    // Fetch Options (AI + Manual)
    $stmt = $pdo->prepare("SELECT * FROM decision_options WHERE decision_id = ? ORDER BY created_at ASC");
    $stmt->execute([$decisionId]);
    $options = $stmt->fetchAll();

    // Fetch Existing Simulation result if it exists
    $stmt = $pdo->prepare("SELECT * FROM decision_simulations WHERE decision_id = ? LIMIT 1");
    $stmt->execute([$decisionId]);
    $simulation = $stmt->fetch();

} catch (Exception $e) {
    die("Intelligence Retrieval Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($decision['title']); ?> | DecisionVault</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; color: #0f172a; }
        .premium-card { background: white; border: 1px solid #f1f5f9; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03); border-radius: 32px; }
        .gradient-text { background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    </style>
</head>
<body class="selection:bg-indigo-100 min-h-screen">

    <nav class="bg-white/80 backdrop-blur-md border-b border-slate-100 p-5 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <a href="/dashboard.php" class="flex items-center gap-2 text-slate-500 hover:text-indigo-600 transition font-bold text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Dashboard
            </a>
            <div class="flex items-center gap-4">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Live Session</span>
                <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto py-12 px-6">
        <div class="grid lg:grid-cols-3 gap-12">
            
            <!-- Left: Strategic Narrative -->
            <div class="lg:col-span-2 space-y-12">
                <header>
                    <div class="flex items-center gap-3 mb-6">
                        <span class="px-3 py-1 bg-indigo-50 text-indigo-600 text-[10px] font-black uppercase tracking-widest rounded-full border border-indigo-100">
                            <?php echo htmlspecialchars($decision['category'] ?: 'Strategic'); ?>
                        </span>
                        <span class="text-[10px] font-black text-slate-300 uppercase tracking-widest">
                            Vault ID: #<?php echo str_pad($decision['id'], 5, '0', STR_PAD_LEFT); ?>
                        </span>
                    </div>
                    <h1 class="text-5xl md:text-6xl font-black text-slate-900 tracking-tighter leading-none mb-8">
                        <?php echo htmlspecialchars($decision['title']); ?>
                    </h1>
                    <div class="p-10 premium-card bg-white">
                        <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4">Core Problem Statement</h3>
                        <p class="text-xl text-slate-600 leading-relaxed font-medium">
                            <?php echo nl2br(htmlspecialchars($decision['problem_statement'])); ?>
                        </p>
                    </div>
                </header>

                <section>
                    <div class="flex justify-between items-end mb-8">
                        <h2 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Evaluated Paths</h2>
                        <span class="text-[10px] font-black text-indigo-600 uppercase"><?php echo count($options); ?> Options Recorded</span>
                    </div>
                    <div class="space-y-6">
                        <?php foreach($options as $opt): ?>
                            <div class="p-8 premium-card group hover:border-indigo-200 transition-all">
                                <div class="flex justify-between items-start mb-4">
                                    <h3 class="text-2xl font-bold text-slate-900 group-hover:text-indigo-600 transition">
                                        <?php echo htmlspecialchars($opt['name']); ?>
                                    </h3>
                                    <?php if($opt['is_ai_suggested']): ?>
                                        <span class="bg-indigo-600 text-white text-[10px] font-black px-3 py-1.5 rounded-full uppercase tracking-widest shadow-lg shadow-indigo-100">
                                            AI Pattern
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-slate-500 leading-relaxed text-base font-medium">
                                    <?php echo nl2br(htmlspecialchars($opt['description'])); ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            </div>

            <!-- Right: Intelligence Sidebar -->
            <aside class="space-y-8">
                <!-- Creator Profile -->
                <div class="p-8 premium-card bg-white">
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-6">Decision Lead</h3>
                    <div class="flex items-center gap-4">
                        <img src="<?php echo $decision['avatar_url'] ?: 'https://ui-avatars.com/api/?name='.urlencode($decision['creator_name']); ?>" class="w-12 h-12 rounded-full border-2 border-white shadow-sm bg-slate-100">
                        <div>
                            <div class="font-bold text-slate-900"><?php echo htmlspecialchars($decision['creator_name']); ?></div>
                            <div class="text-[10px] text-slate-400 font-black uppercase tracking-widest">Master Strategist</div>
                        </div>
                    </div>
                </div>

                <!-- Strategic Stress Test (Pre-Mortem) -->
                <div class="sticky top-32">
                    <?php
                        // We set the decision ID for the component
                        $targetDecisionId = $decision['id'];
                        include __DIR__ . '/components/simulator-ui.php';
                    ?>
                </div>
            </aside>
        </div>
    </main>

    <footer class="py-20 text-center border-t border-slate-100">
        <p class="text-[10px] font-black text-slate-300 uppercase tracking-[0.3em]">DecisionVault Intelligence &bull; Secured with 256-bit Logic</p>
    </footer>

</body>
</html>
