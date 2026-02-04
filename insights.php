<?php
require_once __DIR__ . '/config.php';
if (!isLoggedIn()) redirect(APP_URL . '/index.php');

$user = getCurrentUser();
$pdo = getDbConnection();

// Get user's reviewed decisions
$stmt = $pdo->prepare("
    SELECT d.*, dr.rating, dr.would_decide_same
    FROM decisions d
    LEFT JOIN decision_reviews dr ON d.id = dr.decision_id
    WHERE d.created_by = ? AND d.review_completed_at IS NOT NULL
    ORDER BY d.review_completed_at DESC
");
$stmt->execute([$user['id']]);
$reviewedDecisions = $stmt->fetchAll();

// Get streak info
$stmt = $pdo->prepare("SELECT * FROM user_streaks WHERE user_id = ?");
$stmt->execute([$user['id']]);
$streak = $stmt->fetch();

// Calculate Decision IQ Score (0-200)
$totalReviews = count($reviewedDecisions);
$accurateDecisions = 0;
$byCategory = [];

foreach ($reviewedDecisions as $decision) {
    $cat = $decision['category'];
    if (!isset($byCategory[$cat])) {
        $byCategory[$cat] = ['total' => 0, 'accurate' => 0, 'ratings' => []];
    }
    $byCategory[$cat]['total']++;
    $byCategory[$cat]['ratings'][] = $decision['rating'];
    
    if (in_array($decision['rating'], ['as_expected', 'better', 'much_better'])) {
        $accurateDecisions++;
        $byCategory[$cat]['accurate']++;
    }
}

// Decision IQ Formula: Base (accuracy%) + Bonuses
$baseScore = $totalReviews > 0 ? ($accurateDecisions / $totalReviews) * 100 : 0;
$volumeBonus = min(20, $totalReviews * 2); // Up to +20 for volume
$streakBonus = min(10, ($streak['current_streak'] ?? 0)); // Up to +10 for streak
$consistencyBonus = ($streak['longest_streak'] ?? 0) >= 7 ? 10 : 0; // +10 for 7-day streak

$decisionIQ = round($baseScore + $volumeBonus + $streakBonus + $consistencyBonus);
$decisionIQ = min(200, $decisionIQ); // Cap at 200

// Get insights
$stmt = $pdo->prepare("SELECT * FROM decision_insights WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$user['id']]);
$insights = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Decision Intelligence - <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
        body { font-family: 'Inter', sans-serif; }
        .iq-score {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
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
                <span class="font-medium">Dashboard</span>
            </a>
            <h1 class="text-lg font-bold text-gray-900">Decision Intelligence</h1>
            <div class="w-24"></div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 py-8">
        <?php if ($totalReviews === 0): ?>
        <!-- No Data State -->
        <div class="text-center py-16">
            <div class="text-6xl mb-4">📊</div>
            <h2 class="text-3xl font-bold text-gray-900 mb-4">No Intelligence Data Yet</h2>
            <p class="text-xl text-gray-600 mb-8">Review some decisions to unlock your Decision IQ Score!</p>
            <a href="dashboard.php" class="inline-block px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium">
                View Decisions to Review
            </a>
        </div>
        <?php else: ?>

        <!-- Decision IQ Score (Hero) -->
        <div class="mb-8">
            <div class="bg-white rounded-3xl border border-gray-200 p-8 text-center relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-purple-50 to-pink-50 opacity-50"></div>
                <div class="relative z-10">
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Your Decision IQ</h2>
                    <div class="text-8xl font-black iq-score mb-4"><?php echo $decisionIQ; ?></div>
                    <p class="text-lg text-gray-600 mb-6">
                        <?php
                        if ($decisionIQ >= 140) echo "🏆 Decision Master";
                        elseif ($decisionIQ >= 120) echo "🌟 Excellent Decision Maker";
                        elseif ($decisionIQ >= 100) echo "📈 Above Average";
                        elseif ($decisionIQ >= 80) echo "✅ Good Decision Maker";
                        else echo "🌱 Growing";
                        ?>
                    </p>
                    
                    <div class="flex justify-center gap-8 mb-6">
                        <div>
                            <div class="text-3xl font-bold text-indigo-600"><?php echo $totalReviews; ?></div>
                            <div class="text-sm text-gray-500">Decisions Reviewed</div>
                        </div>
                        <div>
                            <div class="text-3xl font-bold text-emerald-600"><?php echo $totalReviews > 0 ? round(($accurateDecisions / $totalReviews) * 100) : 0; ?>%</div>
                            <div class="text-sm text-gray-500">Accuracy Rate</div>
                        </div>
                        <div>
                            <div class="text-3xl font-bold text-purple-600"><?php echo $streak['current_streak'] ?? 0; ?>🔥</div>
                            <div class="text-sm text-gray-500">Day Streak</div>
                        </div>
                    </div>

                    <button onclick="shareScore()" class="px-8 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg hover:from-indigo-700 hover:to-purple-700 font-bold">
                        📤 Share My Score
                    </button>
                </div>
            </div>
        </div>

        <!-- Category Breakdown -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Accuracy by Category -->
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4">Accuracy by Category</h3>
                <div class="space-y-3">
                    <?php foreach ($byCategory as $category => $stats): ?>
                    <?php 
                        $accuracy = $stats['total'] > 0 ? round(($stats['accurate'] / $stats['total']) * 100) : 0;
                        $color = $accuracy >= 70 ? 'emerald' : ($accuracy >= 50 ? 'blue' : 'orange');
                    ?>
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-medium text-gray-700"><?php echo $category; ?></span>
                            <span class="text-sm font-bold text-<?php echo $color; ?>-600"><?php echo $accuracy; ?>%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-<?php echo $color; ?>-500 h-2 rounded-full" style="width: <?php echo $accuracy; ?>%"></div>
                        </div>
                        <div class="text-xs text-gray-500 mt-1"><?php echo $stats['total']; ?> decisions</div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Recent Insights -->
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4">🧠 Insights</h3>
                <?php if (empty($insights)): ?>
                <p class="text-gray-500 text-center py-8">Review more decisions to unlock insights!</p>
                <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($insights as $insight): ?>
                    <div class="p-4 bg-gradient-to-r from-purple-50 to-pink-50 border border-purple-200 rounded-lg">
                        <div class="flex items-start gap-3">
                            <span class="text-2xl">
                                <?php
                                echo match($insight['insight_type']) {
                                    'success_factor' => '🌟',
                                    'warning' => '⚠️',
                                    'pattern' => '🔍',
                                    'bias' => '🎯',
                                    default => '💡'
                                };
                                ?>
                            </span>
                            <div class="flex-1">
                                <h4 class="font-bold text-gray-900 mb-1"><?php echo htmlspecialchars($insight['title']); ?></h4>
                                <p class="text-sm text-gray-700 mb-2"><?php echo htmlspecialchars($insight['description']); ?></p>
                                <?php if ($insight['action_suggested']): ?>
                                <p class="text-xs text-purple-700 font-medium">💡 <?php echo htmlspecialchars($insight['action_suggested']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Reviews -->
        <div class="bg-white rounded-2xl border border-gray-200 p-6">
            <h3 class="text-xl font-bold text-gray-900 mb-4">Recent Reviews</h3>
            <div class="space-y-4">
                <?php foreach (array_slice($reviewedDecisions, 0, 10) as $decision): ?>
                <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
                    <div class="flex-1">
                        <a href="decision.php?id=<?php echo $decision['id']; ?>" class="font-medium text-gray-900 hover:text-indigo-600">
                            <?php echo htmlspecialchars($decision['title']); ?>
                        </a>
                        <div class="text-sm text-gray-500 mt-1">
                            <?php echo $decision['category']; ?> • 
                            Reviewed <?php echo date('M j, Y', strtotime($decision['review_completed_at'])); ?>
                        </div>
                    </div>
                    <div class="text-right ml-4">
                        <div class="text-2xl mb-1">
                            <?php
                            echo match($decision['rating']) {
                                'much_better' => '😄',
                                'better' => '🙂',
                                'as_expected' => '😐',
                                'worse' => '😕',
                                'much_worse' => '😞',
                                default => '❓'
                            };
                            ?>
                        </div>
                        <div class="text-xs text-gray-500">
                            <?php echo ucfirst(str_replace('_', ' ', $decision['rating'])); ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <?php endif; ?>
    </div>

    <script>
        function shareScore() {
            const score = <?php echo $decisionIQ; ?>;
            const text = `My Decision IQ is ${score}! I'm tracking my decision-making accuracy with <?php echo APP_NAME; ?>. 🧠📊`;
            
            if (navigator.share) {
                navigator.share({
                    title: 'My Decision IQ',
                    text: text,
                    url: window.location.href
                });
            } else {
                // Fallback: Copy to clipboard
                navigator.clipboard.writeText(text + '\n' + window.location.href);
                alert('Copied to clipboard! Share on your social media.');
            }
        }
    </script>
</body>
</html>
