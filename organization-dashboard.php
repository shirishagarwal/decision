<?php
require_once __DIR__ . '/config.php';

if (!isLoggedIn()) {
    redirect(APP_URL . '/index.php');
}

$user = getCurrentUser();
$pdo = getDbConnection();

// Get organization ID from URL or session
$orgId = $_GET['id'] ?? $_SESSION['current_org_id'] ?? null;

// If no org specified, get user's first org
if (!$orgId) {
    $stmt = $pdo->prepare("
        SELECT organization_id 
        FROM organization_members 
        WHERE user_id = ? AND status = 'active'
        ORDER BY role = 'owner' DESC, id ASC
        LIMIT 1
    ");
    $stmt->execute([$user['id']]);
    $orgId = $stmt->fetchColumn();
    
    if (!$orgId) {
        redirect(APP_URL . '/onboarding/check.php');
    }
}

// Store current org in session
$_SESSION['current_org_id'] = $orgId;

// Get organization details and user's role
$stmt = $pdo->prepare("
    SELECT o.*, om.role as user_role
    FROM organizations o
    INNER JOIN organization_members om ON o.id = om.organization_id
    WHERE o.id = ? AND om.user_id = ? AND o.deleted_at IS NULL
");
$stmt->execute([$orgId, $user['id']]);
$org = $stmt->fetch();

if (!$org) {
    die('Organization not found or access denied');
}

// Get organization stats
$stmt = $pdo->prepare("SELECT * FROM v_organization_stats WHERE organization_id = ?");
$stmt->execute([$orgId]);
$stats = $stmt->fetch();

// Get recent members
$stmt = $pdo->prepare("
    SELECT om.*, u.name, u.email, u.avatar_url
    FROM organization_members om
    INNER JOIN users u ON om.user_id = u.id
    WHERE om.organization_id = ? AND om.status = 'active'
    ORDER BY om.role = 'owner' DESC, om.role = 'admin' DESC, om.joined_at DESC
    LIMIT 10
");
$stmt->execute([$orgId]);
$members = $stmt->fetchAll();

// Get recent decisions
$stmt = $pdo->prepare("
    SELECT d.*, u.name as creator_name
    FROM decisions d
    INNER JOIN workspaces w ON d.workspace_id = w.id
    INNER JOIN users u ON d.created_by = u.id
    WHERE w.organization_id = ?
    ORDER BY d.created_at DESC
    LIMIT 5
");
$stmt->execute([$orgId]);
$recentDecisions = $stmt->fetchAll();

// Get pending invitations
$stmt = $pdo->prepare("
    SELECT * FROM organization_invitations
    WHERE organization_id = ? AND accepted_at IS NULL AND expires_at > NOW()
    ORDER BY created_at DESC
");
$stmt->execute([$orgId]);
$pendingInvites = $stmt->fetchAll();

$isOwnerOrAdmin = in_array($org['user_role'], ['owner', 'admin']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($org['name']); ?> - Organization Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
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
                <h1 class="text-lg font-bold text-gray-900"><?php echo e($org['name']); ?></h1>
                <span class="px-2 py-1 bg-indigo-100 text-indigo-800 rounded text-xs font-medium uppercase">
                    <?php echo e($org['plan_type']); ?>
                </span>
            </div>
            
            <?php if ($isOwnerOrAdmin): ?>
            <a href="organization-settings.php?id=<?php echo $orgId; ?>" class="text-gray-600 hover:text-gray-900">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="3"></circle>
                    <path d="M12 1v6m0 6v6m-6-6h6m6 0h-6m-3.3-7.5l4.2 4.2m4.2 4.2l-4.2 4.2m-4.2-4.2l-4.2 4.2m4.2-4.2l4.2-4.2"></path>
                </svg>
            </a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <div class="text-sm text-gray-500 mb-1">Team Members</div>
                <div class="text-3xl font-bold text-gray-900"><?php echo $stats['total_members'] ?? 0; ?></div>
                <div class="text-xs text-gray-500 mt-1">of <?php echo $org['max_users']; ?> max</div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <div class="text-sm text-gray-500 mb-1">Workspaces</div>
                <div class="text-3xl font-bold text-gray-900"><?php echo $stats['total_workspaces'] ?? 0; ?></div>
                <div class="text-xs text-gray-500 mt-1">of <?php echo $org['max_workspaces']; ?> max</div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <div class="text-sm text-gray-500 mb-1">Total Decisions</div>
                <div class="text-3xl font-bold text-gray-900"><?php echo $stats['total_decisions'] ?? 0; ?></div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <div class="text-sm text-gray-500 mb-1">This Month</div>
                <div class="text-3xl font-bold text-gray-900"><?php echo $stats['decisions_this_month'] ?? 0; ?></div>
                <div class="text-xs text-gray-500 mt-1">of <?php echo $org['max_decisions_per_month']; ?> max</div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Team Members -->
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-900">Team Members (<?php echo count($members); ?>)</h2>
                    <?php if ($isOwnerOrAdmin): ?>
                    <button onclick="showInviteModal()" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium">
                        + Invite
                    </button>
                    <?php endif; ?>
                </div>

                <div class="space-y-3">
                    <?php foreach ($members as $member): ?>
                    <div class="flex items-center gap-3 p-3 hover:bg-gray-50 rounded-lg">
                        <img src="<?php echo e($member['avatar_url'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($member['name'])); ?>" 
                             alt="<?php echo e($member['name']); ?>"
                             class="w-10 h-10 rounded-full">
                        <div class="flex-1">
                            <div class="font-medium text-gray-900"><?php echo e($member['name']); ?></div>
                            <div class="text-sm text-gray-500"><?php echo e($member['email']); ?></div>
                        </div>
                        <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs font-medium uppercase">
                            <?php echo e($member['role']); ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>

                <?php if (count($pendingInvites) > 0): ?>
                <div class="mt-4 pt-4 border-t">
                    <div class="text-sm font-semibold text-gray-500 mb-2">Pending Invitations (<?php echo count($pendingInvites); ?>)</div>
                    <?php foreach ($pendingInvites as $invite): ?>
                    <div class="flex items-center justify-between py-2">
                        <span class="text-sm text-gray-600"><?php echo e($invite['email']); ?></span>
                        <span class="text-xs text-gray-400">invited <?php echo date('M j', strtotime($invite['created_at'])); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Recent Decisions -->
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-900">Recent Decisions</h2>
                    <a href="dashboard.php" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">
                        View All →
                    </a>
                </div>

                <?php if (empty($recentDecisions)): ?>
                <div class="text-center py-8 text-gray-500">
                    <div class="text-4xl mb-2">📋</div>
                    <p>No decisions yet</p>
                    <a href="templates.php" class="text-indigo-600 hover:underline text-sm">Create your first decision</a>
                </div>
                <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($recentDecisions as $decision): ?>
                    <a href="decision.php?id=<?php echo $decision['id']; ?>" 
                       class="block p-3 hover:bg-gray-50 rounded-lg border border-gray-100">
                        <div class="font-medium text-gray-900 mb-1"><?php echo e($decision['title']); ?></div>
                        <div class="flex items-center gap-2 text-xs text-gray-500">
                            <span><?php echo e($decision['creator_name']); ?></span>
                            <span>•</span>
                            <span><?php echo date('M j, Y', strtotime($decision['created_at'])); ?></span>
                            <span>•</span>
                            <span class="px-2 py-0.5 bg-gray-100 rounded"><?php echo e($decision['status']); ?></span>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Plan Info -->
        <?php if ($org['plan_status'] === 'trialing'): ?>
        <div class="mt-6 bg-gradient-to-r from-indigo-50 to-purple-50 border-2 border-indigo-200 rounded-xl p-6">
            <div class="flex items-start gap-4">
                <div class="text-3xl">🎉</div>
                <div class="flex-1">
                    <h3 class="font-bold text-indigo-900 mb-1">Free Trial Active</h3>
                    <p class="text-indigo-700 mb-3">
                        Your <?php echo ucfirst($org['plan_type']); ?> plan trial ends on 
                        <?php echo date('F j, Y', strtotime($org['trial_ends_at'])); ?>
                    </p>
                    <?php if ($org['user_role'] === 'owner'): ?>
                    <a href="billing.php?org=<?php echo $orgId; ?>" 
                       class="inline-block px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium text-sm">
                        Add Payment Method
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Invite Modal -->
    <div id="inviteModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-2xl max-w-md w-full p-6">
            <h2 class="text-2xl font-bold mb-4">Invite Team Member</h2>
            <form id="inviteForm">
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Email Address</label>
                    <input type="email" id="inviteEmail" required 
                           placeholder="colleague@company.com"
                           class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Role</label>
                    <select id="inviteRole" 
                            class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="member">Member</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="flex gap-3">
                    <button type="submit" 
                            class="flex-1 px-4 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium">
                        Send Invitation
                    </button>
                    <button type="button" onclick="hideInviteModal()"
                            class="px-4 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showInviteModal() {
            document.getElementById('inviteModal').classList.remove('hidden');
        }

        function hideInviteModal() {
            document.getElementById('inviteModal').classList.add('hidden');
        }

        document.getElementById('inviteForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const email = document.getElementById('inviteEmail').value;
            const role = document.getElementById('inviteRole').value;
            
            try {
                const response = await fetch('<?php echo APP_URL; ?>/api/organization-invites.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        organization_id: <?php echo $orgId; ?>,
                        email: email,
                        role: role
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert('✅ Invitation sent to ' + email);
                    hideInviteModal();
                    location.reload();
                } else {
                    alert('Error: ' + (data.error || 'Failed to send invitation'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to send invitation. Please try again.');
            }
        });
    </script>
</body>
</html>