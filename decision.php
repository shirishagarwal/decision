<?php
/**
 * File Path: decision.php
 * Description: The definitive high-fidelity view for strategic decisions.
 * Restores visual depth, card shadows, and adds robust Markdown formatting.
 */
require_once __DIR__ . '/config.php';
requireLogin();

$decisionId = $_GET['id'] ?? null;
$pdo = getDbConnection();

try {
    // 1. Fetch Decision details
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

    // 2. Fetch Options
    $stmt = $pdo->prepare("SELECT * FROM decision_options WHERE decision_id = ? ORDER BY created_at ASC");
    $stmt->execute([$decisionId]);
    $options = $stmt->fetchAll();

    // 3. Fetch Simulation
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
    
    <!-- Favicon -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><path d=%22M50 5 L15 20 L15 45 C15 70 50 95 50 95 C50 95 85 70 85 45 L85 20 L50 5 Z%22 fill=%22%234f46e5%22 /><path d=%22M50 15 L25 25 L25 45 C25 62 50 82 50 82 C50 82 75 62 75 45 L75 25 L50 15 Z%22 fill=%22white%22 opacity=%220.2%22 /></svg>">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; color: #1e293b; }
        
        /* Premium UI Components */
        .glass-card {
            background: white;
            border: 1px solid #f1f5f9;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.03), 0 10px 10px -5px rgba(0, 0, 0, 0.02);
            border-radius: 2.5rem;
        }
        
        .premium-shadow {
            box-shadow: 0 0 0 1px rgba(0,0,0,0.05), 0 20px 50px 0 rgba(0,0,0,0.05);
        }

        /* Markdown Formatting Overrides */
        .markdown-body { font-size: 1rem; line-height: 1.7; color: #475569; }
        .markdown-body h1, .markdown-body h2, .markdown-body h3 {
            color: #0f172a; font-weight: 800; margin-top: 1.5rem; margin-bottom: 0.75rem; tracking: -0.025em;
        }
        .markdown-body p { margin-bottom: 1.25rem; }
        .markdown-body ul { list-style-type: disc; padding-left: 1.5rem; margin-bottom: 1.25rem; }
        .markdown-body strong { color: #0f172a; font-weight: 700; }
        .markdown-body blockquote {
            border-left: 4px solid #e2e8f0; padding-left: 1rem; italic; color: #64748b; margin: 1.5rem 0;
        }

        .loader-spin {
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top: 2px solid #ffffff;
            border-radius: 50%;
            width: 14px; height: 14px;
            animation: spin 1s linear infinite;
            display: inline-block;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body class="selection:bg-indigo-100 min-h-screen flex flex-col">

    <?php include __DIR__ . '/includes/header.php'; ?>

    <main class="max-w-7xl mx-auto py-16 px-6 flex-grow w-full">
        <div class="grid lg:grid-cols-3 gap-12">
            
            <!-- Strategic Narrative -->
            <div class="lg:col-span-2 space-y-12">
                <header>
                    <div class="flex justify-between items-center mb-8">
                        <div class="flex items-center gap-3">
                            <span class="px-4 py-1.5 bg-indigo-50 text-indigo-600 text-[10px] font-black uppercase tracking-[0.2em] rounded-full border border-indigo-100">
                                <?php echo htmlspecialchars($decision['category'] ?: 'Strategic Intelligence'); ?>
                            </span>
                            <a href="/dashboard.php" class="text-[10px] font-black text-slate-400 hover:text-indigo-600 uppercase tracking-widest transition">← Back to Vault</a>
                        </div>
                        
                        <div class="flex items-center gap-6">
                            <a href="/edit-decision.php?id=<?php echo $decision['id']; ?>" class="text-[10px] font-black text-slate-400 hover:text-indigo-600 uppercase tracking-widest transition">Edit</a>
                            <button onclick="deleteDecision(<?php echo $decision['id']; ?>)" class="text-[10px] font-black text-red-300 hover:text-red-500 uppercase tracking-widest transition">Delete</button>
                        </div>
                    </div>

                    <h1 class="text-5xl md:text-7xl font-black text-slate-900 tracking-tighter leading-[0.9] mb-12">
                        <?php echo htmlspecialchars($decision['title']); ?>
                    </h1>

                    <!-- Learning Loop Outcome -->
                    <?php if ($decision['status'] === 'Implemented'): ?>
                        <div class="mb-12 p-10 bg-emerald-50 border border-emerald-100 rounded-[3rem] premium-shadow animate-fade-in">
                            <div class="flex items-center gap-2 mb-4">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                <span class="text-[10px] font-black text-emerald-600 uppercase tracking-[0.2em]">Closed Learning Loop</span>
                            </div>
                            <div class="text-3xl font-black text-slate-900 capitalize mb-4">
                                <?php echo str_replace('_', ' ', $decision['review_rating']); ?>
                            </div>
                            <div class="markdown-body text-slate-600" data-markdown>
                                <?php echo htmlspecialchars($decision['actual_outcome']); ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="p-12 glass-card">
                        <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-6">Strategic Problem Statement</h3>
                        <div class="markdown-body text-xl font-medium" data-markdown>
                            <?php echo htmlspecialchars($decision['problem_statement']); ?>
                        </div>
                    </div>
                </header>

                <!-- Learning Loop Input -->
                <?php if ($decision['status'] !== 'Implemented'): ?>
                    <section class="p-12 glass-card bg-indigo-600 text-white border-none shadow-2xl shadow-indigo-100">
                        <h2 class="text-[10px] font-black text-indigo-300 uppercase tracking-[0.2em] mb-8">Close the Loop</h2>
                        <p class="text-xl font-bold mb-10 tracking-tight leading-relaxed">
                            To build a Strategic Moat, you must record the outcome. Was this path correct?
                        </p>
                        
                        <form id="reviewForm" class="space-y-8">
                            <input type="hidden" name="decision_id" value="<?php echo $decision['id']; ?>">
                            
                            <div class="grid grid-cols-5 gap-3">
                                <?php
                                $ratings = [
                                    'much_worse' => '😞', 'worse' => '🙁', 'as_expected' => '😐', 'better' => '🙂', 'much_better' => '🚀'
                                ];
                                foreach($ratings as $key => $emoji): ?>
                                    <label class="cursor-pointer group">
                                        <input type="radio" name="rating" value="<?php echo $key; ?>" class="hidden peer" required>
                                        <div class="p-5 bg-white/10 rounded-3xl text-center border border-white/10 peer-checked:bg-white peer-checked:text-indigo-600 peer-checked:border-white transition-all transform active:scale-95">
                                            <div class="text-3xl mb-2"><?php echo $emoji; ?></div>
                                            <div class="text-[8px] font-black uppercase tracking-tighter opacity-60">
                                                <?php echo str_replace('_', ' ', $key); ?>
                                            </div>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            </div>

                            <textarea name="outcome" class="w-full p-6 bg-white/10 border border-white/10 rounded-[2rem] text-white outline-none focus:border-white h-32 placeholder:text-indigo-300/50 text-sm font-medium" placeholder="Describe the actual outcome and lessons learned..."></textarea>

                            <button type="submit" id="reviewBtn" class="w-full bg-white text-indigo-600 py-5 rounded-[1.5rem] font-black uppercase tracking-widest text-xs flex items-center justify-center gap-3 hover:bg-indigo-50 transition-colors">
                                Verify Strategic Intelligence
                            </button>
                        </form>
                    </section>
                <?php endif; ?>

                <section>
                    <h2 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-10">Evaluated Strategic Paths</h2>
                    <div class="space-y-8">
                        <?php foreach($options as $opt): ?>
                            <div class="p-10 glass-card hover:border-indigo-200 transition-all group">
                                <div class="flex justify-between items-start mb-6">
                                    <h3 class="text-3xl font-black text-slate-900 group-hover:text-indigo-600 transition tracking-tight">
                                        <?php echo htmlspecialchars($opt['name']); ?>
                                    </h3>
                                    <?php if($opt['is_ai_suggested']): ?>
                                        <span class="bg-indigo-600 text-white text-[10px] font-black px-4 py-1.5 rounded-full uppercase tracking-widest">AI Pattern</span>
                                    <?php endif; ?>
                                </div>
                                <div class="markdown-body font-medium" data-markdown>
                                    <?php echo htmlspecialchars($opt['description']); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            </div>

            <!-- Intelligence Sidebar -->
            <aside class="space-y-8">
                <!-- User Profile -->
                <div class="p-8 glass-card">
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-6">Strategist</h3>
                    <div class="flex items-center gap-4">
                        <img src="<?php echo $decision['avatar_url']; ?>" class="w-14 h-14 rounded-full border-4 border-slate-50 shadow-sm">
                        <div>
                            <div class="font-black text-slate-900"><?php echo htmlspecialchars($decision['creator_name']); ?></div>
                            <div class="text-[10px] text-slate-400 font-black uppercase tracking-widest">Decision Lead</div>
                        </div>
                    </div>
                </div>

                <!-- Aggressive Stress Test -->
                <div class="sticky top-32">
                    <div class="p-10 glass-card bg-slate-900 text-white shadow-2xl shadow-indigo-200 border-none relative overflow-hidden">
                        <div class="relative z-10">
                            <div class="flex items-center gap-2 mb-10">
                                <div class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></div>
                                <h2 class="text-[10px] font-black text-red-400 uppercase tracking-[0.2em]">Aggressive Stress Test</h2>
                            </div>
                            
                            <div id="sim-content" class="<?php echo $simulation ? '' : 'hidden'; ?> space-y-8">
                                <div class="space-y-6">
                                    <div class="p-6 bg-white/5 border border-white/10 rounded-[2rem]">
                                        <div class="text-[10px] font-black text-red-500 mb-2 uppercase tracking-widest">Day 30 Red Flags</div>
                                        <p id="d30" class="text-xs text-slate-300 leading-relaxed font-medium"><?php echo $simulation['day30'] ?? ''; ?></p>
                                    </div>
                                    <div class="p-6 bg-white/5 border border-white/10 rounded-[2rem]">
                                        <div class="text-[10px] font-black text-red-500 mb-2 uppercase tracking-widest">Day 365 Autopsy</div>
                                        <p id="d365" class="text-xs text-slate-300 leading-relaxed font-medium"><?php echo $simulation['day365'] ?? ''; ?></p>
                                    </div>
                                </div>
                                <div class="pt-8 border-t border-white/10">
                                    <div class="text-[10px] font-black text-indigo-400 mb-3 uppercase tracking-widest">Required Mitigation</div>
                                    <p id="mitigation" class="text-sm text-indigo-100 font-bold italic leading-relaxed">
                                        <?php echo $simulation['mitigation_plan'] ?? ''; ?>
                                    </p>
                                </div>
                            </div>

                            <button onclick="runStressTest()" id="simBtn" class="w-full bg-red-600 text-white py-5 rounded-[1.5rem] font-black text-xs uppercase tracking-widest hover:bg-red-700 transition-all mt-8 flex items-center justify-center gap-3">
                                <?php echo $simulation ? 'Re-Run Simulation' : 'Simulate Failure'; ?>
                            </button>
                        </div>
                        <!-- Decorative glow -->
                        <div class="absolute -top-24 -right-24 w-64 h-64 bg-indigo-600/10 blur-[100px] rounded-full"></div>
                    </div>
                </div>
            </aside>
        </div>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>

    <script>
    // Initialize Markdown Rendering
    function renderMarkdown() {
        document.querySelectorAll('[data-markdown]').forEach(el => {
            const raw = el.innerText || el.textContent;
            el.innerHTML = marked.parse(raw.trim());
            el.removeAttribute('data-markdown'); // Prevent double rendering
            el.classList.add('markdown-body');
        });
    }
    
    window.addEventListener('DOMContentLoaded', renderMarkdown);

    // Stress Test Logic
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

    // Review Form Logic
    document.getElementById('reviewForm')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = document.getElementById('reviewBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="loader-spin"></span> SECURING LOOP...';

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
            btn.disabled = false;
            btn.innerHTML = 'VERIFY STRATEGIC INTELLIGENCE';
        }
    });

    // Deletion Logic
    async function deleteDecision(id) {
        if (!confirm("Permanently remove this strategic log? This will affect your Moat IQ score.")) return;
        try {
            const res = await fetch('/api/delete-decision.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            });
            const data = await res.json();
            if (data.success) window.location.href = '/dashboard.php';
        } catch (e) { alert("Deletion failed."); }
    }
    </script>
</body>
</html>
