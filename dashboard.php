<?php
/**
 * PRODUCTION DASHBOARD - Complete with Workspaces, AI, Everything
 */

require_once __DIR__ . '/config.php';
requireLogin();

$user = getCurrentUser();
$pdo = getDbConnection();

// Get user's default workspace or create one
$stmt = $pdo->prepare("
    SELECT w.* 
    FROM workspaces w
    LEFT JOIN workspace_members wm ON w.id = wm.workspace_id
    WHERE (wm.user_id = ? OR w.owner_id = ?) AND w.is_default = 1
    LIMIT 1
");
$stmt->execute([$user['id'], $user['id']]);
$workspace = $stmt->fetch();

// If no workspace, create default personal workspace
if (!$workspace) {
    $stmt = $pdo->prepare("
        INSERT INTO workspaces (organization_id, name, type, is_default, owner_id, created_at, updated_at)
        VALUES (?, 'My Workspace', 'personal', 1, ?, NOW(), NOW())
    ");
    $stmt->execute([$user['organization_id'], $user['id']]);
    $workspaceId = $pdo->lastInsertId();
    
    $stmt = $pdo->prepare("
        INSERT INTO workspace_members (workspace_id, user_id, role, joined_at)
        VALUES (?, ?, 'admin', NOW())
    ");
    $stmt->execute([$workspaceId, $user['id']]);
    
    $stmt = $pdo->prepare("SELECT * FROM workspaces WHERE id = ?");
    $stmt->execute([$workspaceId]);
    $workspace = $stmt->fetch();
}

// Get all user's workspaces for switcher
$stmt = $pdo->prepare("
    SELECT DISTINCT w.* 
    FROM workspaces w
    LEFT JOIN workspace_members wm ON w.id = wm.workspace_id
    WHERE wm.user_id = ? OR w.owner_id = ?
    ORDER BY w.is_default DESC, w.created_at DESC
");
$stmt->execute([$user['id'], $user['id']]);
$allWorkspaces = $stmt->fetchAll();

// Get decisions for current workspace
$stmt = $pdo->prepare("
    SELECT d.*, u.name as creator_name,
           (SELECT COUNT(*) FROM options WHERE decision_id = d.id) as option_count
    FROM decisions d
    LEFT JOIN users u ON d.created_by = u.id
    WHERE d.workspace_id = ?
    ORDER BY d.created_at DESC
    LIMIT 6
");
$stmt->execute([$workspace['id']]);
$recentDecisions = $stmt->fetchAll();

// Get stats
$stmt = $pdo->prepare("SELECT COUNT(*) FROM decisions WHERE workspace_id = ?");
$stmt->execute([$workspace['id']]);
$totalDecisions = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM decisions WHERE workspace_id = ? AND status IN ('Proposed', 'Decided')");
$stmt->execute([$workspace['id']]);
$activeDecisions = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM decisions WHERE workspace_id = ? AND status = 'Implemented'");
$stmt->execute([$workspace['id']]);
$completedDecisions = $stmt->fetchColumn();

// Get AI insights count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM decisions WHERE workspace_id = ? AND ai_generated = 1");
$stmt->execute([$workspace['id']]);
$aiGeneratedCount = $stmt->fetchColumn();

// Check onboarding
$showOnboarding = !($user['onboarding_completed'] ?? false);

$pageTitle = "Dashboard";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> | DecisionVault</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50">
    
    <!-- Navigation -->
    <nav class="bg-white border-b border-gray-200 sticky top-0 z-50">
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
                        <a href="/templates" class="text-gray-600 hover:text-gray-900 font-medium">
                            Templates
                        </a>
                        <a href="/insights" class="text-gray-600 hover:text-gray-900 font-medium">
                            Insights
                        </a>
                    </div>
                </div>
                
                <div class="flex items-center gap-4">
                    
                    <!-- Workspace Switcher -->
                    <?php if (count($allWorkspaces) > 1): ?>
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="flex items-center gap-2 px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            <span class="text-sm font-medium text-gray-700"><?php echo htmlspecialchars($workspace['name']); ?></span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        
                        <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-xl border border-gray-200 py-2" style="display: none;">
                            <?php foreach ($allWorkspaces as $ws): ?>
                            <a href="?workspace_id=<?php echo $ws['id']; ?>" class="block px-4 py-2 hover:bg-gray-50 <?php echo $ws['id'] == $workspace['id'] ? 'bg-purple-50' : ''; ?>">
                                <div class="font-medium text-gray-900"><?php echo htmlspecialchars($ws['name']); ?></div>
                                <div class="text-xs text-gray-500"><?php echo ucfirst($ws['type']); ?></div>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="hidden md:flex items-center gap-2 px-3 py-2 bg-gray-100 rounded-lg">
                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <span class="text-sm font-medium text-gray-700"><?php echo htmlspecialchars($workspace['name']); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <!-- AI Assistant Quick Access -->
                    <a href="/ai-assistant" class="hidden md:flex items-center gap-2 px-3 py-2 text-purple-600 hover:bg-purple-50 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                        <span class="text-sm font-semibold">AI Assistant</span>
                    </a>
                    
                    <!-- Create Decision Button -->
                    <a href="/create-decision" class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg font-semibold hover:bg-purple-700 transition-colors shadow-sm">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        New Decision
                    </a>
                    
                    <!-- User Menu -->
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="flex items-center gap-2">
                            <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                                <?php if ($user['avatar_url']): ?>
                                <img src="<?php echo htmlspecialchars($user['avatar_url']); ?>" class="w-8 h-8 rounded-full" alt="">
                                <?php else: ?>
                                <span class="text-purple-600 font-semibold text-sm">
                                    <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </button>
                        
                        <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border border-gray-200 py-2" style="display: none;">
                            <div class="px-4 py-2 border-b border-gray-200">
                                <div class="font-medium text-gray-900"><?php echo htmlspecialchars($user['name']); ?></div>
                                <div class="text-xs text-gray-500"><?php echo htmlspecialchars($user['email']); ?></div>
                            </div>
                            <a href="/settings" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Settings</a>
                            <?php if ($user['organization_id']): ?>
                            <a href="/organization-dashboard" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Organization</a>
                            <?php endif; ?>
                            <a href="/help" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Help</a>
                            <hr class="my-2">
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
                Here's an overview of <strong><?php echo htmlspecialchars($workspace['name']); ?></strong>
            </p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
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
                <div class="text-xs text-gray-500 mt-1">Implemented</div>
            </div>

            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-pink-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                    </div>
                    <span class="text-3xl font-black text-gray-900"><?php echo $aiGeneratedCount; ?></span>
                </div>
                <div class="text-sm font-semibold text-gray-900">AI Insights</div>
                <div class="text-xs text-gray-500 mt-1">Generated</div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <a href="/create-decision" class="bg-gradient-to-br from-purple-600 to-pink-600 rounded-xl p-6 text-white hover:shadow-lg transition-all">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                    </div>
                    <div>
                        <div class="font-bold text-lg">New Decision</div>
                        <div class="text-sm text-white/80">Start tracking a decision</div>
                    </div>
                </div>
            </a>

            <a href="/ai-assistant" class="bg-white rounded-xl p-6 border-2 border-purple-200 hover:border-purple-400 transition-all">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="font-bold text-lg text-gray-900">AI Assistant</div>
                        <div class="text-sm text-gray-600">Get AI recommendations</div>
                    </div>
                </div>
            </a>

            <a href="/templates" class="bg-white rounded-xl p-6 border-2 border-gray-200 hover:border-gray-400 transition-all">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="font-bold text-lg text-gray-900">Templates</div>
                        <div class="text-sm text-gray-600">Use decision templates</div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Recent Decisions -->
        <?php if (empty($recentDecisions)): ?>
            <?php if (file_exists(__DIR__ . '/components/empty-state-dashboard.php')): ?>
                <?php include __DIR__ . '/components/empty-state-dashboard.php'; ?>
            <?php else: ?>
                <div class="bg-white rounded-xl p-12 text-center shadow-sm border border-gray-200">
                    <div class="text-6xl mb-4">🎯</div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">No decisions yet</h3>
                    <p class="text-gray-600 mb-6">Create your first decision to get AI-powered recommendations</p>
                    <a href="/create-decision" class="inline-block bg-purple-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-purple-700">
                        Create First Decision
                    </a>
                </div>
            <?php endif; ?>
        <?php else: ?>
            
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-900">Recent Decisions</h2>
                <a href="/decisions" class="text-purple-600 hover:text-purple-700 font-semibold text-sm flex items-center gap-1">
                    View All
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <?php foreach ($recentDecisions as $decision): ?>
                <a href="/decision/<?php echo $decision['id']; ?>" class="block bg-white rounded-xl p-6 shadow-sm border border-gray-200 hover:shadow-md hover:border-purple-300 transition-all">
                    
                    <div class="flex items-center justify-between mb-3">
                        <?php
                        $statusColors = [
                            'Proposed' => 'bg-yellow-100 text-yellow-700',
                            'Decided' => 'bg-blue-100 text-blue-700',
                            'Implemented' => 'bg-green-100 text-green-700',
                            'Reviewing' => 'bg-purple-100 text-purple-700',
                            'Abandoned' => 'bg-gray-100 text-gray-700'
                        ];
                        $statusColor = $statusColors[$decision['status']] ?? 'bg-gray-100 text-gray-700';
                        ?>
                        <span class="px-3 py-1 <?php echo $statusColor; ?> rounded-full text-xs font-semibold">
                            <?php echo $decision['status']; ?>
                        </span>
                        <span class="text-xs text-gray-500">
                            <?php echo timeAgo($decision['created_at']); ?>
                        </span>
                    </div>

                    <h3 class="font-bold text-lg text-gray-900 mb-2">
                        <?php echo htmlspecialchars($decision['title']); ?>
                    </h3>

                    <?php if ($decision['description']): ?>
                    <p class="text-sm text-gray-600 mb-4 line-clamp-2">
                        <?php echo htmlspecialchars($decision['description']); ?>
                    </p>
                    <?php endif; ?>

                    <div class="flex items-center gap-4 text-sm text-gray-500">
                        <?php if ($decision['category']): ?>
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                            <?php echo $decision['category']; ?>
                        </span>
                        <?php endif; ?>
                        
                        <?php if ($decision['option_count'] > 0): ?>
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                            </svg>
                            <?php echo $decision['option_count']; ?> options
                        </span>
                        <?php endif; ?>

                        <?php if ($decision['ai_generated']): ?>
                        <span class="flex items-center gap-1 text-purple-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                            </svg>
                            AI-powered
                        </span>
                        <?php endif; ?>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Onboarding Modal -->
    <?php if ($showOnboarding && file_exists(__DIR__ . '/components/onboarding-modal.php')): ?>
        <?php include __DIR__ . '/components/onboarding-modal.php'; ?>
    <?php endif; ?>

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
