<?php
/**
 * File Path: includes/header.php
 * Description: Global navigation and branding.
 */
require_once __DIR__ . '/../config.php';
$user = getCurrentUser();
$orgId = $_SESSION['current_org_id'] ?? null;

// Ensure we have an org for the header display
$orgName = "Workspace";
if ($orgId) {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("SELECT name FROM organizations WHERE id = ?");
    $stmt->execute([$orgId]);
    $orgName = $stmt->fetchColumn() ?: "Workspace";
}
?>
<nav class="bg-white border-b border-gray-100 p-5 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto flex justify-between items-center">
        <div class="flex items-center gap-6">
            <a href="/dashboard.php" class="font-black text-2xl tracking-tighter text-gray-900 group">
                DECISION<span class="text-indigo-600">VAULT</span>
            </a>
            <div class="h-6 w-px bg-gray-200 hidden md:block"></div>
            <div class="hidden md:flex items-center gap-2 bg-gray-50 px-3 py-1.5 rounded-xl border border-gray-100">
                <div class="w-6 h-6 bg-indigo-600 rounded-md flex items-center justify-center text-white font-bold text-[10px] uppercase">
                    <?php echo substr($orgName, 0, 1); ?>
                </div>
                <span class="font-bold text-xs text-gray-700"><?php echo htmlspecialchars($orgName); ?></span>
            </div>
        </div>

        <div class="flex items-center gap-6">
            <div class="hidden lg:flex items-center gap-6 mr-4">
                <a href="/dashboard.php" class="text-xs font-black text-gray-400 uppercase tracking-widest hover:text-indigo-600 transition">Vault</a>
                <a href="/marketplace.php" class="text-xs font-black text-gray-400 uppercase tracking-widest hover:text-indigo-600 transition">Marketplace</a>
                <a href="/organization-settings.php" class="text-xs font-black text-gray-400 uppercase tracking-widest hover:text-indigo-600 transition">Settings</a>
            </div>
            
            <div class="flex items-center gap-3 pl-6 border-l border-gray-100">
                <div class="text-right hidden sm:block">
                    <div class="text-xs font-black text-gray-900 leading-none mb-1"><?php echo htmlspecialchars($user['name']); ?></div>
                    <a href="/auth/logout.php" class="text-[10px] font-bold text-red-400 uppercase hover:text-red-600 transition">Sign Out</a>
                </div>
                <img src="<?php echo $user['avatar_url'] ?: 'https://ui-avatars.com/api/?name='.urlencode($user['name']); ?>"
                     class="w-10 h-10 rounded-full border-2 border-white shadow-sm bg-gray-100">
            </div>
        </div>
    </div>
</nav>
