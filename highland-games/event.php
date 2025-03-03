<?php
/**
 * Highland Games Scoreboard - Event Scores
 * 
 * This page displays the scores for a specific event in a competition
 */

// Define constant to allow includes
define('HIGHLAND_GAMES', true);

// Include configuration and functions
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Get competition ID and event ID from query string
$competitionId = isset($_GET['competition_id']) ? $_GET['competition_id'] : null;
$eventId = isset($_GET['event_id']) ? $_GET['event_id'] : null;

// Redirect to competitions list if no IDs provided
if (!$competitionId || !$eventId) {
    redirect('competitions.php');
}

// Get competition details
$competition = getCompetition($competitionId);

// Redirect to competitions list if competition not found
if (!$competition) {
    setFlashMessage('error', 'Competition not found.');
    redirect('competitions.php');
}

// Get event details
$event = getEvent($eventId);

// Redirect to competition page if event not found
if (!$event) {
    setFlashMessage('error', 'Event not found.');
    redirect('competition.php?id=' . $competitionId);
}

// Check if event is part of the competition
if (!in_array($eventId, $competition['event_ids'])) {
    setFlashMessage('error', 'Event is not part of this competition.');
    redirect('competition.php?id=' . $competitionId);
}

// Get participants for this competition
$participants = [];
foreach ($competition['participant_ids'] as $participantId) {
    $participant = getParticipant($participantId);
    if ($participant) {
        $participants[] = $participant;
    }
}

// Get scores for this event
$eventScores = getEventScores($competitionId, $eventId);

// Organize scores by participant ID for easy lookup
$scoresByParticipant = [];
foreach ($eventScores as $score) {
    $scoresByParticipant[$score['participant_id']] = $score;
}

// Sort participants by score (highest to lowest)
usort($participants, function($a, $b) use ($scoresByParticipant) {
    $scoreA = isset($scoresByParticipant[$a['id']]) ? $scoresByParticipant[$a['id']]['points'] : 0;
    $scoreB = isset($scoresByParticipant[$b['id']]) ? $scoresByParticipant[$b['id']]['points'] : 0;
    return $scoreB - $scoreA;
});

// Set page title
$pageTitle = $event['name'] . ' - ' . $competition['name'];

// Set extra head content for AJAX polling
$extraHead = '
<script>
    // Set AJAX polling interval
    window.AJAX_POLL_INTERVAL = ' . AJAX_POLL_INTERVAL . ';
</script>
';

