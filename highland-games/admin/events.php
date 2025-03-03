<?php
/**
 * Highland Games Scoreboard - Admin Events
 * 
 * This page allows the admin to manage events
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

// Get all events
$events = getEvents();

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
                // Add new event
                $name = isset($_POST['name']) ? sanitizeInput($_POST['name']) : '';
                $description = isset($_POST['description']) ? sanitizeInput($_POST['description']) : '';
                $scoringType = isset($_POST['scoring_type']) ? sanitizeInput($_POST['scoring_type']) : 'points';
                $maxPoints = isset($_POST['max_points']) ? intval($_POST['max_points']) : 10;
                
                // Validate input
                if (empty($name)) {
                    setFlashMessage('error', 'Event name is required.');
                } else {
                    // Create event data
                    $event = [
                        'name' => $name,
                        'description' => $description,
                        'scoring_type' => $scoringType,
                        'max_points' => $maxPoints,
                        'created_at' => date('c')
                    ];
                    
                    // Add event
                    if (addEvent($event)) {
                        setFlashMessage('success', 'Event added successfully.');
                        redirect('events.php');
                    } else {
                        setFlashMessage('error', 'Failed to add event. Please try again.');
                    }
                }
                break;
                
            case 'edit':
                // Edit existing event
                $id = isset($_POST['id']) ? sanitizeInput($_POST['id']) : '';
                $name = isset($_POST['name']) ? sanitizeInput($_POST['name']) : '';
                $description = isset($_POST['description']) ? sanitizeInput($_POST['description']) : '';
                $scoringType = isset($_POST['scoring_type']) ? sanitizeInput($_POST['scoring_type']) : 'points';
                $maxPoints = isset($_POST['max_points']) ? intval($_POST['max_points']) : 10;
                
                // Validate input
                if (empty($id) || empty($name)) {
                    setFlashMessage('error', 'Event ID and name are required.');
                } else {
                    // Get existing event
                    $event = getEvent($id);
                    
                    if (!$event) {
                        setFlashMessage('error', 'Event not found.');
                    } else {
                        // Update event data
                        $event['name'] = $name;
                        $event['description'] = $description;
                        $event['scoring_type'] = $scoringType;
                        $event['max_points'] = $maxPoints;
                        
                        // Update event
                        if (updateEvent($id, $event)) {
                            setFlashMessage('success', 'Event updated successfully.');
                            redirect('events.php');
                        } else {
                            setFlashMessage('error', 'Failed to update event. Please try again.');
                        }
                    }
                }
                break;
                
            case 'delete':
                // Delete event
                $id = isset($_POST['id']) ? sanitizeInput($_POST['id']) : '';
                
                // Validate input
                if (empty($id)) {
                    setFlashMessage('error', 'Event ID is required.');
                } else {
                    // Delete event
                    if (deleteEvent($id)) {
                        setFlashMessage('success', 'Event deleted successfully.');
                        redirect('events.php');
                    } else {
                        setFlashMessage('error', 'Failed to delete event. Please try again.');
                    }
                }
                break;
        }
    }
}

// Get action and ID from query string for edit form
$action = isset($_GET['action']) ? $_GET['action'] : '';
$id = isset($_GET['id']) ? $_GET['id'] : '';

// Get event for edit form
$editEvent = null;
if ($action === 'edit' && $id) {
    $editEvent = getEvent($id);
    if (!$editEvent) {
        setFlashMessage('error', 'Event not found.');
        redirect('events.php');
    }
}

// Set page title and admin flag
$pageTitle = 'Manage Events';
$isAdmin = true;

// Include header
include '../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h1 class="mb-3">
            <i class="fas fa-tasks me-2 text-primary"></i>Manage Events
        </h1>
        
        <div class="card">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" id="eventTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?php echo $action !== 'edit' ? 'active' : ''; ?>" id="events-tab" data-bs-toggle="tab" data-bs-target="#events" type="button" role="tab" aria-controls="events" aria-selected="<?php echo $action !== 'edit' ? 'true' : 'false'; ?>">
                            <i class="fas fa-list me-1"></i>All Events
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?php echo $action === 'edit' ? 'active' : ''; ?>" id="add-event-tab" data-bs-toggle="tab" data-bs-target="#add-event" type="button" role="tab" aria-controls="add-event" aria-selected="<?php echo $action === 'edit' ? 'true' : 'false'; ?>">
                            <i class="fas fa-<?php echo $action === 'edit' ? 'edit' : 'plus'; ?> me-1"></i><?php echo $action === 'edit' ? 'Edit Event' : 'Add Event'; ?>
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="eventTabsContent">
                    <!-- Events List Tab -->
                    <div class="tab-pane fade <?php echo $action !== 'edit' ? 'show active' : ''; ?>" id="events" role="tabpanel" aria-labelledby="events-tab">
                        <?php if (empty($events)): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>No events found. Add your first event using the "Add Event" tab.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Description</th>
                                            <th>Scoring Type</th>
                                            <th>Max Points</th>
                                            <th>Created</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($events as $event): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($event['name']); ?></td>
                                                <td><?php echo isset($event['description']) && $event['description'] ? htmlspecialchars($event['description']) : '-'; ?></td>
                                                <td><?php echo ucfirst(htmlspecialchars($event['scoring_type'])); ?></td>
                                                <td><?php echo isset($event['max_points']) ? $event['max_points'] : '-'; ?></td>
                                                <td><?php echo isset($event['created_at']) ? formatDate($event['created_at']) : '-'; ?></td>
                                                <td class="text-center">
                                                    <a href="events.php?action=edit&id=<?php echo $event['id']; ?>" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-edit me-1"></i>Edit
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $event['id']; ?>">
                                                        <i class="fas fa-trash-alt me-1"></i>Delete
                                                    </button>
                                                    
                                                    <!-- Delete Confirmation Modal -->
                                                    <div class="modal fade" id="deleteModal<?php echo $event['id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?php echo $event['id']; ?>" aria-hidden="true">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="deleteModalLabel<?php echo $event['id']; ?>">Confirm Deletion</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <p>Are you sure you want to delete the event "<?php echo htmlspecialchars($event['name']); ?>"?</p>
                                                                    <p class="text-danger"><strong>Warning:</strong> This action cannot be undone.</p>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                    <form method="post" action="events.php">
                                                                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                                                        <input type="hidden" name="action" value="delete">
                                                                        <input type="hidden" name="id" value="<?php echo $event['id']; ?>">
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
                    
                    <!-- Add/Edit Event Tab -->
                    <div class="tab-pane fade <?php echo $action === 'edit' ? 'show active' : ''; ?>" id="add-event" role="tabpanel" aria-labelledby="add-event-tab">
                        <form method="post" action="events.php" class="admin-form-container">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                            <input type="hidden" name="action" value="<?php echo $action === 'edit' ? 'edit' : 'add'; ?>">
                            <?php if ($action === 'edit'): ?>
                                <input type="hidden" name="id" value="<?php echo $editEvent['id']; ?>">
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required value="<?php echo $action === 'edit' ? htmlspecialchars($editEvent['name']) : ''; ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"><?php echo $action === 'edit' && isset($editEvent['description']) ? htmlspecialchars($editEvent['description']) : ''; ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="scoring_type" class="form-label">Scoring Type <span class="text-danger">*</span></label>
                                <select class="form-select" id="scoring_type" name="scoring_type" required>
                                    <option value="points" <?php echo $action === 'edit' && isset($editEvent['scoring_type']) && $editEvent['scoring_type'] === 'points' ? 'selected' : ''; ?>>Points</option>
                                    <option value="time" <?php echo $action === 'edit' && isset($editEvent['scoring_type']) && $editEvent['scoring_type'] === 'time' ? 'selected' : ''; ?>>Time</option>
                                    <option value="distance" <?php echo $action === 'edit' && isset($editEvent['scoring_type']) && $editEvent['scoring_type'] === 'distance' ? 'selected' : ''; ?>>Distance</option>
                                    <option value="height" <?php echo $action === 'edit' && isset($editEvent['scoring_type']) && $editEvent['scoring_type'] === 'height' ? 'selected' : ''; ?>>Height</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="max_points" class="form-label">Maximum Points <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="max_points" name="max_points" required min="1" value="<?php echo $action === 'edit' && isset($editEvent['max_points']) ? $editEvent['max_points'] : '10'; ?>">
                            </div>
                            
                            <div class="mb-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-<?php echo $action === 'edit' ? 'save' : 'plus'; ?> me-2"></i><?php echo $action === 'edit' ? 'Update Event' : 'Add Event'; ?>
                                </button>
                                <?php if ($action === 'edit'): ?>
                                    <a href="events.php" class="btn btn-secondary ms-2">
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