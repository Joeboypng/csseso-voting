<?php
/**
 * Application environment
 * local | production
 */

if (
    $_SERVER['SERVER_NAME'] === 'localhost' ||
    $_SERVER['SERVER_ADDR'] === '127.0.0.1'
) {
    define('APP_ENV', 'local');
} else {
    define('APP_ENV', 'production');
}
