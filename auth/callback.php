<?php
/**
 * DecisionVault - Auth Callback
 * Processes Google OAuth response and initializes the user session.
 */

require_once __DIR__ . '/../config.php';

// 1. Basic OAuth Validation
if (!isset($_GET['code'])) {
    die('Authorization failed. No code received from Google.');
}

// 2. Exchange Code for Access Token
$tokenUrl = 'https://oauth2.googleapis.com/token';
$tokenParams = [
    'code'          => $_GET['code'],
    'client_id'     => GOOGLE_CLIENT_ID,
    'client_secret' => GOOGLE_CLIENT_SECRET,
    'redirect_uri'  => GOOGLE_REDIRECT_URI,
    'grant_type'    => 'authorization_code'
];

$ch = curl_init($tokenUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($tokenParams));
$tokenData = json_decode(curl_exec($ch), true);
curl_close($ch);

if (!isset($tokenData['access_token'])) {
    die('Failed to obtain access token.');
}

// 3. Get User Profile from Google
$userUrl = 'https://www.googleapis.com/oauth2/v2/userinfo';
$ch = curl_init($userUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $tokenData['access_token']]);
$googleProfile = json_decode(curl_exec($ch), true);
curl_close($ch);

if (!isset($googleProfile['id'])) {
    die('Failed to fetch user profile.');
}

// 4. Initialize/Update User in Database
$pdo = getDbConnection();
$email = $googleProfile['email'];

$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user) {
    // 1. Create User
    $stmt = $pdo->prepare("INSERT INTO users (email, name, google_id, avatar_url) VALUES (?, ?, ?, ?)");
    $stmt->execute([$email, $googleProfile['name'], $googleProfile['id'], $googleProfile['picture']]);
    $userId = $pdo->lastInsertId();
    
    // 2. Auto-Create Personal Vault (Slug based on unique ID to prevent "taken" error)
    $orgSlug = 'vault-' . $userId . '-' . substr(md5(uniqid()), 0, 4);
    $stmt = $pdo->prepare("INSERT INTO organizations (name, slug, type, owner_id) VALUES (?, ?, 'personal', ?)");
    $stmt->execute([$googleProfile['name'] . "'s Vault", $orgSlug, $userId]);
    $orgId = $pdo->lastInsertId();
    
    // 3. Add as Owner
    $stmt = $pdo->prepare("INSERT INTO organization_members (organization_id, user_id, role) VALUES (?, ?, 'owner')");
    $stmt->execute([$orgId, $userId]);
} else {
    $userId = $user['id'];
    // Auto-fetch their primary org for this session
    $stmt = $pdo->prepare("SELECT organization_id FROM organization_members WHERE user_id = ? LIMIT 1");
    $stmt->execute([$userId]);
    $orgId = $stmt->fetchColumn();
}

$_SESSION['user_id'] = $userId;
$_SESSION['current_org_id'] = $orgId;

header('Location: /dashboard.php');
exit;
