<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/**
 * Sanitize input
 */
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Check admin login
 */
function admin_guard() {
    if (!isset($_SESSION['admin_id'])) {
        header("Location: login.php");
        exit;
    }
}

/**
 * Check voter login
 */
function voter_guard() {
    if (!isset($_SESSION['voter_id'])) {
        header("Location: login.php");
        exit;
    }
}
