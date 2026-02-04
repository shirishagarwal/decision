<?php require_once __DIR__ . '/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>DecisionVault | AI Intelligence</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen">
    <div class="text-center max-w-md bg-white p-12 rounded-3xl shadow-xl">
        <h1 class="text-4xl font-black mb-4">DecisionVault</h1>
        <p class="text-gray-600 mb-8">AI-powered intelligence for personal and business strategy.</p>
        
        <?php if(isLoggedIn()): ?>
            <a href="/dashboard.php" class="bg-indigo-600 text-white px-8 py-3 rounded-lg font-bold">Go to Dashboard</a>
        <?php else: ?>
            <a href="/auth/google.php" class="border-2 border-gray-200 flex items-center justify-center gap-3 px-8 py-3 rounded-lg font-bold hover:bg-gray-50 transition">
                <img src="https://upload.wikimedia.org/wikipedia/commons/c/c1/Google_%22G%22_logo.svg" class="w-5" alt="Google">
                Continue with Google
            </a>
        <?php endif; ?>
    </div>
</body>
</html>
