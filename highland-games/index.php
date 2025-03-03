<?php
/**
 * Highland Games Scoreboard - Homepage
 * 
 * This is the main entry point for the public interface
 */

// Define constant to allow includes
define('HIGHLAND_GAMES', true);

// Include configuration and functions
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Set page title
$pageTitle = 'Home';

// Get competitions
$activeCompetitions = getActiveCompetitions();
$upcomingCompetitions = getUpcomingCompetitions();
$completedCompetitions = getCompletedCompetitions();

// Include header
include 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <h1 class="display-4 mb-3">Welcome to the Highland Games Scoreboard</h1>
                <p class="lead">Track scores and rankings for Highland Games competitions in real-time.</p>
                <div class="highland-divider mx-auto" style="max-width: 200px;"></div>
                <?php if (!empty($activeCompetitions)): ?>
                    <p class="mt-4">
                        <a href="competitions.php?status=active" class="btn btn-primary btn-lg">
                            <i class="fas fa-flag-checkered me-2"></i>View Active Competitions
                        </a>
                    </p>
                <?php elseif (!empty($upcomingCompetitions)): ?>
                    <p class="mt-4">
                        <a href="competitions.php?status=upcoming" class="btn btn-secondary btn-lg">
                            <i class="fas fa-calendar-alt me-2"></i>View Upcoming Competitions
                        </a>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($activeCompetitions)): ?>
<div class="row mb-4">
    <div class="col-md-12">
        <h2 class="mb-3">
            <i class="fas fa-flag-checkered me-2 text-primary"></i>Active Competitions
        </h2>
        <div class="row">
            <?php foreach ($activeCompetitions as $competition): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
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
                        </div>
                        <div class="card-footer bg-white">
                            <a href="competition.php?id=<?php echo $competition['id']; ?>" class="btn btn-primary w-100">
                                <i class="fas fa-eye me-2"></i>View Competition
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($upcomingCompetitions)): ?>
<div class="row mb-4">
    <div class="col-md-12">
        <h2 class="mb-3">
            <i class="fas fa-calendar-alt me-2 text-secondary"></i>Upcoming Competitions
        </h2>
        <div class="row">
            <?php 
            // Display only the next 3 upcoming competitions
            $displayCompetitions = array_slice($upcomingCompetitions, 0, 3);
            foreach ($displayCompetitions as $competition): 
            ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100">
                        <div class="card-header card-header-secondary">
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
                        </div>
                        <div class="card-footer bg-white">
                            <a href="competition.php?id=<?php echo $competition['id']; ?>" class="btn btn-secondary w-100">
                                <i class="fas fa-info-circle me-2"></i>Competition Details
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <?php if (count($upcomingCompetitions) > 3): ?>
                <div class="col-md-12 text-center mt-2">
                    <a href="competitions.php?status=upcoming" class="btn btn-outline-secondary">
                        <i class="fas fa-list me-2"></i>View All Upcoming Competitions
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($completedCompetitions)): ?>
<div class="row mb-4">
    <div class="col-md-12">
        <h2 class="mb-3">
            <i class="fas fa-trophy me-2 text-accent"></i>Recent Competitions
        </h2>
        <div class="row">
            <?php 
            // Display only the 3 most recent completed competitions
            $displayCompetitions = array_slice($completedCompetitions, 0, 3);
            foreach ($displayCompetitions as $competition): 
            ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100">
                        <div class="card-header card-header-accent">
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
                        </div>
                        <div class="card-footer bg-white">
                            <a href="competition.php?id=<?php echo $competition['id']; ?>" class="btn btn-accent w-100">
                                <i class="fas fa-trophy me-2"></i>View Results
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <?php if (count($completedCompetitions) > 3): ?>
                <div class="col-md-12 text-center mt-2">
                    <a href="competitions.php?status=completed" class="btn btn-outline-accent">
                        <i class="fas fa-list me-2"></i>View All Past Competitions
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (empty($activeCompetitions) && empty($upcomingCompetitions) && empty($completedCompetitions)): ?>
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <h2 class="mb-3">No Competitions Yet</h2>
                <p class="lead">There are no competitions scheduled at this time. Please check back later.</p>
                <div class="highland-quote mx-auto mt-4" style="max-width: 600px;">
                    "In the Highland Games, it's not just about strength, but about tradition, community, and the spirit of competition."
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">About Highland Games</h5>
            </div>
            <div class="card-body">
                <p>Highland Games are traditional events celebrating Scottish and Celtic culture, particularly those featuring heavy athletics competitions. These events test strength, skill, and endurance through various challenges.</p>
                
                <p>Traditional Highland Games events include:</p>
                
                <div class="row mt-4">
                    <div class="col-md-4 mb-4">
                        <div class="card event-card">
                            <div class="card-body text-center">
                                <div class="event-icon">
                                    <i class="fas fa-tree"></i>
                                </div>
                                <h5>Caber Toss</h5>
                                <p>Flipping a large wooden pole (caber) so it lands in the 12 o'clock position.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card event-card">
                            <div class="card-body text-center">
                                <div class="event-icon">
                                    <i class="fas fa-bowling-ball"></i>
                                </div>
                                <h5>Stone Put</h5>
                                <p>Similar to modern shot put, throwing a large stone for distance.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card event-card">
                            <div class="card-body text-center">
                                <div class="event-icon">
                                    <i class="fas fa-hammer"></i>
                                </div>
                                <h5>Scottish Hammer Throw</h5>
                                <p>Throwing a metal ball attached to a wooden pole for distance.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card event-card">
                            <div class="card-body text-center">
                                <div class="event-icon">
                                    <i class="fas fa-weight-hanging"></i>
                                </div>
                                <h5>Weight Throw</h5>
                                <p>Throwing weights for distance or height over a bar.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card event-card">
                            <div class="card-body text-center">
                                <div class="event-icon">
                                    <i class="fas fa-haykal"></i>
                                </div>
                                <h5>Sheaf Toss</h5>
                                <p>Using a pitchfork to toss a burlap bag stuffed with straw over a bar.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card event-card">
                            <div class="card-body text-center">
                                <div class="event-icon">
                                    <i class="fas fa-people-carry"></i>
                                </div>
                                <h5>Tug-of-War</h5>
                                <p>Teams compete in a test of strength by pulling on opposite ends of a rope.</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <p class="mt-3">However, this scoreboard system is flexible and can accommodate any type of event with a points-based scoring system, not just traditional Highland Games events.</p>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include 'includes/footer.php';
?>