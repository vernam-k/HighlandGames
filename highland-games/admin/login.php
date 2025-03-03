<?php
/**
 * Highland Games Scoreboard - Admin Login
 * 
 * This page handles admin authentication
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

// Check if already logged in
if (isAdminSessionValid()) {
    // Redirect to admin dashboard or the page they were trying to access
    if (isset($_SESSION['redirect_after_login'])) {
        $redirect = $_SESSION['redirect_after_login'];
        unset($_SESSION['redirect_after_login']);
        redirect($redirect);
    } else {
        redirect('index.php');
    }
}

// Process login form submission
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        $error = 'Invalid form submission. Please try again.';
    } else {
        // Get username and password
        $username = isset($_POST['username']) ? sanitizeInput($_POST['username']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        
        // Authenticate
        if (authenticateAdmin($username, $password)) {
            // Redirect to admin dashboard or the page they were trying to access
            if (isset($_SESSION['redirect_after_login'])) {
                $redirect = $_SESSION['redirect_after_login'];
                unset($_SESSION['redirect_after_login']);
                redirect($redirect);
            } else {
                redirect('index.php');
            }
        } else {
            $error = 'Invalid username or password.';
        }
    }
}

// Set page title and admin flag
$pageTitle = 'Admin Login';
$isAdmin = true;

// Include header
include '../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="login-container">
            <div class="login-logo">
                <i class="fas fa-user-shield"></i>
                <h1 class="h3 mt-3">Admin Login</h1>
                <p class="text-muted">Enter your credentials to access the admin area</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-body">
                    <form method="post" action="login.php">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                        
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" class="form-control" id="username" name="username" required autofocus>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-sign-in-alt me-2"></i>Login
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="text-center mt-3">
                <a href="../index.php" class="text-decoration-none">
                    <i class="fas fa-arrow-left me-1"></i>Back to Public Site
                </a>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include '../includes/footer.php';
?>