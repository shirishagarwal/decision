<?php
require_once __DIR__ . '/../config.php';

// Destroy session
session_unset();
session_destroy();

// Redirect to login page
redirect(APP_URL . '/index.php');
