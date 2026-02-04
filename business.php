<?php
require_once __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DecisionVault for Business | Strategy Intelligence</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap');
        body { font-family: 'Inter', sans-serif; }
        .gradient-text {
            background: linear-gradient(to right, #6366f1, #ec4899);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>
<body class="bg-white text-gray-900">

    <header class="relative overflow-hidden bg-white pt-16 pb-32">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center">
                <nav class="flex justify-center space-x-8 mb-12">
                    <a href="/" class="font-bold text-gray-900">DecisionVault</a>
                    <a href="#features" class="text-gray-500 hover:text-gray-900">Features</a>
                    <a href="#pricing" class="text-gray-500 hover:text-gray-900">Pricing</a>
                </nav>
                
                <h1 class="text-6xl md:text-8xl font-black tracking-tight mb-8">
                    Stop Guessing. <br/>
                    <span class="gradient-text">Start Leading.</span>
                </h1>
                
                <p class="max-w-2xl mx-auto text-xl text-gray-600 mb-10 leading-relaxed">
                    The AI-powered "Black Box" for your organization's strategy. Turn team logic into measurable, data-backed intelligence.
                </p>

                <div class="flex flex-col sm:flex-row justify-center gap-4">
                    <a href="/onboarding/create-organization.php" class="bg-indigo-600 text-white px-10 py-5 rounded-2xl font-black text-xl shadow-2xl shadow-indigo-200 hover:bg-indigo-700 transition-all hover:scale-105">
                        Start 14-Day Free Trial
                    </a>
                    <a href="#demo" class="bg-white border-2 border-gray-100 text-gray-900 px-10 py-5 rounded-2xl font-bold text-xl hover:bg-gray-50 transition-all">
                        Watch the 2min Demo
                    </a>
                </div>
            </div>
        </div>
        
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full h-full -z-0 opacity-10">
            <div class="absolute top-0 left-0 w-96 h-96 bg-purple-400 rounded-full mix-blend-multiply filter blur-3xl"></div>
            <div class="absolute bottom-0 right-0 w-96 h-96 bg-indigo-400 rounded-full mix-blend-multiply filter blur-3xl"></div>
        </div>
    </header>

    <section id="features" class="py-24 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-20">
                <h2 class="text-4xl font-black text-gray-900">Enterprise-Grade Strategy Tools</h2>
                <p class="text-gray-500 mt-4">Everything you need to reach unicorn status.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-12">
                <div class="bg-white p-10 rounded-3xl shadow-sm border border-gray-100 hover:shadow-xl transition-all">
                    <div class="w-14 h-14 bg-indigo-100 rounded-2xl flex items-center justify-center mb-6">
                        <span class="text-2xl">🧠</span>
                    </div>
                    <h3 class="text-2xl font-bold mb-4">Predictive Strategy</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Our AI analyzes your options against 2,000+ real-world failure patterns. Avoid the mistakes that killed Quibi, Theranos, and Fast.co.
                    </p>
                </div>

                <div class="bg-white p-10 rounded-3xl shadow-sm border border-gray-100 hover:shadow-xl transition-all">
                    <div class="w-14 h-14 bg-pink-100 rounded-2xl flex items-center justify-center mb-6">
                        <span class="text-2xl">🌿</span>
                    </div>
                    <h3 class="text-2xl font-bold mb-4">Dependency Mapping</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Visualize your strategic critical path. Instantly see which decisions are blocking your product launch or series B.
                    </p>
                </div>

                <div class="bg-white p-10 rounded-3xl shadow-sm border border-gray-100 hover:shadow-xl transition-all">
                    <div class="w-14 h-14 bg-green-100 rounded-2xl flex items-center justify-center mb-6">
                        <span class="text-2xl">🗳️</span>
                    </div>
                    <h3 class="text-2xl font-bold mb-4">Team Consensus</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Capture team buy-in with structured voting. Document the 'why' behind every choice to build a permanent audit trail.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section class="py-24 bg-white border-y border-gray-100">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-12">
                <div>
                    <div class="text-5xl font-black text-indigo-600 mb-2">2k+</div>
                    <div class="text-sm font-bold text-gray-400 uppercase tracking-widest">Failure Patterns</div>
                </div>
                <div>
                    <div class="text-5xl font-black text-indigo-600 mb-2">81%</div>
                    <div class="text-sm font-bold text-gray-400 uppercase tracking-widest">Avg Accuracy</div>
                </div>
                <div>
                    <div class="text-5xl font-black text-indigo-600 mb-2">10min</div>
                    <div class="text-sm font-bold text-gray-400 uppercase tracking-widest">Setup Time</div>
                </div>
                <div>
                    <div class="text-5xl font-black text-indigo-600 mb-2">ROI</div>
                    <div class="text-sm font-bold text-gray-400 uppercase tracking-widest">Guaranteed</div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-32 bg-indigo-600">
        <div class="max-w-4xl mx-auto px-4 text-center text-white">
            <h2 class="text-5xl font-black mb-8">Ready to build the next unicorn?</h2>
            <p class="text-xl text-indigo-100 mb-12">
                Join 500+ startups documenting their strategic path to success.
            </p>
            <a href="/onboarding/create-organization.php" class="bg-white text-indigo-600 px-12 py-6 rounded-2xl font-black text-2xl hover:bg-gray-100 transition-all shadow-2xl">
                Get Started for Free
            </a>
            <p class="mt-8 text-sm text-indigo-300">No credit card required for 14-day trial.</p>
        </div>
    </section>

    <footer class="py-12 bg-white text-center text-gray-400 text-sm">
        &copy; <?php echo date('Y'); ?> DecisionVault. Built for the world's most ambitious teams.
    </footer>

</body>
</html>
