<?php
http_response_code(500);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Server Error | DecisionVault</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 via-red-50 to-orange-50">
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="text-center max-w-2xl">
            <!-- Large 500 -->
            <div class="mb-8">
                <h1 class="text-9xl font-black text-transparent bg-clip-text bg-gradient-to-r from-red-600 to-orange-600">
                    500
                </h1>
            </div>
            
            <!-- Icon -->
            <div class="mb-6">
                <svg class="w-24 h-24 mx-auto text-red-600 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            
            <!-- Message -->
            <h2 class="text-3xl font-bold text-gray-900 mb-4">
                Something Went Wrong
            </h2>
            <p class="text-lg text-gray-600 mb-8">
                We're experiencing technical difficulties. Our team has been notified and is working on it.
            </p>
            
            <!-- Actions -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <button onclick="window.location.reload()" class="inline-flex items-center justify-center px-6 py-3 bg-red-600 text-white rounded-lg font-semibold hover:bg-red-700 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Try Again
                </button>
                <a href="/" class="inline-flex items-center justify-center px-6 py-3 bg-white text-red-600 rounded-lg font-semibold border-2 border-red-600 hover:bg-red-50 transition-colors">
                    Go to Homepage
                </a>
            </div>
            
            <!-- Support info -->
            <div class="mt-12 pt-8 border-t border-gray-200">
                <p class="text-sm text-gray-600 mb-2">If this problem persists, please contact us:</p>
                <a href="mailto:support@yourdomain.com" class="text-red-600 hover:text-red-700 font-medium">
                    support@yourdomain.com
                </a>
            </div>
            
            <!-- Technical details (only show if logged in as admin) -->
            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
            <div class="mt-6 p-4 bg-gray-100 rounded-lg text-left">
                <p class="text-xs font-mono text-gray-600">
                    Error ID: <?php echo uniqid(); ?><br>
                    Time: <?php echo date('Y-m-d H:i:s'); ?><br>
                    URL: <?php echo htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'unknown'); ?>
                </p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>