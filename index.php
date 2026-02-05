<?php
/**
 * File Path: index.php
 * Description: Premium high-conversion landing page for DecisionVault.
 * Sells the "Intelligence Moat" and "Strategic Velocity" methodology.
 */
require_once __DIR__ . '/config.php';
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DecisionVault | The Strategic Intelligence OS</title>
    
    <!-- Favicon -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><path d=%22M50 5 L15 20 L15 45 C15 70 50 95 50 95 C50 95 85 70 85 45 L85 20 L50 5 Z%22 fill=%22%234f46e5%22 /><path d=%22M50 15 L25 25 L25 45 C25 62 50 82 50 82 C50 82 75 62 75 45 L75 25 L50 15 Z%22 fill=%22white%22 opacity=%220.2%22 /></svg>">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #ffffff; color: #0f172a; overflow-x: hidden; }
        
        .hero-mesh {
            background-image: radial-gradient(at 0% 0%, rgba(79, 70, 229, 0.08) 0px, transparent 50%),
                              radial-gradient(at 100% 0%, rgba(124, 58, 237, 0.05) 0px, transparent 50%),
                              radial-gradient(at 50% 100%, rgba(79, 70, 229, 0.05) 0px, transparent 50%);
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(241, 245, 249, 1);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.02), 0 10px 10px -5px rgba(0, 0, 0, 0.01);
            border-radius: 2.5rem;
        }

        .feature-hover { transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
        .feature-hover:hover { transform: translateY(-8px); box-shadow: 0 30px 60px -12px rgba(0, 0, 0, 0.05); }
        
        .gradient-text {
            background: linear-gradient(to right, #4f46e5, #7c3aed);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        .animate-float { animation: float 6s ease-in-out infinite; }
    </style>
</head>
<body class="selection:bg-indigo-100 hero-mesh">

    <!-- Premium Navigation -->
    <nav class="fixed top-0 w-full z-50 bg-white/70 backdrop-blur-xl border-b border-slate-100">
        <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
            <div class="flex items-center gap-10">
                <div class="text-2xl font-black tracking-tighter text-slate-900 group cursor-default">
                    DECISION<span class="text-indigo-600 transition-colors duration-300 group-hover:text-indigo-500">VAULT</span>
                </div>
                <div class="hidden lg:flex items-center gap-8 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
                    <a href="#moat" class="hover:text-indigo-600 transition">The Moat</a>
                    <a href="#intelligence" class="hover:text-indigo-600 transition">Intelligence</a>
                    <a href="#marketplace" class="hover:text-indigo-600 transition">Marketplace</a>
                </div>
            </div>
            
            <div class="flex items-center gap-4">
                <?php if($user): ?>
                    <a href="dashboard.php" class="bg-slate-900 text-white px-6 py-2.5 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-indigo-600 transition-all shadow-lg shadow-slate-200">Go to Vault</a>
                <?php else: ?>
                    <a href="auth/google.php" class="bg-indigo-600 text-white px-8 py-3 rounded-xl font-black text-xs uppercase tracking-widest shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition-all">Launch Vault</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Hero: The Strategic Command -->
    <section class="pt-48 pb-32 px-6 overflow-hidden">
        <div class="max-w-6xl mx-auto text-center relative">
            <div class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-50 rounded-full text-indigo-600 text-[10px] font-black uppercase tracking-[0.2em] mb-12 border border-indigo-100 animate-fade-in">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-600"></span>
                </span>
                Intelligence OS Deployment Active
            </div>
            
            <h1 class="text-6xl md:text-8xl lg:text-[7rem] font-black text-slate-900 tracking-tighter leading-[0.85] mb-12">
                Stop guessing.<br/>
                <span class="gradient-text">Build the Moat.</span>
            </h1>

            <p class="max-w-2xl mx-auto text-xl text-slate-500 font-medium leading-relaxed mb-16">
                Decisions are your most valuable assets. DecisionVault cross-references your strategy against <strong>2,000+ historical failure patterns</strong> and simulates your collapse before you spend a single dollar.
            </p>

            <div class="flex flex-col sm:flex-row gap-5 justify-center items-center">
                <a href="auth/google.php" class="w-full sm:w-auto bg-slate-900 text-white px-12 py-6 rounded-[2rem] font-black text-xl shadow-2xl shadow-slate-200 hover:bg-indigo-600 transition-all transform hover:-translate-y-1">
                    Enter the Vault
                </a>
                <a href="#intelligence" class="w-full sm:w-auto px-12 py-6 border-2 border-slate-100 rounded-[2rem] font-bold text-xl text-slate-400 hover:bg-slate-50 transition-all">
                    Explore Methodology
                </a>
            </div>

            <!-- Floating Decorative Element -->
            <div class="hidden lg:block absolute -top-20 -left-20 w-64 h-64 bg-indigo-100/30 blur-3xl rounded-full animate-float"></div>
        </div>
    </section>

    <!-- The Intelligence Metrics -->
    <section id="moat" class="py-24 px-6 border-y border-slate-50 bg-slate-50/30">
        <div class="max-w-7xl mx-auto grid grid-cols-2 lg:grid-cols-4 gap-12 text-center">
            <div class="space-y-2">
                <div class="text-5xl font-black text-slate-900 tracking-tighter">2,042</div>
                <div class="text-[10px] font-black text-slate-400 uppercase tracking-[0.25em]">Failure Patterns</div>
            </div>
            <div class="space-y-2">
                <div class="text-5xl font-black text-slate-900 tracking-tighter">84%</div>
                <div class="text-[10px] font-black text-slate-400 uppercase tracking-[0.25em]">Logic Retention</div>
            </div>
            <div class="space-y-2">
                <div class="text-5xl font-black text-slate-900 tracking-tighter">Top 1%</div>
                <div class="text-[10px] font-black text-slate-400 uppercase tracking-[0.25em]">Strategic Velocity</div>
            </div>
            <div class="space-y-2">
                <div class="text-5xl font-black text-slate-900 tracking-tighter">0%</div>
                <div class="text-[10px] font-black text-slate-400 uppercase tracking-[0.25em]">Context Drift</div>
            </div>
        </div>
    </section>

    <!-- Methodology: The Pre-Mortem -->
    <section id="intelligence" class="py-40 px-6">
        <div class="max-w-7xl mx-auto">
            <div class="grid lg:grid-cols-2 gap-24 items-center">
                <div class="space-y-10">
                    <div class="inline-flex px-4 py-2 bg-red-50 text-red-600 text-[10px] font-black uppercase tracking-widest rounded-full border border-red-100">
                        Aggressive Stress Testing
                    </div>
                    <h2 class="text-5xl font-black text-slate-900 tracking-tighter leading-tight">
                        Intercept failure 12 months before it happens.
                    </h2>
                    <p class="text-lg text-slate-500 font-medium leading-relaxed">
                        Optimism is the leading cause of startup death. Our <strong>"Chief Disaster Officer"</strong> AI performs a brutal pre-mortem on your logic, identifying "Day 30 Red Flags" and "Day 365 Autopsies" so you can pivot while it's still cheap.
                    </p>
                    <ul class="space-y-6">
                        <li class="flex items-start gap-4">
                            <div class="w-6 h-6 bg-indigo-600 rounded-full flex items-center justify-center shrink-0 mt-1">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <div>
                                <h4 class="font-black text-slate-900 text-sm uppercase tracking-widest mb-1">Moat IQ Monitoring</h4>
                                <p class="text-sm text-slate-500 font-medium">Track your organizational accuracy and decision velocity in real-time.</p>
                            </div>
                        </li>
                        <li class="flex items-start gap-4">
                            <div class="w-6 h-6 bg-indigo-600 rounded-full flex items-center justify-center shrink-0 mt-1">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <div>
                                <h4 class="font-black text-slate-900 text-sm uppercase tracking-widest mb-1">The Learning Loop</h4>
                                <p class="text-sm text-slate-500 font-medium">Close the feedback loop by recording outcomes and auditing AI predictions.</p>
                            </div>
                        </li>
                    </ul>
                </div>

                <div class="relative">
                    <div class="glass-card p-10 bg-slate-900 text-white shadow-3xl transform lg:rotate-2 relative z-10 border-none">
                        <div class="flex items-center gap-2 mb-8">
                            <div class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></div>
                            <span class="text-[10px] font-black text-red-400 uppercase tracking-widest">Running Stress Test...</span>
                        </div>
                        <div class="space-y-6">
                            <div class="p-4 bg-white/5 border border-white/10 rounded-2xl">
                                <div class="text-[8px] font-black text-red-500 mb-1 uppercase">Day 365 Autopsy</div>
                                <div class="text-xs text-slate-300 italic">"The aggressive UK expansion failed because customer acquisition costs (CAC) were modeled on US data, ignoring local competition..."</div>
                            </div>
                            <div class="p-4 bg-white/10 border border-white/20 rounded-2xl">
                                <div class="text-[8px] font-black text-indigo-400 mb-1 uppercase">Required Mitigation</div>
                                <div class="text-xs text-indigo-100 font-bold leading-relaxed">Verify local LTV data before committing to the Q3 marketing spend.</div>
                            </div>
                        </div>
                    </div>
                    <div class="absolute -inset-4 bg-indigo-500/10 blur-3xl rounded-[3rem] -z-0"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Marketplace Section -->
    <section id="marketplace" class="py-40 px-6 bg-slate-50/50">
        <div class="max-w-7xl mx-auto text-center mb-20">
            <h2 class="text-4xl md:text-5xl font-black text-slate-900 tracking-tighter mb-6">Don't reinvent the wheel.</h2>
            <p class="max-w-xl mx-auto text-slate-500 font-medium">Adopt proven strategic patterns from world-class operators in our Strategic Marketplace.</p>
        </div>
        <div class="max-w-7xl mx-auto grid md:grid-cols-3 gap-8">
            <div class="glass-card p-10 feature-hover">
                <div class="text-3xl mb-6">🦄</div>
                <h3 class="text-xl font-black text-slate-900 mb-4 tracking-tight">The Unicorn Hire</h3>
                <p class="text-sm text-slate-500 leading-relaxed font-medium mb-8">Hiring patterns for early-stage executives to avoid cultural debt and misaligned equity.</p>
                <div class="text-[10px] font-black text-indigo-600 uppercase">1,240 Uses</div>
            </div>
            <div class="glass-card p-10 feature-hover">
                <div class="text-3xl mb-6">📈</div>
                <h3 class="text-xl font-black text-slate-900 mb-4 tracking-tight">SaaS Pricing Pivot</h3>
                <p class="text-sm text-slate-500 leading-relaxed font-medium mb-8">Frameworks for moving from flat-fee to usage-based billing without mass churn.</p>
                <div class="text-[10px] font-black text-indigo-600 uppercase">850 Uses</div>
            </div>
            <div class="glass-card p-10 feature-hover">
                <div class="text-3xl mb-6">🌍</div>
                <h3 class="text-xl font-black text-slate-900 mb-4 tracking-tight">EMEA Expansion</h3>
                <p class="text-sm text-slate-500 leading-relaxed font-medium mb-8">Critical failure patterns for US-based startups entering the European market.</p>
                <div class="text-[10px] font-black text-indigo-600 uppercase">310 Uses</div>
            </div>
        </div>
    </section>

    <!-- Closing CTA -->
    <section class="py-40 px-6">
        <div class="max-w-4xl mx-auto text-center bg-slate-900 rounded-[4rem] p-16 md:p-24 relative overflow-hidden shadow-3xl">
            <div class="relative z-10">
                <h2 class="text-5xl md:text-6xl font-black text-white tracking-tighter mb-10 leading-none">
                    Capture the logic.<br/>
                    <span class="text-indigo-400">Secure the future.</span>
                </h2>
                <a href="auth/google.php" class="inline-block bg-white text-slate-900 px-16 py-8 rounded-[2.5rem] font-black text-2xl shadow-2xl hover:scale-105 transition-transform">
                    Initialize Your Vault
                </a>
                <p class="mt-12 text-slate-500 font-bold uppercase tracking-[0.3em] text-[10px]">GDPR Compliant • SSO Ready • Logic First</p>
            </div>
            <!-- Decorative Background Glow -->
            <div class="absolute top-0 right-0 w-[50%] h-[100%] bg-indigo-600/20 blur-[120px] rounded-full"></div>
            <div class="absolute bottom-0 left-0 w-[50%] h-[100%] bg-violet-600/10 blur-[120px] rounded-full"></div>
        </div>
    </section>

    <?php include __DIR__ . '/includes/footer.php'; ?>

</body>
</html>
