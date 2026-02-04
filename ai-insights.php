<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/DecisionAssistant.php';

if (!isLoggedIn()) {
    redirect(APP_URL . '/index.php');
}

$user = getCurrentUser();
$orgId = $_SESSION['current_org_id'] ?? null;

$assistant = new DecisionAssistant($user['id'], $orgId);

// Get all analyses
$timeline = $assistant->analyzeTimelineAccuracy();
$categories = $assistant->analyzeAccuracyByCategory();
$leaderboard = $assistant->getDecisionMakerLeaderboard();
$patterns = $assistant->detectOrganizationPatterns();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Insights - <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 20px rgba(79, 70, 229, 0.3); }
            50% { box-shadow: 0 0 40px rgba(79, 70, 229, 0.6); }
        }
        .ai-glow { animation: pulse-glow 3s ease-in-out infinite; }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 via-purple-50 to-pink-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="dashboard.php" class="text-gray-600 hover:text-gray-900">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                </a>
                <h1 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <span class="text-2xl">🤖</span>
                    AI Decision Insights
                </h1>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Hero Card -->
        <div class="bg-gradient-to-br from-indigo-600 to-purple-600 rounded-3xl p-8 mb-8 text-white ai-glow">
            <div class="flex items-start justify-between">
                <div>
                    <h2 class="text-3xl font-black mb-2">Your Decision Intelligence</h2>
                    <p class="text-purple-100 text-lg mb-6">
                        AI-powered insights from <?php echo $timeline['sample_size'] + count($categories['categories']); ?> analyzed decisions
                    </p>
                    <div class="inline-block px-4 py-2 bg-white/20 rounded-lg backdrop-blur">
                        <div class="text-sm text-purple-100">AI Confidence Level</div>
                        <div class="text-2xl font-bold">
                            <?php echo $timeline['has_data'] ? '85%' : 'Building...'; ?>
                        </div>
                    </div>
                </div>
                <div class="text-6xl">🧠</div>
            </div>
        </div>

        <!-- Timeline Analysis -->
        <?php if ($timeline['has_data']): ?>
        <div class="bg-white rounded-2xl border-2 border-gray-200 p-6 mb-6">
            <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                <span>⏱️</span> Timeline Estimation Pattern
            </h3>

            <div class="mb-4">
                <div class="text-3xl font-black text-indigo-600 mb-2">
                    <?php echo abs(round($timeline['avg_variance'])); ?>% 
                    <?php echo $timeline['avg_variance'] > 0 ? 'Over' : 'Under'; ?>
                </div>
                <div class="text-gray-700 text-lg">
                    <?php echo $timeline['pattern']; ?>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4 p-4 bg-gray-50 rounded-xl">
                <div>
                    <div class="text-sm text-gray-500">Decisions Analyzed</div>
                    <div class="text-2xl font-bold text-gray-900"><?php echo $timeline['sample_size']; ?></div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Avg Variance</div>
                    <div class="text-2xl font-bold text-gray-900"><?php echo round($timeline['avg_variance']); ?>%</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Recommendation</div>
                    <div class="text-sm font-semibold text-indigo-600">
                        <?php if ($timeline['avg_variance'] > 20): ?>
                        Add <?php echo round($timeline['avg_variance']); ?>% buffer
                        <?php elseif ($timeline['avg_variance'] < -20): ?>
                        You can be more aggressive
                        <?php else: ?>
                        Keep current approach
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Category Accuracy -->
        <?php if (!empty($categories['categories'])): ?>
        <div class="bg-white rounded-2xl border-2 border-gray-200 p-6 mb-6">
            <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                <span>📊</span> Accuracy by Decision Type
            </h3>

            <div class="space-y-3">
                <?php foreach ($categories['categories'] as $cat): ?>
                <div class="flex items-center gap-4 p-4 hover:bg-gray-50 rounded-lg transition">
                    <div class="flex-1">
                        <div class="font-semibold text-gray-900"><?php echo e($cat['category']); ?></div>
                        <div class="text-sm text-gray-500"><?php echo $cat['total_decisions']; ?> decisions</div>
                    </div>

                    <div class="w-64">
                        <div class="h-8 bg-gray-200 rounded-full overflow-hidden">
                            <div class="h-full <?php echo $cat['success_rate'] >= 75 ? 'bg-emerald-500' : ($cat['success_rate'] >= 50 ? 'bg-amber-500' : 'bg-red-500'); ?> flex items-center justify-end pr-2 text-white text-xs font-bold transition-all"
                                 style="width: <?php echo $cat['success_rate']; ?>%">
                                <?php echo round($cat['success_rate']); ?>%
                            </div>
                        </div>
                    </div>

                    <div class="text-2xl">
                        <?php if ($cat['success_rate'] >= 75): ?>
                        ✅
                        <?php elseif ($cat['success_rate'] >= 50): ?>
                        ⚠️
                        <?php else: ?>
                        🚨
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <?php if (!empty($categories['insights'])): ?>
            <div class="mt-6 space-y-2">
                <div class="text-sm font-bold text-gray-700 mb-2">💡 AI Insights:</div>
                <?php foreach ($categories['insights'] as $insight): ?>
                <div class="p-3 <?php echo $insight['type'] === 'strength' ? 'bg-emerald-50 border-emerald-200' : 'bg-amber-50 border-amber-200'; ?> border rounded-lg">
                    <div class="text-sm font-medium">
                        <?php echo e($insight['message']); ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Organization Patterns -->
        <?php if (!empty($patterns)): ?>
        <div class="bg-white rounded-2xl border-2 border-gray-200 p-6 mb-6">
            <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                <span>🔍</span> Team Patterns Detected
            </h3>

            <div class="space-y-4">
                <?php foreach ($patterns as $pattern): ?>
                <div class="p-4 bg-gradient-to-r from-indigo-50 to-purple-50 rounded-xl border border-indigo-200">
                    <div class="flex items-start gap-3">
                        <div class="text-2xl">💡</div>
                        <div class="flex-1">
                            <div class="font-semibold text-gray-900 mb-1">
                                <?php echo e($pattern['pattern']); ?>
                            </div>
                            <div class="text-sm text-gray-600">
                                <strong>Recommendation:</strong> <?php echo e($pattern['recommendation']); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Decision Maker Leaderboard -->
        <?php if (!empty($leaderboard)): ?>
        <div class="bg-white rounded-2xl border-2 border-gray-200 p-6 mb-6">
            <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                <span>🏆</span> Decision Maker Leaderboard
            </h3>

            <div class="space-y-2">
                <?php foreach ($leaderboard as $index => $maker): ?>
                <div class="flex items-center gap-4 p-4 hover:bg-gray-50 rounded-lg transition">
                    <div class="text-2xl font-black <?php echo $index === 0 ? 'text-yellow-500' : ($index === 1 ? 'text-gray-400' : ($index === 2 ? 'text-orange-600' : 'text-gray-300')); ?>">
                        #<?php echo $index + 1; ?>
                    </div>

                    <img src="<?php echo e($maker['avatar_url'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($maker['name'])); ?>"
                         alt="<?php echo e($maker['name']); ?>"
                         class="w-10 h-10 rounded-full ring-2 ring-gray-200">

                    <div class="flex-1">
                        <div class="font-semibold text-gray-900"><?php echo e($maker['name']); ?></div>
                        <div class="text-sm text-gray-500">
                            <?php echo $maker['reviewed_decisions']; ?> decisions reviewed
                        </div>
                    </div>

                    <div class="text-right">
                        <div class="text-2xl font-bold text-indigo-600">
                            <?php echo round($maker['success_rate']); ?>%
                        </div>
                        <div class="text-xs text-gray-500">accuracy</div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- No Data State -->
        <?php if (!$timeline['has_data'] && empty($categories['categories']) && empty($leaderboard)): ?>
        <div class="bg-white rounded-2xl border-2 border-dashed border-gray-300 p-12 text-center">
            <div class="text-6xl mb-4">🤖</div>
            <h3 class="text-2xl font-bold text-gray-900 mb-2">AI Learning in Progress</h3>
            <p class="text-gray-600 mb-6">
                The AI needs at least 3 reviewed decisions to detect patterns.<br>
                Create and review decisions to unlock insights!
            </p>
            <a href="templates.php" class="inline-block px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold">
                Create Your First Decision
            </a>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>