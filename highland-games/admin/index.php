<?php
/**
 * Highland Games Scoreboard - Admin Dashboard
 * 
 * This is the main page of the admin interface
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

// Get counts for dashboard
$activeCompetitions = getActiveCompetitions();
$upcomingCompetitions = getUpcomingCompetitions();
$completedCompetitions = getCompletedCompetitions();
$participants = getParticipants();
$events = getEvents();
$categories = getCategories();
$teams = getTeams();

// Set page title and admin flag
$pageTitle = 'Admin Dashboard';
$isAdmin = true;

// Include header
include '../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h1 class="mb-3">
            <i class="fas fa-tachometer-alt me-2 text-primary"></i>Admin Dashboard
        </h1>
        
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>Welcome to the Highland Games Scoreboard admin area. From here, you can manage competitions, events, participants, scores, categories, and teams.
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4 mb-4">
        <div class="card h-100 admin-dashboard-card">
            <div class="admin-dashboard-icon">
                <i class="fas fa-flag-checkered"></i>
            </div>
            <h2 class="h4">Competitions</h2>
            <div class="mt-3">
                <div class="d-flex justify-content-around">
                    <div class="text-center">
                        <h3 class="h2 mb-0"><?php echo count($activeCompetitions); ?></h3>
                        <p class="text-muted mb-0">Active</p>
                    </div>
                    <div class="text-center">
                        <h3 class="h2 mb-0"><?php echo count($upcomingCompetitions); ?></h3>
                        <p class="text-muted mb-0">Upcoming</p>
                    </div>
                    <div class="text-center">
                        <h3 class="h2 mb-0"><?php echo count($completedCompetitions); ?></h3>
                        <p class="text-muted mb-0">Completed</p>
                    </div>
                </div>
            </div>
            <div class="mt-4">
                <a href="competitions.php" class="btn btn-primary">
                    <i class="fas fa-cog me-2"></i>Manage Competitions
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card h-100 admin-dashboard-card">
            <div class="admin-dashboard-icon">
                <i class="fas fa-users"></i>
            </div>
            <h2 class="h4">Participants</h2>
            <div class="mt-3">
                <h3 class="h2 mb-0"><?php echo count($participants); ?></h3>
                <p class="text-muted mb-0">Total Participants</p>
            </div>
            <div class="mt-4">
                <a href="participants.php" class="btn btn-primary">
                    <i class="fas fa-cog me-2"></i>Manage Participants
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card h-100 admin-dashboard-card">
            <div class="admin-dashboard-icon">
                <i class="fas fa-tasks"></i>
            </div>
            <h2 class="h4">Events</h2>
            <div class="mt-3">
                <h3 class="h2 mb-0"><?php echo count($events); ?></h3>
                <p class="text-muted mb-0">Total Events</p>
            </div>
            <div class="mt-4">
                <a href="events.php" class="btn btn-primary">
                    <i class="fas fa-cog me-2"></i>Manage Events
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card h-100 admin-dashboard-card">
            <div class="admin-dashboard-icon">
                <i class="fas fa-star"></i>
            </div>
            <h2 class="h4">Scores</h2>
            <div class="mt-3">
                <?php if (count($activeCompetitions) > 0): ?>
                    <p class="text-success mb-0">
                        <i class="fas fa-check-circle me-1"></i>Active competitions need scoring
                    </p>
                <?php else: ?>
                    <p class="text-muted mb-0">No active competitions</p>
                <?php endif; ?>
            </div>
            <div class="mt-4">
                <a href="scores.php" class="btn btn-primary">
                    <i class="fas fa-cog me-2"></i>Manage Scores
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card h-100 admin-dashboard-card">
            <div class="admin-dashboard-icon">
                <i class="fas fa-tags"></i>
            </div>
            <h2 class="h4">Categories</h2>
            <div class="mt-3">
                <h3 class="h2 mb-0"><?php echo count($categories); ?></h3>
                <p class="text-muted mb-0">Total Categories</p>
            </div>
            <div class="mt-4">
                <a href="categories.php" class="btn btn-primary">
                    <i class="fas fa-cog me-2"></i>Manage Categories
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card h-100 admin-dashboard-card">
            <div class="admin-dashboard-icon">
                <i class="fas fa-users-cog"></i>
            </div>
            <h2 class="h4">Teams</h2>
            <div class="mt-3">
                <h3 class="h2 mb-0"><?php echo count($teams); ?></h3>
                <p class="text-muted mb-0">Total Teams</p>
            </div>
            <div class="mt-4">
                <a href="teams.php" class="btn btn-primary">
                    <i class="fas fa-cog me-2"></i>Manage Teams
                </a>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($activeCompetitions)): ?>
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title h4 mb-0">
                    <i class="fas fa-flag-checkered me-2"></i>Active Competitions
                </h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Date</th>
                                <th>Location</th>
                                <th class="text-center">Participants</th>
                                <th class="text-center">Events</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($activeCompetitions as $competition): ?>
                                <tr>
                                    <td>
                                        <a href="../competition.php?id=<?php echo $competition['id']; ?>" target="_blank">
                                            <?php echo htmlspecialchars($competition['name']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo formatDate($competition['date']); ?></td>
                                    <td><?php echo htmlspecialchars($competition['location']); ?></td>
                                    <td class="text-center"><?php echo count($competition['participant_ids']); ?></td>
                                    <td class="text-center"><?php echo count($competition['event_ids']); ?></td>
                                    <td class="text-center">
                                        <a href="scores.php?competition_id=<?php echo $competition['id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-star me-1"></i>Record Scores
                                        </a>
                                        <a href="competitions.php?action=edit&id=<?php echo $competition['id']; ?>" class="btn btn-sm btn-secondary">
                                            <i class="fas fa-edit me-1"></i>Edit
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($upcomingCompetitions)): ?>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title h4 mb-0">
                    <i class="fas fa-calendar-alt me-2"></i>Upcoming Competitions
                </h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Date</th>
                                <th>Location</th>
                                <th class="text-center">Participants</th>
                                <th class="text-center">Events</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            // Display only the next 5 upcoming competitions
                            $displayCompetitions = array_slice($upcomingCompetitions, 0, 5);
                            foreach ($displayCompetitions as $competition): 
                            ?>
                                <tr>
                                    <td>
                                        <a href="../competition.php?id=<?php echo $competition['id']; ?>" target="_blank">
                                            <?php echo htmlspecialchars($competition['name']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo formatDate($competition['date']); ?></td>
                                    <td><?php echo htmlspecialchars($competition['location']); ?></td>
                                    <td class="text-center"><?php echo count($competition['participant_ids']); ?></td>
                                    <td class="text-center"><?php echo count($competition['event_ids']); ?></td>
                                    <td class="text-center">
                                        <a href="competitions.php?action=edit&id=<?php echo $competition['id']; ?>" class="btn btn-sm btn-secondary">
                                            <i class="fas fa-edit me-1"></i>Edit
                                        </a>
                                        <button class="btn btn-sm btn-success competition-status-btn" 
                                                data-competition-id="<?php echo $competition['id']; ?>" 
                                                data-status="active">
                                            <i class="fas fa-play me-1"></i>Start
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <?php if (count($upcomingCompetitions) > 5): ?>
                        <div class="text-center mt-3">
                            <a href="competitions.php" class="btn btn-outline-primary">
                                <i class="fas fa-list me-2"></i>View All Upcoming Competitions
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Hidden CSRF token for AJAX requests -->
<input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">

<?php
// Include footer
include '../includes/footer.php';
?>