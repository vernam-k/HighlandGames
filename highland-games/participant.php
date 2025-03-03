<?php
/**
 * Highland Games Scoreboard - Participant Profile
 * 
 * This page displays the profile of a specific participant
 */

// Define constant to allow includes
define('HIGHLAND_GAMES', true);

// Include configuration and functions
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Get participant ID from query string
$participantId = isset($_GET['id']) ? $_GET['id'] : null;

// Redirect to home if no ID provided
if (!$participantId) {
    redirect('index.php');
}

// Get participant details
$participant = getParticipant($participantId);

// Redirect to home if participant not found
if (!$participant) {
    setFlashMessage('error', 'Participant not found.');
    redirect('index.php');
}

// Get team if participant is part of a team
$team = null;
if (isset($participant['team_id']) && $participant['team_id']) {
    $team = getTeam($participant['team_id']);
}

// Get categories
$categories = [];
if (isset($participant['category_ids']) && !empty($participant['category_ids'])) {
    foreach ($participant['category_ids'] as $categoryId) {
        $category = getCategory($categoryId);
        if ($category) {
            $categories[] = $category;
        }
    }
}

// Get personal bests
$personalBests = isset($participant['stats']['personal_bests']) ? $participant['stats']['personal_bests'] : [];

// Get competition history
$competitionHistory = isset($participant['stats']['competition_history']) ? $participant['stats']['competition_history'] : [];

// Get all events for displaying personal bests
$allEvents = getEvents();
$eventsById = [];
foreach ($allEvents as $event) {
    $eventsById[$event['id']] = $event;
}

// Set page title
$pageTitle = $participant['name'];

