<?php
/**
 * Onboarding Check
 * Determines if the user needs to choose between Personal (B2C) or Business (B2B).
 */

require_once __DIR__ . '/../config.php';
requireLogin();

$user = getCurrentUser();
$pdo = getDbConnection();

// Check for organization memberships
$stmt = $pdo->prepare("
    SELECT organization_id 
    FROM organization_members 
    WHERE user_id = ? AND status = 'active'
    LIMIT 1
");
$stmt->execute([$user['id']]);
$org = $stmt->fetch();

if ($org) {
    // If they already have an org, set it as current and go to dashboard
    $_SESSION['current_org_id'] = $org['organization_id'];
    header('Location: /dashboard.php');
} else {
    // Brand new user: Force them to choose Account Type
    header('Location: /choose-account-type.php');
}
exit;
