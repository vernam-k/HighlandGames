<?php
/**
 * Highland Games Scoreboard - Admin Participants
 * 
 * This page allows the admin to manage participants
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

// Get all participants
$participants = getParticipants();

// Get all teams for the dropdown
$teams = getTeams();

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
                // Add new participant
                $name = isset($_POST['name']) ? sanitizeInput($_POST['name']) : '';
                $teamId = isset($_POST['team_id']) && $_POST['team_id'] !== '' ? sanitizeInput($_POST['team_id']) : null;
                $categoryIds = isset($_POST['category_ids']) && is_array($_POST['category_ids']) ? $_POST['category_ids'] : [];
                
                // Validate input
                if (empty($name)) {
                    setFlashMessage('error', 'Participant name is required.');
                } else {
                    // Create participant data
                    $participant = [
                        'name' => $name,
                        'team_id' => $teamId,
                        'category_ids' => $categoryIds,
                        'created_at' => date('c'),
                        'stats' => [
                            'personal_bests' => [],
                            'competition_history' => []
                        ]
                    ];
                    
                    // Add participant
                    if (addParticipant($participant)) {
                        setFlashMessage('success', 'Participant added successfully.');
                        redirect('participants.php');
                    } else {
                        setFlashMessage('error', 'Failed to add participant. Please try again.');
                    }
                }
                break;
                
            case 'edit':
                // Edit existing participant
                $id = isset($_POST['id']) ? sanitizeInput($_POST['id']) : '';
                $name = isset($_POST['name']) ? sanitizeInput($_POST['name']) : '';
                $teamId = isset($_POST['team_id']) && $_POST['team_id'] !== '' ? sanitizeInput($_POST['team_id']) : null;
                $categoryIds = isset($_POST['category_ids']) && is_array($_POST['category_ids']) ? $_POST['category_ids'] : [];
                
                // Validate input
                if (empty($id) || empty($name)) {
                    setFlashMessage('error', 'Participant ID and name are required.');
                } else {
                    // Get existing participant
                    $participant = getParticipant($id);
                    
                    if (!$participant) {
                        setFlashMessage('error', 'Participant not found.');
                    } else {
                        // Update participant data
                        $participant['name'] = $name;
                        $participant['team_id'] = $teamId;
                        $participant['category_ids'] = $categoryIds;
                        
                        // Update participant
                        if (updateParticipant($id, $participant)) {
                            setFlashMessage('success', 'Participant updated successfully.');
                            redirect('participants.php');
                        } else {
                            setFlashMessage('error', 'Failed to update participant. Please try again.');
                        }
                    }
                }
                break;
                
            case 'delete':
                // Delete participant
                $id = isset($_POST['id']) ? sanitizeInput($_POST['id']) : '';
                
                // Validate input
                if (empty($id)) {
                    setFlashMessage('error', 'Participant ID is required.');
                } else {
                    // Delete participant
                    if (deleteParticipant($id)) {
                        setFlashMessage('success', 'Participant deleted successfully.');
                        redirect('participants.php');
                    } else {
                        setFlashMessage('error', 'Failed to delete participant. Please try again.');
                    }
                }
                break;
        }
    }
}

// Get action and ID from query string for edit form
$action = isset($_GET['action']) ? $_GET['action'] : '';
$id = isset($_GET['id']) ? $_GET['id'] : '';

// Get participant for edit form
$editParticipant = null;
if ($action === 'edit' && $id) {
    $editParticipant = getParticipant($id);
    if (!$editParticipant) {
        setFlashMessage('error', 'Participant not found.');
        redirect('participants.php');
    }
}

// Set page title and admin flag
$pageTitle = 'Manage Participants';
$isAdmin = true;

// Include header
include '../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h1 class="mb-3">
            <i class="fas fa-users me-2 text-primary"></i>Manage Participants
        </h1>
        
        <div class="card">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" id="participantTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?php echo $action !== 'edit' ? 'active' : ''; ?>" id="participants-tab" data-bs-toggle="tab" data-bs-target="#participants" type="button" role="tab" aria-controls="participants" aria-selected="<?php echo $action !== 'edit' ? 'true' : 'false'; ?>">
                            <i class="fas fa-list me-1"></i>All Participants
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?php echo $action === 'edit' ? 'active' : ''; ?>" id="add-participant-tab" data-bs-toggle="tab" data-bs-target="#add-participant" type="button" role="tab" aria-controls="add-participant" aria-selected="<?php echo $action === 'edit' ? 'true' : 'false'; ?>">
                            <i class="fas fa-<?php echo $action === 'edit' ? 'edit' : 'plus'; ?> me-1"></i><?php echo $action === 'edit' ? 'Edit Participant' : 'Add Participant'; ?>
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="participantTabsContent">
                    <!-- Participants List Tab -->
                    <div class="tab-pane fade <?php echo $action !== 'edit' ? 'show active' : ''; ?>" id="participants" role="tabpanel" aria-labelledby="participants-tab">
                        <?php if (empty($participants)): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>No participants found. Add your first participant using the "Add Participant" tab.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Team</th>
                                            <th>Categories</th>
                                            <th>Created</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($participants as $participant): ?>
                                            <tr>
                                                <td>
                                                    <a href="../participant.php?id=<?php echo $participant['id']; ?>" target="_blank">
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
                                                <td>
                                                    <?php 
                                                    if (isset($participant['category_ids']) && !empty($participant['category_ids'])) {
                                                        $categoryNames = [];
                                                        foreach ($participant['category_ids'] as $catId) {
                                                            $category = getCategory($catId);
                                                            if ($category) {
                                                                $categoryNames[] = htmlspecialchars($category['name']);
                                                            }
                                                        }
                                                        echo implode(', ', $categoryNames);
                                                    } else {
                                                        echo '-';
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php echo isset($participant['created_at']) ? formatDate($participant['created_at']) : '-'; ?>
                                                </td>
                                                <td class="text-center">
                                                    <a href="participants.php?action=edit&id=<?php echo $participant['id']; ?>" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-edit me-1"></i>Edit
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $participant['id']; ?>">
                                                        <i class="fas fa-trash-alt me-1"></i>Delete
                                                    </button>
                                                    
                                                    <!-- Delete Confirmation Modal -->
                                                    <div class="modal fade" id="deleteModal<?php echo $participant['id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?php echo $participant['id']; ?>" aria-hidden="true">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="deleteModalLabel<?php echo $participant['id']; ?>">Confirm Deletion</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <p>Are you sure you want to delete the participant "<?php echo htmlspecialchars($participant['name']); ?>"?</p>
                                                                    <p class="text-danger"><strong>Warning:</strong> This action cannot be undone.</p>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                    <form method="post" action="participants.php">
                                                                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                                                        <input type="hidden" name="action" value="delete">
                                                                        <input type="hidden" name="id" value="<?php echo $participant['id']; ?>">
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
                    
                    <!-- Add/Edit Participant Tab -->
                    <div class="tab-pane fade <?php echo $action === 'edit' ? 'show active' : ''; ?>" id="add-participant" role="tabpanel" aria-labelledby="add-participant-tab">
                        <form method="post" action="participants.php" class="admin-form-container">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                            <input type="hidden" name="action" value="<?php echo $action === 'edit' ? 'edit' : 'add'; ?>">
                            <?php if ($action === 'edit'): ?>
                                <input type="hidden" name="id" value="<?php echo $editParticipant['id']; ?>">
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required value="<?php echo $action === 'edit' ? htmlspecialchars($editParticipant['name']) : ''; ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="team_id" class="form-label">Team</label>
                                <select class="form-select" id="team_id" name="team_id">
                                    <option value="">No Team</option>
                                    <?php foreach ($teams as $team): ?>
                                        <option value="<?php echo $team['id']; ?>" <?php echo $action === 'edit' && isset($editParticipant['team_id']) && $editParticipant['team_id'] === $team['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($team['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="category_ids" class="form-label">Categories</label>
                                <select class="form-select" id="category_ids" name="category_ids[]" multiple>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>" <?php echo $action === 'edit' && isset($editParticipant['category_ids']) && in_array($category['id'], $editParticipant['category_ids']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">Hold Ctrl (or Cmd on Mac) to select multiple categories.</div>
                            </div>
                            
                            <div class="mb-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-<?php echo $action === 'edit' ? 'save' : 'plus'; ?> me-2"></i><?php echo $action === 'edit' ? 'Update Participant' : 'Add Participant'; ?>
                                </button>
                                <?php if ($action === 'edit'): ?>
                                    <a href="participants.php" class="btn btn-secondary ms-2">
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