<?php
/**
 * Highland Games Scoreboard - Admin API: Get Competition Participants
 * 
 * This endpoint returns participants for a specific competition
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

// Get participants for this competition
$participants = [];
foreach ($competition['participant_ids'] as $participantId) {
    $participant = getParticipant($participantId);
    if ($participant) {
        // Add team name if participant is part of a team
        if (isset($participant['team_id']) && $participant['team_id']) {
            $team = getTeam($participant['team_id']);
            if ($team) {
                $participant['team_name'] = $team['name'];
            }
        }
        
        $participants[] = $participant;
    }
}

// Return participants as JSON
echo json_encode($participants);