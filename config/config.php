<?php
// Start session everywhere
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// Base URL (adjust later when hosting)
define('BASE_URL', 'http://localhost/wide_voting_system/');

// OTP settings
define('OTP_EXPIRY_MINUTES', 5);

// Token expiry (minutes)
define('TOKEN_EXPIRY_MINUTES', 10);

// Environment
date_default_timezone_set('Africa/Accra');
