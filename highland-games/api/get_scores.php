<?php
/**
 * Highland Games Scoreboard - API: Get Scores
 * 
 * This endpoint returns scores for a competition or a specific event in a competition
 */

// Define constant to allow includes
define('HIGHLAND_GAMES', true);

// Include configuration and functions
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Set content type to JSON
header('Content-Type: application/json');

// Get competition ID and event ID from query string
$competitionId = isset($_GET['competition_id']) ? $_GET['competition_id'] : null;
$eventId = isset($_GET['event_id']) ? $_GET['event_id'] : null;

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

// Get scores
$scores = [];
if ($eventId) {
    // Get scores for a specific event
    $eventScores = getEventScores($competitionId, $eventId);
    
    // Organize scores by participant ID
    $scores[$eventId] = [];
    foreach ($eventScores as $score) {
        $scores[$eventId][$score['participant_id']] = $score;
    }
} else {
    // Get all scores for the competition
    $competitionScores = getCompetitionScores($competitionId);
    
    // Organize scores by event ID and participant ID
    foreach ($competitionScores as $score) {
        if (!isset($scores[$score['event_id']])) {
            $scores[$score['event_id']] = [];
        }
        $scores[$score['event_id']][$score['participant_id']] = $score;
    }
}

// Return scores as JSON
echo json_encode($scores);