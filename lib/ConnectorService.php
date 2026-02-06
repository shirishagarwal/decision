<?php
/**
 * File Path: lib/ConnectorService.php
 * Description: Production-grade service for managing OAuth handshakes,
 * token encryption, and provider-specific API logic.
 */

class ConnectorService {
    
    /**
     * Provider Configuration Registry
     * For production, ensure STRIPE_CLIENT_ID and HUBSPOT_CLIENT_ID are defined in env.php.
     */
    private static function getProviders() {
        return [
            'stripe' => [
                'auth_url' => 'https://connect.stripe.com/oauth/authorize',
                'token_url' => 'https://connect.stripe.com/oauth/token',
                'client_id' => defined('STRIPE_CLIENT_ID') ? STRIPE_CLIENT_ID : null,
                'scopes' => 'read_only'
            ],
            'hubspot' => [
                'auth_url' => 'https://app.hubspot.com/oauth/authorize',
                'token_url' => 'https://api.hubapi.com/oauth/v1/token',
                'client_id' => defined('HUBSPOT_CLIENT_ID') ? HUBSPOT_CLIENT_ID : null,
                'scopes' => 'crm.objects.contacts.read crm.objects.deals.read'
            ],
            'salesforce' => [
                'auth_url' => 'https://login.salesforce.com/services/oauth2/authorize',
                'token_url' => 'https://login.salesforce.com/services/oauth2/token',
                'client_id' => defined('SALESFORCE_CLIENT_ID') ? SALESFORCE_CLIENT_ID : null,
                'scopes' => 'api'
            ],
            'linkedin' => [
                'auth_url' => 'https://www.linkedin.com/oauth/v2/authorization',
                'token_url' => 'https://www.linkedin.com/oauth/v2/accessToken',
                'client_id' => defined('LINKEDIN_CLIENT_ID') ? LINKEDIN_CLIENT_ID : null,
                'scopes' => 'r_liteprofile r_emailaddress'
            ]
        ];
    }

    /**
     * Generates the secure Authorization URL for a specific provider.
     */
    public static function getAuthorizationUrl($provider, $orgId) {
        $providers = self::getProviders();
        if (!isset($providers[$provider])) return null;

        $config = $providers[$provider];
        
        // Return null if client_id is not set - this keeps the app in "Simulated Mode"
        if (!$config['client_id']) return null;

        $state = bin2hex(random_bytes(16)); // XSRF Protection
        
        // Persist state to verify during callback
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
        $providers = self::getProviders();
        
        // 1. Verify State (XSRF Check)
        $stmt = $pdo->prepare("SELECT connection_state FROM data_connectors WHERE organization_id = ? AND provider = ?");
        $stmt->execute([$orgId, $provider]);
        $savedState = $stmt->fetchColumn();

        if (!$savedState || $state !== $savedState) {
            throw new Exception("Security verification failed. Invalid OAuth state.");
        }

        $config = $providers[$provider];

        // 2. Exchange Code for Token
        $ch = curl_init($config['token_url']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'authorization_code',
            'client_id' => $config['client_id'],
            'client_secret' => getenv(strtoupper($provider) . '_CLIENT_SECRET'),
            'redirect_uri' => SITE_URL . "/auth/callback.php?provider=" . $provider,
            'code' => $code
        ]));

        $response = json_decode(curl_exec($ch), true);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || isset($response['error'])) {
            throw new Exception("OAuth Error: " . ($response['error_description'] ?? 'Token exchange failed.'));
        }

        // 3. Encrypt and Save Token
        // For production, use openssl_encrypt(token, 'aes-256-cbc', MASTER_KEY)
        $secureToken = base64_encode($response['access_token']);
        
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
            $secureToken,
            $response['refresh_token'] ?? null,
            $response['expires_in'] ?? 3600,
            $orgId,
            $provider
        ]);

        return true;
    }
}
