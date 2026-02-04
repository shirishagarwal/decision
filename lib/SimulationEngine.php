<?php
/**
 * File Path: onboarding/create-organization.php
 * Description: Clean, formatted UI for business users to set up their team workspace.
 */

require_once __DIR__ . '/../config.php';
requireLogin();

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = getDbConnection();
    $name = trim($_POST['org_name']);
    $user = getCurrentUser();
    
    // Generate simple URL-friendly slug
    $slug = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)) . '-' . rand(100, 999);

    try {
        $pdo->beginTransaction();
        
        // 1. Create Business Org
        $stmt = $pdo->prepare("INSERT INTO organizations (name, slug, type, owner_id) VALUES (?, ?, 'business', ?)");
        $stmt->execute([$name, $slug, $user['id']]);
        $orgId = $pdo->lastInsertId();

        // 2. Add Owner
        $pdo->prepare("INSERT INTO organization_members (organization_id, user_id, role) VALUES (?, ?, 'owner')")->execute([$orgId, $user['id']]);
        
        $pdo->commit();

        $_SESSION['current_org_id'] = $orgId;
        header('Location: /dashboard.php');
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "The name or slug is taken. Please try a more unique name.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Launch Your Team Vault | DecisionVault</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap');
        body { background: #020617; font-family: 'Inter', sans-serif; color: white; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-6 bg-[radial-gradient(circle_at_center,_var(--tw-gradient-stops))] from-indigo-900/20 via-slate-950 to-slate-950">
    
    <div class="max-w-md w-full">
        <div class="bg-slate-900 border border-slate-800 p-10 rounded-3xl shadow-2xl">
            <div class="mb-10 text-center">
                <div class="w-20 h-20 bg-indigo-600 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-2xl shadow-indigo-500/20 text-3xl">🏢</div>
                <h1 class="text-3xl font-black">Company Identity</h1>
                <p class="text-slate-400 mt-2">Create the secure vault for your team's strategic intelligence.</p>
            </div>

            <?php if ($error): ?>
                <div class="mb-6 p-4 bg-red-500/10 border border-red-500/50 text-red-500 rounded-2xl text-sm font-bold text-center">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <div>
                    <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">Organization Name</label>
                    <input type="text" name="org_name" required autofocus placeholder="e.g. Acme Strategy Group"
                           class="w-full bg-slate-800 border-2 border-slate-700 p-4 rounded-2xl text-white outline-none focus:border-indigo-500 transition-all placeholder:text-slate-600">
                </div>
                
                <button type="submit" class="w-full bg-indigo-600 text-white py-5 rounded-2xl font-black text-lg hover:bg-indigo-700 transition shadow-xl shadow-indigo-500/20 active:scale-[0.98]">
                    Launch Organization
                </button>
            </form>
            
            <div class="mt-8 pt-8 border-t border-slate-800 text-center">
                <p class="text-slate-500 text-xs font-bold uppercase tracking-widest">Enterprise Security • Priority Support</p>
            </div>
        </div>
        
        <div class="mt-6 text-center">
            <a href="/dashboard.php" class="text-slate-500 hover:text-white transition text-sm font-bold">← Back to Personal Vault</a>
        </div>
    </div>

</body>
</html>
