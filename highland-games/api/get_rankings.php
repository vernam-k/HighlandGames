<?php
/**
 * Highland Games Scoreboard - API: Get Rankings
 * 
 * This endpoint returns rankings for a competition, optionally filtered by category
 */

// Define constant to allow includes
define('HIGHLAND_GAMES', true);

// Include configuration and functions
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Set content type to JSON
header('Content-Type: application/json');

// Get competition ID and category ID from query string
$competitionId = isset($_GET['competition_id']) ? $_GET['competition_id'] : null;
$categoryId = isset($_GET['category_id']) ? $_GET['category_id'] : null;

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

// Calculate rankings
$rankings = calculateRankings($competitionId);

// Filter by category if specified
if ($categoryId) {
    $filteredRankings = [];
    foreach ($rankings as $ranking) {
        if (isset($ranking['category_ids']) && in_array($categoryId, $ranking['category_ids'])) {
            $filteredRankings[] = $ranking;
        }
    }
    $rankings = $filteredRankings;
    
    // Recalculate ranks
    $rank = 1;
    $prevPoints = null;
    $sameRankCount = 0;
    
    foreach ($rankings as $key => $participant) {
        if ($prevPoints !== null && $participant['total_points'] < $prevPoints) {
            $rank += $sameRankCount;
            $sameRankCount = 1;
        } else if ($prevPoints !== null && $participant['total_points'] === $prevPoints) {
            $sameRankCount++;
        } else {
            $sameRankCount = 1;
        }
        
        $rankings[$key]['rank'] = $rank;
        $prevPoints = $participant['total_points'];
    }
}

// Add team names to rankings
foreach ($rankings as $key => $ranking) {
    if (isset($ranking['team_id']) && $ranking['team_id']) {
        $team = getTeam($ranking['team_id']);
        if ($team) {
            $rankings[$key]['team_name'] = $team['name'];
        }
    }
}

// Return rankings as JSON
echo json_encode($rankings);