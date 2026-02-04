<?php
/**
 * File Path: index.php
 * Description: The premium, dark-mode landing page for the DecisionVault platform.
 */
require_once __DIR__ . '/config.php';
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
        body { font-family: 'Inter', sans-serif; background-color: #020617; color: #f8fafc; overflow-x: hidden; }
        .hero-glow { background: radial-gradient(circle at 50% 50%, rgba(79, 70, 229, 0.15) 0%, transparent 80%); }
        .text-gradient { background: linear-gradient(to right, #818cf8, #c084fc); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    </style>
</head>
<body class="hero-glow min-h-screen flex flex-col items-center justify-center p-6">

    <div class="max-w-5xl text-center relative z-10">
        <!-- Badge -->
        <div class="mb-12 inline-block px-4 py-1.5 border border-indigo-500/30 bg-indigo-500/5 rounded-full text-indigo-400 text-[10px] font-black tracking-[0.2em] uppercase">
            Strategic Intelligence for the Next Unicorn
        </div>
        
        <!-- Hero Headline -->
        <h1 class="text-7xl md:text-9xl font-black mb-10 tracking-tighter leading-[0.9]">
            BUILD THE <br/>
            <span class="text-gradient">UNFAILABLE.</span>
        </h1>
        
        <!-- Subheadline -->
        <p class="text-lg md:text-2xl text-slate-400 mb-14 max-w-2xl mx-auto leading-relaxed font-medium">
            Stop making high-stakes decisions in a vacuum. DecisionVault uses 2,000+ historical failure patterns to stress-test your strategy before you ship.
        </p>

        <!-- CTA Cluster -->
        <div class="flex flex-col sm:flex-row gap-6 justify-center items-center">
            <a href="/auth/google.php" class="group bg-white text-black px-12 py-6 rounded-3xl font-black text-2xl hover:scale-105 transition-all shadow-2xl shadow-indigo-500/20 flex items-center gap-3">
                Enter Your Vault
                <span class="text-indigo-600 group-hover:translate-x-1 transition-transform">→</span>
            </a>
            
            <a href="/onboarding/create-organization.php" class="px-10 py-5 rounded-3xl font-bold text-xl text-slate-400 hover:text-white hover:bg-white/5 transition-all">
                Business Onboarding
            </a>
        </div>

        <!-- Social Proof / Tickers -->
        <div class="mt-32 pt-12 border-t border-slate-900 grid grid-cols-2 md:grid-cols-4 gap-12 opacity-50 grayscale hover:grayscale-0 transition-all duration-700">
            <div>
                <div class="text-2xl font-black mb-1">2,000+</div>
                <div class="text-[10px] uppercase font-black tracking-widest text-slate-500">Failure Modes</div>
            </div>
            <div>
                <div class="text-2xl font-black mb-1">81%</div>
                <div class="text-[10px] uppercase font-black tracking-widest text-slate-500">Avg. Accuracy</div>
            </div>
            <div>
                <div class="text-2xl font-black mb-1">Zero</div>
                <div class="text-[10px] uppercase font-black tracking-widest text-slate-500">Blind Spots</div>
            </div>
            <div>
                <div class="text-2xl font-black mb-1">SECURE</div>
                <div class="text-[10px] uppercase font-black tracking-widest text-slate-500">Audit Trail</div>
            </div>
        </div>
    </div>

    <!-- Background Elements -->
    <div class="absolute top-0 left-0 w-full h-full pointer-events-none -z-10">
        <div class="absolute top-1/4 -left-20 w-96 h-96 bg-indigo-600/10 blur-[120px] rounded-full"></div>
        <div class="absolute bottom-1/4 -right-20 w-96 h-96 bg-purple-600/10 blur-[120px] rounded-full"></div>
    </div>

</body>
</html>
