<?php
/**
 * DecisionVault - Decision Detail View
 * Displays the core decision context and allows for voting/interaction.
 */

require_once __DIR__ . '/lib/auth.php';
requireOrgAccess();

$user = getCurrentUser();
$pdo = getDbConnection();
$decisionId = $_GET['id'] ?? null;

if (!$decisionId) {
    header('Location: /dashboard.php');
    exit;
}

// Fetch Decision with Ownership/Workspace Validation
$stmt = $pdo->prepare("
    SELECT d.*, u.name as creator_name, w.name as workspace_name
    FROM decisions d
    INNER JOIN users u ON d.created_by = u.id
    INNER JOIN workspaces w ON d.workspace_id = w.id
    WHERE d.id = ? AND w.organization_id = ?
");
$stmt->execute([$decisionId, $_SESSION['current_org_id']]);
$decision = $stmt->fetch();

if (!$decision) {
    die("Decision not found or access denied.");
}

// Fetch Options
$stmt = $pdo->prepare("SELECT * FROM options WHERE decision_id = ? ORDER BY sort_order ASC");
$stmt->execute([$decisionId]);
$options = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title><?php echo htmlspecialchars($decision['title']); ?> | <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <nav class="bg-white border-b p-4 mb-8">
        <div class="max-w-4xl mx-auto flex justify-between items-center">
            <a href="/dashboard.php" class="text-indigo-600 font-bold">← Dashboard</a>
            <span class="text-sm text-gray-400"><?php echo $decision['workspace_name']; ?></span>
        </div>
    </nav>

    <main class="max-w-4xl mx-auto px-4 pb-24">
        <?php if ($decision['review_date'] && !$decision['review_completed_at']): ?>
            <div class="bg-indigo-600 text-white p-6 rounded-2xl mb-8 shadow-lg flex justify-between items-center">
                <div>
                    <h3 class="font-bold">Review Loop Active</h3>
                    <p class="text-indigo-100 text-sm">Scheduled for: <?php echo $decision['review_date']; ?></p>
                </div>
                <a href="/review-decision.php?id=<?php echo $decisionId; ?>" class="bg-white text-indigo-600 px-4 py-2 rounded-lg font-bold text-sm">Review Now</a>
            </div>
        <?php endif; ?>

        <header class="mb-12">
            <h1 class="text-4xl font-black text-gray-900 mb-4"><?php echo htmlspecialchars($decision['title']); ?></h1>
            <div class="flex gap-3 mb-6">
                <span class="px-3 py-1 bg-gray-100 rounded-full text-xs font-bold uppercase"><?php echo $decision['category']; ?></span>
                <span class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-xs font-bold uppercase"><?php echo $decision['status']; ?></span>
            </div>
            <div class="bg-white p-6 rounded-2xl border shadow-sm">
                <h3 class="text-sm font-bold text-gray-400 uppercase mb-2">Problem Statement</h3>
                <p class="text-lg text-gray-700 italic">"<?php echo htmlspecialchars($decision['problem_statement']); ?>"</p>
            </div>
        </header>

        <section class="space-y-6">
            <h2 class="text-2xl font-bold">Considered Options</h2>
            <?php foreach($options as $opt): ?>
                <div class="bg-white p-8 rounded-3xl border-2 hover:border-indigo-500 transition-all shadow-sm">
                    <div class="flex justify-between items-start mb-4">
                        <h3 class="text-xl font-bold"><?php echo htmlspecialchars($opt['name']); ?></h3>
                        <?php if ($opt['was_chosen']): ?>
                            <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-black">✓ CHOSEN</span>
                        <?php endif; ?>
                    </div>
                    <p class="text-gray-600 mb-6"><?php echo nl2br(htmlspecialchars($opt['description'])); ?></p>
                    
                    <div class="flex items-center gap-4 pt-4 border-t border-gray-50">
                        <button class="text-sm font-bold text-indigo-600 hover:underline">👍 Vote for this</button>
                        <span class="text-xs text-gray-300">0 Votes</span>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>
    </main>
</body>
</html>
<?php include __DIR__ . '/components/simulator-ui.php'; ?>
