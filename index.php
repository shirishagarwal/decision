<?php
/**
 * File Path: index.php
 * Description: Premium light-themed landing page showcasing DecisionVault's strategic features.
 */
require_once __DIR__ . '/config.php';

/**
 * We fetch the full user record instead of just checking isLoggedIn().
 * This ensures that if the database was wiped but a session cookie exists,
 * the user is forced to re-authenticate rather than hitting a null constraint error in the dashboard.
 */
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DecisionVault | Build Your Strategic Moat</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #ffffff; color: #0f172a; }
        .hero-gradient {
            background: radial-gradient(circle at 18.7% 37.8%, rgb(250, 250, 250) 0%, rgb(225, 234, 238) 90%);
        }
        .feature-card { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .feature-card:hover { transform: translateY(-5px); box-shadow: 0 20px 40px -10px rgba(0,0,0,0.05); }
    </style>
            <link rel="icon" type="image/svg+xml" href="/favicon.svg">
</head>
<body class="selection:bg-indigo-100">

    <!-- Sticky Navigation -->
    <nav class="fixed top-0 w-full z-50 bg-white/80 backdrop-blur-md border-b border-slate-100">
        <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
            <div class="flex items-center gap-8">
                <div class="text-2xl font-black tracking-tighter text-slate-900">DECISION<span class="text-indigo-600">VAULT</span></div>
                <div class="hidden md:flex items-center gap-6 text-sm font-bold text-slate-400 uppercase tracking-widest">
                    <a href="#features" class="hover:text-indigo-600 transition">Moat</a>
                    <a href="#intelligence" class="hover:text-indigo-600 transition">Intelligence</a>
                    <a href="#marketplace" class="hover:text-indigo-600 transition">Marketplace</a>
                </div>
            </div>
            
            <div class="flex items-center gap-4">
                <?php if($user): ?>
                    <a href="dashboard.php" class="bg-slate-900 text-white px-6 py-2.5 rounded-xl font-black text-xs uppercase tracking-widest shadow-xl shadow-slate-200 hover:bg-indigo-600 transition-all">Go to Vault</a>
                <?php else: ?>
                    <a href="auth/google.php" class="bg-indigo-600 text-white px-6 py-2.5 rounded-xl font-black text-xs uppercase tracking-widest shadow-xl shadow-indigo-200 hover:bg-indigo-700 transition-all">Launch Vault</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="pt-40 pb-32 px-6 overflow-hidden">
        <div class="max-w-7xl mx-auto text-center">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-indigo-50 border border-indigo-100 rounded-full text-indigo-600 text-[10px] font-black uppercase tracking-[0.2em] mb-12 animate-fade-in">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-600"></span>
                </span>
                Intelligence OS for Startups
            </div>
            
            <h1 class="text-6xl md:text-[8rem] font-black text-slate-900 tracking-tighter leading-[0.9] mb-12">
                Stop guessing.<br/>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-violet-600">Build the moat.</span>
            </h1>

            <p class="max-w-3xl mx-auto text-xl md:text-2xl text-slate-500 font-medium leading-relaxed mb-16">
                DecisionVault cross-references your strategy against 2,000+ historical failure patterns. Simulate the collapse before you spend a dollar.
            </p>

            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                <a href="auth/google.php" class="w-full sm:w-auto bg-slate-900 text-white px-12 py-6 rounded-2xl font-black text-xl shadow-2xl shadow-slate-200 hover:bg-indigo-600 transition-all transform hover:-translate-y-1">
                    Start Recording Strategy
                </a>
                <a href="#demo" class="w-full sm:w-auto px-12 py-6 border-2 border-slate-100 rounded-2xl font-bold text-xl text-slate-400 hover:bg-slate-50 transition-all">
                    Watch Demo
                </a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-32 bg-slate-50/50 border-y border-slate-100">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                
                <!-- Feature 1: Intelligence Moat -->
                <div class="bg-white p-10 rounded-[2.5rem] border border-slate-100 feature-card">
                    <div class="w-14 h-14 bg-indigo-50 rounded-2xl flex items-center justify-center text-2xl mb-8">🛡️</div>
                    <h3 class="text-xl font-black text-slate-900 mb-4 tracking-tight">Intelligence Moat</h3>
                    <p class="text-slate-500 text-sm leading-relaxed font-medium">
                        Access our proprietary library of 2,042 startup failure patterns to identify blind spots in your logic.
                    </p>
                </div>

                <!-- Feature 2: AI Stress Tests -->
                <div class="bg-white p-10 rounded-[2.5rem] border border-slate-100 feature-card">
                    <div class="w-14 h-14 bg-emerald-50 rounded-2xl flex items-center justify-center text-2xl mb-8">⚡</div>
                    <h3 class="text-xl font-black text-slate-900 mb-4 tracking-tight">AI Stress Tests</h3>
                    <p class="text-slate-500 text-sm leading-relaxed font-medium">
                        Our 'Chief Disaster Officer' AI simulates your decision's collapse at 30, 90, and 365 days.
                    </p>
                </div>

                <!-- Feature 3: Strategic IQ -->
                <div class="bg-white p-10 rounded-[2.5rem] border border-slate-100 feature-card">
                    <div class="w-14 h-14 bg-amber-50 rounded-2xl flex items-center justify-center text-2xl mb-8">📈</div>
                    <h3 class="text-xl font-black text-slate-900 mb-4 tracking-tight">Moat IQ</h3>
                    <p class="text-slate-500 text-sm leading-relaxed font-medium">
                        Track your organizational accuracy. Reward precision and close the strategic learning loop.
                    </p>
                </div>

                <!-- Feature 4: Marketplace -->
                <div class="bg-white p-10 rounded-[2.5rem] border border-slate-100 feature-card">
                    <div class="w-14 h-14 bg-indigo-600 rounded-2xl flex items-center justify-center text-2xl mb-8">🦄</div>
                    <h3 class="text-xl font-black text-slate-900 mb-4 tracking-tight">The Marketplace</h3>
                    <p class="text-slate-500 text-sm leading-relaxed font-medium">
                        Adopt proven decision templates from world-class operators. Don't reinvent the wheel.
                    </p>
                </div>

            </div>
        </div>
    </section>

    <!-- Social Proof/Stats -->
    <section class="py-32 px-6">
        <div class="max-w-7xl mx-auto">
            <div class="bg-slate-900 rounded-[3rem] p-12 md:p-24 text-center text-white relative overflow-hidden">
                <div class="relative z-10">
                    <h2 class="text-4xl md:text-6xl font-black tracking-tighter mb-16">
                        Trusted by the next generation of<br/>
                        <span class="text-indigo-400">Decisive Leaders.</span>
                    </h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-12">
                        <div>
                            <div class="text-5xl font-black mb-2 tracking-tighter">2,042</div>
                            <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Failure Patterns</div>
                        </div>
                        <div>
                            <div class="text-5xl font-black mb-2 tracking-tighter">14k+</div>
                            <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Decisions Audited</div>
                        </div>
                        <div>
                            <div class="text-5xl font-black mb-2 tracking-tighter">84%</div>
                            <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Reduction in Churn</div>
                        </div>
                        <div>
                            <div class="text-5xl font-black mb-2 tracking-tighter">TOP 1%</div>
                            <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Strategic Velocity</div>
                        </div>
                    </div>
                </div>
                <!-- Abstract Design -->
                <div class="absolute -top-20 -left-20 w-96 h-96 bg-indigo-600/20 blur-[120px] rounded-full"></div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="pb-40 pt-20 px-6">
        <div class="max-w-3xl mx-auto text-center">
            <h2 class="text-4xl font-black text-slate-900 tracking-tight mb-8">Ready to secure your strategy?</h2>
            <p class="text-slate-500 text-lg font-medium mb-12">Join 400+ high-growth teams building their defensive strategic moat.</p>
            <a href="auth/google.php" class="inline-block bg-indigo-600 text-white px-12 py-6 rounded-2xl font-black text-xl shadow-2xl shadow-indigo-200 hover:bg-indigo-700 transition-all">
                Enter the Vault
            </a>
            <div class="mt-8 text-slate-400 text-xs font-bold uppercase tracking-widest">
                No credit card required • GDPR Compliant • SSO Ready
            </div>
        </div>
    </section>

    <?php include __DIR__ . '/includes/footer.php'; ?>

</body>
</html>
