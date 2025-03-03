<?php
/**
 * Highland Games Scoreboard - Admin Scores
 * 
 * This page allows the admin to manage scores
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

// Get competition ID from query string
$competitionId = isset($_GET['competition_id']) ? $_GET['competition_id'] : null;

// Get all active competitions for the dropdown
$activeCompetitions = getActiveCompetitions();

// If no competition ID is provided and there are active competitions, use the first one
if (!$competitionId && !empty($activeCompetitions)) {
    $competitionId = $activeCompetitions[0]['id'];
}

// Get competition details if a competition ID is provided
$competition = null;
$events = [];
$participants = [];
if ($competitionId) {
    $competition = getCompetition($competitionId);
    
    // Get events for this competition
    if ($competition) {
        foreach ($competition['event_ids'] as $eventId) {
            $event = getEvent($eventId);
            if ($event) {
                $events[] = $event;
            }
        }
        
        // Get participants for this competition
        foreach ($competition['participant_ids'] as $participantId) {
            $participant = getParticipant($participantId);
            if ($participant) {
                $participants[] = $participant;
            }
        }
    }
}

// Get event ID from query string for filtering
$eventId = isset($_GET['event_id']) ? $_GET['event_id'] : null;

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        setFlashMessage('error', 'Invalid form submission. Please try again.');
    } else {
        // Determine the action
        $action = isset($_POST['action']) ? $_POST['action'] : '';
        
        switch ($action) {
            case 'add_score':
                // Add or update score
                $competitionId = isset($_POST['competition_id']) ? sanitizeInput($_POST['competition_id']) : '';
                $eventId = isset($_POST['event_id']) ? sanitizeInput($_POST['event_id']) : '';
                $participantId = isset($_POST['participant_id']) ? sanitizeInput($_POST['participant_id']) : '';
                $points = isset($_POST['points']) ? intval($_POST['points']) : 0;
                $notes = isset($_POST['notes']) ? sanitizeInput($_POST['notes']) : '';
                
                // Validate input
                if (empty($competitionId) || empty($eventId) || empty($participantId)) {
                    setFlashMessage('error', 'Competition, event, and participant are required.');
                } else {
                    // Create score data
                    $score = [
                        'competition_id' => $competitionId,
                        'event_id' => $eventId,
                        'participant_id' => $participantId,
                        'points' => $points,
                        'notes' => $notes,
                        'recorded_at' => date('c')
                    ];
                    
                    // Add score
                    if (addScore($score)) {
                        // Update participant stats
                        updateParticipantStats($participantId, $competitionId);
                        
                        setFlashMessage('success', 'Score recorded successfully.');
                        redirect('scores.php?competition_id=' . $competitionId . '&event_id=' . $eventId);
                    } else {
                        setFlashMessage('error', 'Failed to record score. Please try again.');
                    }
                }
                break;
                
            case 'delete_score':
                // Delete score
                $scoreId = isset($_POST['score_id']) ? sanitizeInput($_POST['score_id']) : '';
                $competitionId = isset($_POST['competition_id']) ? sanitizeInput($_POST['competition_id']) : '';
                $eventId = isset($_POST['event_id']) ? sanitizeInput($_POST['event_id']) : '';
                
                // Validate input
                if (empty($scoreId)) {
                    setFlashMessage('error', 'Score ID is required.');
                } else {
                    // Delete score
                    if (deleteScore($scoreId)) {
                        setFlashMessage('success', 'Score deleted successfully.');
                        redirect('scores.php?competition_id=' . $competitionId . '&event_id=' . $eventId);
                    } else {
                        setFlashMessage('error', 'Failed to delete score. Please try again.');
                    }
                }
                break;
        }
    }
}

// Get scores for the selected competition and event
$scores = [];
if ($competitionId) {
    if ($eventId) {
        // Get scores for the specific event
        $scores = getEventScores($competitionId, $eventId);
    } else {
        // Get all scores for the competition
        $scores = getCompetitionScores($competitionId);
    }
}

// Set page title and admin flag
$pageTitle = 'Manage Scores';
$isAdmin = true;

// Include header
include '../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h1 class="mb-3">
            <i class="fas fa-star me-2 text-primary"></i>Manage Scores
        </h1>
        
        <?php if (empty($activeCompetitions)): ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>No active competitions found. Please <a href="competitions.php">activate a competition</a> to record scores.
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="scoreTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="record-scores-tab" data-bs-toggle="tab" data-bs-target="#record-scores" type="button" role="tab" aria-controls="record-scores" aria-selected="true">
                                <i class="fas fa-edit me-1"></i>Record Scores
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="view-scores-tab" data-bs-toggle="tab" data-bs-target="#view-scores" type="button" role="tab" aria-controls="view-scores" aria-selected="false">
                                <i class="fas fa-list me-1"></i>View Scores
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="scoreTabsContent">
                        <!-- Record Scores Tab -->
                        <div class="tab-pane fade show active" id="record-scores" role="tabpanel" aria-labelledby="record-scores-tab">
                            <form method="post" action="scores.php" class="admin-form-container" id="score-entry-form">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                <input type="hidden" name="action" value="add_score">
                                
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="competition_id" class="form-label">Competition <span class="text-danger">*</span></label>
                                            <select class="form-select" id="competition_id" name="competition_id" required>
                                                <option value="">Select Competition</option>
                                                <?php foreach ($activeCompetitions as $comp): ?>
                                                    <option value="<?php echo $comp['id']; ?>" <?php echo $competitionId === $comp['id'] ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($comp['name']); ?> (<?php echo formatDate($comp['date']); ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="event_id" class="form-label">Event <span class="text-danger">*</span></label>
                                            <select class="form-select" id="event_id" name="event_id" required <?php echo empty($events) ? 'disabled' : ''; ?>>
                                                <option value="">Select Event</option>
                                                <?php foreach ($events as $event): ?>
                                                    <option value="<?php echo $event['id']; ?>" <?php echo $eventId === $event['id'] ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($event['name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <span id="event-loading" class="spinner-border spinner-border-sm text-primary ms-2" role="status" style="display: none;"></span>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="participant_id" class="form-label">Participant <span class="text-danger">*</span></label>
                                            <select class="form-select" id="participant_id" name="participant_id" required <?php echo empty($participants) ? 'disabled' : ''; ?>>
                                                <option value="">Select Participant</option>
                                                <?php foreach ($participants as $participant): ?>
                                                    <option value="<?php echo $participant['id']; ?>">
                                                        <?php echo htmlspecialchars($participant['name']); ?>
                                                        <?php 
                                                        if (isset($participant['team_id']) && $participant['team_id']) {
                                                            $team = getTeam($participant['team_id']);
                                                            echo $team ? ' (' . htmlspecialchars($team['name']) . ')' : '';
                                                        }
                                                        ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <span id="participant-loading" class="spinner-border spinner-border-sm text-primary ms-2" role="status" style="display: none;"></span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="points" class="form-label">Points <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" id="points" name="points" required min="0" value="0">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="notes" class="form-label">Notes</label>
                                            <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <button type="submit" class="btn btn-primary" <?php echo empty($events) || empty($participants) ? 'disabled' : ''; ?>>
                                        <i class="fas fa-save me-2"></i>Record Score
                                    </button>
                                </div>
                            </form>
                            
                            <?php if ($competitionId && $eventId): ?>
                                <div class="mt-4">
                                    <h3 class="h5">Current Scores for Selected Event</h3>
                                    
                                    <?php
                                    // Get event details
                                    $selectedEvent = null;
                                    foreach ($events as $event) {
                                        if ($event['id'] === $eventId) {
                                            $selectedEvent = $event;
                                            break;
                                        }
                                    }
                                    
                                    // Get scores for this event
                                    $eventScores = getEventScores($competitionId, $eventId);
                                    
                                    // Organize scores by participant ID for easy lookup
                                    $scoresByParticipant = [];
                                    foreach ($eventScores as $score) {
                                        $scoresByParticipant[$score['participant_id']] = $score;
                                    }
                                    ?>
                                    
                                    <?php if ($selectedEvent): ?>
                                        <div class="alert alert-info">
                                            <strong><?php echo htmlspecialchars($selectedEvent['name']); ?></strong>
                                            <?php if (isset($selectedEvent['description']) && $selectedEvent['description']): ?>
                                                <p class="mb-0 mt-1"><?php echo htmlspecialchars($selectedEvent['description']); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (empty($participants)): ?>
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle me-2"></i>No participants in this competition.
                                        </div>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Participant</th>
                                                        <th>Team</th>
                                                        <th class="text-center">Points</th>
                                                        <th>Notes</th>
                                                        <th class="text-center">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($participants as $participant): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($participant['name']); ?></td>
                                                            <td>
                                                                <?php 
                                                                if (isset($participant['team_id']) && $participant['team_id']) {
                                                                    $team = getTeam($participant['team_id']);
                                                                    echo $team ? htmlspecialchars($team['name']) : '-';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td class="text-center">
                                                                <?php 
                                                                if (isset($scoresByParticipant[$participant['id']])) {
                                                                    echo $scoresByParticipant[$participant['id']]['points'];
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td>
                                                                <?php 
                                                                if (isset($scoresByParticipant[$participant['id']]) && $scoresByParticipant[$participant['id']]['notes']) {
                                                                    echo htmlspecialchars($scoresByParticipant[$participant['id']]['notes']);
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td class="text-center">
                                                                <?php if (isset($scoresByParticipant[$participant['id']])): ?>
                                                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteScoreModal<?php echo $scoresByParticipant[$participant['id']]['id']; ?>">
                                                                        <i class="fas fa-trash-alt me-1"></i>Delete
                                                                    </button>
                                                                    
                                                                    <!-- Delete Score Confirmation Modal -->
                                                                    <div class="modal fade" id="deleteScoreModal<?php echo $scoresByParticipant[$participant['id']]['id']; ?>" tabindex="-1" aria-labelledby="deleteScoreModalLabel<?php echo $scoresByParticipant[$participant['id']]['id']; ?>" aria-hidden="true">
                                                                        <div class="modal-dialog">
                                                                            <div class="modal-content">
                                                                                <div class="modal-header">
                                                                                    <h5 class="modal-title" id="deleteScoreModalLabel<?php echo $scoresByParticipant[$participant['id']]['id']; ?>">Confirm Deletion</h5>
                                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                                </div>
                                                                                <div class="modal-body">
                                                                                    <p>Are you sure you want to delete the score for "<?php echo htmlspecialchars($participant['name']); ?>" in this event?</p>
                                                                                    <p class="text-danger"><strong>Warning:</strong> This action cannot be undone.</p>
                                                                                </div>
                                                                                <div class="modal-footer">
                                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                                    <form method="post" action="scores.php">
                                                                                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                                                                        <input type="hidden" name="action" value="delete_score">
                                                                                        <input type="hidden" name="score_id" value="<?php echo $scoresByParticipant[$participant['id']]['id']; ?>">
                                                                                        <input type="hidden" name="competition_id" value="<?php echo $competitionId; ?>">
                                                                                        <input type="hidden" name="event_id" value="<?php echo $eventId; ?>">
                                                                                        <button type="submit" class="btn btn-danger">Delete</button>
                                                                                    </form>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                <?php else: ?>
                                                                    <span class="text-muted">No score</span>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- View Scores Tab -->
                        <div class="tab-pane fade" id="view-scores" role="tabpanel" aria-labelledby="view-scores-tab">
                            <?php if (!$competitionId): ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>Please select a competition to view scores.
                                </div>
                            <?php else: ?>
                                <div class="mb-4">
                                    <form method="get" action="scores.php" class="row g-3">
                                        <div class="col-md-4">
                                            <label for="competition_id_filter" class="form-label">Competition</label>
                                            <select class="form-select" id="competition_id_filter" name="competition_id" onchange="this.form.submit()">
                                                <?php foreach ($activeCompetitions as $comp): ?>
                                                    <option value="<?php echo $comp['id']; ?>" <?php echo $competitionId === $comp['id'] ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($comp['name']); ?> (<?php echo formatDate($comp['date']); ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <label for="event_id_filter" class="form-label">Event (Optional)</label>
                                            <select class="form-select" id="event_id_filter" name="event_id" onchange="this.form.submit()">
                                                <option value="">All Events</option>
                                                <?php foreach ($events as $event): ?>
                                                    <option value="<?php echo $event['id']; ?>" <?php echo $eventId === $event['id'] ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($event['name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </form>
                                </div>
                                
                                <?php if (empty($scores)): ?>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>No scores recorded for this <?php echo $eventId ? 'event' : 'competition'; ?> yet.
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Participant</th>
                                                    <th>Event</th>
                                                    <th class="text-center">Points</th>
                                                    <th>Notes</th>
                                                    <th>Recorded</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($scores as $score): ?>
                                                    <?php 
                                                    $scoreParticipant = getParticipant($score['participant_id']);
                                                    $scoreEvent = getEvent($score['event_id']);
                                                    ?>
                                                    <tr>
                                                        <td>
                                                            <?php echo $scoreParticipant ? htmlspecialchars($scoreParticipant['name']) : 'Unknown Participant'; ?>
                                                        </td>
                                                        <td>
                                                            <?php echo $scoreEvent ? htmlspecialchars($scoreEvent['name']) : 'Unknown Event'; ?>
                                                        </td>
                                                        <td class="text-center"><?php echo $score['points']; ?></td>
                                                        <td><?php echo isset($score['notes']) && $score['notes'] ? htmlspecialchars($score['notes']) : '-'; ?></td>
                                                        <td><?php echo isset($score['recorded_at']) ? formatDate($score['recorded_at'], 'M j, Y g:i A') : '-'; ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($competitionId && !$eventId): ?>
                                    <div class="mt-4">
                                        <h3 class="h5">Competition Progress</h3>
                                        
                                        <?php
                                        // Calculate progress for each event
                                        $eventProgress = [];
                                        foreach ($events as $event) {
                                            $eventScores = getEventScores($competitionId, $event['id']);
                                            $scoredParticipants = count($eventScores);
                                            $totalParticipants = count($participants);
                                            $progressPercentage = $totalParticipants > 0 ? ($scoredParticipants / $totalParticipants) * 100 : 0;
                                            
                                            $eventProgress[] = [
                                                'event' => $event,
                                                'scored' => $scoredParticipants,
                                                'total' => $totalParticipants,
                                                'percentage' => $progressPercentage
                                            ];
                                        }
                                        ?>
                                        
                                        <div class="row">
                                            <?php foreach ($eventProgress as $progress): ?>
                                                <div class="col-md-6 mb-3">
                                                    <div class="card">
                                                        <div class="card-body">
                                                            <h5 class="card-title"><?php echo htmlspecialchars($progress['event']['name']); ?></h5>
                                                            <div class="progress mt-2">
                                                                <div class="progress-bar bg-<?php echo $progress['percentage'] == 100 ? 'success' : 'primary'; ?>" 
                                                                     role="progressbar" 
                                                                     style="width: <?php echo $progress['percentage']; ?>%;" 
                                                                     aria-valuenow="<?php echo $progress['percentage']; ?>" 
                                                                     aria-valuemin="0" 
                                                                     aria-valuemax="100">
                                                                    <?php echo $progress['scored']; ?>/<?php echo $progress['total']; ?>
                                                                </div>
                                                            </div>
                                                            <div class="mt-2">
                                                                <a href="scores.php?competition_id=<?php echo $competitionId; ?>&event_id=<?php echo $progress['event']['id']; ?>" class="btn btn-sm btn-primary">
                                                                    <i class="fas fa-edit me-1"></i>Record Scores
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- JavaScript for AJAX loading of events and participants -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize the form
    const competitionSelect = document.getElementById('competition_id');
    if (competitionSelect) {
        competitionSelect.addEventListener('change', function() {
            window.location.href = 'scores.php?competition_id=' + this.value;
        });
    }
    
    const eventSelect = document.getElementById('event_id');
    if (eventSelect) {
        eventSelect.addEventListener('change', function() {
            if (competitionSelect.value) {
                window.location.href = 'scores.php?competition_id=' + competitionSelect.value + '&event_id=' + this.value;
            }
        });
    }
});
</script>

<?php
// Include footer
include '../includes/footer.php';
?>