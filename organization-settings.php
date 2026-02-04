<?php
/**
 * DecisionVault - Organization Settings
 * Allows Owners and Admins to manage branding and core settings.
 */
require_once __DIR__ . '/config.php';
requireLogin();

$user = getCurrentUser();
$pdo = getDbConnection();

$orgId = $_GET['id'] ?? $_SESSION['current_org_id'] ?? null;

// Permission Check: Must be Owner or Admin
$stmt = $pdo->prepare("
    SELECT o.*, om.role as user_role
    FROM organizations o
    INNER JOIN organization_members om ON o.id = om.organization_id
    WHERE o.id = ? AND om.user_id = ? AND om.status = 'active'
");
$stmt->execute([$orgId, $user['id']]);
$org = $stmt->fetch();

if (!$org || !in_array($org['user_role'], ['owner', 'admin'])) {
    header('Location: /error-403.php');
    exit;
}

$success = '';
$error = '';

// Handle Settings Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $website = trim($_POST['website'] ?? '');
    $colorPrimary = $_POST['color_primary'] ?? '#4F46E5';
    $colorSecondary = $_POST['color_secondary'] ?? '#7C3AED';

    try {
        $stmt = $pdo->prepare("
            UPDATE organizations 
            SET name = ?, website = ?, color_primary = ?, color_secondary = ?, updated_at = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$name, $website, $colorPrimary, $colorSecondary, $orgId]);
        
        // Log Audit Event
        $stmt = $pdo->prepare("
            INSERT INTO audit_logs (organization_id, user_id, action, entity_type, entity_id, new_values)
            VALUES (?, ?, 'updated_settings', 'organization', ?, ?)
        ");
        $stmt->execute([$orgId, $user['id'], $orgId, json_encode($_POST)]);
        
        $success = 'Organization settings updated successfully.';
        $org['name'] = $name; // Update local variable for UI
    } catch (Exception $e) {
        $error = 'Failed to update settings. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Org Settings | <?php echo htmlspecialchars($org['name']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <nav class="bg-white border-b border-gray-200 p-4">
        <div class="max-w-4xl mx-auto flex justify-between items-center">
            <a href="/organization-dashboard.php" class="text-indigo-600 font-bold">← Dashboard</a>
            <h1 class="font-black text-xl">Organization Settings</h1>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto py-12 px-4">
        <?php if ($success): ?>
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="grid md:grid-cols-3 gap-8">
            <aside class="space-y-2">
                <a href="#" class="block p-3 bg-indigo-600 text-white rounded-xl font-bold">General & Branding</a>
                <a href="/organization-dashboard.php" class="block p-3 text-gray-500 hover:bg-gray-100 rounded-xl font-medium">Team Members</a>
                <a href="/billing.php" class="block p-3 text-gray-500 hover:bg-gray-100 rounded-xl font-medium">Plans & Billing</a>
            </aside>

            <form method="POST" class="md:col-span-2 bg-white rounded-3xl border shadow-sm p-8 space-y-8">
                <section>
                    <h2 class="text-xl font-bold mb-6">Company Identity</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-400 uppercase mb-2">Company Name</label>
                            <input type="text" name="name" value="<?php echo htmlspecialchars($org['name']); ?>"
                                   class="w-full p-4 border-2 rounded-2xl outline-indigo-600">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-400 uppercase mb-2">Website</label>
                            <input type="url" name="website" value="<?php echo htmlspecialchars($org['website'] ?? ''); ?>"
                                   placeholder="https://company.com"
                                   class="w-full p-4 border-2 rounded-2xl outline-indigo-600">
                        </div>
                    </div>
                </section>

                <section class="pt-8 border-t">
                    <h2 class="text-xl font-bold mb-6">Brand Customization</h2>
                    <p class="text-sm text-gray-500 mb-6">These colors will be used across your dashboards and shared decisions.</p>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-400 uppercase mb-2">Primary Color</label>
                            <input type="color" name="color_primary" value="<?php echo $org['color_primary'] ?: '#4F46E5'; ?>"
                                   class="w-full h-14 p-1 rounded-xl cursor-pointer">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-400 uppercase mb-2">Secondary Color</label>
                            <input type="color" name="color_secondary" value="<?php echo $org['color_secondary'] ?: '#7C3AED'; ?>"
                                   class="w-full h-14 p-1 rounded-xl cursor-pointer">
                        </div>
                    </div>
                </section>

                <div class="pt-8">
                    <button type="submit" class="w-full bg-indigo-600 text-white py-4 rounded-2xl font-black text-lg shadow-xl hover:bg-indigo-700 transition">
                        Save Organization Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
