<?php
/**
 * Organization Switcher Component
 * Include this in any page where user might need to switch organizations
 * 
 * Usage:
 * include(__DIR__ . '/components/org-switcher.php');
 */

if (!isLoggedIn()) return;

$user = getCurrentUser();
$pdo = getDbConnection();

// Get all user's organizations
$stmt = $pdo->prepare("
    SELECT o.*, om.role,
           (SELECT COUNT(*) FROM organization_members WHERE organization_id = o.id AND status = 'active') as member_count
    FROM organizations o
    INNER JOIN organization_members om ON o.id = om.organization_id
    WHERE om.user_id = ? AND o.deleted_at IS NULL AND om.status = 'active'
    ORDER BY om.role = 'owner' DESC, o.type = 'business' DESC, o.created_at DESC
");
$stmt->execute([$user['id']]);
$userOrgs = $stmt->fetchAll();

// Get current organization
$currentOrgId = $_SESSION['current_org_id'] ?? null;
if (!$currentOrgId && !empty($userOrgs)) {
    $currentOrgId = $userOrgs[0]['id'];
    $_SESSION['current_org_id'] = $currentOrgId;
}

$currentOrg = null;
foreach ($userOrgs as $org) {
    if ($org['id'] == $currentOrgId) {
        $currentOrg = $org;
        break;
    }
}
?>

<!-- Organization Switcher -->
<div class="relative" id="orgSwitcher">
    <button id="orgSwitcherBtn" class="flex items-center gap-2 px-3 py-2 hover:bg-gray-50 rounded-lg transition">
        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center text-white font-bold text-sm">
            <?php echo strtoupper(substr($currentOrg['name'] ?? 'O', 0, 1)); ?>
        </div>
        <div class="text-left hidden sm:block">
            <div class="text-sm font-semibold text-gray-900"><?php echo e($currentOrg['name'] ?? 'Select Org'); ?></div>
            <div class="text-xs text-gray-500"><?php echo ucfirst($currentOrg['plan_type'] ?? ''); ?></div>
        </div>
        <svg class="w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="6 9 12 15 18 9"></polyline>
        </svg>
    </button>

    <!-- Dropdown -->
    <div id="orgDropdown" class="hidden absolute top-full left-0 mt-2 w-80 bg-white rounded-xl shadow-xl border border-gray-200 py-2 z-[9999]">
        <div class="px-4 py-2 border-b border-gray-100">
            <div class="text-xs font-semibold text-gray-500 uppercase">Your Organizations</div>
        </div>

        <?php foreach ($userOrgs as $org): ?>
        <a href="?switch_org=<?php echo $org['id']; ?>" 
           class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 <?php echo $org['id'] == $currentOrgId ? 'bg-indigo-50' : ''; ?>">
            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center text-white font-bold">
                <?php echo strtoupper(substr($org['name'], 0, 1)); ?>
            </div>
            <div class="flex-1 min-w-0">
                <div class="font-semibold text-gray-900 truncate"><?php echo e($org['name']); ?></div>
                <div class="text-xs text-gray-500">
                    <?php echo ucfirst($org['plan_type']); ?> • 
                    <?php echo $org['member_count']; ?> member<?php echo $org['member_count'] != 1 ? 's' : ''; ?> •
                    <?php echo ucfirst($org['role']); ?>
                </div>
            </div>
            <?php if ($org['id'] == $currentOrgId): ?>
            <svg class="w-5 h-5 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
            <?php endif; ?>
        </a>
        <?php endforeach; ?>

        <div class="border-t border-gray-100 mt-2 pt-2">
            <a href="onboarding/create-organization.php" 
               class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 text-indigo-600">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span class="font-medium">Create New Organization</span>
            </a>
        </div>
    </div>
</div>

<script>
// Better hover behavior with delay
(function() {
    const btn = document.getElementById('orgSwitcherBtn');
    const dropdown = document.getElementById('orgDropdown');
    const container = document.getElementById('orgSwitcher');
    let hideTimeout;

    function showDropdown() {
        clearTimeout(hideTimeout);
        dropdown.classList.remove('hidden');
    }

    function hideDropdown() {
        hideTimeout = setTimeout(() => {
            dropdown.classList.add('hidden');
        }, 500); // 500ms delay - gives user more time
    }

    // Show on button hover/click
    btn.addEventListener('mouseenter', showDropdown);
    btn.addEventListener('click', showDropdown);

    // Keep visible when hovering dropdown itself
    dropdown.addEventListener('mouseenter', () => clearTimeout(hideTimeout));
    dropdown.addEventListener('mouseleave', hideDropdown);

    // Hide when leaving container
    container.addEventListener('mouseleave', hideDropdown);

    // Click outside to close
    document.addEventListener('click', (e) => {
        if (!container.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });
})();
</script>

<?php
// Handle organization switching
if (isset($_GET['switch_org'])) {
    $newOrgId = $_GET['switch_org'];
    
    // Verify user has access
    $stmt = $pdo->prepare("SELECT id FROM organization_members WHERE organization_id = ? AND user_id = ? AND status = 'active'");
    $stmt->execute([$newOrgId, $user['id']]);
    
    if ($stmt->fetch()) {
        $_SESSION['current_org_id'] = $newOrgId;
        
        // Redirect to clean URL
        $cleanUrl = strtok($_SERVER['REQUEST_URI'], '?');
        header('Location: ' . $cleanUrl);
        exit;
    }
}
?>