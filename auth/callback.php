<?php
/**
 * File Path: auth/callback.php
 * Description: Receives Google OAuth code and handles user login/registration.
 */

require_once __DIR__ . '/../config.php';

if (!isset($_GET['code'])) {
    die("Authorization failed: No code returned.");
}

// 1. Exchange authorization code for access token
$ch = curl_init('https://oauth2.googleapis.com/token');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'code' => $_GET['code'],
    'client_id' => GOOGLE_CLIENT_ID,
    'client_secret' => GOOGLE_CLIENT_SECRET,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'grant_type' => 'authorization_code'
]));
$tokenResponse = curl_exec($ch);
$tokenData = json_decode($tokenResponse, true);

if (isset($tokenData['error'])) {
    die("Error exchanging code: " . $tokenData['error_description']);
}

// 2. Fetch User Profile from Google
$ch = curl_init('https://www.googleapis.com/oauth2/v2/userinfo');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $tokenData['access_token']]);
$profile = json_decode(curl_exec($ch), true);

$pdo = getDbConnection();
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$profile['email']]);
$user = $stmt->fetch();

if (!$user) {
    // Register New User
    $stmt = $pdo->prepare("INSERT INTO users (email, name, google_id, avatar_url) VALUES (?, ?, ?, ?)");
    $stmt->execute([$profile['email'], $profile['name'], $profile['id'], $profile['picture']]);
    $userId = $pdo->lastInsertId();
    
    // Auto-create their private "Vault" (Personal Organization)
    $slug = 'vault-' . $userId . '-' . substr(md5(uniqid()), 0, 4);
    $stmt = $pdo->prepare("INSERT INTO organizations (name, slug, type, owner_id) VALUES (?, ?, 'personal', ?)");
    $stmt->execute([$profile['name'] . "'s Vault", $slug, $userId]);
    $orgId = $pdo->lastInsertId();
    
    // Grant Ownership
    $pdo->prepare("INSERT INTO organization_members (organization_id, user_id, role) VALUES (?, ?, 'owner')")->execute([$orgId, $userId]);
} else {
    $userId = $user['id'];
    // Find the user's primary organization
    $orgId = $pdo->query("SELECT organization_id FROM organization_members WHERE user_id = $userId LIMIT 1")->fetchColumn();
}

$_SESSION['user_id'] = $userId;
$_SESSION['current_org_id'] = $orgId;

header('Location: /dashboard.php');
exit;