// Include header
include 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($participant['name']); ?></li>
            </ol>
        </nav>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h1 class="card-title h3 mb-0"><?php echo htmlspecialchars($participant['name']); ?></h1>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <?php if ($team): ?>
                            <p>
                                <strong><i class="fas fa-users-cog me-2"></i>Team:</strong> 
                                <?php echo htmlspecialchars($team['name']); ?>
                            </p>
                        <?php endif; ?>
                        
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
                    <div class="col-md-6">
                        <p>
                            <strong><i class="fas fa-calendar-alt me-2"></i>Joined:</strong> 
                            <?php echo isset($participant['created_at']) ? formatDate($participant['created_at']) : 'N/A'; ?>
                        </p>
                        
                        <?php if (!empty($competitionHistory)): ?>
                            <p>
                                <strong><i class="fas fa-flag-checkered me-2"></i>Competitions:</strong> 
                                <?php echo count($competitionHistory); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if (!empty($personalBests)): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h2 class="card-title h4 mb-0"><i class="fas fa-award me-2"></i>Personal Bests</h2>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Event</th>
                                    <th class="text-center">Best Score</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($personalBests as $eventId => $points): ?>
                                    <tr>
                                        <td>
                                            <?php 
                                            if (isset($eventsById[$eventId])) {
                                                echo htmlspecialchars($eventsById[$eventId]['name']);
                                            } else {
                                                echo 'Unknown Event';
                                            }
                                            ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge highland-badge"><?php echo $points; ?> pts</span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($competitionHistory)): ?>
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title h4 mb-0"><i class="fas fa-history me-2"></i>Competition History</h2>
                </div>
                <div class="card-body">
                    <div class="accordion" id="competitionHistoryAccordion">
                        <?php foreach ($competitionHistory as $index => $history): ?>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading<?php echo $index; ?>">
                                    <button class="accordion-button <?php echo $index > 0 ? 'collapsed' : ''; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $index; ?>" aria-expanded="<?php echo $index === 0 ? 'true' : 'false'; ?>" aria-controls="collapse<?php echo $index; ?>">
                                        <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                            <span><?php echo htmlspecialchars($history['competition_name']); ?></span>
                                            <div>
                                                <span class="badge bg-primary me-2"><?php echo formatDate($history['date']); ?></span>
                                                <span class="badge highland-badge"><?php echo $history['total_points']; ?> pts</span>
                                            </div>
                                        </div>
                                    </button>
                                </h2>
                                <div id="collapse<?php echo $index; ?>" class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>" aria-labelledby="heading<?php echo $index; ?>" data-bs-parent="#competitionHistoryAccordion">
                                    <div class="accordion-body">
                                        <?php if (empty($history['scores'])): ?>
                                            <p class="text-muted">No scores recorded for this competition.</p>
                                        <?php else: ?>
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>Event</th>
                                                            <th class="text-center">Score</th>
                                                            <th>Notes</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($history['scores'] as $score): ?>
                                                            <tr>
                                                                <td>
                                                                    <?php 
                                                                    if (isset($eventsById[$score['event_id']])) {
                                                                        echo htmlspecialchars($eventsById[$score['event_id']]['name']);
                                                                    } else {
                                                                        echo 'Unknown Event';
                                                                    }
                                                                    ?>
                                                                </td>
                                                                <td class="text-center">
                                                                    <span class="badge highland-badge"><?php echo $score['points']; ?> pts</span>
                                                                </td>
                                                                <td>
                                                                    <?php echo isset($score['notes']) && $score['notes'] ? htmlspecialchars($score['notes']) : '-'; ?>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                            
                                            <div class="mt-3">
                                                <a href="competition.php?id=<?php echo $history['competition_id']; ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye me-1"></i>View Competition
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="col-md-4">
        <?php if (!empty($competitionHistory)): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h2 class="card-title h5 mb-0"><i class="fas fa-chart-line me-2"></i>Performance Summary</h2>
                </div>
                <div class="card-body">
                    <?php
                    // Calculate total points across all competitions
                    $totalPoints = 0;
                    foreach ($competitionHistory as $history) {
                        $totalPoints += $history['total_points'];
                    }
                    
                    // Calculate average points per competition
                    $averagePoints = count($competitionHistory) > 0 ? round($totalPoints / count($competitionHistory), 1) : 0;
                    
                    // Find best competition
                    $bestCompetition = null;
                    $bestPoints = 0;
                    foreach ($competitionHistory as $history) {
                        if ($history['total_points'] > $bestPoints) {
                            $bestPoints = $history['total_points'];
                            $bestCompetition = $history;
                        }
                    }
                    ?>
                    
                    <div class="participant-stats">
                        <div class="row text-center">
                            <div class="col-4">
                                <h3 class="h2 mb-0"><?php echo count($competitionHistory); ?></h3>
                                <p class="text-muted mb-0">Competitions</p>
                            </div>
                            <div class="col-4">
                                <h3 class="h2 mb-0"><?php echo $totalPoints; ?></h3>
                                <p class="text-muted mb-0">Total Points</p>
                            </div>
                            <div class="col-4">
                                <h3 class="h2 mb-0"><?php echo $averagePoints; ?></h3>
                                <p class="text-muted mb-0">Avg. Points</p>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($bestCompetition): ?>
                        <div class="mt-3">
                            <h3 class="h6">Best Performance</h3>
                            <p class="mb-1">
                                <strong><?php echo htmlspecialchars($bestCompetition['competition_name']); ?></strong>
                            </p>
                            <p class="mb-1">
                                <i class="fas fa-calendar-day me-1"></i><?php echo formatDate($bestCompetition['date']); ?>
                            </p>
                            <p class="mb-0">
                                <i class="fas fa-star me-1"></i><?php echo $bestCompetition['total_points']; ?> points
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if ($team): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h2 class="card-title h5 mb-0"><i class="fas fa-users-cog me-2"></i>Team Information</h2>
                </div>
                <div class="card-body">
                    <h3 class="h5"><?php echo htmlspecialchars($team['name']); ?></h3>
                    
                    <?php if (isset($team['description']) && $team['description']): ?>
                        <p><?php echo htmlspecialchars($team['description']); ?></p>
                    <?php endif; ?>
                    
                    <?php
                    // Get team members
                    $teamMembers = getParticipantsByTeam($team['id']);
                    
                    // Remove current participant
                    $teamMembers = array_filter($teamMembers, function($member) use ($participantId) {
                        return $member['id'] !== $participantId;
                    });
                    
                    if (!empty($teamMembers)):
                    ?>
                        <h4 class="h6 mt-3">Team Members</h4>
                        <ul class="list-group">
                            <?php foreach ($teamMembers as $member): ?>
                                <li class="list-group-item">
                                    <a href="participant.php?id=<?php echo $member['id']; ?>" class="text-decoration-none">
                                        <?php echo htmlspecialchars($member['name']); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($categories)): ?>
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title h5 mb-0"><i class="fas fa-tags me-2"></i>Categories</h2>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        <?php foreach ($categories as $category): ?>
                            <li class="list-group-item">
                                <h3 class="h6 mb-1"><?php echo htmlspecialchars($category['name']); ?></h3>
                                <?php if (isset($category['description']) && $category['description']): ?>
                                    <p class="mb-0 small text-muted"><?php echo htmlspecialchars($category['description']); ?></p>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
// Include footer
include 'includes/footer.php';
?>