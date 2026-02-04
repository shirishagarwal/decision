<?php
http_response_code(403);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Access Denied | DecisionVault</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 via-yellow-50 to-orange-50">
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="text-center max-w-2xl">
            <div class="mb-8">
                <h1 class="text-9xl font-black text-transparent bg-clip-text bg-gradient-to-r from-yellow-600 to-orange-600">
                    403
                </h1>
            </div>
            
            <div class="mb-6">
                <svg class="w-24 h-24 mx-auto text-yellow-600 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            
            <h2 class="text-3xl font-bold text-gray-900 mb-4">
                Access Denied
            </h2>
            <p class="text-lg text-gray-600 mb-8">
                You don't have permission to access this page.
            </p>
            
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="/" class="inline-flex items-center justify-center px-6 py-3 bg-yellow-600 text-white rounded-lg font-semibold hover:bg-yellow-700 transition-colors">
                    Go to Homepage
                </a>
                <a href="/login" class="inline-flex items-center justify-center px-6 py-3 bg-white text-yellow-600 rounded-lg font-semibold border-2 border-yellow-600 hover:bg-yellow-50 transition-colors">
                    Sign In
                </a>
            </div>
        </div>
    </div>
</body>
</html>