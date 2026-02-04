<?php
require_once __DIR__ . '/config.php';

if (!isLoggedIn()) {
    redirect(APP_URL . '/index.php');
}

$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50">
    <nav class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <a href="dashboard.php" class="text-gray-600 hover:text-gray-900">← Back to Dashboard</a>
                <h1 class="text-xl font-bold text-gray-900">Settings</h1>
                <div></div>
            </div>
        </div>
    </nav>

    <div class="max-w-3xl mx-auto px-6 py-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Account Settings</h2>
            
            <div class="space-y-6">
                <div className="flex items-center gap-4 pb-6 border-b border-gray-200">
                    <img 
                        src="<?php echo e($user['avatar_url'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($user['name'])); ?>" 
                        alt="<?php echo e($user['name']); ?>"
                        class="w-20 h-20 rounded-full border-2 border-gray-200"
                    >
                    <div>
                        <h3 class="text-xl font-bold text-gray-900"><?php echo e($user['name']); ?></h3>
                        <p class="text-gray-600"><?php echo e($user['email']); ?></p>
                        <p class="text-sm text-gray-500 mt-1">Member since <?php echo date('F Y', strtotime($user['created_at'])); ?></p>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Workspace Settings</h3>
                    <p class="text-gray-600 mb-4">Manage your workspaces and team members.</p>
                    <button class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        Manage Workspaces
                    </button>
                </div>

                <div className="pt-6 border-t border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Danger Zone</h3>
                    <p class="text-gray-600 mb-4">Once you delete your account, there is no going back.</p>
                    <button class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        Delete Account
                    </button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
