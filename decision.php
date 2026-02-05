<?php
/**
 * File Path: decision.php
 * Description: The high-fidelity view where the Moat IQ, Stress Tests, and Learning Loop live.
 */
require_once __DIR__ . '/config.php';
requireLogin();

$decisionId = $_GET['id'] ?? null;
$pdo = getDbConnection();

try {
    // 1. Fetch Decision details with creator info
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

    // 2. Fetch Evaluated Options
    $stmt = $pdo->prepare("SELECT * FROM decision_options WHERE decision_id = ? ORDER BY created_at ASC");
    $stmt->execute([$decisionId]);
    $options = $stmt->fetchAll();

    // 3. Fetch Existing Simulation result
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($decision['title']); ?> | DecisionVault</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; color: #0f172a; }
        .premium-card { background: white; border: 1px solid #f1f5f9; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); border-radius: 32px; }
        
        .loader-spin {
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top: 2px solid #ffffff;
            border-radius: 50%;
            width: 14px;
            height: 14px;
            animation: spin 1s linear infinite;
            display: inline-block;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body class="selection:bg-indigo-100 min-h-screen flex flex-col">

    <?php include __DIR__ . '/includes/header.php'; ?>

    <main class="max-w-7xl mx-auto py-12 px-6 flex-grow w-full">
        <div class="grid lg:grid-cols-3 gap-12">
            
            <!-- Left Column: Narrative & Review -->
            <div class="lg:col-span-2 space-y-12">
                <header>
                    <div class="flex justify-between items-center mb-6">
                        <div class="flex items-center gap-3">
                            <span class="px-3 py-1 bg-indigo-50 text-indigo-600 text-[10px] font-black uppercase tracking-widest rounded-full border border-indigo-100">
                                <?php echo htmlspecialchars($decision['category'] ?: 'Strategic'); ?>
                            </span>
                            <a href="/dashboard.php" class="text-[10px] font-black text-slate-400 hover:text-indigo-600 uppercase tracking-widest transition">← Vault</a>
                        </div>
                        
                        <!-- Actions Menu -->
                        <div class="flex items-center gap-4">
                            <a href="/edit-decision.php?id=<?php echo $decision['id']; ?>" class="text-[10px] font-black text-slate-400 hover:text-indigo-600 uppercase tracking-widest transition">Edit</a>
                            <button onclick="deleteDecision(<?php echo $decision['id']; ?>)" class="text-[10px] font-black text-red-300 hover:text-red-500 uppercase tracking-widest transition">Delete</button>
                        </div>
                    </div>

                    <h1 class="text-5xl md:text-6xl font-black text-slate-900 tracking-tighter leading-none mb-8">
                        <?php echo htmlspecialchars($decision['title']); ?>
                    </h1>

                    <!-- Success State -->
                    <?php if ($decision['status'] === 'Implemented'): ?>
                        <div class="mb-8 p-8 bg-emerald-50 border border-emerald-100 rounded-[2.5rem] animate-fade-in">
                            <div class="text-[10px] font-black text-emerald-600 uppercase tracking-widest mb-2">Outcome Loop Closed</div>
                            <div class="text-2xl font-black text-slate-900 capitalize"><?php echo str_replace('_', ' ', $decision['review_rating']); ?></div>
                            <p class="text-sm text-slate-600 mt-2 font-medium leading-relaxed"><?php echo htmlspecialchars($decision['actual_outcome']); ?></p>
                        </div>
                    <?php endif; ?>

                    <div class="p-10 premium-card">
                        <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Core Problem</h3>
                        <p class="text-xl text-slate-600 leading-relaxed font-medium">
                            <?php echo nl2br(htmlspecialchars($decision['problem_statement'])); ?>
                        </p>
                    </div>
                </header>

                <!-- Strategic Loop / Review Form -->
                <?php if ($decision['status'] !== 'Implemented'): ?>
                    <section class="p-10 premium-card bg-indigo-600 text-white border-none shadow-2xl shadow-indigo-100">
                        <h2 class="text-[10px] font-black text-indigo-200 uppercase tracking-[0.2em] mb-6">The Learning Loop</h2>
                        <p class="text-lg font-bold mb-8 tracking-tight">Was this strategic path correct? Record the outcome to boost your Moat IQ.</p>
                        
                        <form id="reviewForm" class="space-y-6">
                            <input type="hidden" name="decision_id" value="<?php echo $decision['id']; ?>">
                            
                            <div>
                                <label class="block text-[10px] font-black text-indigo-300 uppercase tracking-widest mb-4">Outcome Rating</label>
                                <div class="grid grid-cols-5 gap-2">
                                    <?php
                                    $ratings = [
                                        'much_worse' => '😞',
                                        'worse' => '🙁',
                                        'as_expected' => '😐',
                                        'better' => '🙂',
                                        'much_better' => '🚀'
                                    ];
                                    foreach($ratings as $key => $emoji): ?>
                                        <label class="cursor-pointer group">
                                            <input type="radio" name="rating" value="<?php echo $key; ?>" class="hidden peer" required>
                                            <div class="p-4 bg-white/10 rounded-2xl text-center border border-white/10 peer-checked:bg-white peer-checked:text-indigo-600 peer-checked:border-white transition-all">
                                                <div class="text-2xl mb-1"><?php echo $emoji; ?></div>
                                                <div class="text-[8px] font-black uppercase tracking-tighter opacity-60">
                                                    <?php echo str_replace('_', ' ', $key); ?>
                                                </div>
                                            </div>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-indigo-300 uppercase tracking-widest mb-4">Actual Outcome / Lessons</label>
                                <textarea name="outcome" class="w-full p-4 bg-white/10 border border-white/10 rounded-2xl text-white outline-none focus:border-white h-24 placeholder:text-indigo-300/50" placeholder="What actually happened? What did the AI miss?"></textarea>
                            </div>

                            <button type="submit" id="reviewBtn" class="w-full bg-white text-indigo-600 py-4 rounded-2xl font-black uppercase tracking-widest text-xs flex items-center justify-center gap-3 hover:scale-[1.02] transition-transform">
                                Verify Strategic Result
                            </button>
                        </form>
                    </section>
                <?php endif; ?>

                <section>
                    <h2 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-8">Evaluated Paths</h2>
                    <div class="space-y-6">
                        <?php foreach($options as $opt): ?>
                            <div class="p-8 premium-card hover:border-indigo-200 transition-all group">
                                <div class="flex justify-between items-start mb-4">
                                    <h3 class="text-2xl font-bold text-slate-900 group-hover:text-indigo-600 transition"><?php echo htmlspecialchars($opt['name']); ?></h3>
                                    <?php if($opt['is_ai_suggested']): ?>
                                        <span class="bg-indigo-50 text-indigo-600 text-[10px] font-black px-3 py-1 rounded-full uppercase">AI Pattern</span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-slate-500 leading-relaxed text-base font-medium"><?php echo nl2br(htmlspecialchars($opt['description'])); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            </div>

            <!-- Stress Test Sidebar -->
            <aside class="space-y-8">
                <!-- Simulation Engine Card -->
                <div class="p-8 premium-card bg-slate-900 text-white shadow-2xl shadow-indigo-200/50 border-none relative overflow-hidden sticky top-32">
                    <div class="relative z-10">
                        <div class="flex items-center gap-2 mb-8">
                            <div class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></div>
                            <h2 class="text-[10px] font-black text-red-400 uppercase tracking-widest">Aggressive Stress Test</h2>
                        </div>
                        
                        <div id="sim-content" class="<?php echo $simulation ? '' : 'hidden'; ?> space-y-6">
                            <div class="space-y-4">
                                <div class="p-4 bg-white/5 border border-white/10 rounded-2xl">
                                    <div class="text-[10px] font-black text-red-500 mb-1 uppercase">Day 30 Red Flags</div>
                                    <p id="d30" class="text-xs text-slate-300 leading-relaxed font-medium"><?php echo $simulation['day30'] ?? ''; ?></p>
                                </div>
                                <div class="p-4 bg-white/5 border border-white/10 rounded-2xl">
                                    <div class="text-[10px] font-black text-red-500 mb-1 uppercase">Day 365 Autopsy</div>
                                    <p id="d365" class="text-xs text-slate-300 leading-relaxed font-medium"><?php echo $simulation['day365'] ?? ''; ?></p>
                                </div>
                            </div>
                            <div class="pt-4 border-t border-white/10">
                                <div class="text-[10px] font-black text-indigo-400 mb-1 uppercase">Recommended Mitigation</div>
                                <p id="mitigation" class="text-xs text-indigo-100 font-bold italic"><?php echo $simulation['mitigation_plan'] ?? ''; ?></p>
                            </div>
                        </div>

                        <button onclick="runStressTest()" id="simBtn" class="w-full bg-red-600 text-white py-4 rounded-2xl font-black text-sm uppercase tracking-widest hover:bg-red-700 transition-all mt-6 flex items-center justify-center gap-2">
                            <?php echo $simulation ? 'Re-Run Simulation' : 'Simulate Failure'; ?>
                        </button>
                    </div>
                </div>
            </aside>
        </div>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>

    <script>
    // 1. Stress Test Logic
    async function runStressTest() {
        const btn = document.getElementById('simBtn');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="loader-spin"></span> ANALYZING...';

        try {
            const res = await fetch(`/api/simulate.php?id=<?php echo $decision['id']; ?>`);
            const json = await res.json();
            if (json.success) {
                document.getElementById('d30').innerText = json.data.day30;
                document.getElementById('d365').innerText = json.data.day365;
                document.getElementById('mitigation').innerText = json.data.mitigation;
                document.getElementById('sim-content').classList.remove('hidden');
                btn.innerHTML = 'RE-RUN SIMULATION';
            } else {
                throw new Error(json.error);
            }
        } catch (e) {
            alert("Simulation failed: " + e.message);
            btn.innerHTML = originalText;
        } finally {
            btn.disabled = false;
        }
    }

    // 2. Learning Loop Logic
    document.getElementById('reviewForm')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = document.getElementById('reviewBtn');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="loader-spin"></span> SAVING...';
        btn.disabled = true;

        const data = Object.fromEntries(new FormData(e.target).entries());
        try {
            const res = await fetch('/api/update-outcome.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const result = await res.json();
            if (result.success) {
                window.location.reload();
            } else {
                throw new Error(result.error);
            }
        } catch (err) {
            alert(err.message);
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    });

    // 3. Deletion Logic
    async function deleteDecision(id) {
        if (!confirm("Are you sure you want to delete this strategic log? This action cannot be undone.")) return;
        
        try {
            const res = await fetch('/api/delete-decision.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            });
            const data = await res.json();
            if (data.success) {
                window.location.href = '/dashboard.php';
            } else {
                alert(data.error);
            }
        } catch (e) {
            alert("Deletion failed.");
        }
    }
    </script>
</body>
</html>
