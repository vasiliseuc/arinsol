<?php
/**
 * Configuration file for the JSON Editor
 */

// Path to the data.json file
define('DATA_FILE', __DIR__ . '/../data.json');

// Admin Credentials
define('ADMIN_USERNAME', 'arinsol');
define('ADMIN_PASSWORD', '!ArinSol123!');

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is authenticated
 */
function isAuthenticated() {
    return isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true;
}

/**
 * Login user
 */
function login($username, $password) {
    if ($username === ADMIN_USERNAME && $password === ADMIN_PASSWORD) {
        $_SESSION['is_logged_in'] = true;
        return true;
    }
    return false;
}

/**
 * Logout user
 */
function logout() {
    $_SESSION = [];
    session_destroy();
}
