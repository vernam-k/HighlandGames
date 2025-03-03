<?php
/**
 * Highland Games Scoreboard - Admin Teams
 * 
 * This page allows the admin to manage teams
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

// Get all teams
$teams = getTeams();

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
                // Add new team
                $name = isset($_POST['name']) ? sanitizeInput($_POST['name']) : '';
                $description = isset($_POST['description']) ? sanitizeInput($_POST['description']) : '';
                
                // Validate input
                if (empty($name)) {
                    setFlashMessage('error', 'Team name is required.');
                } else {
                    // Create team data
                    $team = [
                        'name' => $name,
                        'description' => $description
                    ];
                    
                    // Add team
                    if (addTeam($team)) {
                        setFlashMessage('success', 'Team added successfully.');
                        redirect('teams.php');
                    } else {
                        setFlashMessage('error', 'Failed to add team. Please try again.');
                    }
                }
                break;
                
            case 'edit':
                // Edit existing team
                $id = isset($_POST['id']) ? sanitizeInput($_POST['id']) : '';
                $name = isset($_POST['name']) ? sanitizeInput($_POST['name']) : '';
                $description = isset($_POST['description']) ? sanitizeInput($_POST['description']) : '';
                
                // Validate input
                if (empty($id) || empty($name)) {
                    setFlashMessage('error', 'Team ID and name are required.');
                } else {
                    // Get existing team
                    $team = getTeam($id);
                    
                    if (!$team) {
                        setFlashMessage('error', 'Team not found.');
                    } else {
                        // Update team data
                        $team['name'] = $name;
                        $team['description'] = $description;
                        
                        // Update team
                        if (updateTeam($id, $team)) {
                            setFlashMessage('success', 'Team updated successfully.');
                            redirect('teams.php');
                        } else {
                            setFlashMessage('error', 'Failed to update team. Please try again.');
                        }
                    }
                }
                break;
                
            case 'delete':
                // Delete team
                $id = isset($_POST['id']) ? sanitizeInput($_POST['id']) : '';
                
                // Validate input
                if (empty($id)) {
                    setFlashMessage('error', 'Team ID is required.');
                } else {
                    // Delete team
                    if (deleteTeam($id)) {
                        setFlashMessage('success', 'Team deleted successfully.');
                        redirect('teams.php');
                    } else {
                        setFlashMessage('error', 'Failed to delete team. Please try again.');
                    }
                }
                break;
        }
    }
}

// Get action and ID from query string for edit form
$action = isset($_GET['action']) ? $_GET['action'] : '';
$id = isset($_GET['id']) ? $_GET['id'] : '';

// Get team for edit form
$editTeam = null;
if ($action === 'edit' && $id) {
    $editTeam = getTeam($id);
    if (!$editTeam) {
        setFlashMessage('error', 'Team not found.');
        redirect('teams.php');
    }
}

// Set page title and admin flag
$pageTitle = 'Manage Teams';
$isAdmin = true;

// Include header
include '../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h1 class="mb-3">
            <i class="fas fa-users-cog me-2 text-primary"></i>Manage Teams
        </h1>
        
        <div class="card">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" id="teamTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?php echo $action !== 'edit' ? 'active' : ''; ?>" id="teams-tab" data-bs-toggle="tab" data-bs-target="#teams" type="button" role="tab" aria-controls="teams" aria-selected="<?php echo $action !== 'edit' ? 'true' : 'false'; ?>">
                            <i class="fas fa-list me-1"></i>All Teams
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?php echo $action === 'edit' ? 'active' : ''; ?>" id="add-team-tab" data-bs-toggle="tab" data-bs-target="#add-team" type="button" role="tab" aria-controls="add-team" aria-selected="<?php echo $action === 'edit' ? 'true' : 'false'; ?>">
                            <i class="fas fa-<?php echo $action === 'edit' ? 'edit' : 'plus'; ?> me-1"></i><?php echo $action === 'edit' ? 'Edit Team' : 'Add Team'; ?>
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="teamTabsContent">
                    <!-- Teams List Tab -->
                    <div class="tab-pane fade <?php echo $action !== 'edit' ? 'show active' : ''; ?>" id="teams" role="tabpanel" aria-labelledby="teams-tab">
                        <?php if (empty($teams)): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>No teams found. Add your first team using the "Add Team" tab.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Description</th>
                                            <th>Members</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($teams as $team): ?>
                                            <?php $teamMembers = getParticipantsByTeam($team['id']); ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($team['name']); ?></td>
                                                <td><?php echo isset($team['description']) && $team['description'] ? htmlspecialchars($team['description']) : '-'; ?></td>
                                                <td><?php echo count($teamMembers); ?></td>
                                                <td class="text-center">
                                                    <a href="teams.php?action=edit&id=<?php echo $team['id']; ?>" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-edit me-1"></i>Edit
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $team['id']; ?>">
                                                        <i class="fas fa-trash-alt me-1"></i>Delete
                                                    </button>
                                                    
                                                    <!-- Delete Confirmation Modal -->
                                                    <div class="modal fade" id="deleteModal<?php echo $team['id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?php echo $team['id']; ?>" aria-hidden="true">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="deleteModalLabel<?php echo $team['id']; ?>">Confirm Deletion</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <p>Are you sure you want to delete the team "<?php echo htmlspecialchars($team['name']); ?>"?</p>
                                                                    <?php if (count($teamMembers) > 0): ?>
                                                                        <p class="text-warning"><strong>Note:</strong> This team has <?php echo count($teamMembers); ?> members. Deleting this team will remove it from these participants.</p>
                                                                    <?php endif; ?>
                                                                    <p class="text-danger"><strong>Warning:</strong> This action cannot be undone.</p>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                    <form method="post" action="teams.php">
                                                                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                                                        <input type="hidden" name="action" value="delete">
                                                                        <input type="hidden" name="id" value="<?php echo $team['id']; ?>">
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
                    
                    <!-- Add/Edit Team Tab -->
                    <div class="tab-pane fade <?php echo $action === 'edit' ? 'show active' : ''; ?>" id="add-team" role="tabpanel" aria-labelledby="add-team-tab">
                        <form method="post" action="teams.php" class="admin-form-container">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                            <input type="hidden" name="action" value="<?php echo $action === 'edit' ? 'edit' : 'add'; ?>">
                            <?php if ($action === 'edit'): ?>
                                <input type="hidden" name="id" value="<?php echo $editTeam['id']; ?>">
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required value="<?php echo $action === 'edit' ? htmlspecialchars($editTeam['name']) : ''; ?>">
                                <div class="form-text">Examples: Clan MacLeod, Highland Warriors, etc.</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"><?php echo $action === 'edit' && isset($editTeam['description']) ? htmlspecialchars($editTeam['description']) : ''; ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-<?php echo $action === 'edit' ? 'save' : 'plus'; ?> me-2"></i><?php echo $action === 'edit' ? 'Update Team' : 'Add Team'; ?>
                                </button>
                                <?php if ($action === 'edit'): ?>
                                    <a href="teams.php" class="btn btn-secondary ms-2">
                                        <i class="fas fa-times me-2"></i>Cancel
                                    </a>
                                <?php endif; ?>
                            </div>
                        </form>
                        
                        <?php if ($action === 'edit' && $editTeam): ?>
                            <?php $teamMembers = getParticipantsByTeam($editTeam['id']); ?>
                            <?php if (!empty($teamMembers)): ?>
                                <div class="mt-4">
                                    <h3 class="h5">Team Members</h3>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Categories</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($teamMembers as $member): ?>
                                                    <tr>
                                                        <td>
                                                            <a href="../participant.php?id=<?php echo $member['id']; ?>" target="_blank">
                                                                <?php echo htmlspecialchars($member['name']); ?>
                                                            </a>
                                                        </td>
                                                        <td>
                                                            <?php 
                                                            if (isset($member['category_ids']) && !empty($member['category_ids'])) {
                                                                $categoryNames = [];
                                                                foreach ($member['category_ids'] as $catId) {
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
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
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