// Include header
include 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="competitions.php?status=<?php echo $competition['status']; ?>">
                    <?php 
                    switch ($competition['status']) {
                        case 'active':
                            echo 'Active Competitions';
                            break;
                        case 'upcoming':
                            echo 'Upcoming Competitions';
                            break;
                        case 'completed':
                            echo 'Past Competitions';
                            break;
                    }
                    ?>
                </a></li>
                <li class="breadcrumb-item"><a href="competition.php?id=<?php echo $competitionId; ?>"><?php echo htmlspecialchars($competition['name']); ?></a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($event['name']); ?></li>
            </ol>
        </nav>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-8">
        <div class="scoreboard">
            <div class="scoreboard-header">
                <h1 class="scoreboard-title"><?php echo htmlspecialchars($event['name']); ?></h1>
                <p class="scoreboard-subtitle"><?php echo htmlspecialchars($competition['name']); ?></p>
            </div>
            <div class="scoreboard-body" id="scoreboard-container" data-competition-id="<?php echo $competitionId; ?>" data-event-id="<?php echo $eventId; ?>">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h4 mb-0">Scores</h2>
                    <?php if ($competition['status'] === 'active'): ?>
                        <div class="text-muted">
                            <small><i class="fas fa-sync-alt me-1"></i>Updates automatically</small>
                            <span class="loading-indicator ms-2" style="display: none;">
                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if (empty($participants)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>No participants in this competition.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover scoreboard-table">
                            <thead>
                                <tr>
                                    <th style="width: 60px;">#</th>
                                    <th>Participant</th>
                                    <th>Team</th>
                                    <th class="text-center" style="width: 120px;">Points</th>
                                    <?php if ($competition['status'] === 'completed' || $competition['status'] === 'active'): ?>
                                        <th class="text-center" style="width: 100px;">Rank</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $rank = 1;
                                $prevScore = null;
                                $sameRankCount = 0;
                                
                                foreach ($participants as $index => $participant): 
                                    $score = isset($scoresByParticipant[$participant['id']]) ? $scoresByParticipant[$participant['id']]['points'] : 0;
                                    
                                    // Calculate rank
                                    if ($prevScore !== null && $score < $prevScore) {
                                        $rank += $sameRankCount;
                                        $sameRankCount = 1;
                                    } else if ($prevScore !== null && $score === $prevScore) {
                                        $sameRankCount++;
                                    } else {
                                        $sameRankCount = 1;
                                    }
                                    
                                    $prevScore = $score;
                                ?>
                                    <tr class="<?php echo $rank <= 3 ? 'rank-' . $rank : ''; ?>">
                                        <td><?php echo $index + 1; ?></td>
                                        <td>
                                            <a href="participant.php?id=<?php echo $participant['id']; ?>" class="text-decoration-none">
                                                <?php echo htmlspecialchars($participant['name']); ?>
                                            </a>
                                        </td>
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
                                        <td class="text-center" id="score-<?php echo $eventId; ?>-<?php echo $participant['id']; ?>">
                                            <?php echo $score; ?>
                                        </td>
                                        <?php if ($competition['status'] === 'completed' || $competition['status'] === 'active'): ?>
                                            <td class="text-center">
                                                <?php if ($score > 0): ?>
                                                    <?php if ($rank === 1): ?>
                                                        <span class="badge bg-warning text-dark"><i class="fas fa-medal me-1"></i><?php echo $rank; ?></span>
                                                    <?php elseif ($rank === 2): ?>
                                                        <span class="badge bg-secondary"><i class="fas fa-medal me-1"></i><?php echo $rank; ?></span>
                                                    <?php elseif ($rank === 3): ?>
                                                        <span class="badge bg-danger"><i class="fas fa-medal me-1"></i><?php echo $rank; ?></span>
                                                    <?php else: ?>
                                                        <span class="badge bg-light text-dark"><?php echo $rank; ?></span>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="badge bg-light text-dark">-</span>
                                                <?php endif; ?>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h2 class="h5 mb-0">Event Details</h2>
            </div>
            <div class="card-body">
                <?php if (isset($event['description']) && $event['description']): ?>
                    <p><?php echo htmlspecialchars($event['description']); ?></p>
                    <hr>
                <?php endif; ?>
                
                <p>
                    <strong><i class="fas fa-star me-2"></i>Scoring Type:</strong> 
                    <?php echo ucfirst(htmlspecialchars($event['scoring_type'])); ?>
                </p>
                
                <?php if (isset($event['max_points']) && $event['max_points']): ?>
                    <p>
                        <strong><i class="fas fa-trophy me-2"></i>Maximum Points:</strong> 
                        <?php echo $event['max_points']; ?>
                    </p>
                <?php endif; ?>
                
                <?php
                // Count how many participants have scores for this event
                $scoredParticipants = count($eventScores);
                $totalParticipants = count($participants);
                $progressPercentage = $totalParticipants > 0 ? ($scoredParticipants / $totalParticipants) * 100 : 0;
                ?>
                
                <div class="mt-3">
                    <strong><i class="fas fa-chart-line me-2"></i>Completion:</strong>
                    <div class="progress mt-2">
                        <div class="progress-bar bg-<?php echo $progressPercentage == 100 ? 'success' : 'primary'; ?>" 
                             role="progressbar" 
                             style="width: <?php echo $progressPercentage; ?>%;" 
                             aria-valuenow="<?php echo $progressPercentage; ?>" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                            <?php echo $scoredParticipants; ?>/<?php echo $totalParticipants; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h2 class="h5 mb-0">Competition Info</h2>
            </div>
            <div class="card-body">
                <p>
                    <strong><i class="fas fa-calendar-day me-2"></i>Date:</strong> 
                    <?php echo formatDate($competition['date']); ?>
                </p>
                <p>
                    <strong><i class="fas fa-map-marker-alt me-2"></i>Location:</strong> 
                    <?php echo htmlspecialchars($competition['location']); ?>
                </p>
                <p>
                    <strong><i class="fas fa-info-circle me-2"></i>Status:</strong> 
                    <?php 
                    switch ($competition['status']) {
                        case 'active':
                            echo '<span class="badge bg-success">Active</span>';
                            break;
                        case 'upcoming':
                            echo '<span class="badge bg-secondary">Upcoming</span>';
                            break;
                        case 'completed':
                            echo '<span class="badge bg-primary">Completed</span>';
                            break;
                    }
                    ?>
                </p>
                <p>
                    <strong><i class="fas fa-users me-2"></i>Participants:</strong> 
                    <?php echo count($participants); ?>
                </p>
                <p>
                    <strong><i class="fas fa-tasks me-2"></i>Total Events:</strong> 
                    <?php echo count($competition['event_ids']); ?>
                </p>
                
                <div class="mt-3">
                    <a href="competition.php?id=<?php echo $competitionId; ?>" class="btn btn-primary w-100">
                        <i class="fas fa-arrow-left me-2"></i>Back to Competition
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Alert Container for AJAX messages -->
<div id="alert-container"></div>

<?php
// Include footer
include 'includes/footer.php';
?>