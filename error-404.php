<?php
http_response_code(404);
require_once __DIR__ . '/config.php';
$isLoggedIn = isLoggedIn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found | DecisionVault</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 via-purple-50 to-pink-50">
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="text-center max-w-2xl">
            <!-- Large 404 -->
            <div class="mb-8">
                <h1 class="text-9xl font-black text-transparent bg-clip-text bg-gradient-to-r from-purple-600 to-pink-600">
                    404
                </h1>
            </div>
            
            <!-- Icon -->
            <div class="mb-6">
                <svg class="w-24 h-24 mx-auto text-purple-600 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            
            <!-- Message -->
            <h2 class="text-3xl font-bold text-gray-900 mb-4">
                Oops! Page Not Found
            </h2>
            <p class="text-lg text-gray-600 mb-8">
                The page you're looking for doesn't exist or has been moved.
            </p>
            
            <!-- Actions -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <?php if ($isLoggedIn): ?>
                    <a href="/dashboard" class="inline-flex items-center justify-center px-6 py-3 bg-purple-600 text-white rounded-lg font-semibold hover:bg-purple-700 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        Go to Dashboard
                    </a>
                    <a href="/decisions" class="inline-flex items-center justify-center px-6 py-3 bg-white text-purple-600 rounded-lg font-semibold border-2 border-purple-600 hover:bg-purple-50 transition-colors">
                        View Decisions
                    </a>
                <?php else: ?>
                    <a href="/" class="inline-flex items-center justify-center px-6 py-3 bg-purple-600 text-white rounded-lg font-semibold hover:bg-purple-700 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        Go to Homepage
                    </a>
                    <a href="/login" class="inline-flex items-center justify-center px-6 py-3 bg-white text-purple-600 rounded-lg font-semibold border-2 border-purple-600 hover:bg-purple-50 transition-colors">
                        Sign In
                    </a>
                <?php endif; ?>
            </div>
            
            <!-- Helpful links -->
            <div class="mt-12 pt-8 border-t border-gray-200">
                <p class="text-sm text-gray-600 mb-4">Looking for something specific?</p>
                <div class="flex flex-wrap gap-4 justify-center text-sm">
                    <?php if ($isLoggedIn): ?>
                        <a href="/decisions/create" class="text-purple-600 hover:text-purple-700 font-medium">Create Decision</a>
                        <a href="/help" class="text-purple-600 hover:text-purple-700 font-medium">Help Center</a>
                        <a href="/settings" class="text-purple-600 hover:text-purple-700 font-medium">Settings</a>
                    <?php else: ?>
                        <a href="/pricing" class="text-purple-600 hover:text-purple-700 font-medium">Pricing</a>
                        <a href="/help" class="text-purple-600 hover:text-purple-700 font-medium">Help</a>
                        <a href="/signup" class="text-purple-600 hover:text-purple-700 font-medium">Sign Up</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>