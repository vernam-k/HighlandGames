<?php
/**
 * Highland Games Scoreboard - Competition Details
 * 
 * This page displays the details of a specific competition
 */

// Define constant to allow includes
define('HIGHLAND_GAMES', true);

// Include configuration and functions
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Get competition ID from query string
$competitionId = isset($_GET['id']) ? $_GET['id'] : null;

// Redirect to competitions list if no ID provided
if (!$competitionId) {
    redirect('competitions.php');
}

// Get competition details
$competition = getCompetition($competitionId);

// Redirect to competitions list if competition not found
if (!$competition) {
    setFlashMessage('error', 'Competition not found.');
    redirect('competitions.php');
}

// Get events for this competition
$events = [];
foreach ($competition['event_ids'] as $eventId) {
    $event = getEvent($eventId);
    if ($event) {
        $events[] = $event;
    }
}

// Get participants for this competition
$participants = [];
foreach ($competition['participant_ids'] as $participantId) {
    $participant = getParticipant($participantId);
    if ($participant) {
        $participants[] = $participant;
    }
}

// Get categories for this competition
$categories = [];
foreach ($competition['category_ids'] as $categoryId) {
    $category = getCategory($categoryId);
    if ($category) {
        $categories[] = $category;
    }
}

// Get scores for this competition
$scores = getCompetitionScores($competitionId);

// Calculate rankings
$rankings = calculateRankings($competitionId);

