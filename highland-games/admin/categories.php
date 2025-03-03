<?php
/**
 * Highland Games Scoreboard - Admin Categories
 * 
 * This page allows the admin to manage categories
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

// Get all categories
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
                // Add new category
                $name = isset($_POST['name']) ? sanitizeInput($_POST['name']) : '';
                $description = isset($_POST['description']) ? sanitizeInput($_POST['description']) : '';
                
                // Validate input
                if (empty($name)) {
                    setFlashMessage('error', 'Category name is required.');
                } else {
                    // Create category data
                    $category = [
                        'name' => $name,
                        'description' => $description
                    ];
                    
                    // Add category
                    if (addCategory($category)) {
                        setFlashMessage('success', 'Category added successfully.');
                        redirect('categories.php');
                    } else {
                        setFlashMessage('error', 'Failed to add category. Please try again.');
                    }
                }
                break;
                
            case 'edit':
                // Edit existing category
                $id = isset($_POST['id']) ? sanitizeInput($_POST['id']) : '';
                $name = isset($_POST['name']) ? sanitizeInput($_POST['name']) : '';
                $description = isset($_POST['description']) ? sanitizeInput($_POST['description']) : '';
                
                // Validate input
                if (empty($id) || empty($name)) {
                    setFlashMessage('error', 'Category ID and name are required.');
                } else {
                    // Get existing category
                    $category = getCategory($id);
                    
                    if (!$category) {
                        setFlashMessage('error', 'Category not found.');
                    } else {
                        // Update category data
                        $category['name'] = $name;
                        $category['description'] = $description;
                        
                        // Update category
                        if (updateCategory($id, $category)) {
                            setFlashMessage('success', 'Category updated successfully.');
                            redirect('categories.php');
                        } else {
                            setFlashMessage('error', 'Failed to update category. Please try again.');
                        }
                    }
                }
                break;
                
            case 'delete':
                // Delete category
                $id = isset($_POST['id']) ? sanitizeInput($_POST['id']) : '';
                
                // Validate input
                if (empty($id)) {
                    setFlashMessage('error', 'Category ID is required.');
                } else {
                    // Delete category
                    if (deleteCategory($id)) {
                        setFlashMessage('success', 'Category deleted successfully.');
                        redirect('categories.php');
                    } else {
                        setFlashMessage('error', 'Failed to delete category. Please try again.');
                    }
                }
                break;
        }
    }
}

// Get action and ID from query string for edit form
$action = isset($_GET['action']) ? $_GET['action'] : '';
$id = isset($_GET['id']) ? $_GET['id'] : '';

// Get category for edit form
$editCategory = null;
if ($action === 'edit' && $id) {
    $editCategory = getCategory($id);
    if (!$editCategory) {
        setFlashMessage('error', 'Category not found.');
        redirect('categories.php');
    }
}

// Set page title and admin flag
$pageTitle = 'Manage Categories';
$isAdmin = true;

// Include header
include '../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h1 class="mb-3">
            <i class="fas fa-tags me-2 text-primary"></i>Manage Categories
        </h1>
        
        <div class="card">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" id="categoryTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?php echo $action !== 'edit' ? 'active' : ''; ?>" id="categories-tab" data-bs-toggle="tab" data-bs-target="#categories" type="button" role="tab" aria-controls="categories" aria-selected="<?php echo $action !== 'edit' ? 'true' : 'false'; ?>">
                            <i class="fas fa-list me-1"></i>All Categories
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?php echo $action === 'edit' ? 'active' : ''; ?>" id="add-category-tab" data-bs-toggle="tab" data-bs-target="#add-category" type="button" role="tab" aria-controls="add-category" aria-selected="<?php echo $action === 'edit' ? 'true' : 'false'; ?>">
                            <i class="fas fa-<?php echo $action === 'edit' ? 'edit' : 'plus'; ?> me-1"></i><?php echo $action === 'edit' ? 'Edit Category' : 'Add Category'; ?>
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="categoryTabsContent">
                    <!-- Categories List Tab -->
                    <div class="tab-pane fade <?php echo $action !== 'edit' ? 'show active' : ''; ?>" id="categories" role="tabpanel" aria-labelledby="categories-tab">
                        <?php if (empty($categories)): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>No categories found. Add your first category using the "Add Category" tab.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Description</th>
                                            <th>Participants</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($categories as $category): ?>
                                            <?php $categoryParticipants = getParticipantsByCategory($category['id']); ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($category['name']); ?></td>
                                                <td><?php echo isset($category['description']) && $category['description'] ? htmlspecialchars($category['description']) : '-'; ?></td>
                                                <td><?php echo count($categoryParticipants); ?></td>
                                                <td class="text-center">
                                                    <a href="categories.php?action=edit&id=<?php echo $category['id']; ?>" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-edit me-1"></i>Edit
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $category['id']; ?>">
                                                        <i class="fas fa-trash-alt me-1"></i>Delete
                                                    </button>
                                                    
                                                    <!-- Delete Confirmation Modal -->
                                                    <div class="modal fade" id="deleteModal<?php echo $category['id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?php echo $category['id']; ?>" aria-hidden="true">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="deleteModalLabel<?php echo $category['id']; ?>">Confirm Deletion</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <p>Are you sure you want to delete the category "<?php echo htmlspecialchars($category['name']); ?>"?</p>
                                                                    <?php if (count($categoryParticipants) > 0): ?>
                                                                        <p class="text-warning"><strong>Note:</strong> This category has <?php echo count($categoryParticipants); ?> participants assigned to it. Deleting this category will remove it from these participants.</p>
                                                                    <?php endif; ?>
                                                                    <p class="text-danger"><strong>Warning:</strong> This action cannot be undone.</p>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                    <form method="post" action="categories.php">
                                                                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                                                        <input type="hidden" name="action" value="delete">
                                                                        <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
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
                    
                    <!-- Add/Edit Category Tab -->
                    <div class="tab-pane fade <?php echo $action === 'edit' ? 'show active' : ''; ?>" id="add-category" role="tabpanel" aria-labelledby="add-category-tab">
                        <form method="post" action="categories.php" class="admin-form-container">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                            <input type="hidden" name="action" value="<?php echo $action === 'edit' ? 'edit' : 'add'; ?>">
                            <?php if ($action === 'edit'): ?>
                                <input type="hidden" name="id" value="<?php echo $editCategory['id']; ?>">
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required value="<?php echo $action === 'edit' ? htmlspecialchars($editCategory['name']) : ''; ?>">
                                <div class="form-text">Examples: Men's Open, Women's Open, Masters (40+), Lightweight, etc.</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"><?php echo $action === 'edit' && isset($editCategory['description']) ? htmlspecialchars($editCategory['description']) : ''; ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-<?php echo $action === 'edit' ? 'save' : 'plus'; ?> me-2"></i><?php echo $action === 'edit' ? 'Update Category' : 'Add Category'; ?>
                                </button>
                                <?php if ($action === 'edit'): ?>
                                    <a href="categories.php" class="btn btn-secondary ms-2">
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