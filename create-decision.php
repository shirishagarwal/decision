<?php
/**
 * Create Decision Page - WORKING VERSION
 */

require_once __DIR__ . '/config.php';
requireLogin();

$user = getCurrentUser();
$pdo = getDbConnection();

$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $context = trim($_POST['context'] ?? '');
    $deadline = $_POST['deadline'] ?? null;
    
    if (empty($title)) {
        $error = 'Please enter a decision title';
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO decisions (
                    title, 
                    context, 
                    deadline, 
                    status, 
                    created_by, 
                    created_at
                ) VALUES (?, ?, ?, 'draft', ?, NOW())
            ");
            
            $stmt->execute([$title, $context, $deadline ?: null, $user['id']]);
            $decisionId = $pdo->lastInsertId();
            
            header("Location: /decision/$decisionId");
            exit;
            
        } catch (Exception $e) {
            error_log("Create decision error: " . $e->getMessage());
            $error = 'Failed to create decision: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Decision | DecisionVault</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50">
    
    <nav class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <a href="/dashboard" class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-gradient-to-br from-purple-600 to-pink-600 rounded-lg flex items-center justify-center">
                        <span class="text-white font-bold text-sm">D</span>
                    </div>
                    <span class="text-xl font-bold text-gray-900">DecisionVault</span>
                </a>
                <a href="/dashboard" class="text-gray-600 hover:text-gray-900 text-sm font-medium">
                    ← Back to Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        
        <div class="mb-8">
            <h1 class="text-3xl font-black text-gray-900 mb-2">Create New Decision</h1>
            <p class="text-gray-600">Get AI-powered recommendations</p>
        </div>

        <?php if ($error): ?>
        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
            <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
            
            <form method="POST" action="/create-decision" class="space-y-6">
                
                <div>
                    <label for="title" class="block text-sm font-semibold text-gray-900 mb-2">
                        What decision do you need to make? <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="title"
                        name="title"
                        required
                        value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none text-lg"
                        placeholder="e.g., Should we hire a VP of Sales?"
                    >
                    <p class="mt-2 text-sm text-gray-500">
                        Be specific. The AI works better with clear questions.
                    </p>
                </div>

                <div>
                    <label for="context" class="block text-sm font-semibold text-gray-900 mb-2">
                        Tell us more about your situation
                    </label>
                    <textarea
                        id="context"
                        name="context"
                        rows="6"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none"
                        placeholder="Provide context: company size, industry, budget, timeline, constraints..."
                    ><?php echo htmlspecialchars($_POST['context'] ?? ''); ?></textarea>
                    <p class="mt-2 text-sm text-gray-500">
                        💡 Tip: More detail = better AI recommendations
                    </p>
                </div>

                <div>
                    <label for="deadline" class="block text-sm font-semibold text-gray-900 mb-2">
                        Decision deadline (optional)
                    </label>
                    <input
                        type="date"
                        id="deadline"
                        name="deadline"
                        value="<?php echo htmlspecialchars($_POST['deadline'] ?? ''); ?>"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none"
                    >
                </div>

                <div class="flex items-center gap-4 pt-4">
                    <button
                        type="submit"
                        class="flex-1 bg-purple-600 text-white px-6 py-3 rounded-lg font-bold hover:bg-purple-700 transition-colors"
                    >
                        Create Decision
                    </button>
                    <a href="/dashboard" class="px-6 py-3 text-gray-600 hover:text-gray-900 font-medium">
                        Cancel
                    </a>
                </div>
            </form>

        </div>

        <div class="mt-8 bg-blue-50 rounded-xl p-6 border border-blue-200">
            <h3 class="font-bold text-gray-900 mb-3">💡 Examples:</h3>
            <ul class="space-y-2 text-sm text-gray-700">
                <li>• "Should we hire a VP of Sales now or wait 6 months?"</li>
                <li>• "Which pricing model: monthly or annual?"</li>
                <li>• "Should we pivot from B2C to B2B?"</li>
            </ul>
        </div>

    </div>

</body>
</html>
