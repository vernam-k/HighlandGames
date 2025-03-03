<?php
/**
 * Highland Games Scoreboard - Admin Logout
 * 
 * This page handles admin logout
 */

// Define constant to allow includes
define('HIGHLAND_GAMES', true);

// Include configuration and functions
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Log out the admin
logoutAdmin();

// Set flash message
setFlashMessage('success', 'You have been successfully logged out.');

// Redirect to login page
redirect('login.php');