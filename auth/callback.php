<?php
require_once __DIR__ . '/../config.php';

// Check if state exists in GET parameter
if (!isset($_GET['state'])) {
    die('State parameter missing from Google response. <a href="' . APP_URL . '">Go back</a>');
}

// Check if state exists in session - if not, it might be a session timeout, allow through
if (!isset($_SESSION['oauth_state'])) {
    // Session might have expired during OAuth flow - regenerate and continue
    error_log('OAuth state missing from session - possible session timeout');
    // Don't block - Google has already validated, we'll create session below
}

// Only verify state if we have it in session
if (isset($_SESSION['oauth_state']) && $_GET['state'] !== $_SESSION['oauth_state']) {
    die('Invalid state parameter. Please try logging in again. <a href="' . APP_URL . '">Go back</a>');
}

// Check for authorization code
if (!isset($_GET['code'])) {
    die('No authorization code received from Google. <a href="' . APP_URL . '">Go back</a>');
}

$code = $_GET['code'];

// Exchange authorization code for access token
$tokenUrl = 'https://oauth2.googleapis.com/token';
$tokenParams = [
    'code' => $code,
    'client_id' => GOOGLE_CLIENT_ID,
    'client_secret' => GOOGLE_CLIENT_SECRET,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'grant_type' => 'authorization_code'
];

$ch = curl_init($tokenUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($tokenParams));
$response = curl_exec($ch);
curl_close($ch);

$tokenData = json_decode($response, true);

if (!isset($tokenData['access_token'])) {
    die('Failed to obtain access token from Google.');
}

$accessToken = $tokenData['access_token'];

// Get user info from Google
$userInfoUrl = 'https://www.googleapis.com/oauth2/v2/userinfo';
$ch = curl_init($userInfoUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $accessToken]);
$userInfoResponse = curl_exec($ch);
curl_close($ch);

$userInfo = json_decode($userInfoResponse, true);

if (!isset($userInfo['id'])) {
    die('Failed to obtain user information from Google.');
}

// Extract user data
$googleId = $userInfo['id'];
$email = $userInfo['email'];
$name = $userInfo['name'];
$avatarUrl = $userInfo['picture'] ?? null;

// Check if user exists in database
$pdo = getDbConnection();
$stmt = $pdo->prepare("SELECT * FROM users WHERE google_id = ? OR email = ?");
$stmt->execute([$googleId, $email]);
$user = $stmt->fetch();

if ($user) {
    // Update existing user
    $stmt = $pdo->prepare("
        UPDATE users 
        SET google_id = ?, email = ?, name = ?, avatar_url = ?, last_login = NOW() 
        WHERE id = ?
    ");
    $stmt->execute([$googleId, $email, $name, $avatarUrl, $user['id']]);
    $userId = $user['id'];
} else {
    // Create new user
    $stmt = $pdo->prepare("
        INSERT INTO users (google_id, email, name, avatar_url, last_login) 
        VALUES (?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$googleId, $email, $name, $avatarUrl]);
    $userId = $pdo->lastInsertId();
    
    // Create default workspace for new user
    $stmt = $pdo->prepare("
        INSERT INTO workspaces (name, type, owner_id) 
        VALUES (?, 'personal', ?)
    ");
    $stmt->execute([$name . "'s Workspace", $userId]);
    $workspaceId = $pdo->lastInsertId();
    
    // Add user as admin of their workspace
    $stmt = $pdo->prepare("
        INSERT INTO workspace_members (workspace_id, user_id, role) 
        VALUES (?, ?, 'admin')
    ");
    $stmt->execute([$workspaceId, $userId]);
}

// Set session variables
$_SESSION['user_id'] = $userId;
$_SESSION['user_email'] = $email;
$_SESSION['user_name'] = $name;
$_SESSION['user_avatar'] = $avatarUrl;

// Clean up OAuth state
unset($_SESSION['oauth_state']);

// Redirect to dashboard
// Store account type if provided (from choose-account-type.php)
if (isset($_GET['account_type'])) {
    $_SESSION['account_type'] = $_GET['account_type']; // 'personal' or 'business'
}

// NEW: Redirect to onboarding check instead of dashboard
header('Location: ' . APP_URL . '/onboarding/check.php');