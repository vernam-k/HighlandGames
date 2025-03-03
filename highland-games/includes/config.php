<?php
/**
 * Configuration file for Highland Games Scoreboard
 * 
 * This file contains all the configuration settings for the application
 * including admin credentials, file paths, and other settings.
 */

// Prevent direct access to this file
if (!defined('HIGHLAND_GAMES')) {
    die('Direct access to this file is not allowed.');
}

// Admin credentials
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', password_hash('highland2025', PASSWORD_DEFAULT)); // Default password: highland2025

// Application settings
define('SITE_NAME', 'Highland Games Scoreboard');
define('SITE_DESCRIPTION', 'Track scores and rankings for Highland Games competitions');
define('DEBUG_MODE', false);

// File paths (relative to the root directory)
define('DATA_DIR', __DIR__ . '/../data/');
define('PARTICIPANTS_FILE', DATA_DIR . 'participants.json');
define('EVENTS_FILE', DATA_DIR . 'events.json');
define('COMPETITIONS_FILE', DATA_DIR . 'competitions.json');
define('SCORES_FILE', DATA_DIR . 'scores.json');
define('CATEGORIES_FILE', DATA_DIR . 'categories.json');
define('TEAMS_FILE', DATA_DIR . 'teams.json');

// AJAX settings
define('AJAX_POLL_INTERVAL', 5000); // Polling interval in milliseconds (5 seconds)

// Session settings
define('SESSION_LIFETIME', 3600); // Session lifetime in seconds (1 hour)

// Initialize empty JSON files if they don't exist
$jsonFiles = [
    PARTICIPANTS_FILE => ['participants' => []],
    EVENTS_FILE => ['events' => []],
    COMPETITIONS_FILE => ['competitions' => []],
    SCORES_FILE => ['scores' => []],
    CATEGORIES_FILE => ['categories' => []],
    TEAMS_FILE => ['teams' => []]
];

foreach ($jsonFiles as $file => $defaultContent) {
    if (!file_exists($file)) {
        if (!is_dir(dirname($file))) {
            mkdir(dirname($file), 0755, true);
        }
        file_put_contents($file, json_encode($defaultContent, JSON_PRETTY_PRINT));
    }
}