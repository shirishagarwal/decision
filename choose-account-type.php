<?php
require_once __DIR__ . '/config.php';

// This is the landing page for new signups
// Shows option to create Personal or Business account

// If user is not logged in, show login button instead
if (!isLoggedIn()) {
    // Not logged in - show this page with login option
    // Will redirect here after OAuth
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
        body { font-family: 'Inter', sans-serif; }
        .card { transition: all 0.3s ease; }
        .card:hover { transform: translateY(-8px); box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
    </style>
</head>
<body class="bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-5xl w-full">
        <div class="text-center mb-12">
            <h1 class="text-5xl font-black text-gray-900 mb-4">
                Welcome to <span class="bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">DecisionVault</span>
            </h1>
            <p class="text-xl text-gray-600">How will you be using DecisionVault?</p>
        </div>

        <div class="grid md:grid-cols-2 gap-8">
            <!-- Personal Account -->
            <div class="card bg-white rounded-3xl border-2 border-gray-200 p-8 cursor-pointer" onclick="selectAccountType('personal')">
                <div class="text-center mb-6">
                    <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-2xl mx-auto flex items-center justify-center mb-4">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Personal</h2>
                    <p class="text-gray-600">For individual use</p>
                </div>

                <div class="space-y-3 mb-8">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-sm text-gray-700">Personal workspace</span>
                    </div>
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-sm text-gray-700">10 decisions per month</span>
                    </div>
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-sm text-gray-700">Decision Intelligence</span>
                    </div>
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-sm text-gray-700">All templates</span>
                    </div>
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-sm text-gray-700">Share with friends/family</span>
                    </div>
                </div>

                <div class="text-center">
                    <div class="text-3xl font-bold text-gray-900 mb-2">Free</div>
                    <div class="text-sm text-gray-500">Upgrade anytime to Pro ($19/mo)</div>
                </div>
            </div>

            <!-- Business Account -->
            <div class="card bg-gradient-to-br from-indigo-600 to-purple-600 rounded-3xl border-2 border-indigo-700 p-8 cursor-pointer text-white" onclick="selectAccountType('business')">
                <div class="text-center mb-6">
                    <div class="w-20 h-20 bg-white/20 backdrop-blur rounded-2xl mx-auto flex items-center justify-center mb-4">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="3" y1="9" x2="21" y2="9"></line>
                            <line x1="9" y1="21" x2="9" y2="9"></line>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold mb-2">Business</h2>
                    <p class="text-purple-100">For teams & companies</p>
                </div>

                <div class="space-y-3 mb-8">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-green-300 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-sm text-purple-50">Everything in Personal, plus:</span>
                    </div>
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-green-300 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-sm">Team collaboration & voting</span>
                    </div>
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-green-300 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-sm">Multiple workspaces</span>
                    </div>
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-green-300 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-sm">Organization analytics</span>
                    </div>
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-green-300 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-sm">Role-based permissions</span>
                    </div>
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-green-300 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-sm">Priority support</span>
                    </div>
                </div>

                <div class="text-center">
                    <div class="text-3xl font-bold mb-2">14-day free trial</div>
                    <div class="text-sm text-purple-100">Then from $49/mo for 5-25 users</div>
                </div>
            </div>
        </div>

        <div class="text-center mt-8 text-sm text-gray-500">
            Already have an account? <a href="<?php echo APP_URL; ?>/auth/google-login.php" class="text-indigo-600 hover:text-indigo-700 font-medium">Sign in</a>
        </div>
    </div>

    <script>
        function selectAccountType(type) {
            // Redirect to auth with account type parameter
            // After OAuth completes, onboarding/check.php will handle the rest
            window.location.href = '<?php echo APP_URL; ?>/auth/google-login.php?account_type=' + type;
        }
    </script>
</body>
</html>