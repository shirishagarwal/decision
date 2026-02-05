<?php
/**
 * File Path: decision.php
 * Description: The high-fidelity view for a strategic decision.
 * Updated: Restored Outcome Review loop and kept Edit/Delete actions.
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
        .loader {
            border: 2px solid #ffffff44;
            border-top: 2px solid #ffffff;
            border-radius: 50%;
            width: 14px;
            height: 14px;
            animation: spin 1s linear infinite;
            display: inline-block;
            margin-right: 8px;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
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

                <!-- Strategic Loop / Review Section -->
                <?php if ($decision['status'] !== 'Implemented'): ?>
                <section class="p-10 premium-card bg-indigo-600 text-white border-none shadow-2xl shadow-indigo-100">
                    <h2 class="text-[10px] font-black text-indigo-200 uppercase tracking-[0.2em] mb-6">Learning Loop</h2>
                    <p class="text-lg font-bold mb-8 tracking-tight">Was this path correct? Boost your IQ by recording the result.</p>
                    <form id="reviewForm" class="space-y-6">
                        <input type="hidden" name="decision_id" value="<?php echo $decision['id']; ?>">
                        <div class="grid grid-cols-5 gap-2">
                            <?php foreach(['much_worse', 'worse', 'as_expected', 'better', 'much_better'] as $k): ?>
                                <label class="cursor-pointer group">
                                    <input type="radio" name="rating" value="<?php echo $k; ?>" class="hidden peer" required>
                                    <div class="p-4 bg-white/10 rounded-2xl border border-white/10 peer-checked:bg-white peer-checked:text-indigo-600 transition-all text-center">
                                        <div class="text-[8px] font-black uppercase tracking-tighter opacity-60"><?php echo str_replace('_', ' ', $k); ?></div>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <textarea name="outcome" class="w-full p-4 bg-white/10 border border-white/10 rounded-2xl text-white outline-none focus:border-white h-24 placeholder:text-indigo-300/50" placeholder="Actual outcome and lessons learned..."></textarea>
                        <button type="submit" id="reviewBtn" class="w-full bg-white text-indigo-600 py-4 rounded-2xl font-black uppercase tracking-widest text-xs flex items-center justify-center">
                            Verify Strategic Result
                        </button>
                    </form>
                </section>
                <?php endif; ?>

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
                <div class="p-8 premium-card">
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-6">Lead Strategist</h3>
                    <div class="flex items-center gap-4">
                        <img src="<?php echo $decision['avatar_url']; ?>" class="w-12 h-12 rounded-full border-2 border-slate-50">
                        <div>
                            <div class="font-bold text-slate-900"><?php echo htmlspecialchars($decision['creator_name']); ?></div>
                            <div class="text-[10px] text-slate-400 font-black uppercase tracking-widest">Decision Owner</div>
                        </div>
                    </div>
                </div>

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
    document.getElementById('reviewForm')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = document.getElementById('reviewBtn');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="loader"></span>VERIFYING...';
        btn.disabled = true;

        const data = Object.fromEntries(new FormData(e.target).entries());
        try {
            const res = await fetch('/api/update-outcome.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const result = await res.json();
            if (result.success) window.location.reload();
            else throw new Error(result.error);
        } catch (err) {
            alert(err.message);
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    });

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
