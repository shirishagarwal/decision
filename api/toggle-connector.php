<?php
/**
 * File Path: api/toggle-connector.php
 * Description: Connects or disconnects a third-party data provider.
 * Supports production OAuth redirect logic.
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
        /**
         * PRODUCTION OAUTH REDIRECT LOGIC
         * In a production environment, this is where you generate the OAuth URL.
         */
        $redirectUrl = null;
        
        switch ($provider) {
            case 'stripe':
                // Sample Stripe Connect URL
                // $redirectUrl = "https://connect.stripe.com/oauth/authorize?client_id=" . STRIPE_CLIENT_ID . "&state=" . $orgId . "...";
                break;
            case 'hubspot':
                // Sample HubSpot OAuth URL
                // $redirectUrl = "https://app.hubspot.com/oauth/authorize?client_id=" . HUBSPOT_CLIENT_ID . "&scope=crm.objects.contacts.read...";
                break;
        }

        // For now, if no redirect URL is configured, we immediately mark as active for simulation.
        if (!$redirectUrl) {
            $stmt = $pdo->prepare("
                INSERT INTO data_connectors (organization_id, provider, status, last_sync)
                VALUES (?, ?, 'active', NOW())
                ON DUPLICATE KEY UPDATE status = 'active', last_sync = NOW()
            ");
            $stmt->execute([$orgId, $provider]);
        }

        echo json_encode([
            'success' => true,
            'redirect_url' => $redirectUrl
        ]);
    } else {
        $stmt = $pdo->prepare("DELETE FROM data_connectors WHERE organization_id = ? AND provider = ?");
        $stmt->execute([$orgId, $provider]);
        echo json_encode(['success' => true]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
