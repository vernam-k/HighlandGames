<?php
/**
 * Highland Games Scoreboard - Competitions List
 * 
 * This page displays a list of competitions filtered by status
 */

// Define constant to allow includes
define('HIGHLAND_GAMES', true);

// Include configuration and functions
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Get status filter from query string (default to 'active')
$status = isset($_GET['status']) ? $_GET['status'] : 'active';

// Validate status
if (!in_array($status, ['active', 'upcoming', 'completed'])) {
    $status = 'active';
}

// Get competitions based on status
$competitions = [];
switch ($status) {
    case 'active':
        $competitions = getActiveCompetitions();
        $pageTitle = 'Active Competitions';
        $icon = 'flag-checkered';
        $buttonClass = 'primary';
        $buttonText = 'View Competition';
        $buttonIcon = 'eye';
        break;
    case 'upcoming':
        $competitions = getUpcomingCompetitions();
        $pageTitle = 'Upcoming Competitions';
        $icon = 'calendar-alt';
        $buttonClass = 'secondary';
        $buttonText = 'Competition Details';
        $buttonIcon = 'info-circle';
        break;
    case 'completed':
        $competitions = getCompletedCompetitions();
        $pageTitle = 'Past Competitions';
        $icon = 'trophy';
        $buttonClass = 'accent';
        $buttonText = 'View Results';
        $buttonIcon = 'trophy';
        break;
}

// Include header
include 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h1 class="mb-3">
            <i class="fas fa-<?php echo $icon; ?> me-2 text-<?php echo $buttonClass; ?>"></i><?php echo $pageTitle; ?>
        </h1>
        
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link <?php echo $status === 'active' ? 'active' : ''; ?>" href="competitions.php?status=active">
                    <i class="fas fa-flag-checkered me-1"></i>Active
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $status === 'upcoming' ? 'active' : ''; ?>" href="competitions.php?status=upcoming">
                    <i class="fas fa-calendar-alt me-1"></i>Upcoming
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $status === 'completed' ? 'active' : ''; ?>" href="competitions.php?status=completed">
                    <i class="fas fa-trophy me-1"></i>Past
                </a>
            </li>
        </ul>
        
        <?php if (empty($competitions)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>No <?php echo strtolower($pageTitle); ?> found.
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($competitions as $competition): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100">
                            <div class="card-header <?php echo $status !== 'active' ? "card-header-{$buttonClass}" : ''; ?>">
                                <h5 class="card-title mb-0">
                                    <?php echo htmlspecialchars($competition['name']); ?>
                                </h5>
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
                                    <strong><i class="fas fa-users me-2"></i>Participants:</strong> 
                                    <?php echo count($competition['participant_ids']); ?>
                                </p>
                                <p>
                                    <strong><i class="fas fa-tasks me-2"></i>Events:</strong> 
                                    <?php echo count($competition['event_ids']); ?>
                                </p>
                                
                                <?php if ($status === 'upcoming'): ?>
                                    <p>
                                        <strong><i class="fas fa-clock me-2"></i>Starts In:</strong> 
                                        <?php 
                                        $daysUntil = ceil((strtotime($competition['date']) - time()) / (60 * 60 * 24));
                                        echo $daysUntil > 0 ? "{$daysUntil} days" : "Today";
                                        ?>
                                    </p>
                                <?php endif; ?>
                                
                                <?php if ($status === 'completed'): ?>
                                    <?php 
                                    // Get top 3 rankings
                                    $rankings = calculateRankings($competition['id']);
                                    $topRankings = array_slice($rankings, 0, 3);
                                    
                                    if (!empty($topRankings)):
                                    ?>
                                        <div class="mt-3">
                                            <strong><i class="fas fa-medal me-2"></i>Top Performers:</strong>
                                            <ol class="mb-0 mt-1">
                                                <?php foreach ($topRankings as $ranking): ?>
                                                    <li>
                                                        <a href="participant.php?id=<?php echo $ranking['id']; ?>" class="text-decoration-none">
                                                            <?php echo htmlspecialchars($ranking['name']); ?>
                                                        </a>
                                                        <span class="badge highland-badge ms-1"><?php echo $ranking['total_points']; ?> pts</span>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ol>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                            <div class="card-footer bg-white">
                                <a href="competition.php?id=<?php echo $competition['id']; ?>" class="btn btn-<?php echo $buttonClass; ?> w-100">
                                    <i class="fas fa-<?php echo $buttonIcon; ?> me-2"></i><?php echo $buttonText; ?>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
// Include footer
include 'includes/footer.php';
?>