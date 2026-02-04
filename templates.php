<?php
require_once __DIR__ . '/config.php';
if (!isLoggedIn()) redirect(APP_URL . '/index.php');
$user = getCurrentUser();
$pdo = getDbConnection();

// Get all templates
$stmt = $pdo->query("SELECT * FROM decision_templates WHERE is_public = 1 ORDER BY category, use_count DESC");
$templates = $stmt->fetchAll(PDO::FETCH_GROUP);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choose Template - <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
        .template-card:hover { transform: translateY(-4px); }
        .template-card { transition: all 0.3s ease; }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 via-purple-50 to-pink-50 min-h-screen">
    <nav class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="dashboard.php" class="flex items-center gap-2 text-gray-600 hover:text-gray-900">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                <span class="font-medium">Back</span>
            </a>
            <h1 class="text-lg font-bold text-gray-900">Choose Decision Template</h1>
            <div class="w-16"></div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">What are you deciding?</h1>
            <p class="text-xl text-gray-600">Choose a template to get started faster, or start from scratch</p>
        </div>

        <!-- Start from Scratch -->
        <div class="mb-8">
            <a href="create-decision.php" class="block p-6 bg-white border-2 border-indigo-600 rounded-2xl hover:shadow-xl transition-all">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">🎨 Start from Scratch</h3>
                        <p class="text-gray-600">Create a completely custom decision with AI help</p>
                    </div>
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                        <polyline points="12 5 19 12 12 19"></polyline>
                    </svg>
                </div>
            </a>
        </div>

        <!-- Templates by Category -->
        <?php
        $categories = [
            'Personnel' => ['icon' => '👥', 'color' => 'blue'],
            'Product' => ['icon' => '🚀', 'color' => 'purple'],
            'Strategic' => ['icon' => '🎯', 'color' => 'indigo'],
            'Financial' => ['icon' => '💰', 'color' => 'green']
        ];

        $stmt = $pdo->query("SELECT * FROM decision_templates WHERE is_public = 1 ORDER BY category, use_count DESC");
        $allTemplates = $stmt->fetchAll();
        
        $grouped = [];
        foreach ($allTemplates as $template) {
            $grouped[$template['category']][] = $template;
        }

        foreach ($categories as $category => $meta):
            if (!isset($grouped[$category])) continue;
        ?>
        <div class="mb-12">
            <div class="flex items-center gap-3 mb-6">
                <span class="text-4xl"><?php echo $meta['icon']; ?></span>
                <h2 class="text-2xl font-bold text-gray-900"><?php echo $category; ?> Decisions</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($grouped[$category] as $template): ?>
                <a href="create-decision.php?template=<?php echo $template['id']; ?>" 
                   class="template-card block p-6 bg-white rounded-xl border-2 border-gray-200 hover:border-<?php echo $meta['color']; ?>-400 hover:shadow-xl">
                    <div class="mb-4">
                        <h3 class="text-lg font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($template['name']); ?></h3>
                        <p class="text-sm text-gray-600"><?php echo htmlspecialchars($template['description']); ?></p>
                    </div>
                    
                    <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                        <span class="text-xs text-gray-500">
                            Used <?php echo number_format($template['use_count']); ?> times
                        </span>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-<?php echo $meta['color']; ?>-500">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <polyline points="12 5 19 12 12 19"></polyline>
                        </svg>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>

        <!-- No templates message -->
        <?php if (empty($allTemplates)): ?>
        <div class="text-center py-12">
            <p class="text-gray-500 mb-4">No templates available yet. Run database-unicorn.sql to add templates!</p>
            <a href="create-decision.php" class="inline-block px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                Start from Scratch Instead
            </a>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>