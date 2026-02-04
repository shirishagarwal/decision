<?php
/**
 * Profile Menu Component with Sticky Hover
 * Include this in navigation where you want the profile dropdown
 * 
 * Usage:
 * include(__DIR__ . '/components/profile-menu.php');
 */

if (!isLoggedIn()) return;

$user = getCurrentUser();
?>

<!-- Profile Menu -->
<div class="relative" id="profileMenu">
    <button id="profileBtn" class="flex items-center gap-2 px-3 py-2 hover:bg-gray-50 rounded-lg transition">
        <img src="<?php echo e($user['avatar_url'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($user['name'])); ?>" 
             alt="<?php echo e($user['name']); ?>"
             class="w-8 h-8 rounded-full ring-2 ring-gray-200">
        <svg class="w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="6 9 12 15 18 9"></polyline>
        </svg>
    </button>

    <!-- Dropdown -->
    <div id="profileDropdown" class="hidden absolute right-0 top-full mt-2 w-64 bg-white rounded-xl shadow-xl border border-gray-200 py-2 z-[9999]">
        <!-- User Info -->
        <div class="px-4 py-3 border-b border-gray-100">
            <div class="font-semibold text-gray-900 truncate"><?php echo e($user['name']); ?></div>
            <div class="text-sm text-gray-500 truncate"><?php echo e($user['email']); ?></div>
        </div>

        <!-- Menu Items -->
        <div class="py-2">
            <a href="organization-dashboard.php" class="flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50 transition">
                <svg class="w-5 h-5 text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="3" y1="9" x2="21" y2="9"></line>
                    <line x1="9" y1="21" x2="9" y2="9"></line>
                </svg>
                <span class="text-gray-700 font-medium">Organization</span>
            </a>

            <a href="insights.php" class="flex items-center gap-3 px-4 py-2.5 hover:bg-purple-50 transition">
                <svg class="w-5 h-5 text-purple-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
                </svg>
                <span class="text-purple-600 font-medium">🧠 Decision Intelligence</span>
            </a>

            <a href="settings.php" class="flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50 transition">
                <svg class="w-5 h-5 text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="3"></circle>
                    <path d="M12 1v6m0 6v6m-6-6h6m6 0h-6m-3.3-7.5l4.2 4.2m4.2 4.2l-4.2 4.2m-4.2-4.2l-4.2 4.2m4.2-4.2l4.2-4.2"></path>
                </svg>
                <span class="text-gray-700 font-medium">Settings</span>
            </a>
        </div>

        <!-- Sign Out -->
        <div class="border-t border-gray-100 pt-2">
            <a href="auth/logout.php" class="flex items-center gap-3 px-4 py-2.5 hover:bg-red-50 transition">
                <svg class="w-5 h-5 text-red-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                    <polyline points="16 17 21 12 16 7"></polyline>
                    <line x1="21" y1="12" x2="9" y2="12"></line>
                </svg>
                <span class="text-red-600 font-medium">Sign Out</span>
            </a>
        </div>
    </div>
</div>

<script>
// Better hover behavior with delay (same as org switcher)
(function() {
    const btn = document.getElementById('profileBtn');
    const dropdown = document.getElementById('profileDropdown');
    const container = document.getElementById('profileMenu');
    let hideTimeout;

    function showDropdown() {
        clearTimeout(hideTimeout);
        dropdown.classList.remove('hidden');
    }

    function hideDropdown() {
        hideTimeout = setTimeout(() => {
            dropdown.classList.add('hidden');
        }, 500); // 500ms delay - same as org switcher
    }

    // Show on button hover/click
    btn.addEventListener('mouseenter', showDropdown);
    btn.addEventListener('click', (e) => {
        e.preventDefault();
        showDropdown();
    });

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