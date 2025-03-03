<?php
/**
 * Highland Games Scoreboard - Admin Competitions
 * 
 * This page allows the admin to manage competitions
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

// Get all competitions
$competitions = getCompetitions();

// Get all events for the dropdown
$events = getEvents();

// Get all participants for the dropdown
$participants = getParticipants();

// Get all categories for the dropdown
$categories = getCategories();

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        setFlashMessage('error', 'Invalid form submission. Please try again.');
    } else {
        // Determine the action
        $action = isset($_POST['action']) ? $_POST['action'] : '';
        
        switch ($action) {
            case 'add':
                // Add new competition
                $name = isset($_POST['name']) ? sanitizeInput($_POST['name']) : '';
                $date = isset($_POST['date']) ? sanitizeInput($_POST['date']) : '';
                $location = isset($_POST['location']) ? sanitizeInput($_POST['location']) : '';
                $status = isset($_POST['status']) ? sanitizeInput($_POST['status']) : 'upcoming';
                $eventIds = isset($_POST['event_ids']) && is_array($_POST['event_ids']) ? $_POST['event_ids'] : [];
                $participantIds = isset($_POST['participant_ids']) && is_array($_POST['participant_ids']) ? $_POST['participant_ids'] : [];
                $categoryIds = isset($_POST['category_ids']) && is_array($_POST['category_ids']) ? $_POST['category_ids'] : [];
                
                // Validate input
                if (empty($name) || empty($date) || empty($location)) {
                    setFlashMessage('error', 'Competition name, date, and location are required.');
                } else {
                    // Create competition data
                    $competition = [
                        'name' => $name,
                        'date' => $date,
                        'location' => $location,
                        'status' => $status,
                        'event_ids' => $eventIds,
                        'participant_ids' => $participantIds,
                        'category_ids' => $categoryIds,
                        'created_at' => date('c')
                    ];
                    
                    // Add competition
                    if (addCompetition($competition)) {
                        setFlashMessage('success', 'Competition added successfully.');
                        redirect('competitions.php');
                    } else {
                        setFlashMessage('error', 'Failed to add competition. Please try again.');
                    }
                }
                break;
                
            case 'edit':
                // Edit existing competition
                $id = isset($_POST['id']) ? sanitizeInput($_POST['id']) : '';
                $name = isset($_POST['name']) ? sanitizeInput($_POST['name']) : '';
                $date = isset($_POST['date']) ? sanitizeInput($_POST['date']) : '';
                $location = isset($_POST['location']) ? sanitizeInput($_POST['location']) : '';
                $status = isset($_POST['status']) ? sanitizeInput($_POST['status']) : 'upcoming';
                $eventIds = isset($_POST['event_ids']) && is_array($_POST['event_ids']) ? $_POST['event_ids'] : [];
                $participantIds = isset($_POST['participant_ids']) && is_array($_POST['participant_ids']) ? $_POST['participant_ids'] : [];
                $categoryIds = isset($_POST['category_ids']) && is_array($_POST['category_ids']) ? $_POST['category_ids'] : [];
                
                // Validate input
                if (empty($id) || empty($name) || empty($date) || empty($location)) {
                    setFlashMessage('error', 'Competition ID, name, date, and location are required.');
                } else {
                    // Get existing competition
                    $competition = getCompetition($id);
                    
                    if (!$competition) {
                        setFlashMessage('error', 'Competition not found.');
                    } else {
                        // Update competition data
                        $competition['name'] = $name;
                        $competition['date'] = $date;
                        $competition['location'] = $location;
                        $competition['status'] = $status;
                        $competition['event_ids'] = $eventIds;
                        $competition['participant_ids'] = $participantIds;
                        $competition['category_ids'] = $categoryIds;
                        
                        // Update competition
                        if (updateCompetition($id, $competition)) {
                            setFlashMessage('success', 'Competition updated successfully.');
                            redirect('competitions.php');
                        } else {
                            setFlashMessage('error', 'Failed to update competition. Please try again.');
                        }
                    }
                }
                break;
                
            case 'delete':
                // Delete competition
                $id = isset($_POST['id']) ? sanitizeInput($_POST['id']) : '';
                
                // Validate input
                if (empty($id)) {
                    setFlashMessage('error', 'Competition ID is required.');
                } else {
                    // Delete competition
                    if (deleteCompetition($id)) {
                        setFlashMessage('success', 'Competition deleted successfully.');
                        redirect('competitions.php');
                    } else {
                        setFlashMessage('error', 'Failed to delete competition. Please try again.');
                    }
                }
                break;
                
            case 'update_status':
                // Update competition status
                $id = isset($_POST['id']) ? sanitizeInput($_POST['id']) : '';
                $status = isset($_POST['status']) ? sanitizeInput($_POST['status']) : '';
                
                // Validate input
                if (empty($id) || empty($status) || !in_array($status, ['upcoming', 'active', 'completed'])) {
                    setFlashMessage('error', 'Competition ID and valid status are required.');
                } else {
                    // Get existing competition
                    $competition = getCompetition($id);
                    
                    if (!$competition) {
                        setFlashMessage('error', 'Competition not found.');
                    } else {
                        // Update competition status
                        $competition['status'] = $status;
                        
                        // Update competition
                        if (updateCompetition($id, $competition)) {
                            setFlashMessage('success', 'Competition status updated successfully.');
                            redirect('competitions.php');
                        } else {
                            setFlashMessage('error', 'Failed to update competition status. Please try again.');
                        }
                    }
                }
                break;
        }
    }
}

// Get action and ID from query string for edit form
$action = isset($_GET['action']) ? $_GET['action'] : '';
$id = isset($_GET['id']) ? $_GET['id'] : '';

// Get competition for edit form
$editCompetition = null;
if ($action === 'edit' && $id) {
    $editCompetition = getCompetition($id);
    if (!$editCompetition) {
        setFlashMessage('error', 'Competition not found.');
        redirect('competitions.php');
    }
}

// Set page title and admin flag
$pageTitle = 'Manage Competitions';
$isAdmin = true;

// Include header
include '../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h1 class="mb-3">
            <i class="fas fa-flag-checkered me-2 text-primary"></i>Manage Competitions
        </h1>
        
        <div class="card">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" id="competitionTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?php echo $action !== 'edit' ? 'active' : ''; ?>" id="competitions-tab" data-bs-toggle="tab" data-bs-target="#competitions" type="button" role="tab" aria-controls="competitions" aria-selected="<?php echo $action !== 'edit' ? 'true' : 'false'; ?>">
                            <i class="fas fa-list me-1"></i>All Competitions
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?php echo $action === 'edit' ? 'active' : ''; ?>" id="add-competition-tab" data-bs-toggle="tab" data-bs-target="#add-competition" type="button" role="tab" aria-controls="add-competition" aria-selected="<?php echo $action === 'edit' ? 'true' : 'false'; ?>">
                            <i class="fas fa-<?php echo $action === 'edit' ? 'edit' : 'plus'; ?> me-1"></i><?php echo $action === 'edit' ? 'Edit Competition' : 'Add Competition'; ?>
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="competitionTabsContent">
                    <!-- Competitions List Tab -->
                    <div class="tab-pane fade <?php echo $action !== 'edit' ? 'show active' : ''; ?>" id="competitions" role="tabpanel" aria-labelledby="competitions-tab">
                        <?php if (empty($competitions)): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>No competitions found. Add your first competition using the "Add Competition" tab.
                            </div>
                        <?php else: ?>
                            <ul class="nav nav-pills mb-3">
                                <li class="nav-item">
                                    <a class="nav-link active" href="#active-competitions" data-bs-toggle="tab">Active</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#upcoming-competitions" data-bs-toggle="tab">Upcoming</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#completed-competitions" data-bs-toggle="tab">Completed</a>
                                </li>
                            </ul>
                            
                            <div class="tab-content">
                                <!-- Active Competitions -->
                                <div class="tab-pane fade show active" id="active-competitions">
                                    <?php
                                    $activeCompetitions = array_filter($competitions, function($comp) {
                                        return $comp['status'] === 'active';
                                    });
                                    
                                    if (empty($activeCompetitions)):
                                    ?>
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle me-2"></i>No active competitions found.
                                        </div>
                                    <?php else: ?>
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
                                                                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#statusModal<?php echo $competition['id']; ?>_completed">
                                                                    <i class="fas fa-check me-1"></i>Complete
                                                                </button>
                                                                
                                                                <!-- Status Change Modal -->
                                                                <div class="modal fade" id="statusModal<?php echo $competition['id']; ?>_completed" tabindex="-1" aria-labelledby="statusModalLabel<?php echo $competition['id']; ?>_completed" aria-hidden="true">
                                                                    <div class="modal-dialog">
                                                                        <div class="modal-content">
                                                                            <div class="modal-header">
                                                                                <h5 class="modal-title" id="statusModalLabel<?php echo $competition['id']; ?>_completed">Confirm Status Change</h5>
                                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                            </div>
                                                                            <div class="modal-body">
                                                                                <p>Are you sure you want to mark the competition "<?php echo htmlspecialchars($competition['name']); ?>" as completed?</p>
                                                                                <p class="text-info"><strong>Note:</strong> This will finalize all scores and rankings.</p>
                                                                            </div>
                                                                            <div class="modal-footer">
                                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                                <form method="post" action="competitions.php">
                                                                                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                                                                    <input type="hidden" name="action" value="update_status">
                                                                                    <input type="hidden" name="id" value="<?php echo $competition['id']; ?>">
                                                                                    <input type="hidden" name="status" value="completed">
                                                                                    <button type="submit" class="btn btn-success">Mark as Completed</button>
                                                                                </form>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Upcoming Competitions -->
                                <div class="tab-pane fade" id="upcoming-competitions">
                                    <?php
                                    $upcomingCompetitions = array_filter($competitions, function($comp) {
                                        return $comp['status'] === 'upcoming';
                                    });
                                    
                                    if (empty($upcomingCompetitions)):
                                    ?>
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle me-2"></i>No upcoming competitions found.
                                        </div>
                                    <?php else: ?>
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
                                                    <?php foreach ($upcomingCompetitions as $competition): ?>
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
                                                                <a href="competitions.php?action=edit&id=<?php echo $competition['id']; ?>" class="btn btn-sm btn-primary">
                                                                    <i class="fas fa-edit me-1"></i>Edit
                                                                </a>
                                                                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#statusModal<?php echo $competition['id']; ?>_active">
                                                                    <i class="fas fa-play me-1"></i>Start
                                                                </button>
                                                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $competition['id']; ?>">
                                                                    <i class="fas fa-trash-alt me-1"></i>Delete
                                                                </button>
                                                                
                                                                <!-- Status Change Modal -->
                                                                <div class="modal fade" id="statusModal<?php echo $competition['id']; ?>_active" tabindex="-1" aria-labelledby="statusModalLabel<?php echo $competition['id']; ?>_active" aria-hidden="true">
                                                                    <div class="modal-dialog">
                                                                        <div class="modal-content">
                                                                            <div class="modal-header">
                                                                                <h5 class="modal-title" id="statusModalLabel<?php echo $competition['id']; ?>_active">Confirm Status Change</h5>
                                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                            </div>
                                                                            <div class="modal-body">
                                                                                <p>Are you sure you want to start the competition "<?php echo htmlspecialchars($competition['name']); ?>"?</p>
                                                                                <p class="text-info"><strong>Note:</strong> This will make the competition active and allow score entry.</p>
                                                                            </div>
                                                                            <div class="modal-footer">
                                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                                <form method="post" action="competitions.php">
                                                                                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                                                                    <input type="hidden" name="action" value="update_status">
                                                                                    <input type="hidden" name="id" value="<?php echo $competition['id']; ?>">
                                                                                    <input type="hidden" name="status" value="active">
                                                                                    <button type="submit" class="btn btn-success">Start Competition</button>
                                                                                </form>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                
                                                                <!-- Delete Confirmation Modal -->
                                                                <div class="modal fade" id="deleteModal<?php echo $competition['id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?php echo $competition['id']; ?>" aria-hidden="true">
                                                                    <div class="modal-dialog">
                                                                        <div class="modal-content">
                                                                            <div class="modal-header">
                                                                                <h5 class="modal-title" id="deleteModalLabel<?php echo $competition['id']; ?>">Confirm Deletion</h5>
                                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                            </div>
                                                                            <div class="modal-body">
                                                                                <p>Are you sure you want to delete the competition "<?php echo htmlspecialchars($competition['name']); ?>"?</p>
                                                                                <p class="text-danger"><strong>Warning:</strong> This action cannot be undone.</p>
                                                                            </div>
                                                                            <div class="modal-footer">
                                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                                <form method="post" action="competitions.php">
                                                                                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                                                                    <input type="hidden" name="action" value="delete">
                                                                                    <input type="hidden" name="id" value="<?php echo $competition['id']; ?>">
                                                                                    <button type="submit" class="btn btn-danger">Delete</button>
                                                                                </form>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Completed Competitions -->
                                <div class="tab-pane fade" id="completed-competitions">
                                    <?php
                                    $completedCompetitions = array_filter($competitions, function($comp) {
                                        return $comp['status'] === 'completed';
                                    });
                                    
                                    if (empty($completedCompetitions)):
                                    ?>
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle me-2"></i>No completed competitions found.
                                        </div>
                                    <?php else: ?>
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
                                                    <?php foreach ($completedCompetitions as $competition): ?>
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
                                                                <a href="../competition.php?id=<?php echo $competition['id']; ?>" target="_blank" class="btn btn-sm btn-primary">
                                                                    <i class="fas fa-eye me-1"></i>View Results
                                                                </a>
                                                                <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#statusModal<?php echo $competition['id']; ?>_active">
                                                                    <i class="fas fa-redo me-1"></i>Reactivate
                                                                </button>
                                                                
                                                                <!-- Status Change Modal -->
                                                                <div class="modal fade" id="statusModal<?php echo $competition['id']; ?>_active" tabindex="-1" aria-labelledby="statusModalLabel<?php echo $competition['id']; ?>_active" aria-hidden="true">
                                                                    <div class="modal-dialog">
                                                                        <div class="modal-content">
                                                                            <div class="modal-header">
                                                                                <h5 class="modal-title" id="statusModalLabel<?php echo $competition['id']; ?>_active">Confirm Status Change</h5>
                                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                            </div>
                                                                            <div class="modal-body">
                                                                                <p>Are you sure you want to reactivate the competition "<?php echo htmlspecialchars($competition['name']); ?>"?</p>
                                                                                <p class="text-warning"><strong>Note:</strong> This will allow further score modifications.</p>
                                                                            </div>
                                                                            <div class="modal-footer">
                                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                                <form method="post" action="competitions.php">
                                                                                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                                                                    <input type="hidden" name="action" value="update_status">
                                                                                    <input type="hidden" name="id" value="<?php echo $competition['id']; ?>">
                                                                                    <input type="hidden" name="status" value="active">
                                                                                    <button type="submit" class="btn btn-warning">Reactivate Competition</button>
                                                                                </form>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Add/Edit Competition Tab -->
                    <div class="tab-pane fade <?php echo $action === 'edit' ? 'show active' : ''; ?>" id="add-competition" role="tabpanel" aria-labelledby="add-competition-tab">
                        <form method="post" action="competitions.php" class="admin-form-container">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                            <input type="hidden" name="action" value="<?php echo $action === 'edit' ? 'edit' : 'add'; ?>">
                            <?php if ($action === 'edit'): ?>
                                <input type="hidden" name="id" value="<?php echo $editCompetition['id']; ?>">
                            <?php endif; ?>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="name" name="name" required value="<?php echo $action === 'edit' ? htmlspecialchars($editCompetition['name']) : ''; ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="date" name="date" required value="<?php echo $action === 'edit' ? date('Y-m-d', strtotime($editCompetition['date'])) : ''; ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="location" class="form-label">Location <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="location" name="location" required value="<?php echo $action === 'edit' ? htmlspecialchars($editCompetition['location']) : ''; ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                        <select class="form-select" id="status" name="status" required>
                                            <option value="upcoming" <?php echo $action === 'edit' && $editCompetition['status'] === 'upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                                            <option value="active" <?php echo $action === 'edit' && $editCompetition['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                            <option value="completed" <?php echo $action === 'edit' && $editCompetition['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="event_ids" class="form-label">Events</label>
                                        <select class="form-select" id="event_ids" name="event_ids[]" multiple size="8">
                                            <?php foreach ($events as $event): ?>
                                                <option value="<?php echo $event['id']; ?>" <?php echo $action === 'edit' && in_array($event['id'], $editCompetition['event_ids']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($event['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="form-text">Hold Ctrl (or Cmd on Mac) to select multiple events.</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="participant_ids" class="form-label">Participants</label>
                                        <select class="form-select" id="participant_ids" name="participant_ids[]" multiple size="8">
                                            <?php foreach ($participants as $participant): ?>
                                                <option value="<?php echo $participant['id']; ?>" <?php echo $action === 'edit' && in_array($participant['id'], $editCompetition['participant_ids']) ? 'selected' : ''; ?>>
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
                                        <div class="form-text">Hold Ctrl (or Cmd on Mac) to select multiple participants.</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="category_ids" class="form-label">Categories</label>
                                        <select class="form-select" id="category_ids" name="category_ids[]" multiple>
                                            <?php foreach ($categories as $category): ?>
                                                <option value="<?php echo $category['id']; ?>" <?php echo $action === 'edit' && isset($editCompetition['category_ids']) && in_array($category['id'], $editCompetition['category_ids']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($category['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="form-text">Hold Ctrl (or Cmd on Mac) to select multiple categories.</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-<?php echo $action === 'edit' ? 'save' : 'plus'; ?> me-2"></i><?php echo $action === 'edit' ? 'Update Competition' : 'Add Competition'; ?>
                                </button>
                                <?php if ($action === 'edit'): ?>
                                    <a href="competitions.php" class="btn btn-secondary ms-2">
                                        <i class="fas fa-times me-2"></i>Cancel
                                    </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include '../includes/footer.php';
?>