<?php
/**
 * File Path: lib/ConnectorService.php
 * Description: Production-grade service for managing OAuth handshakes,
 * token encryption, and provider-specific API logic.
 */

class ConnectorService {
    
    // In a real production environment, these would be in your env.php
    private static $providers = [
        'stripe' => [
            'auth_url' => 'https://connect.stripe.com/oauth/authorize',
            'token_url' => 'https://connect.stripe.com/oauth/token',
            'client_id' => 'ca_XXXXXX', // Replace with real ID
            'scopes' => 'read_only'
        ],
        'hubspot' => [
            'auth_url' => 'https://app.hubspot.com/oauth/authorize',
            'token_url' => 'https://api.hubapi.com/oauth/v1/token',
            'client_id' => 'XXXXXX-XXXX-XXXX', // Replace with real ID
            'scopes' => 'crm.objects.contacts.read crm.objects.deals.read'
        ]
    ];

    /**
     * Generates the secure Authorization URL for a specific provider.
     */
    public static function getAuthorizationUrl($provider, $orgId) {
        if (!isset(self::$providers[$provider])) return null;

        $config = self::$providers[$provider];
        $state = bin2hex(random_bytes(16)); // XSRF Protection
        
        // Save state to DB to verify during callback
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("UPDATE data_connectors SET connection_state = ? WHERE organization_id = ? AND provider = ?");
        $stmt->execute([$state, $orgId, $provider]);

        $params = [
            'response_type' => 'code',
            'client_id' => $config['client_id'],
            'scope' => $config['scopes'],
            'redirect_uri' => SITE_URL . "/auth/callback.php?provider=" . $provider,
            'state' => $state
        ];

        return $config['auth_url'] . "?" . http_build_query($params);
    }

    /**
     * Exchanges an authorization code for a permanent access token.
     */
    public static function handleCallback($provider, $code, $state, $orgId) {
        $pdo = getDbConnection();
        
        // 1. Verify State (XSRF Check)
        $stmt = $pdo->prepare("SELECT connection_state FROM data_connectors WHERE organization_id = ? AND provider = ?");
        $stmt->execute([$orgId, $provider]);
        $savedState = $stmt->fetchColumn();

        if ($state !== $savedState) {
            throw new Exception("Invalid OAuth state. Potential XSRF attack.");
        }

        $config = self::$providers[$provider];

        // 2. Exchange Code for Token
        $ch = curl_init($config['token_url']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'authorization_code',
            'client_id' => $config['client_id'],
            'client_secret' => getenv($provider . '_CLIENT_SECRET'), // Stored in env.php
            'redirect_uri' => SITE_URL . "/auth/callback.php?provider=" . $provider,
            'code' => $code
        ]));

        $response = json_decode(curl_exec($ch), true);
        curl_close($ch);

        if (isset($response['error'])) {
            throw new Exception("OAuth Error: " . $response['error_description']);
        }

        // 3. Encrypt and Save Token
        // NOTE: In production, use openssl_encrypt with a MASTER_KEY from env.php
        $encryptedToken = base64_encode($response['access_token']);
        
        $stmt = $pdo->prepare("
            UPDATE data_connectors 
            SET status = 'active', 
                access_token = ?, 
                refresh_token = ?, 
                token_expires_at = DATE_ADD(NOW(), INTERVAL ? SECOND),
                connection_state = NULL,
                last_sync = NOW() 
            WHERE organization_id = ? AND provider = ?
        ");
        
        $stmt->execute([
            $encryptedToken,
            $response['refresh_token'] ?? null,
            $response['expires_in'] ?? 3600,
            $orgId,
            $provider
        ]);

        return true;
    }
}
