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
$name = $googleProfile['name'];
$googleId = $googleProfile['id'];
$avatar = $googleProfile['picture'] ?? null;

// Check if user exists
$stmt = $pdo->prepare("SELECT id, onboarding_completed FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user) {
    // Existing user: Update last login info
    $userId = $user['id'];
    $stmt = $pdo->prepare("UPDATE users SET google_id = ?, avatar_url = ?, name = ? WHERE id = ?");
    $stmt->execute([$googleId, $avatar, $name, $userId]);
} else {
    // New user: Create record
    $stmt = $pdo->prepare("INSERT INTO users (email, google_id, name, avatar_url) VALUES (?, ?, ?, ?)");
    $stmt->execute([$email, $googleId, $name, $avatar]);
    $userId = $pdo->lastInsertId();
}

// 5. Establish Session
$_SESSION['user_id'] = $userId;
$_SESSION['user_email'] = $email;
$_SESSION['user_name'] = $name;

// 6. Route to Onboarding
// This logic determines if they are a new "Sarah" or "TechCorp" user
header('Location: /onboarding/check.php');
exit;
