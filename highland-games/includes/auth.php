<?php
/**
 * Authentication functions for Highland Games Scoreboard
 * 
 * This file handles admin authentication
 */

// Prevent direct access to this file
if (!defined('HIGHLAND_GAMES')) {
    die('Direct access to this file is not allowed.');
}

/**
 * Authenticate admin user
 * 
 * @param string $username The username
 * @param string $password The password
 * @return bool True if authenticated, false otherwise
 */
function authenticateAdmin($username, $password) {
    if ($username === ADMIN_USERNAME && password_verify($password, ADMIN_PASSWORD)) {
        // Set session variables
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        $_SESSION['admin_last_activity'] = time();
        
        return true;
    }
    
    return false;
}

/**
 * Log out admin user
 */
function logoutAdmin() {
    // Unset session variables
    unset($_SESSION['admin_logged_in']);
    unset($_SESSION['admin_username']);
    unset($_SESSION['admin_last_activity']);
    
    // Destroy the session
    session_destroy();
}

/**
 * Check if admin session is valid
 * 
 * @return bool True if valid, false otherwise
 */
function isAdminSessionValid() {
    // Check if logged in
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        return false;
    }
    
    // Check session lifetime
    if (isset($_SESSION['admin_last_activity']) && (time() - $_SESSION['admin_last_activity'] > SESSION_LIFETIME)) {
        // Session expired
        logoutAdmin();
        return false;
    }
    
    // Update last activity time
    $_SESSION['admin_last_activity'] = time();
    
    return true;
}

/**
 * Require admin authentication
 * 
 * Redirects to login page if not authenticated
 */
function requireAdmin() {
    if (!isAdminSessionValid()) {
        // Store the requested URL for redirection after login
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        
        // Redirect to login page
        redirect('../admin/login.php');
    }
}