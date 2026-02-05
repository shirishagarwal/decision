<?php
/**
 * File Path: index.php
 * Description: High-conversion landing page for the Decision Intelligence OS.
 */
require_once __DIR__ . '/config.php';
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>DecisionVault | The Strategic Intelligence OS</title>
    <?php include __DIR__ . '/includes/head-meta.php'; ?>
    <style>
        .hero-mesh {
            background-color: #ffffff;
            background-image: radial-gradient(at 0% 0%, rgba(79, 70, 229, 0.05) 0px, transparent 50%),
                              radial-gradient(at 100% 0%, rgba(124, 58, 237, 0.05) 0px, transparent 50%);
        }
        .feature-hover:hover { transform: translateY(-8px); }
    </style>
</head>
<body class="selection:bg-indigo-100 hero-mesh">

    <!-- Premium Navigation -->
    <nav class="fixed top-0 w-full z-50 bg-white/70 backdrop-blur-xl border-b border-slate-100">
        <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
            <div class="flex items-center gap-10">
                <div class="text-2xl font-black tracking-tighter text-slate-900">DECISION<span class="text-indigo-600">VAULT</span></div>
                <div class="hidden lg:flex items-center gap-8 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
                    <a href="#moat" class="hover:text-indigo-600 transition">The Moat</a>
                    <a href="#intelligence" class="hover:text-indigo-600 transition">Intelligence</a>
                    <a href="#marketplace" class="hover:text-indigo-600 transition">Marketplace</a>
                </div>
            </div>
            
            <div class="flex items-center gap-4">
                <?php if($user): ?>
                    <a href="dashboard.php" class="bg-slate-900 text-white px-6 py-2.5 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-indigo-600 transition-all">Go to Vault</a>
                <?php else: ?>
                    <a href="auth/google.php" class="bg-indigo-600 text-white px-8 py-3 rounded-xl font-black text-xs uppercase tracking-widest shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition-all">Launch Vault</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Hero: The Big Idea -->
    <section class="pt-48 pb-32 px-6">
        <div class="max-w-5xl mx-auto text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-50 rounded-full text-indigo-600 text-[10px] font-black uppercase tracking-[0.2em] mb-12 border border-indigo-100">
                <span class="w-1.5 h-1.5 rounded-full bg-indigo-600 animate-pulse"></span>
                Strategic Velocity Monitoring Active
            </div>
            
            <h1 class="text-6xl md:text-8xl font-black text-slate-900 tracking-tighter leading-[0.9] mb-10">
                Don't just make decisions.<br/>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-violet-600">Build an Intelligence Moat.</span>
            </h1>

            <p class="max-w-2xl mx-auto text-xl text-slate-500 font-medium leading-relaxed mb-16">
                Most companies fail because they forget <strong>why</strong> they made a choice. DecisionVault cross-references your strategy against 2,000+ historical failure patterns and simulates the collapse before you spend a single dollar.
            </p>

            <div class="flex flex-col sm:flex-row gap-5 justify-center">
                <a href="auth/google.php" class="bg-slate-900 text-white px-12 py-6 rounded-2xl font-black text-xl shadow-2xl shadow-slate-200 hover:bg-indigo-600 transition-all transform hover:-translate-y-1">
                    Start Strategic Recording
                </a>
                <a href="#features" class="px-12 py-6 border-2 border-slate-100 rounded-2xl font-bold text-xl text-slate-400 hover:bg-slate-50 transition-all">
                    Explore the Methodology
                </a>
            </div>
        </div>
    </section>

    <!-- The Moat Statistics -->
    <section class="py-24 px-6 border-y border-slate-50 bg-slate-50/30">
        <div class="max-w-7xl mx-auto grid grid-cols-2 md:grid-cols-4 gap-12 text-center">
            <div>
                <div class="text-4xl font-black text-slate-900 mb-1">2,042</div>
                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Failure Patterns</div>
            </div>
            <div>
                <div class="text-4xl font-black text-slate-900 mb-1">84%</div>
                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Logic Retention</div>
            </div>
            <div>
                <div class="text-4xl font-black text-slate-900 mb-1">Top 1%</div>
                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Strategic Velocity</div>
            </div>
            <div>
                <div class="text-4xl font-black text-slate-900 mb-1">$0</div>
                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Wasted on Drift</div>
            </div>
        </div>
    </section>

    <!-- Features: The Three Pillars -->
    <section id="features" class="py-40 px-6">
        <div class="max-w-7xl mx-auto">
            <div class="grid lg:grid-cols-3 gap-16">
                <!-- Pillar 1 -->
                <div class="space-y-8 feature-hover transition-all duration-500">
                    <div class="w-20 h-20 bg-indigo-600 rounded-[2.5rem] flex items-center justify-center text-3xl shadow-2xl shadow-indigo-200 text-white">🛡️</div>
                    <h3 class="text-3xl font-black text-slate-900 tracking-tight">Intercept Failure Before it Happens.</h3>
                    <p class="text-slate-500 leading-relaxed font-medium">
                        Our <strong>Aggressive Stress Test</strong> simulates 12 months of failure. We identify the "Day 30 Red Flags" your team would normally ignore.
                    </p>
                </div>
                <!-- Pillar 2 -->
                <div class="space-y-8 feature-hover transition-all duration-500">
                    <div class="w-20 h-20 bg-emerald-500 rounded-[2.5rem] flex items-center justify-center text-3xl shadow-2xl shadow-emerald-100 text-white">📈</div>
                    <h3 class="text-3xl font-black text-slate-900 tracking-tight">Track Your Organizational IQ.</h3>
                    <p class="text-slate-500 leading-relaxed font-medium">
                        Close the loop. Every decision is tracked. By recording actual outcomes, the OS calculates your accuracy and rewards strategic precision.
                    </p>
                </div>
                <!-- Pillar 3 -->
                <div class="space-y-8 feature-hover transition-all duration-500">
                    <div class="w-20 h-20 bg-slate-900 rounded-[2.5rem] flex items-center justify-center text-3xl shadow-2xl shadow-slate-200 text-white">🦄</div>
                    <h3 class="text-3xl font-black text-slate-900 tracking-tight">Adopt the Best Strategic Patterns.</h3>
                    <p class="text-slate-500 leading-relaxed font-medium">
                        Access the <strong>Marketplace</strong> to adopt proven logic templates from world-class operators. Stop reinventing the wheel.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Closing CTA -->
    <section class="py-40 px-6 bg-slate-900 text-white rounded-t-[5rem] overflow-hidden relative">
        <div class="max-w-4xl mx-auto text-center relative z-10">
            <h2 class="text-5xl md:text-7xl font-black tracking-tighter mb-12">
                Stop operating in the dark.<br/>
                <span class="text-indigo-400">Capture the logic.</span>
            </h2>
            <a href="auth/google.php" class="inline-block bg-white text-slate-900 px-16 py-8 rounded-[2.5rem] font-black text-2xl shadow-2xl hover:scale-105 transition-transform">
                Enter the DecisionVault
            </a>
            <p class="mt-12 text-slate-500 font-bold uppercase tracking-[0.3em] text-[10px]">GDPR Compliant • SSO Integrated • Logic First</p>
        </div>
        <!-- Abstract Background -->
        <div class="absolute top-0 left-0 w-full h-full opacity-10 pointer-events-none">
            <div class="absolute top-[-20%] left-[-10%] w-[60%] h-[100%] bg-indigo-600 blur-[150px] rounded-full"></div>
            <div class="absolute bottom-[-20%] right-[-10%] w-[60%] h-[100%] bg-violet-600 blur-[150px] rounded-full"></div>
        </div>
    </section>

    <?php include __DIR__ . '/includes/footer.php'; ?>

</body>
</html>
