<?php
/**
 * Onboarding Check
 * 
 * Call this after user successfully logs in via OAuth
 * Determines where to send them based on account state
 */

require_once __DIR__ . '/../config.php';

if (!isLoggedIn()) {
    redirect(APP_URL . '/index.php');
}

$user = getCurrentUser();
$pdo = getDbConnection();

// Check if user has any organizations
$stmt = $pdo->prepare("
    SELECT COUNT(*) as org_count 
    FROM organization_members 
    WHERE user_id = ? AND status = 'active'
");
$stmt->execute([$user['id']]);
$orgCount = $stmt->fetch()['org_count'];

// Check if they came from account type selection
$accountType = $_GET['type'] ?? $_SESSION['account_type'] ?? null;

if ($orgCount > 0) {
    // User already has organization(s) - go to dashboard
    redirect(APP_URL . '/dashboard.php');
}

// New user - needs to choose account type or create organization
if ($accountType === 'personal') {
    // User chose personal - create personal org automatically
    try {
        $pdo->beginTransaction();
        
        // Create personal organization
        $stmt = $pdo->prepare("
            INSERT INTO organizations (
                name, slug, type, plan_type, plan_status,
                max_users, max_workspaces, max_decisions_per_month
            ) VALUES (?, ?, 'personal', 'free', 'active', 1, 1, 10)
        ");
        
        $slug = 'personal-' . $user['id'] . '-' . substr(md5($user['email']), 0, 6);
        $stmt->execute([
            $user['name'] . "'s Workspace",
            $slug
        ]);
        
        $orgId = $pdo->lastInsertId();
        
        // Add user as owner
        $stmt = $pdo->prepare("
            INSERT INTO organization_members (organization_id, user_id, role, status, joined_at)
            VALUES (?, ?, 'owner', 'active', NOW())
        ");
        $stmt->execute([$orgId, $user['id']]);
        
        // Create default workspace
        $stmt = $pdo->prepare("
            INSERT INTO workspaces (organization_id, name, type, is_default, created_by)
            VALUES (?, 'My Decisions', 'personal', TRUE, ?)
        ");
        $stmt->execute([$orgId, $user['id']]);
        $workspaceId = $pdo->lastInsertId();
        
        // Add user to workspace
        $stmt = $pdo->prepare("
            INSERT INTO workspace_members (workspace_id, user_id, role)
            VALUES (?, ?, 'admin')
        ");
        $stmt->execute([$workspaceId, $user['id']]);
        
        $pdo->commit();
        
        // Clear session type
        unset($_SESSION['account_type']);
        
        // Redirect to dashboard
        redirect(APP_URL . '/dashboard.php');
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Personal org creation error: " . $e->getMessage());
        die("Failed to create your workspace. Please try again or contact support.");
    }
    
} elseif ($accountType === 'business') {
    // User chose business - redirect to org creation form
    unset($_SESSION['account_type']);
    redirect(APP_URL . '/onboarding/create-organization.php');
    
} else {
    // No account type chosen yet - show selection page
    redirect(APP_URL . '/choose-account-type.php');
}