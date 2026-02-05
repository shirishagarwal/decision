<?php
/**
 * File Path: decision.php
 * Description: High-fidelity decision detail view with Markdown support for readability.
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
    die("Intelligence Retrieval Error.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title><?php echo htmlspecialchars($decision['title']); ?> | DecisionVault</title>
    <?php include __DIR__ . '/includes/head-meta.php'; ?>
    <!-- Markdown Parser -->
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <style>
        body { background-color: #f8fafc; color: #0f172a; }
        .markdown-content h1, .markdown-content h2, .markdown-content h3 { font-weight: 800; margin-top: 1.5rem; margin-bottom: 0.5rem; }
        .markdown-content p { margin-bottom: 1rem; line-height: 1.7; }
        .markdown-content ul { list-style-type: disc; margin-left: 1.5rem; margin-bottom: 1rem; }
        .markdown-content strong { color: #0f172a; font-weight: 700; }
    </style>
</head>
<body class="selection:bg-indigo-100 min-h-screen flex flex-col">

    <?php include __DIR__ . '/includes/header.php'; ?>

    <main class="max-w-7xl mx-auto py-12 px-6 flex-grow w-full">
        <div class="grid lg:grid-cols-3 gap-12">
            
            <div class="lg:col-span-2 space-y-12">
                <header>
                    <div class="flex justify-between items-center mb-6">
                        <div class="flex items-center gap-3">
                            <span class="px-3 py-1 bg-indigo-50 text-indigo-600 text-[10px] font-black uppercase tracking-widest rounded-full border border-indigo-100">
                                <?php echo htmlspecialchars($decision['category'] ?: 'Strategic'); ?>
                            </span>
                            <a href="/dashboard.php" class="text-[10px] font-black text-slate-400 hover:text-indigo-600 uppercase tracking-widest transition">← Vault</a>
                        </div>
                        <div class="flex items-center gap-4">
                            <a href="/edit-decision.php?id=<?php echo $decision['id']; ?>" class="text-[10px] font-black text-slate-400 hover:text-indigo-600 uppercase tracking-widest transition">Edit</a>
                            <button onclick="deleteDecision(<?php echo $decision['id']; ?>)" class="text-[10px] font-black text-red-300 hover:text-red-500 uppercase tracking-widest transition">Delete</button>
                        </div>
                    </div>

                    <h1 class="text-5xl md:text-6xl font-black text-slate-900 tracking-tighter leading-none mb-8">
                        <?php echo htmlspecialchars($decision['title']); ?>
                    </h1>

                    <?php if ($decision['status'] === 'Implemented'): ?>
                        <div class="mb-8 p-8 bg-emerald-50 border border-emerald-100 rounded-[2.5rem] animate-fade-in">
                            <div class="text-[10px] font-black text-emerald-600 uppercase tracking-widest mb-2">Outcome Loop Closed</div>
                            <div class="text-2xl font-black text-slate-900 capitalize"><?php echo str_replace('_', ' ', $decision['review_rating']); ?></div>
                            <div class="markdown-content text-sm text-slate-600 mt-2" data-markdown><?php echo htmlspecialchars($decision['actual_outcome']); ?></div>
                        </div>
                    <?php endif; ?>

                    <div class="p-10 premium-card">
                        <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Core Problem</h3>
                        <div class="markdown-content text-xl text-slate-600 font-medium" data-markdown>
                            <?php echo htmlspecialchars($decision['problem_statement']); ?>
                        </div>
                    </div>
                </header>

                <!-- Learning Loop -->
                <?php if ($decision['status'] !== 'Implemented'): ?>
                    <section class="p-10 premium-card bg-indigo-600 text-white border-none shadow-2xl shadow-indigo-100">
                        <h2 class="text-[10px] font-black text-indigo-200 uppercase tracking-[0.2em] mb-6">The Learning Loop</h2>
                        <form id="reviewForm" class="space-y-6">
                            <input type="hidden" name="decision_id" value="<?php echo $decision['id']; ?>">
                            <div class="grid grid-cols-5 gap-2">
                                <?php foreach(['much_worse' => '😞', 'worse' => '🙁', 'as_expected' => '😐', 'better' => '🙂', 'much_better' => '🚀'] as $k => $emoji): ?>
                                    <label class="cursor-pointer group">
                                        <input type="radio" name="rating" value="<?php echo $k; ?>" class="hidden peer" required>
                                        <div class="p-4 bg-white/10 rounded-2xl text-center border border-white/10 peer-checked:bg-white peer-checked:text-indigo-600 transition-all">
                                            <div class="text-2xl mb-1"><?php echo $emoji; ?></div>
                                            <div class="text-[8px] font-black uppercase opacity-60"><?php echo str_replace('_', ' ', $k); ?></div>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <textarea name="outcome" class="w-full p-4 bg-white/10 border border-white/10 rounded-2xl text-white outline-none focus:border-white h-24 placeholder:text-indigo-300/50" placeholder="What actually happened?"></textarea>
                            <button type="submit" id="reviewBtn" class="w-full bg-white text-indigo-600 py-4 rounded-2xl font-black uppercase tracking-widest text-xs flex items-center justify-center gap-3">Verify Result</button>
                        </form>
                    </section>
                <?php endif; ?>

                <section>
                    <h2 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-8">Evaluated Paths</h2>
                    <div class="space-y-6">
                        <?php foreach($options as $opt): ?>
                            <div class="p-8 premium-card hover:border-indigo-200 transition-all group">
                                <h3 class="text-2xl font-bold text-slate-900 group-hover:text-indigo-600 transition mb-4"><?php echo htmlspecialchars($opt['name']); ?></h3>
                                <div class="markdown-content text-slate-500 font-medium" data-markdown><?php echo htmlspecialchars($opt['description']); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            </div>

            <aside class="space-y-8">
                <div class="p-8 premium-card bg-slate-900 text-white sticky top-32">
                    <div class="flex items-center gap-2 mb-8">
                        <div class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></div>
                        <h2 class="text-[10px] font-black text-red-400 uppercase tracking-widest">Aggressive Stress Test</h2>
                    </div>
                    <div id="sim-content" class="<?php echo $simulation ? '' : 'hidden'; ?> space-y-6">
                        <div class="p-4 bg-white/5 border border-white/10 rounded-2xl">
                            <div class="text-[10px] font-black text-red-500 mb-1 uppercase">Day 365 Autopsy</div>
                            <p id="d365" class="text-xs text-slate-300 leading-relaxed"><?php echo $simulation['day365'] ?? ''; ?></p>
                        </div>
                        <div class="pt-4 border-t border-white/10">
                            <div class="text-[10px] font-black text-indigo-400 mb-1 uppercase">Mitigation</div>
                            <p id="mitigation" class="text-xs text-indigo-100 font-bold italic"><?php echo $simulation['mitigation_plan'] ?? ''; ?></p>
                        </div>
                    </div>
                    <button onclick="runStressTest()" id="simBtn" class="w-full bg-red-600 text-white py-4 rounded-2xl font-black text-sm uppercase tracking-widest mt-6">
                        <?php echo $simulation ? 'Re-Run Test' : 'Simulate Failure'; ?>
                    </button>
                </div>
            </aside>
        </div>
    </main>

    <script>
    // Initialize Markdown Rendering
    document.querySelectorAll('[data-markdown]').forEach(el => {
        el.innerHTML = marked.parse(el.innerText || el.textContent);
    });

    async function runStressTest() {
        const btn = document.getElementById('simBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="loader-spin"></span> ANALYZING...';
        try {
            const res = await fetch(`/api/simulate.php?id=<?php echo $decision['id']; ?>`);
            const json = await res.json();
            if (json.success) {
                document.getElementById('d365').innerText = json.data.day365;
                document.getElementById('mitigation').innerText = json.data.mitigation;
                document.getElementById('sim-content').classList.remove('hidden');
                btn.innerHTML = 'RE-RUN TEST';
            }
        } catch (e) { alert("Simulation failed."); } finally { btn.disabled = false; }
    }

    document.getElementById('reviewForm')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = document.getElementById('reviewBtn');
        btn.disabled = true;
        const data = Object.fromEntries(new FormData(e.target).entries());
        const res = await fetch('/api/update-outcome.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        if ((await res.json()).success) window.location.reload();
    });

    async function deleteDecision(id) {
        if (confirm("Confirm deletion of this strategic log?")) {
            const res = await fetch('/api/delete-decision.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            });
            if ((await res.json()).success) window.location.href = '/dashboard.php';
        }
    }
    </script>
</body>
</html>
