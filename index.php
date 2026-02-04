<?php
/**
 * File Path: index.php
 * Description: Redesigned landing page with Debug Mode enabled to catch blank page errors.
 */

// TEMPORARY: Enable error reporting to debug the "blank page" issue
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ensure this path is correct based on your folder structure
if (!file_exists(__DIR__ . '/config.php')) {
    die("Fatal Error: config.php not found at " . __DIR__ . "/config.php");
}

require_once __DIR__ . '/config.php';

$isLoggedIn = isLoggedIn();
$user = $isLoggedIn ? getCurrentUser() : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DecisionVault - Build the Unfailable with AI Strategy</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
        body { font-family: 'Inter', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); }
        .gradient-text {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .feature-card { transition: all 0.3s ease; }
        .feature-card:hover { transform: translateY(-8px); box-shadow: 0 20px 40px rgba(79, 70, 229, 0.15); }
        .stat-number { font-size: 3.5rem; font-weight: 900; line-height: 1; }
        @keyframes float { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-20px); } }
        .floating { animation: float 6s ease-in-out infinite; }
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
                    <a href="#features" class="text-gray-600 hover:text-indigo-600 font-medium transition-colors">Features</a>
                    <a href="#vision" class="text-gray-600 hover:text-indigo-600 font-medium transition-colors">Vision</a>
                    <?php if ($isLoggedIn): ?>
                        <a href="dashboard.php" class="bg-indigo-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-indigo-700 transition-colors">Dashboard</a>
                    <?php else: ?>
                        <a href="auth/google.php" class="bg-indigo-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-indigo-700 transition-colors">Enter Vault</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="pt-32 pb-20 px-4 sm:px-6 lg:px-8 bg-gradient-to-br from-indigo-50 via-white to-purple-50">
        <div class="max-w-7xl mx-auto">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div>
                    <div class="inline-block mb-4 px-4 py-2 bg-indigo-100 rounded-full">
                        <span class="text-indigo-700 font-semibold text-sm">🚀 Strategic Intelligence</span>
                    </div>
                    <h1 class="text-5xl lg:text-7xl font-black text-gray-900 leading-[0.9] mb-8">
                        BUILD THE<br/>
                        <span class="gradient-text">UNFAILABLE.</span>
                    </h1>
                    <p class="text-xl text-gray-600 mb-10 leading-relaxed">
                        Stop guessing. DecisionVault uses <strong>2,000+ historical failure patterns</strong> to stress-test your strategy and recommend the path to success.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="auth/google.php" class="inline-flex items-center justify-center px-8 py-4 bg-indigo-600 text-white rounded-xl font-bold text-lg hover:bg-indigo-700 transition-all shadow-lg">
                            Get Started Free
                        </a>
                        <a href="onboarding/create-organization.php" class="inline-flex items-center justify-center px-8 py-4 bg-white text-indigo-600 rounded-xl font-bold text-lg border-2 border-indigo-600 hover:bg-indigo-50 transition-all">
                            Build Team Org
                        </a>
                    </div>
                </div>
                <div class="relative hidden lg:block">
                    <div class="floating bg-white rounded-2xl shadow-2xl p-8 border border-gray-200">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-bold text-gray-900">🤖 AI Recommendation</h3>
                            <span class="text-xs bg-green-100 text-green-700 px-3 py-1 rounded-full font-bold">87% CONFIDENCE</span>
                        </div>
                        <div class="bg-indigo-50 rounded-xl p-4 mb-4">
                            <div class="text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-1">Safe Path</div>
                            <div class="font-bold">Promote from within</div>
                            <p class="text-xs text-indigo-600 mt-1">78% success rate in your industry.</p>
                        </div>
                        <div class="bg-red-50 rounded-xl p-4">
                            <div class="text-[10px] font-black text-red-400 uppercase tracking-widest mb-1">Danger Zone</div>
                            <div class="font-bold text-gray-900">External Senior Hire</div>
                            <p class="text-xs text-red-600 mt-1">23% of startups failed at this stage due to culture mismatch.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats -->
    <section class="py-16 bg-white border-y border-gray-100">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                <div>
                    <div class="stat-number gradient-text">2,000+</div>
                    <div class="text-gray-400 font-bold text-[10px] uppercase tracking-widest mt-2">Failure Patterns</div>
                </div>
                <div>
                    <div class="stat-number gradient-text">81%</div>
                    <div class="text-gray-400 font-bold text-[10px] uppercase tracking-widest mt-2">Accuracy Rate</div>
                </div>
                <div>
                    <div class="stat-number gradient-text">ROI</div>
                    <div class="text-gray-400 font-bold text-[10px] uppercase tracking-widest mt-2">Guaranteed</div>
                </div>
                <div>
                    <div class="stat-number gradient-text">SECURE</div>
                    <div class="text-gray-400 font-bold text-[10px] uppercase tracking-widest mt-2">Audit Trails</div>
                </div>
            </div>
        </div>
    </section>

</body>
</html>
