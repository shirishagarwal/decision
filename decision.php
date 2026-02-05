<?php
/**
 * File Path: decision.php
 * Description: View for a strategic decision with the "Learning Loop" review form.
 */
require_once __DIR__ . '/config.php';
requireLogin();

$decisionId = $_GET['id'] ?? null;
$pdo = getDbConnection();

$stmt = $pdo->prepare("SELECT d.*, u.name as creator_name FROM decisions d JOIN users u ON d.created_by = u.id WHERE d.id = ?");
$stmt->execute([$decisionId]);
$decision = $stmt->fetch();

if (!$decision) die("Decision not found.");

$stmt = $pdo->prepare("SELECT * FROM decision_options WHERE decision_id = ?");
$stmt->execute([$decisionId]);
$options = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT * FROM decision_simulations WHERE decision_id = ? LIMIT 1");
$stmt->execute([$decisionId]);
$simulation = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($decision['title']); ?> | DecisionVault</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap'); body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-50 min-h-screen">
    <?php include __DIR__ . '/includes/header.php'; ?>

    <main class="max-w-7xl mx-auto py-12 px-6">
        <div class="grid lg:grid-cols-3 gap-12">
            <div class="lg:col-span-2 space-y-12">
                <header>
                    <div class="text-[10px] font-black text-indigo-500 uppercase tracking-widest mb-4">Strategic Log #<?php echo $decision['id']; ?></div>
                    <h1 class="text-6xl font-black tracking-tighter mb-8"><?php echo htmlspecialchars($decision['title']); ?></h1>
                    
                    <?php if ($decision['status'] === 'Implemented'): ?>
                        <div class="bg-emerald-50 border border-emerald-100 p-8 rounded-[40px] mb-8">
                            <div class="text-[10px] font-black text-emerald-600 uppercase tracking-widest mb-2">Closed Loop Intelligence</div>
                            <div class="text-2xl font-black text-slate-900 capitalize"><?php echo str_replace('_', ' ', $decision['review_rating']); ?></div>
                            <p class="text-slate-600 mt-4 font-medium"><?php echo htmlspecialchars($decision['actual_outcome']); ?></p>
                        </div>
                    <?php endif; ?>

                    <div class="bg-white p-10 rounded-[40px] border shadow-sm">
                        <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Problem Context</h3>
                        <p class="text-xl text-slate-700 leading-relaxed font-medium"><?php echo nl2br(htmlspecialchars($decision['problem_statement'])); ?></p>
                    </div>
                </header>

                <?php if ($decision['status'] !== 'Implemented'): ?>
                    <section class="bg-indigo-600 p-10 rounded-[40px] text-white shadow-2xl shadow-indigo-200">
                        <h2 class="text-[10px] font-black text-indigo-300 uppercase tracking-widest mb-6">Learning Loop</h2>
                        <form id="reviewForm" class="space-y-6">
                            <input type="hidden" name="decision_id" value="<?php echo $decision['id']; ?>">
                            <div class="grid grid-cols-5 gap-2">
                                <?php foreach(['much_worse', 'worse', 'as_expected', 'better', 'much_better'] as $r): ?>
                                    <label class="cursor-pointer text-center group">
                                        <input type="radio" name="rating" value="<?php echo $r; ?>" class="hidden peer" required>
                                        <div class="p-4 bg-white/10 rounded-2xl border border-white/10 peer-checked:bg-white peer-checked:text-indigo-600 transition uppercase text-[8px] font-black"><?php echo str_replace('_', ' ', $r); ?></div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <textarea name="outcome" class="w-full bg-white/10 border border-white/10 p-4 rounded-2xl text-white h-24" placeholder="What actually happened?"></textarea>
                            <button class="w-full bg-white text-indigo-600 py-4 rounded-2xl font-black uppercase text-xs tracking-widest">Verify Outcome & Boost IQ</button>
                        </form>
                    </section>
                <?php endif; ?>

                <div class="space-y-6">
                    <h2 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Defined Paths</h2>
                    <?php foreach($options as $opt): ?>
                        <div class="bg-white p-8 rounded-[40px] border shadow-sm">
                            <h3 class="text-xl font-bold mb-2"><?php echo htmlspecialchars($opt['name']); ?></h3>
                            <p class="text-slate-500 text-sm leading-relaxed"><?php echo htmlspecialchars($opt['description']); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <aside class="space-y-8">
                <div class="sticky top-32">
                    <?php include __DIR__ . '/components/simulator-ui.php'; ?>
                </div>
            </aside>
        </div>
    </main>

    <script>
    document.getElementById('reviewForm')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const data = Object.fromEntries(new FormData(e.target).entries());
        const res = await fetch('/api/update-outcome.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        if ((await res.json()).success) window.location.reload();
    });
    </script>
    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
