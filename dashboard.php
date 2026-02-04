<?php
require_once __DIR__ . '/config.php';

// Require authentication
requireLogin();

$user = getCurrentUser();
$pdo = getDbConnection();

// Get user's organization
$orgId = $user['organization_id'] ?? null;

// Fetch user's decisions
$stmt = $pdo->prepare("
    SELECT d.*, 
           COUNT(DISTINCT do.id) as option_count,
           COUNT(DISTINCT v.id) as vote_count,
           u.name as creator_name
    FROM decisions d
    LEFT JOIN decision_options do ON d.id = do.decision_id
    LEFT JOIN votes v ON d.id = v.decision_id
    LEFT JOIN users u ON d.user_id = u.id
    WHERE d.user_id = ? OR d.organization_id = ?
    GROUP BY d.id
    ORDER BY d.created_at DESC
");
$stmt->execute([$user['id'], $orgId]);
$decisions = $stmt->fetchAll();

// Check if user has completed onboarding
$showOnboarding = !($user['onboarding_completed'] ?? false);

// Get stats for dashboard
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM decisions WHERE user_id = ? OR organization_id = ?");
$stmt->execute([$user['id'], $orgId]);
$totalDecisions = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM decisions WHERE (user_id = ? OR organization_id = ?) AND status = 'completed'");
$stmt->execute([$user['id'], $orgId]);
$completedDecisions = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM decisions WHERE (user_id = ? OR organization_id = ?) AND status = 'in_progress'");
$stmt->execute([$user['id'], $orgId]);
$activeDecisions = $stmt->fetch()['total'];

$pageTitle = "Dashboard";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> | DecisionVault</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50">
    
    <!-- Navigation -->
    <nav class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center gap-8">
                    <a href="/dashboard" class="flex items-center gap-2">
                        <div class="w-8 h-8 bg-gradient-to-br from-purple-600 to-pink-600 rounded-lg flex items-center justify-center">
                            <span class="text-white font-bold text-sm">D</span>
                        </div>
                        <span class="text-xl font-bold text-gray-900">DecisionVault</span>
                    </a>
                    
                    <div class="hidden md:flex items-center gap-6">
                        <a href="/dashboard" class="text-purple-600 font-semibold border-b-2 border-purple-600 pb-1">
                            Dashboard
                        </a>
                        <a href="/decisions" class="text-gray-600 hover:text-gray-900 font-medium">
                            All Decisions
                        </a>
                        <?php if ($orgId): ?>
                        <a href="/team" class="text-gray-600 hover:text-gray-900 font-medium">
                            Team
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="flex items-center gap-4">
                    <a href="/decisions/create" class="hidden sm:inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg font-semibold hover:bg-purple-700 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        New Decision
                    </a>
                    
                    <!-- User Menu -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-100">
                            <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                                <span class="text-purple-600 font-semibold text-sm">
                                    <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                                </span>
                            </div>
                            <span class="hidden md:block text-sm font-medium text-gray-700">
                                <?php echo htmlspecialchars($user['name']); ?>
                            </span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        
                        <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-1 border border-gray-200" style="display: none;">
                            <a href="/settings" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Settings</a>
                            <a href="/help" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Help & Support</a>
                            <hr class="my-1">
                            <a href="/logout" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50">Logout</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Welcome Section -->
        <div class="mb-8">
            <h1 class="text-3xl font-black text-gray-900 mb-2">
                Welcome back, <?php echo htmlspecialchars(explode(' ', $user['name'])[0]); ?>! 👋
            </h1>
            <p class="text-gray-600">
                Here's an overview of your decisions
            </p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <span class="text-3xl font-black text-gray-900"><?php echo $totalDecisions; ?></span>
                </div>
                <div class="text-sm font-semibold text-gray-900">Total Decisions</div>
                <div class="text-xs text-gray-500 mt-1">All time</div>
            </div>

            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <span class="text-3xl font-black text-gray-900"><?php echo $activeDecisions; ?></span>
                </div>
                <div class="text-sm font-semibold text-gray-900">Active Decisions</div>
                <div class="text-xs text-gray-500 mt-1">In progress</div>
            </div>

            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <span class="text-3xl font-black text-gray-900"><?php echo $completedDecisions; ?></span>
                </div>
                <div class="text-sm font-semibold text-gray-900">Completed</div>
                <div class="text-xs text-gray-500 mt-1">With outcomes</div>
            </div>
        </div>

        <!-- Decisions List or Empty State -->
        <?php if (empty($decisions)): ?>
            <?php include __DIR__ . '/components/empty-state-dashboard.php'; ?>
        <?php else: ?>
            
            <!-- Decisions Header -->
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-900">Recent Decisions</h2>
                <a href="/decisions" class="text-purple-600 hover:text-purple-700 font-semibold text-sm">
                    View All →
                </a>
            </div>

            <!-- Decisions Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <?php foreach (array_slice($decisions, 0, 6) as $decision): ?>
                <a href="/decisions/<?php echo $decision['id']; ?>" class="block bg-white rounded-xl p-6 shadow-sm border border-gray-200 hover:shadow-md hover:border-purple-300 transition-all">
                    
                    <!-- Status Badge -->
                    <div class="flex items-center justify-between mb-3">
                        <?php
                        $statusColors = [
                            'in_progress' => 'bg-blue-100 text-blue-700',
                            'completed' => 'bg-green-100 text-green-700',
                            'pending' => 'bg-yellow-100 text-yellow-700'
                        ];
                        $statusColor = $statusColors[$decision['status']] ?? 'bg-gray-100 text-gray-700';
                        ?>
                        <span class="px-3 py-1 <?php echo $statusColor; ?> rounded-full text-xs font-semibold">
                            <?php echo ucfirst(str_replace('_', ' ', $decision['status'])); ?>
                        </span>
                        <span class="text-xs text-gray-500">
                            <?php echo timeAgo($decision['created_at']); ?>
                        </span>
                    </div>

                    <!-- Decision Title -->
                    <h3 class="font-bold text-lg text-gray-900 mb-2 line-clamp-2">
                        <?php echo htmlspecialchars($decision['title']); ?>
                    </h3>

                    <!-- Decision Context (if exists) -->
                    <?php if (!empty($decision['context'])): ?>
                    <p class="text-sm text-gray-600 mb-4 line-clamp-2">
                        <?php echo htmlspecialchars($decision['context']); ?>
                    </p>
                    <?php endif; ?>

                    <!-- Meta Info -->
                    <div class="flex items-center gap-4 text-sm text-gray-500">
                        <div class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <span><?php echo $decision['option_count']; ?> options</span>
                        </div>
                        
                        <?php if ($decision['vote_count'] > 0): ?>
                        <div class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                            <span><?php echo $decision['vote_count']; ?> votes</span>
                        </div>
                        <?php endif; ?>

                        <?php if ($decision['deadline']): ?>
                        <div class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span>Due <?php echo formatDate($decision['deadline']); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>

            <!-- Mobile Create Button -->
            <div class="sm:hidden fixed bottom-6 right-6">
                <a href="/decisions/create" class="flex items-center justify-center w-14 h-14 bg-purple-600 text-white rounded-full shadow-lg hover:bg-purple-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Onboarding Modal (show for first-time users) -->
    <?php if ($showOnboarding): ?>
        <?php include __DIR__ . '/components/onboarding-modal.php'; ?>
    <?php endif; ?>

    <!-- Alpine.js for dropdown menu -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
</body>
</html>