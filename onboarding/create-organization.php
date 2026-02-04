<?php
require_once __DIR__ . '/../config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = getDbConnection();
    $user = getCurrentUser();
    
    $name = trim($_POST['org_name']);
    $slug = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $name));

    try {
        $pdo->beginTransaction();
        
        // 1. Create Business Organization
        $stmt = $pdo->prepare("INSERT INTO organizations (name, slug, type, owner_id) VALUES (?, ?, 'business', ?)");
        $stmt->execute([$name, $slug, $user['id']]);
        $orgId = $pdo->lastInsertId();

        // 2. Add Owner
        $stmt = $pdo->prepare("INSERT INTO organization_members (organization_id, user_id, role) VALUES (?, ?, 'owner')");
        $stmt->execute([$orgId, $user['id']]);

        // 3. Create Default Team Workspace
        $stmt = $pdo->prepare("INSERT INTO workspaces (organization_id, name, is_default) VALUES (?, 'General', 1)");
        $stmt->execute([$orgId]);

        $pdo->commit();
        $_SESSION['current_org_id'] = $orgId;
        header('Location: /dashboard.php');
        exit;
    } catch (Exception $e) { $pdo->rollBack(); die("Name already taken."); }
}
?>
<body class="bg-gray-50 flex items-center justify-center min-h-screen">
    <form method="POST" class="bg-white p-8 rounded-2xl shadow-lg max-w-sm w-full">
        <h2 class="text-2xl font-bold mb-4">Name your Company</h2>
        <input type="text" name="org_name" required placeholder="e.g. TechCorp"
               class="w-full border-2 p-3 rounded-lg mb-4 outline-indigo-600">
        <button type="submit" class="w-full bg-indigo-600 text-white py-3 rounded-lg font-bold">Create & Continue</button>
    </form>
</body>
