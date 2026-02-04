<?php
/**
 * File Path: auth/google.php
 * Description: Initiates the Google OAuth login flow.
 */

require_once __DIR__ . '/../config.php';

// Generate a secure state token to prevent CSRF
$state = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $state;

$params = [
    'client_id' => GOOGLE_CLIENT_ID,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'response_type' => 'code',
    'scope' => 'email profile',
    'state' => $state,
    'prompt' => 'select_account'
];

$url = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query($params);
header("Location: $url");
exit;
