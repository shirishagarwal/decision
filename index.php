<?php require_once __DIR__ . '/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>DecisionVault | Build the Unfailable</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #020617; color: #f8fafc; }
        .glow { background: radial-gradient(circle at 50% 50%, rgba(79, 70, 229, 0.15) 0%, transparent 80%); }
    </style>
</head>
<body class="glow min-h-screen flex flex-col items-center justify-center p-6">
    <div class="max-w-4xl text-center">
        <div class="mb-12 inline-block px-4 py-1 border border-indigo-500/30 rounded-full text-indigo-400 text-xs font-bold tracking-widest uppercase">
            Strategic Intelligence for Startups
        </div>
        
        <h1 class="text-7xl md:text-9xl font-black mb-8 tracking-tighter leading-none">
            BUILD THE <br/>
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-purple-600">UNFAILABLE.</span>
        </h1>
        
        <p class="text-xl text-slate-400 mb-12 max-w-2xl mx-auto leading-relaxed">
            Stop making decisions in a vacuum. DecisionVault uses 2,000+ historical failure patterns to stress-test your logic before you ship.
        </p>

        <div class="flex flex-col sm:flex-row gap-6 justify-center">
            <a href="/auth/google.php" class="bg-white text-black px-12 py-5 rounded-2xl font-black text-2xl hover:scale-105 transition shadow-2xl">
                Enter the Vault
            </a>
            <a href="/business.php" class="border border-slate-800 text-slate-400 px-12 py-5 rounded-2xl font-bold text-2xl hover:text-white transition">
                Business Plans
            </a>
        </div>

        <div class="mt-24 grid grid-cols-3 gap-12 opacity-40 grayscale hover:grayscale-0 transition-all duration-500">
            <div class="font-black text-sm uppercase tracking-widest">Predictive Strategy</div>
            <div class="font-black text-sm uppercase tracking-widest">Decision IQ</div>
            <div class="font-black text-sm uppercase tracking-widest">Compliance Ready</div>
        </div>
    </div>
</body>
</html>
