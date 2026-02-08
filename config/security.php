<?php
require_once __DIR__ . '/env.php';

/* =============================
   ERROR HANDLING
============================= */

if (APP_ENV === 'local') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
}

/* =============================
   SECURITY HEADERS
============================= */

header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin");
header("Permissions-Policy: geolocation=()");
header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline';");

/* =============================
   SESSION SECURITY
============================= */

$isHttps = (
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || ($_SERVER['SERVER_PORT'] ?? null) == 443
);

session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'secure'   => APP_ENV === 'production' ? $isHttps : false,
    'httponly' => true,
    'samesite' => 'Lax'
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* =============================
   FLAGS
============================= */

define('ENABLE_IP_RESTRICTION', APP_ENV === 'production');
define('ENABLE_ABUSE_LOCK', APP_ENV === 'production');
define('ENABLE_OTP_RATE_LIMIT', APP_ENV === 'production');
