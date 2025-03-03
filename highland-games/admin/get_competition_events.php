<?php
/**
 * Highland Games Scoreboard - Admin API: Get Competition Events
 * 
 * This endpoint returns events for a specific competition
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

// Require admin authentication
requireAdmin();

// Set content type to JSON
header('Content-Type: application/json');

// Get competition ID from query string
$competitionId = isset($_GET['competition_id']) ? $_GET['competition_id'] : null;

// Validate input
if (!$competitionId) {
    echo json_encode(['error' => 'Competition ID is required.']);
    exit;
}

// Get competition details
$competition = getCompetition($competitionId);
if (!$competition) {
    echo json_encode(['error' => 'Competition not found.']);
    exit;
}

// Get events for this competition
$events = [];
foreach ($competition['event_ids'] as $eventId) {
    $event = getEvent($eventId);
    if ($event) {
        $events[] = $event;
    }
}

// Return events as JSON
echo json_encode($events);