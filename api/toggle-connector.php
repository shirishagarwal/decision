<?php
/**
 * File Path: api/toggle-connector.php
 * Description: Connects or disconnects a third-party data provider.
 */

require_once __DIR__ . '/../config.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$provider = $input['provider'] ?? '';
$action = $input['action'] ?? ''; // 'connect' or 'disconnect'
$orgId = $_SESSION['current_org_id'];

if (empty($provider) || !in_array($action, ['connect', 'disconnect'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid parameters.']);
    exit;
}

$pdo = getDbConnection();

try {
    if ($action === 'connect') {
        // In a real production app, this would trigger an OAuth flow.
        // For now, we simulate an active connection.
        $stmt = $pdo->prepare("
            INSERT INTO data_connectors (organization_id, provider, status, last_sync)
            VALUES (?, ?, 'active', NOW())
            ON DUPLICATE KEY UPDATE status = 'active', last_sync = NOW()
        ");
        $stmt->execute([$orgId, $provider]);
    } else {
        $stmt = $pdo->prepare("DELETE FROM data_connectors WHERE organization_id = ? AND provider = ?");
        $stmt->execute([$orgId, $provider]);
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
