<?php
require_once __DIR__ . '/../config.php';

// Generate state token
$state = bin2hex(random_bytes(16));

// Store state in session for verification
$_SESSION['oauth_state'] = $state;

// Force session to be written before redirect
session_write_close();

// Restart session (this ensures the state is saved)
session_start();

// Build Google OAuth URL
$params = [
    'client_id' => GOOGLE_CLIENT_ID,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'response_type' => 'code',
    'scope' => 'email profile',
    'access_type' => 'online',
    'state' => $state
];

$authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);

// Redirect to Google
header('Location: ' . $authUrl);
exit;
