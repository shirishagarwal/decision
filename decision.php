<?php
/**
 * File Path: decision.php
 * Description: The high-fidelity view for a strategic decision.
 * Updated: Added Edit and Delete actions in the header.
 */
require_once __DIR__ . '/config.php';
requireLogin();

$decisionId = $_GET['id'] ?? null;
$pdo = getDbConnection();

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

    $stmt = $pdo->prepare("SELECT * FROM decision_options WHERE decision_id = ? ORDER BY created_at ASC");
    $stmt->execute([$decisionId]);
    $options = $stmt->fetchAll();

    $stmt = $pdo->prepare("SELECT * FROM decision_simulations WHERE decision_id = ? LIMIT 1");
    $stmt->execute([$decisionId]);
    $simulation = $stmt->fetch();

} catch (Exception $e) {
    die("Intelligence Retrieval Error");
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
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .premium-card { background: white; border: 1px solid #f1f5f9; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); border-radius: 2.5rem; }
    </style>
</head>
<body class="flex flex-col min-h-screen">

    <?php include __DIR__ . '/includes/header.php'; ?>

    <main class="max-w-7xl mx-auto py-12 px-6 flex-grow">
        <div class="grid lg:grid-cols-3 gap-12">
            
            <div class="lg:col-span-2 space-y-12">
                <header>
                    <div class="flex justify-between items-center mb-6">
                        <div class="flex items-center gap-3">
                            <span class="px-3 py-1 bg-indigo-50 text-indigo-600 text-[10px] font-black uppercase tracking-widest rounded-full border border-indigo-100">
                                <?php echo htmlspecialchars($decision['category'] ?: 'Strategic'); ?>
                            </span>
                            <a href="/dashboard.php" class="text-[10px] font-black text-slate-400 hover:text-indigo-600 uppercase tracking-widest">← Vault</a>
                        </div>
                        
                        <div class="flex items-center gap-4">
                            <a href="/edit-decision.php?id=<?= $decision['id'] ?>" class="text-[10px] font-black text-slate-400 hover:text-indigo-600 uppercase tracking-widest">Edit</a>
                            <button onclick="deleteDecision(<?= $decision['id'] ?>)" class="text-[10px] font-black text-red-300 hover:text-red-500 uppercase tracking-widest">Delete</button>
                        </div>
                    </div>

                    <h1 class="text-5xl md:text-6xl font-black text-slate-900 tracking-tighter leading-none mb-8">
                        <?php echo htmlspecialchars($decision['title']); ?>
                    </h1>
                    
                    <?php if ($decision['status'] === 'Implemented'): ?>
                        <div class="mb-8 p-6 bg-emerald-50 border border-emerald-100 rounded-[2rem]">
                            <div class="text-[10px] font-black text-emerald-600 uppercase tracking-widest mb-2">Outcome Loop Closed</div>
                            <div class="text-xl font-black text-slate-900 capitalize"><?php echo str_replace('_', ' ', $decision['review_rating']); ?></div>
                            <p class="text-sm text-slate-600 mt-2 font-medium"><?php echo htmlspecialchars($decision['actual_outcome']); ?></p>
                        </div>
                    <?php endif; ?>

                    <div class="p-10 premium-card">
                        <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4">Core Problem Statement</h3>
                        <p class="text-xl text-slate-600 leading-relaxed font-medium">
                            <?php echo nl2br(htmlspecialchars($decision['problem_statement'])); ?>
                        </p>
                    </div>
                </header>

                <div class="space-y-6">
                    <h2 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Evaluated Paths</h2>
                    <?php foreach($options as $opt): ?>
                        <div class="p-8 premium-card">
                            <h3 class="text-2xl font-bold text-slate-900 mb-2"><?php echo htmlspecialchars($opt['name']); ?></h3>
                            <p class="text-slate-500 leading-relaxed text-base font-medium"><?php echo nl2br(htmlspecialchars($opt['description'])); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <aside class="space-y-8">
                <div class="sticky top-32">
                    <?php
                        $targetDecisionId = $decision['id'];
                        include __DIR__ . '/components/simulator-ui.php';
                    ?>
                </div>
            </aside>
        </div>
    </main>

    <script>
    async function deleteDecision(id) {
        if (!confirm("Are you sure you want to delete this strategic log? It will be removed from your Moat IQ history.")) return;
        
        try {
            const res = await fetch('/api/delete-decision.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            });
            const data = await res.json();
            if (data.success) window.location.href = '/dashboard.php';
            else alert(data.error);
        } catch (e) { alert("Deletion failed."); }
    }
    </script>
    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
