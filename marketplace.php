<?php
/**
 * File Path: marketplace.php
 * Description: High-end marketplace for strategic decision templates.
 * Updated: Added error handling to prevent 500 errors when the database table is missing.
 */

// Enable error reporting to diagnose the specific cause of 500 errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';
requireLogin();

$templates = [];
$dbError = false;

try {
    $pdo = getDbConnection();
    // Check if the table exists first to avoid a hard PDO exception
    $stmt = $pdo->query("SELECT * FROM decision_templates WHERE is_public = 1 ORDER BY use_count DESC");
    $templates = $stmt->fetchAll();
} catch (Exception $e) {
    // If the table doesn't exist yet, we'll use the mock data below
    $dbError = true;
}

// Fallback to mock templates if DB is empty or table is missing
if (empty($templates)) {
    $templates = [
        ['id' => 1, 'name' => 'The Unicorn Hire', 'category' => 'Hiring', 'description' => 'A template for hiring critical early-stage executives (VP, Head of).', 'use_count' => 1240],
        ['id' => 2, 'name' => 'SaaS Pricing Pivot', 'category' => 'Pricing', 'description' => 'Tested patterns for moving from flat to usage-based pricing.', 'use_count' => 850],
        ['id' => 3, 'name' => 'Market Expansion', 'category' => 'Expansion', 'description' => 'Strategic checklist for moving into new geographical territories.', 'use_count' => 420],
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Marketplace | DecisionVault</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap'); body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-50 flex flex-col min-h-screen">
    <?php include __DIR__ . '/includes/header.php'; ?>

    <main class="max-w-7xl mx-auto py-16 px-6 flex-grow w-full">
        <header class="mb-12">
            <h1 class="text-4xl font-black text-gray-900 tracking-tight leading-none mb-4">Strategic Marketplace</h1>
            <p class="text-gray-500 font-medium">Download pre-mortems and decision templates from the world's best operators.</p>
            
            <?php if ($dbError): ?>
                <div class="mt-4 p-4 bg-amber-50 border border-amber-200 text-amber-700 rounded-2xl text-xs font-bold">
                    Note: Database tables for templates not found. Showing preview templates.
                </div>
            <?php endif; ?>
        </header>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach($templates as $t): ?>
                <div class="bg-white p-8 rounded-[40px] border border-gray-100 shadow-sm hover:shadow-xl transition-all group flex flex-col">
                    <div class="flex justify-between items-start mb-6">
                        <span class="text-[10px] font-black px-3 py-1 bg-indigo-50 text-indigo-600 rounded-full uppercase tracking-widest border border-indigo-100">
                            <?php echo htmlspecialchars($t['category']); ?>
                        </span>
                        <span class="text-[10px] font-black text-gray-300 uppercase tracking-widest">
                            Used by <?php echo number_format($t['use_count']); ?> Teams
                        </span>
                    </div>
                    <h3 class="text-2xl font-black text-gray-900 mb-4 group-hover:text-indigo-600 transition"><?php echo htmlspecialchars($t['name']); ?></h3>
                    <p class="text-gray-500 text-sm leading-relaxed mb-8 flex-grow">
                        <?php echo htmlspecialchars($t['description']); ?>
                    </p>
                    <a href="/create-decision.php?template_id=<?php echo $t['id']; ?>" class="w-full text-center py-4 bg-gray-900 text-white rounded-2xl font-black uppercase tracking-widest text-xs hover:bg-indigo-600 transition-all">
                        Adopt Template
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
