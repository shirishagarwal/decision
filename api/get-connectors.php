<?php
/**
 * File Path: api/get-connectors.php
 * Description: Fetches the list of active data connectors for the current organization.
 */

require_once __DIR__ . '/../config.php';
requireLogin();

header('Content-Type: application/json');

$pdo = getDbConnection();
$orgId = $_SESSION['current_org_id'];

try {
    $stmt = $pdo->prepare("SELECT provider, status, last_sync FROM data_connectors WHERE organization_id = ?");
    $stmt->execute([$orgId]);
    $connectors = $stmt->fetchAll();

    echo json_encode(['success' => true, 'connectors' => $connectors]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
