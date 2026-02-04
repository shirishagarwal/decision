<?php
/**
 * Setup Personal Workspace
 * Automatically creates a free personal organization for the user.
 */

require_once __DIR__ . '/../config.php';
requireLogin();

$user = getCurrentUser();
$pdo = getDbConnection();

try {
    $pdo->beginTransaction();

    // 1. Create a "Personal" Organization
    $orgName = $user['name'] . "'s Private Vault";
    $slug = 'personal-' . $user['id'] . '-' . bin2hex(random_bytes(3));
    
    $stmt = $pdo->prepare("
        INSERT INTO organizations (name, slug, type, plan_type, owner_id) 
        VALUES (?, ?, 'personal', 'free', ?)
    ");
    $stmt->execute([$orgName, $slug, $user['id']]);
    $orgId = $pdo->lastInsertId();

    // 2. Add User as Owner of this Org
    $stmt = $pdo->prepare("
        INSERT INTO organization_members (organization_id, user_id, role, status) 
        VALUES (?, ?, 'owner', 'active')
    ");
    $stmt->execute([$orgId, $user['id']]);

    // 3. Create Default Workspace
    $stmt = $pdo->prepare("
        INSERT INTO workspaces (organization_id, name, is_default) 
        VALUES (?, 'My Decisions', 1)
    ");
    $stmt->execute([$orgId]);

    // 4. Update User Profile
    $stmt = $pdo->prepare("UPDATE users SET onboarding_completed = 1 WHERE id = ?");
    $stmt->execute([$user['id']]);

    $pdo->commit();

    // Set current session org
    $_SESSION['current_org_id'] = $orgId;
    header('Location: /dashboard.php');
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Personal setup failed: " . $e->getMessage());
    die("Setup failed. Please try again.");
}
