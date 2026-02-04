<?php
/**
 * File Path: index.php
 * Description: Premium, dark-mode focused landing page with high-end typography and mesh gradients.
 */
require_once __DIR__ . '/config.php';
$isLoggedIn = isLoggedIn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DecisionVault | Build the Unfailable</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #020617; }
        .mesh-gradient {
            background-image: radial-gradient(at 0% 0%, hsla(253,16%,7%,1) 0, transparent 50%),
                              radial-gradient(at 50% 0%, hsla(225,39%,30%,1) 0, transparent 50%),
                              radial-gradient(at 100% 0%, hsla(339,49%,30%,1) 0, transparent 50%);
        }
    </style>
</head>
<body class="mesh-gradient min-h-screen text-white selection:bg-indigo-500/30">
    <nav class="max-w-7xl mx-auto px-6 h-24 flex items-center justify-between relative z-10">
        <div class="text-2xl font-black tracking-tighter">DECISION<span class="text-indigo-500">VAULT</span></div>
        <?php if($isLoggedIn): ?>
            <a href="dashboard.php" class="bg-white text-black px-6 py-2.5 rounded-full font-bold text-sm">Dashboard</a>
        <?php else: ?>
            <a href="auth/google.php" class="bg-indigo-600 text-white px-6 py-2.5 rounded-full font-bold text-sm shadow-xl shadow-indigo-500/20">Enter Vault</a>
        <?php endif; ?>
    </nav>

    <main class="max-w-7xl mx-auto px-6 pt-24 pb-32 relative z-10 text-center">
        <div class="inline-block px-4 py-1.5 border border-indigo-500/30 rounded-full bg-indigo-500/5 text-indigo-400 text-[10px] font-black uppercase tracking-[0.2em] mb-12">
            AI-Powered Strategic Intelligence
        </div>
        
        <h1 class="text-7xl md:text-[10rem] font-black tracking-tighter leading-[0.85] mb-12">
            BUILD THE<br/><span class="text-indigo-500">UNFAILABLE.</span>
        </h1>

        <p class="max-w-2xl mx-auto text-xl md:text-2xl text-slate-400 font-medium leading-relaxed mb-16">
            Stop guessing. DecisionVault uses 2,000+ historical failure patterns to stress-test your strategy before you ship.
        </p>

        <div class="flex flex-col sm:flex-row gap-6 justify-center">
            <a href="auth/google.php" class="bg-white text-black px-12 py-6 rounded-[2rem] font-black text-2xl hover:scale-105 transition-all shadow-2xl">Start Free Trial</a>
            <a href="#features" class="border border-slate-800 text-slate-400 px-12 py-6 rounded-[2rem] font-bold text-2xl hover:bg-white/5 transition-all">Watch Demo</a>
        </div>

        <div class="mt-40 grid grid-cols-2 md:grid-cols-4 gap-12 opacity-40">
            <div>
                <div class="text-4xl font-black mb-2">2,000+</div>
                <div class="text-[10px] font-bold uppercase tracking-widest">Failure Patterns</div>
            </div>
            <div>
                <div class="text-4xl font-black mb-2">81%</div>
                <div class="text-[10px] font-bold uppercase tracking-widest">Accuracy Rate</div>
            </div>
            <div>
                <div class="text-4xl font-black mb-2">ROI</div>
                <div class="text-[10px] font-bold uppercase tracking-widest">Guaranteed</div>
            </div>
            <div>
                <div class="text-4xl font-black mb-2">SECURE</div>
                <div class="text-[10px] font-bold uppercase tracking-widest">Audit Trails</div>
            </div>
        </div>
    </main>
</body>
</html>