// Set page title
$pageTitle = $competition['name'];

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
                <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($competition['name']); ?></li>
            </ol>
        </nav>
        
        <div class="card mb-4">
            <div class="card-header">
                <h1 class="card-title h3 mb-0"><?php echo htmlspecialchars($competition['name']); ?></h1>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
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
                    </div>
                    <div class="col-md-6">
                        <p>
                            <strong><i class="fas fa-users me-2"></i>Participants:</strong> 
                            <?php echo count($participants); ?>
                        </p>
                        <p>
                            <strong><i class="fas fa-tasks me-2"></i>Events:</strong> 
                            <?php echo count($events); ?>
                        </p>
                        <?php if (!empty($categories)): ?>
                            <p>
                                <strong><i class="fas fa-tags me-2"></i>Categories:</strong> 
                                <?php 
                                $categoryNames = array_map(function($category) {
                                    return htmlspecialchars($category['name']);
                                }, $categories);
                                echo implode(', ', $categoryNames);
                                ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-12">
        <ul class="nav nav-tabs mb-4" id="competitionTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="rankings-tab" data-bs-toggle="tab" data-bs-target="#rankings" type="button" role="tab" aria-controls="rankings" aria-selected="true">
                    <i class="fas fa-trophy me-1"></i>Rankings
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="events-tab" data-bs-toggle="tab" data-bs-target="#events" type="button" role="tab" aria-controls="events" aria-selected="false">
                    <i class="fas fa-tasks me-1"></i>Events
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="participants-tab" data-bs-toggle="tab" data-bs-target="#participants" type="button" role="tab" aria-controls="participants" aria-selected="false">
                    <i class="fas fa-users me-1"></i>Participants
                </button>
            </li>
            <?php if (!empty($categories)): ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="categories-tab" data-bs-toggle="tab" data-bs-target="#categories" type="button" role="tab" aria-controls="categories" aria-selected="false">
                        <i class="fas fa-tags me-1"></i>Categories
                    </button>
                </li>
            <?php endif; ?>
        </ul>
        
        <div class="tab-content" id="competitionTabsContent">
            <!-- Rankings Tab -->
            <div class="tab-pane fade show active" id="rankings" role="tabpanel" aria-labelledby="rankings-tab">
                <div id="rankings-container" data-competition-id="<?php echo $competitionId; ?>">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="h4 mb-0">Overall Rankings</h2>
                        <?php if ($competition['status'] === 'active'): ?>
                            <div class="text-muted">
                                <small><i class="fas fa-sync-alt me-1"></i>Updates automatically</small>
                                <span class="loading-indicator ms-2" style="display: none;">
                                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (empty($rankings)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>No scores have been recorded yet.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover" id="rankings-table">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width: 80px;">Rank</th>
                                        <th>Participant</th>
                                        <th>Team</th>
                                        <th class="text-center" style="width: 120px;">Total Points</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rankings as $ranking): ?>
                                        <tr id="ranking-<?php echo $ranking['id']; ?>" class="<?php echo $ranking['rank'] <= 3 ? 'rank-' . $ranking['rank'] : ''; ?>">
                                            <td class="text-center"><?php echo $ranking['rank']; ?></td>
                                            <td>
                                                <a href="participant.php?id=<?php echo $ranking['id']; ?>" class="text-decoration-none">
                                                    <?php echo htmlspecialchars($ranking['name']); ?>
                                                </a>
                                            </td>
                                            <td>
                                                <?php 
                                                if (isset($ranking['team_id']) && $ranking['team_id']) {
                                                    $team = getTeam($ranking['team_id']);
                                                    echo $team ? htmlspecialchars($team['name']) : '-';
                                                } else {
                                                    echo '-';
                                                }
                                                ?>
                                            </td>
                                            <td class="text-center"><?php echo $ranking['total_points']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($categories)): ?>
                        <div class="mt-4">
                            <h3 class="h5 mb-3">Filter by Category</h3>
                            <div class="btn-group mb-3">
                                <a href="competition.php?id=<?php echo $competitionId; ?>" class="btn btn-outline-primary active">All</a>
                                <?php foreach ($categories as $category): ?>
                                    <a href="competition.php?id=<?php echo $competitionId; ?>&category=<?php echo $category['id']; ?>" class="btn btn-outline-primary">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Events Tab -->
            <div class="tab-pane fade" id="events" role="tabpanel" aria-labelledby="events-tab">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h4 mb-0">Events</h2>
                </div>
                
                <?php if (empty($events)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>No events have been added to this competition.
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($events as $event): ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0"><?php echo htmlspecialchars($event['name']); ?></h5>
                                    </div>
                                    <div class="card-body">
                                        <?php if (isset($event['description']) && $event['description']): ?>
                                            <p><?php echo htmlspecialchars($event['description']); ?></p>
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
                                        $eventScores = getEventScores($competitionId, $event['id']);
                                        $scoredParticipants = count($eventScores);
                                        $totalParticipants = count($participants);
                                        $progressPercentage = $totalParticipants > 0 ? ($scoredParticipants / $totalParticipants) * 100 : 0;
                                        ?>
                                        
                                        <div class="mt-3">
                                            <strong><i class="fas fa-chart-line me-2"></i>Progress:</strong>
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
                                    <div class="card-footer bg-white">
                                        <a href="event.php?competition_id=<?php echo $competitionId; ?>&event_id=<?php echo $event['id']; ?>" class="btn btn-primary w-100">
                                            <i class="fas fa-eye me-2"></i>View Scores
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Participants Tab -->
            <div class="tab-pane fade" id="participants" role="tabpanel" aria-labelledby="participants-tab">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h4 mb-0">Participants</h2>
                </div>
                
                <?php if (empty($participants)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>No participants have been added to this competition.
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($participants as $participant): ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0"><?php echo htmlspecialchars($participant['name']); ?></h5>
                                    </div>
                                    <div class="card-body">
                                        <?php if (isset($participant['team_id']) && $participant['team_id']): ?>
                                            <?php $team = getTeam($participant['team_id']); ?>
                                            <?php if ($team): ?>
                                                <p>
                                                    <strong><i class="fas fa-users-cog me-2"></i>Team:</strong> 
                                                    <?php echo htmlspecialchars($team['name']); ?>
                                                </p>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        
                                        <?php if (isset($participant['category_ids']) && !empty($participant['category_ids'])): ?>
                                            <p>
                                                <strong><i class="fas fa-tags me-2"></i>Categories:</strong> 
                                                <?php 
                                                $categoryNames = [];
                                                foreach ($participant['category_ids'] as $catId) {
                                                    $category = getCategory($catId);
                                                    if ($category) {
                                                        $categoryNames[] = htmlspecialchars($category['name']);
                                                    }
                                                }
                                                echo implode(', ', $categoryNames);
                                                ?>
                                            </p>
                                        <?php endif; ?>
                                        
                                        <?php
                                        // Get participant's scores for this competition
                                        $participantScores = getParticipantScores($competitionId, $participant['id']);
                                        $totalPoints = 0;
                                        foreach ($participantScores as $score) {
                                            $totalPoints += $score['points'];
                                        }
                                        
                                        // Get participant's rank
                                        $rank = '-';
                                        foreach ($rankings as $ranking) {
                                            if ($ranking['id'] === $participant['id']) {
                                                $rank = $ranking['rank'];
                                                break;
                                            }
                                        }
                                        ?>
                                        
                                        <p>
                                            <strong><i class="fas fa-star me-2"></i>Total Points:</strong> 
                                            <?php echo $totalPoints; ?>
                                        </p>
                                        
                                        <p>
                                            <strong><i class="fas fa-trophy me-2"></i>Current Rank:</strong> 
                                            <?php echo $rank; ?>
                                        </p>
                                        
                                        <?php
                                        // Calculate completion percentage
                                        $completedEvents = count($participantScores);
                                        $totalEvents = count($events);
                                        $completionPercentage = $totalEvents > 0 ? ($completedEvents / $totalEvents) * 100 : 0;
                                        ?>
                                        
                                        <div class="mt-3">
                                            <strong><i class="fas fa-chart-line me-2"></i>Events Completed:</strong>
                                            <div class="progress mt-2">
                                                <div class="progress-bar bg-<?php echo $completionPercentage == 100 ? 'success' : 'primary'; ?>" 
                                                     role="progressbar" 
                                                     style="width: <?php echo $completionPercentage; ?>%;" 
                                                     aria-valuenow="<?php echo $completionPercentage; ?>" 
                                                     aria-valuemin="0" 
                                                     aria-valuemax="100">
                                                    <?php echo $completedEvents; ?>/<?php echo $totalEvents; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-white">
                                        <a href="participant.php?id=<?php echo $participant['id']; ?>" class="btn btn-primary w-100">
                                            <i class="fas fa-user me-2"></i>View Profile
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Categories Tab -->
            <?php if (!empty($categories)): ?>
                <div class="tab-pane fade" id="categories" role="tabpanel" aria-labelledby="categories-tab">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="h4 mb-0">Categories</h2>
                    </div>
                    
                    <div class="row">
                        <?php foreach ($categories as $category): ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0"><?php echo htmlspecialchars($category['name']); ?></h5>
                                    </div>
                                    <div class="card-body">
                                        <?php if (isset($category['description']) && $category['description']): ?>
                                            <p><?php echo htmlspecialchars($category['description']); ?></p>
                                        <?php endif; ?>
                                        
                                        <?php
                                        // Count participants in this category
                                        $categoryParticipants = [];
                                        foreach ($participants as $participant) {
                                            if (isset($participant['category_ids']) && in_array($category['id'], $participant['category_ids'])) {
                                                $categoryParticipants[] = $participant;
                                            }
                                        }
                                        ?>
                                        
                                        <p>
                                            <strong><i class="fas fa-users me-2"></i>Participants:</strong> 
                                            <?php echo count($categoryParticipants); ?>
                                        </p>
                                    </div>
                                    <div class="card-footer bg-white">
                                        <a href="competition.php?id=<?php echo $competitionId; ?>&category=<?php echo $category['id']; ?>" class="btn btn-primary w-100">
                                            <i class="fas fa-filter me-2"></i>View Rankings
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Alert Container for AJAX messages -->
<div id="alert-container"></div>

<?php
// Include footer
include 'includes/footer.php';
?>