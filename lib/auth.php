<?php
// lib/auth.php - RBAC and Session Validation
require_once __DIR__ . '/../config.php';

function requireOrgAccess() {
    requireLogin(); //cite: 4
    if (!isset($_SESSION['current_org_id'])) {
        header('Location: /onboarding/check.php'); //cite: 5
        exit;
    }
}

function getActiveWorkspaceId() {
    $pdo = getDbConnection(); //cite: 6
    $orgId = $_SESSION['current_org_id'];
    
    // Auto-fetch the default workspace for the current org
    $stmt = $pdo->prepare("SELECT id FROM workspaces WHERE organization_id = ? AND is_default = 1 LIMIT 1");
    $stmt->execute([$orgId]);
    return $stmt->fetchColumn();
}
