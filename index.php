<?php require_once __DIR__ . '/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>DecisionVault | The Intelligence OS for Strategy</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #030712; color: white; }
        .hero-gradient { background: radial-gradient(circle at 50% 50%, rgba(79, 70, 229, 0.2) 0%, rgba(3, 7, 18, 1) 70%); }
        .feature-card { background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.1); }
    </style>
</head>
<body>
    <div class="hero-gradient min-h-screen flex flex-col items-center justify-center px-4 pt-20">
        <nav class="absolute top-0 w-full max-w-7xl flex justify-between p-8 items-center">
            <div class="text-2xl font-black tracking-tighter">DECISION<span class="text-indigo-500">VAULT</span></div>
            <div class="flex gap-8 text-sm font-medium text-gray-400">
                <a href="/business.php" class="hover:text-white transition">Business</a>
                <a href="#simulator" class="hover:text-white transition">Simulator</a>
                <a href="/login.php" class="bg-white text-black px-4 py-2 rounded-full font-bold">Sign In</a>
            </div>
        </nav>

        <div class="text-center max-w-4xl">
            <div class="inline-block px-4 py-1 border border-indigo-500/30 rounded-full text-indigo-400 text-xs font-bold mb-8 tracking-widest uppercase">
                Now Powered by Gemini 2.5 Flash
            </div>
            <h1 class="text-7xl md:text-9xl font-black mb-8 tracking-tight leading-none">
                BUILD THE <br/><span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-purple-600">UNFAILABLE.</span>
            </h1>
            <p class="text-xl text-gray-400 mb-12 max-w-2xl mx-auto leading-relaxed">
                DecisionVault is the first Decision Intelligence OS. We use 2,000+ historical failure patterns to stress-test your strategy before you ship.
            </p>
            <div class="flex gap-4 justify-center">
                <a href="/onboarding/check.php" class="bg-indigo-600 px-10 py-5 rounded-2xl font-black text-xl hover:bg-indigo-700 transition shadow-2xl shadow-indigo-500/20">
                    Get Started Free
                </a>
            </div>
        </div>

        <div class="mt-24 w-full max-w-6xl rounded-t-3xl border-x border-t border-white/10 bg-white/5 p-4">
            <div class="rounded-t-2xl bg-gray-900 h-96 w-full flex items-center justify-center text-gray-600 font-mono text-sm">
                [ Interactive Decision Graph Visualization ]
            </div>
        </div>
    </div>

    <section class="max-w-7xl mx-auto py-32 px-4 grid md:grid-cols-3 gap-8">
        <div class="feature-card p-10 rounded-3xl md:col-span-2 overflow-hidden relative">
            <h3 class="text-3xl font-black mb-4">Worst Case Simulator</h3>
            <p class="text-gray-400 max-w-md">Our CDO (Chief Disaster Officer) AI runs 1,000+ simulations to find where your plan breaks. See the Day 30, 90, and 365 failure states before they happen.</p>
            <div class="absolute -bottom-10 -right-10 w-64 h-64 bg-indigo-600/20 blur-3xl"></div>
        </div>
        <div class="feature-card p-10 rounded-3xl">
            <h3 class="text-2xl font-black mb-4">Decision IQ</h3>
            <p class="text-gray-400">Measure your organization's strategic accuracy over time. Turn your past mistakes into your future edge.</p>
        </div>
        <div class="feature-card p-10 rounded-3xl">
            <h3 class="text-2xl font-black mb-4">Dependency Maps</h3>
            <p class="text-gray-400">Visualize the critical path. Identify which pending decisions are blocking your $100M roadmap.</p>
        </div>
        <div class="feature-card p-10 rounded-3xl md:col-span-2">
            <h3 class="text-3xl font-black mb-4">Enterprise Moat</h3>
            <p class="text-gray-400">Built for TechCorp-scale logic. Role-based voting, immutable audit trails, and automated compliance workflows.</p>
        </div>
    </section>
</body>
</html>
