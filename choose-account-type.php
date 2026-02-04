<?php
require_once __DIR__ . '/config.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome to <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-indigo-50 via-white to-purple-50 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-4xl w-full">
        <div class="text-center mb-12">
            <h1 class="text-4xl font-extrabold text-gray-900 mb-4">How will you use DecisionVault?</h1>
            <p class="text-gray-600 text-lg">Pick the path that fits your decision-making style.</p>
        </div>

        <div class="grid md:grid-cols-2 gap-8">
            <div class="bg-white border-2 border-gray-200 rounded-3xl p-8 hover:border-indigo-500 transition-all cursor-pointer group shadow-sm hover:shadow-xl"
                 onclick="window.location.href='/onboarding/setup-personal.php'">
                <div class="w-16 h-16 bg-indigo-100 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                    <span class="text-3xl">👤</span>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Personal</h2>
                <p class="text-gray-600 mb-6 text-sm">For individuals and families. Track life choices, travel plans, and personal goals.</p>
                <ul class="space-y-3 mb-8 text-sm text-gray-700">
                    <li class="flex items-center gap-2 text-green-600">✓ 10 decisions/mo free</li>
                    <li class="flex items-center gap-2">✓ AI Option Generator</li>
                    <li class="flex items-center gap-2">✓ Personal Decision IQ</li>
                </ul>
                <button class="w-full py-3 bg-gray-100 text-gray-900 font-bold rounded-xl group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                    Start Personal
                </button>
            </div>

            <div class="bg-white border-2 border-indigo-600 rounded-3xl p-8 hover:shadow-xl transition-all cursor-pointer relative shadow-md"
                 onclick="window.location.href='/onboarding/create-organization.php'">
                <div class="absolute -top-4 right-8 bg-indigo-600 text-white text-xs font-bold px-3 py-1 rounded-full">RECOMMENDED</div>
                <div class="w-16 h-16 bg-indigo-600 rounded-2xl flex items-center justify-center mb-6">
                    <span class="text-3xl">🏢</span>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Business</h2>
                <p class="text-gray-600 mb-6 text-sm">For teams and startups. Hiring, product strategy, and budget allocation.</p>
                <ul class="space-y-3 mb-8 text-sm text-gray-700">
                    <li class="flex items-center gap-2 text-indigo-600 font-bold">✓ Team Collaboration & Voting</li>
                    <li class="flex items-center gap-2">✓ Industry Failure Patterns</li>
                    <li class="flex items-center gap-2">✓ Role-based Permissions</li>
                </ul>
                <button class="w-full py-3 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition-all">
                    Create Organization
                </button>
            </div>
        </div>
    </div>
</body>
</html>
