<?php
require_once __DIR__ . '/config.php';

$isLoggedIn = isLoggedIn();
$user = $isLoggedIn ? getCurrentUser() : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DecisionVault - AI-Powered Decision Intelligence Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
        body { font-family: 'Inter', sans-serif; }
        
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .feature-card {
            transition: all 0.3s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(102, 126, 234, 0.2);
        }
        
        .stat-number {
            font-size: 3.5rem;
            font-weight: 900;
            line-height: 1;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        .floating {
            animation: float 6s ease-in-out infinite;
        }
    </style>
</head>
<body class="bg-white">
    
    <!-- Navigation -->
    <nav class="fixed w-full bg-white/90 backdrop-blur-md z-50 border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 gradient-bg rounded-lg flex items-center justify-center">
                        <span class="text-white font-bold text-xl">D</span>
                    </div>
                    <span class="text-2xl font-bold gradient-text">DecisionVault</span>
                </div>
                
                <div class="hidden md:flex items-center gap-8">
                    <a href="#features" class="text-gray-600 hover:text-purple-600 font-medium transition-colors">Features</a>
                    <a href="#vision" class="text-gray-600 hover:text-purple-600 font-medium transition-colors">Vision</a>
                    <a href="#pricing" class="text-gray-600 hover:text-purple-600 font-medium transition-colors">Pricing</a>
                    <?php if ($isLoggedIn): ?>
                        <a href="dashboard.php" class="bg-purple-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-purple-700 transition-colors">
                            Dashboard
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="text-gray-600 hover:text-purple-600 font-medium transition-colors">Login</a>
                        <a href="signup.php" class="bg-purple-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-purple-700 transition-colors">
                            Start Free Trial
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="pt-32 pb-20 px-4 sm:px-6 lg:px-8 bg-gradient-to-br from-purple-50 via-white to-pink-50">
        <div class="max-w-7xl mx-auto">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div>
                    <div class="inline-block mb-4 px-4 py-2 bg-purple-100 rounded-full">
                        <span class="text-purple-700 font-semibold text-sm">🚀 AI-Powered Decision Intelligence</span>
                    </div>
                    
                    <h1 class="text-5xl lg:text-6xl font-black text-gray-900 leading-tight mb-6">
                        Stop Guessing.<br/>
                        Start <span class="gradient-text">Knowing.</span>
                    </h1>
                    
                    <p class="text-xl text-gray-600 mb-8 leading-relaxed">
                        AI tells you <strong>which option to choose</strong> based on what actually worked for 2,000+ companies. 
                        No more flying blind on hiring, pricing, pivots, or funding decisions.
                    </p>
                    
                    <div class="flex flex-col sm:flex-row gap-4 mb-8">
                        <a href="<?php echo $isLoggedIn ? 'dashboard.php' : 'signup.php'; ?>" 
                           class="inline-flex items-center justify-center px-8 py-4 bg-purple-600 text-white rounded-lg font-bold text-lg hover:bg-purple-700 transition-all shadow-lg hover:shadow-xl">
                            Get Started Free
                            <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                        </a>
                        <a href="#demo" class="inline-flex items-center justify-center px-8 py-4 bg-white text-purple-600 rounded-lg font-bold text-lg border-2 border-purple-600 hover:bg-purple-50 transition-all">
                            Watch Demo
                            <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </a>
                    </div>
                    
                    <div class="flex items-center gap-6 text-sm text-gray-600">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Free 14-day trial</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>No credit card required</span>
                        </div>
                    </div>
                </div>
                
                <div class="relative">
                    <div class="floating">
                        <div class="bg-white rounded-2xl shadow-2xl p-6 border border-gray-200">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="font-bold text-gray-900">🤖 AI Recommendation</h3>
                                <span class="text-sm bg-green-100 text-green-700 px-3 py-1 rounded-full font-semibold">87% Confidence</span>
                            </div>
                            
                            <div class="bg-gradient-to-r from-purple-50 to-blue-50 rounded-lg p-4 mb-3">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <div class="text-sm text-gray-600 mb-1">✅ RECOMMENDED</div>
                                        <div class="font-bold text-lg text-gray-900">Promote from within</div>
                                        <div class="text-sm text-gray-600 mt-1">Success rate: 78%</div>
                                    </div>
                                    <div class="text-3xl font-black text-purple-600">78%</div>
                                </div>
                            </div>
                            
                            <div class="bg-gray-50 rounded-lg p-4 mb-4">
                                <div class="text-sm text-gray-600 mb-1">⚠️ NOT RECOMMENDED</div>
                                <div class="font-semibold text-gray-900">Hire senior external</div>
                                <div class="text-xs text-red-600 mt-2">⚠️ 23% of companies failed due to wrong senior hires</div>
                            </div>
                            
                            <div class="text-xs text-gray-500">
                                Based on 12 similar decisions from companies like yours
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Social Proof Stats -->
    <section class="py-16 bg-white border-y border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                <div>
                    <div class="stat-number gradient-text">2,000+</div>
                    <div class="text-gray-600 font-medium mt-2">Startups Analyzed</div>
                </div>
                <div>
                    <div class="stat-number gradient-text">78%</div>
                    <div class="text-gray-600 font-medium mt-2">Success Rate Increase</div>
                </div>
                <div>
                    <div class="stat-number gradient-text">$300K</div>
                    <div class="text-gray-600 font-medium mt-2">Avg. Savings Per Decision</div>
                </div>
                <div>
                    <div class="stat-number gradient-text">51,000%</div>
                    <div class="text-gray-600 font-medium mt-2">ROI</div>
                </div>
            </div>
        </div>
    </section>

    <!-- The Problem -->
    <section class="py-20 px-4 sm:px-6 lg:px-8 bg-gradient-to-br from-red-50 to-orange-50">
        <div class="max-w-4xl mx-auto text-center">
            <h2 class="text-4xl font-black text-gray-900 mb-6">
                You're Making $100K Decisions<br/>With Zero Data
            </h2>
            
            <div class="grid md:grid-cols-3 gap-6 mt-12">
                <div class="bg-white rounded-xl p-6 shadow-lg">
                    <div class="text-4xl mb-3">😰</div>
                    <h3 class="font-bold text-gray-900 mb-2">Hire the wrong VP</h3>
                    <p class="text-gray-600 text-sm">$200K salary + equity. Gone in 18 months. Team demoralized.</p>
                </div>
                
                <div class="bg-white rounded-xl p-6 shadow-lg">
                    <div class="text-4xl mb-3">📉</div>
                    <h3 class="font-bold text-gray-900 mb-2">Wrong pricing change</h3>
                    <p class="text-gray-600 text-sm">15% churn overnight. Months to recover lost revenue.</p>
                </div>
                
                <div class="bg-white rounded-xl p-6 shadow-lg">
                    <div class="text-4xl mb-3">💸</div>
                    <h3 class="font-bold text-gray-900 mb-2">Failed pivot</h3>
                    <p class="text-gray-600 text-sm">6 months building the wrong thing. Burn $500K. 82% of pivots fail.</p>
                </div>
            </div>
            
            <div class="mt-12 text-xl text-gray-700 font-semibold">
                <strong>The problem:</strong> You're guessing based on gut feeling, not data.
            </div>
        </div>
    </section>

    <!-- How It Works / Current Features -->
    <section id="features" class="py-20 px-4 sm:px-6 lg:px-8 bg-white">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16">
                <div class="inline-block mb-4 px-4 py-2 bg-purple-100 rounded-full">
                    <span class="text-purple-700 font-semibold text-sm">⚡ AVAILABLE NOW</span>
                </div>
                <h2 class="text-4xl font-black text-gray-900 mb-4">
                    How DecisionVault Works Today
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    The intelligent decision system that learns from 2,000+ real company failures
                </p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="feature-card bg-white rounded-2xl p-8 border-2 border-gray-200">
                    <div class="w-14 h-14 bg-purple-100 rounded-xl flex items-center justify-center mb-6">
                        <span class="text-3xl">🤖</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">AI Recommendations</h3>
                    <p class="text-gray-600 mb-4">
                        AI analyzes your decision and suggests options with success rates based on what actually worked for similar companies.
                    </p>
                    <div class="text-sm text-purple-600 font-semibold">
                        → Based on 2,000+ real failures
                    </div>
                </div>
                
                <!-- Feature 2 -->
                <div class="feature-card bg-white rounded-2xl p-8 border-2 border-gray-200">
                    <div class="w-14 h-14 bg-blue-100 rounded-xl flex items-center justify-center mb-6">
                        <span class="text-3xl">⚠️</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Failure Warnings</h3>
                    <p class="text-gray-600 mb-4">
                        See exactly why companies like yours failed. "23% failed due to wrong team" shows up BEFORE you make the mistake.
                    </p>
                    <div class="text-sm text-purple-600 font-semibold">
                        → Learn from others' $100M mistakes
                    </div>
                </div>
                
                <!-- Feature 3 -->
                <div class="feature-card bg-white rounded-2xl p-8 border-2 border-gray-200">
                    <div class="w-14 h-14 bg-green-100 rounded-xl flex items-center justify-center mb-6">
                        <span class="text-3xl">🔮</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Learning Loop</h3>
                    <p class="text-gray-600 mb-4">
                        Review decisions 3-6 months later. Track what worked. Your team gets smarter with every decision.
                    </p>
                    <div class="text-sm text-purple-600 font-semibold">
                        → Build institutional knowledge
                    </div>
                </div>
                
                <!-- Feature 4 -->
                <div class="feature-card bg-white rounded-2xl p-8 border-2 border-gray-200">
                    <div class="w-14 h-14 bg-yellow-100 rounded-xl flex items-center justify-center mb-6">
                        <span class="text-3xl">📊</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Pattern Detection</h3>
                    <p class="text-gray-600 mb-4">
                        AI spots patterns you'd miss. "You succeed 90% of the time when you involve engineering early."
                    </p>
                    <div class="text-sm text-purple-600 font-semibold">
                        → Know YOUR decision DNA
                    </div>
                </div>
                
                <!-- Feature 5 -->
                <div class="feature-card bg-white rounded-2xl p-8 border-2 border-gray-200">
                    <div class="w-14 h-14 bg-pink-100 rounded-xl flex items-center justify-center mb-6">
                        <span class="text-3xl">👥</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Team Collaboration</h3>
                    <p class="text-gray-600 mb-4">
                        Invite stakeholders, collect votes, track who chose what. Document the "why" before everyone forgets.
                    </p>
                    <div class="text-sm text-purple-600 font-semibold">
                        → Never repeat "wait, why did we do that?"
                    </div>
                </div>
                
                <!-- Feature 6 -->
                <div class="feature-card bg-white rounded-2xl p-8 border-2 border-gray-200">
                    <div class="w-14 h-14 bg-indigo-100 rounded-xl flex items-center justify-center mb-6">
                        <span class="text-3xl">📈</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Success Benchmarks</h3>
                    <p class="text-gray-600 mb-4">
                        Compare your decisions to industry standards. "78% of SaaS companies raised prices successfully."
                    </p>
                    <div class="text-sm text-purple-600 font-semibold">
                        → Data-driven confidence
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Vision / Roadmap -->
    <section id="vision" class="py-20 px-4 sm:px-6 lg:px-8 bg-gradient-to-br from-purple-900 via-purple-800 to-indigo-900 text-white">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16">
                <div class="inline-block mb-4 px-4 py-2 bg-white/20 backdrop-blur rounded-full">
                    <span class="text-white font-semibold text-sm">🚀 THE VISION</span>
                </div>
                <h2 class="text-4xl lg:text-5xl font-black mb-4">
                    Where We're Going
                </h2>
                <p class="text-xl text-purple-200 max-w-3xl mx-auto">
                    From decision tracker → to decision intelligence platform → to AI decision co-pilot
                </p>
            </div>
            
            <!-- Timeline -->
            <div class="max-w-4xl mx-auto">
                <!-- Phase 1: NOW -->
                <div class="relative pl-8 pb-12 border-l-4 border-green-400">
                    <div class="absolute -left-3 top-0 w-6 h-6 bg-green-400 rounded-full border-4 border-purple-900"></div>
                    <div class="bg-white/10 backdrop-blur rounded-xl p-6">
                        <div class="flex items-center gap-3 mb-3">
                            <span class="text-sm font-bold bg-green-400 text-purple-900 px-3 py-1 rounded-full">NOW</span>
                            <h3 class="text-2xl font-bold">Phase 1: Decision Intelligence</h3>
                        </div>
                        <p class="text-purple-200 mb-4">
                            ✅ AI recommends options based on 2,000+ real company failures<br/>
                            ✅ Pattern detection from your own decisions<br/>
                            ✅ Learning loop with review reminders<br/>
                            ✅ Team collaboration & voting
                        </p>
                        <div class="text-sm text-green-300 font-semibold">
                            → LIVE TODAY
                        </div>
                    </div>
                </div>
                
                <!-- Phase 2: Next 3 Months -->
                <div class="relative pl-8 pb-12 border-l-4 border-yellow-400">
                    <div class="absolute -left-3 top-0 w-6 h-6 bg-yellow-400 rounded-full border-4 border-purple-900"></div>
                    <div class="bg-white/10 backdrop-blur rounded-xl p-6">
                        <div class="flex items-center gap-3 mb-3">
                            <span class="text-sm font-bold bg-yellow-400 text-purple-900 px-3 py-1 rounded-full">Q2 2026</span>
                            <h3 class="text-2xl font-bold">Phase 2: Predictive Intelligence</h3>
                        </div>
                        <p class="text-purple-200 mb-4">
                            🔄 Real-time data feeds (layoffs, funding, news)<br/>
                            🎯 Personalized success predictions based on YOUR company profile<br/>
                            📊 Industry-specific benchmarks (SaaS, Fintech, E-commerce)<br/>
                            🤝 Integration with Slack, Notion, Linear
                        </p>
                        <div class="text-sm text-yellow-300 font-semibold">
                            → In Development
                        </div>
                    </div>
                </div>
                
                <!-- Phase 3: 6-12 Months -->
                <div class="relative pl-8 pb-12 border-l-4 border-blue-400">
                    <div class="absolute -left-3 top-0 w-6 h-6 bg-blue-400 rounded-full border-4 border-purple-900"></div>
                    <div class="bg-white/10 backdrop-blur rounded-xl p-6">
                        <div class="flex items-center gap-3 mb-3">
                            <span class="text-sm font-bold bg-blue-400 text-purple-900 px-3 py-1 rounded-full">Q3-Q4 2026</span>
                            <h3 class="text-2xl font-bold">Phase 3: Proactive Co-Pilot</h3>
                        </div>
                        <p class="text-purple-200 mb-4">
                            🔮 AI predicts WHICH decisions you should make next quarter<br/>
                            ⚡ "You should plan a VP Sales hire in Q3 based on your growth"<br/>
                            🎯 Automated decision workflows (approvals, reminders, escalations)<br/>
                            📈 ROI tracking: "This decision saved you $200K"
                        </p>
                        <div class="text-sm text-blue-300 font-semibold">
                            → Planned
                        </div>
                    </div>
                </div>
                
                <!-- Phase 4: Future Vision -->
                <div class="relative pl-8">
                    <div class="absolute -left-3 top-0 w-6 h-6 bg-purple-400 rounded-full border-4 border-purple-900"></div>
                    <div class="bg-white/10 backdrop-blur rounded-xl p-6">
                        <div class="flex items-center gap-3 mb-3">
                            <span class="text-sm font-bold bg-purple-400 text-white px-3 py-1 rounded-full">2027+</span>
                            <h3 class="text-2xl font-bold">Phase 4: Decision Autopilot</h3>
                        </div>
                        <p class="text-purple-200 mb-4">
                            🤖 AI makes low-stakes decisions automatically (with your approval)<br/>
                            🌐 Cross-company decision intelligence network<br/>
                            🎓 Decision playbooks trained on YOUR company's unique patterns<br/>
                            💡 "Companies like yours that made this decision succeeded 94% of the time"
                        </p>
                        <div class="text-sm text-purple-300 font-semibold">
                            → Vision
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-16">
                <p class="text-2xl text-white font-bold mb-6">
                    The endgame: <span class="text-yellow-300">Never make a bad decision again.</span>
                </p>
                <a href="<?php echo $isLoggedIn ? 'dashboard.php' : 'signup.php'; ?>" 
                   class="inline-flex items-center px-8 py-4 bg-white text-purple-900 rounded-lg font-bold text-lg hover:bg-gray-100 transition-all shadow-lg">
                    Join the Journey
                    <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- Pricing -->
    <section id="pricing" class="py-20 px-4 sm:px-6 lg:px-8 bg-gray-50">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-black text-gray-900 mb-4">
                    Simple, Transparent Pricing
                </h2>
                <p class="text-xl text-gray-600">
                    One bad decision costs $100K+. We cost $49/month.
                </p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8 max-w-5xl mx-auto">
                <!-- Starter -->
                <div class="bg-white rounded-2xl p-8 border-2 border-gray-200">
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Starter</h3>
                    <div class="mb-6">
                        <span class="text-4xl font-black text-gray-900">Free</span>
                        <span class="text-gray-600">/forever</span>
                    </div>
                    <ul class="space-y-3 mb-8">
                        <li class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-green-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                            </svg>
                            <span class="text-gray-700">5 decisions/month</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-green-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                            </svg>
                            <span class="text-gray-700">Basic AI recommendations</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-green-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                            </svg>
                            <span class="text-gray-700">3 team members</span>
                        </li>
                    </ul>
                    <a href="<?php echo $isLoggedIn ? 'dashboard.php' : 'signup.php'; ?>" 
                       class="block text-center px-6 py-3 bg-gray-100 text-gray-900 rounded-lg font-semibold hover:bg-gray-200 transition-colors">
                        Get Started
                    </a>
                </div>
                
                <!-- Pro (Popular) -->
                <div class="bg-gradient-to-br from-purple-600 to-indigo-600 rounded-2xl p-8 border-2 border-purple-600 relative transform scale-105">
                    <div class="absolute -top-4 left-1/2 transform -translate-x-1/2">
                        <span class="bg-yellow-400 text-purple-900 px-4 py-1 rounded-full text-sm font-bold">MOST POPULAR</span>
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-2">Pro</h3>
                    <div class="mb-6">
                        <span class="text-4xl font-black text-white">$49</span>
                        <span class="text-purple-200">/month</span>
                    </div>
                    <ul class="space-y-3 mb-8 text-white">
                        <li class="flex items-start gap-2">
                            <svg class="w-5 h-5 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                            </svg>
                            <span>Unlimited decisions</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-5 h-5 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                            </svg>
                            <span>Full AI recommendations + warnings</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-5 h-5 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                            </svg>
                            <span>Unlimited team members</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-5 h-5 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                            </svg>
                            <span>Learning loop & reviews</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-5 h-5 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                            </svg>
                            <span>Pattern detection</span>
                        </li>
                    </ul>
                    <a href="<?php echo $isLoggedIn ? 'dashboard.php' : 'signup.php'; ?>" 
                       class="block text-center px-6 py-3 bg-white text-purple-600 rounded-lg font-bold hover:bg-gray-100 transition-colors">
                        Start Free Trial
                    </a>
                </div>
                
                <!-- Enterprise -->
                <div class="bg-white rounded-2xl p-8 border-2 border-gray-200">
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Enterprise</h3>
                    <div class="mb-6">
                        <span class="text-4xl font-black text-gray-900">Custom</span>
                    </div>
                    <ul class="space-y-3 mb-8">
                        <li class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-green-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                            </svg>
                            <span class="text-gray-700">Everything in Pro</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-green-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                            </svg>
                            <span class="text-gray-700">Custom integrations</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-green-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                            </svg>
                            <span class="text-gray-700">Dedicated support</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-green-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                            </svg>
                            <span class="text-gray-700">SSO & compliance</span>
                        </li>
                    </ul>
                    <a href="mailto:sales@decisionvault.com" 
                       class="block text-center px-6 py-3 bg-gray-100 text-gray-900 rounded-lg font-semibold hover:bg-gray-200 transition-colors">
                        Contact Sales
                    </a>
                </div>
            </div>
            
            <div class="text-center mt-12 text-gray-600">
                <p class="text-lg">
                    💡 <strong>ROI Calculator:</strong> One prevented $200K bad hire = 340 months of Pro ($49/mo)
                </p>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 px-4 sm:px-6 lg:px-8 gradient-bg">
        <div class="max-w-4xl mx-auto text-center text-white">
            <h2 class="text-4xl lg:text-5xl font-black mb-6">
                Ready to Stop Guessing?
            </h2>
            <p class="text-xl mb-8 text-purple-100">
                Join companies making smarter decisions with AI-powered intelligence.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="<?php echo $isLoggedIn ? 'dashboard.php' : 'signup.php'; ?>" 
                   class="inline-flex items-center justify-center px-8 py-4 bg-white text-purple-600 rounded-lg font-bold text-lg hover:bg-gray-100 transition-all shadow-lg">
                    Start Free Trial
                    <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
                <a href="mailto:sales@decisionvault.com" 
                   class="inline-flex items-center justify-center px-8 py-4 bg-purple-800 text-white rounded-lg font-bold text-lg border-2 border-white hover:bg-purple-700 transition-all">
                    Talk to Sales
                </a>
            </div>
            <p class="mt-6 text-purple-200 text-sm">
                14-day free trial • No credit card required • Cancel anytime
            </p>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-400 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="grid md:grid-cols-4 gap-8">
                <div>
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-8 h-8 gradient-bg rounded-lg flex items-center justify-center">
                            <span class="text-white font-bold">D</span>
                        </div>
                        <span class="text-white font-bold text-lg">DecisionVault</span>
                    </div>
                    <p class="text-sm">
                        AI-powered decision intelligence for modern teams.
                    </p>
                </div>
                
                <div>
                    <h4 class="text-white font-semibold mb-4">Product</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#features" class="hover:text-white transition-colors">Features</a></li>
                        <li><a href="#vision" class="hover:text-white transition-colors">Roadmap</a></li>
                        <li><a href="#pricing" class="hover:text-white transition-colors">Pricing</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="text-white font-semibold mb-4">Company</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="about.php" class="hover:text-white transition-colors">About</a></li>
                        <li><a href="mailto:sales@decisionvault.com" class="hover:text-white transition-colors">Contact</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="text-white font-semibold mb-4">Legal</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="privacy.php" class="hover:text-white transition-colors">Privacy</a></li>
                        <li><a href="terms.php" class="hover:text-white transition-colors">Terms</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-800 mt-12 pt-8 text-center text-sm">
                <p>&copy; <?php echo date('Y'); ?> DecisionVault. All rights reserved.</p>
            </div>
        </div>
    </footer>

</body>
</html>