<?php
/**
 * Highland Games Scoreboard - Admin API: Update Competition Status
 * 
 * This endpoint updates the status of a competition
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

// Check if this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Validate CSRF token
if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token.']);
    exit;
}

// Get competition ID and status from POST data
$competitionId = isset($_POST['competition_id']) ? sanitizeInput($_POST['competition_id']) : null;
$status = isset($_POST['status']) ? sanitizeInput($_POST['status']) : null;

// Validate input
if (!$competitionId) {
    echo json_encode(['success' => false, 'message' => 'Competition ID is required.']);
    exit;
}

if (!$status || !in_array($status, ['upcoming', 'active', 'completed'])) {
    echo json_encode(['success' => false, 'message' => 'Valid status is required.']);
    exit;
}

// Get competition details
$competition = getCompetition($competitionId);
if (!$competition) {
    echo json_encode(['success' => false, 'message' => 'Competition not found.']);
    exit;
}

// Update competition status
$competition['status'] = $status;

// Update competition
if (updateCompetition($competitionId, $competition)) {
    // If status is changed to completed, update all participant stats
    if ($status === 'completed') {
        foreach ($competition['participant_ids'] as $participantId) {
            updateParticipantStats($participantId, $competitionId);
        }
    }
    
    echo json_encode([
        'success' => true, 
        'message' => 'Competition status updated successfully.',
        'competition' => [
            'id' => $competitionId,
            'name' => $competition['name'],
            'status' => $status
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update competition status.']);
}