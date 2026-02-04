<?php
require_once __DIR__ . '/config.php';
requireLogin();

$pdo = getDbConnection();
$category = $_GET['category'] ?? 'All';

// Fetch Public Templates with Author Details
$query = "SELECT t.*, u.name as author_name, u.avatar_url as author_avatar 
          FROM decision_templates t 
          LEFT JOIN users u ON t.author_id = u.id 
          WHERE t.is_public = 1";
if ($category !== 'All') { $query .= " AND t.category = :cat"; }
$query .= " ORDER BY t.use_count DESC";

$stmt = $pdo->prepare($query);
if ($category !== 'All') { $stmt->bindValue(':cat', $category); }
$stmt->execute();
$templates = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Framework Marketplace | DecisionVault</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 py-12">
        <header class="flex justify-between items-end mb-12">
            <div>
                <h1 class="text-4xl font-black text-gray-900">Framework Marketplace</h1>
                <p class="text-gray-500 text-lg">Download proven decision frameworks from world-class strategists.</p>
            </div>
            <a href="/submit-template.php" class="bg-indigo-600 text-white px-6 py-3 rounded-xl font-bold shadow-lg shadow-indigo-200">
                + Share Your Framework
            </a>
        </header>

        <div class="flex gap-4 mb-10 overflow-x-auto pb-2">
            <?php foreach(['All', 'Strategic', 'Product', 'Financial', 'Personnel'] as $cat): ?>
                <a href="?category=<?php echo $cat; ?>"
                   class="px-6 py-2 rounded-full font-bold text-sm border-2 <?php echo $category === $cat ? 'bg-indigo-600 border-indigo-600 text-white' : 'bg-white text-gray-500 border-gray-200'; ?>">
                    <?php echo $cat; ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
            <?php foreach($templates as $t): ?>
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm hover:shadow-xl transition-all p-8 flex flex-col">
                    <div class="flex justify-between items-start mb-6">
                        <span class="px-3 py-1 bg-indigo-50 text-indigo-600 rounded-lg text-xs font-black uppercase tracking-widest">
                            <?php echo $t['category']; ?>
                        </span>
                        <div class="flex items-center text-yellow-500 text-sm font-bold">
                            ⭐ <?php echo number_format($t['rating_avg'], 1); ?>
                        </div>
                    </div>

                    <h3 class="text-2xl font-bold text-gray-900 mb-3"><?php echo htmlspecialchars($t['name']); ?></h3>
                    <p class="text-gray-500 text-sm mb-8 flex-grow"><?php echo htmlspecialchars($t['description']); ?></p>

                    <div class="flex items-center justify-between pt-6 border-t border-gray-50">
                        <div class="flex items-center gap-3">
                            <img src="<?php echo $t['author_avatar'] ?: 'https://ui-avatars.com/api/?name=DV'; ?>" class="w-8 h-8 rounded-full border">
                            <span class="text-xs font-bold text-gray-400"><?php echo htmlspecialchars($t['author_name'] ?: 'System'); ?></span>
                        </div>
                        <a href="/create-decision.php?template_id=<?php echo $t['id']; ?>"
                           class="text-indigo-600 font-black text-sm hover:underline">Use This →</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
