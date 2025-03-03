<?php
/**
 * Header template for Highland Games Scoreboard
 */

// Prevent direct access to this file
if (!defined('HIGHLAND_GAMES')) {
    die('Direct access to this file is not allowed.');
}

// Get the current page for navigation highlighting
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    <meta name="description" content="<?php echo SITE_DESCRIPTION; ?>">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo isset($isAdmin) && $isAdmin ? '../' : ''; ?>assets/css/style.css">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <?php if (isset($extraHead)) echo $extraHead; ?>
</head>
<body class="<?php echo isset($bodyClass) ? $bodyClass : ''; ?>">
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
            <div class="container">
                <a class="navbar-brand" href="<?php echo isset($isAdmin) && $isAdmin ? '../index.php' : 'index.php'; ?>">
                    <i class="fas fa-trophy me-2"></i><?php echo SITE_NAME; ?>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <div class="collapse navbar-collapse" id="navbarMain">
                    <?php if (isset($isAdmin) && $isAdmin): ?>
                        <!-- Admin Navigation -->
                        <ul class="navbar-nav me-auto">
                            <li class="nav-item">
                                <a class="nav-link <?php echo $currentPage === 'index.php' ? 'active' : ''; ?>" href="index.php">
                                    <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $currentPage === 'competitions.php' ? 'active' : ''; ?>" href="competitions.php">
                                    <i class="fas fa-flag-checkered me-1"></i>Competitions
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $currentPage === 'events.php' ? 'active' : ''; ?>" href="events.php">
                                    <i class="fas fa-tasks me-1"></i>Events
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $currentPage === 'participants.php' ? 'active' : ''; ?>" href="participants.php">
                                    <i class="fas fa-users me-1"></i>Participants
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $currentPage === 'scores.php' ? 'active' : ''; ?>" href="scores.php">
                                    <i class="fas fa-star me-1"></i>Scores
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $currentPage === 'categories.php' ? 'active' : ''; ?>" href="categories.php">
                                    <i class="fas fa-tags me-1"></i>Categories
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $currentPage === 'teams.php' ? 'active' : ''; ?>" href="teams.php">
                                    <i class="fas fa-users-cog me-1"></i>Teams
                                </a>
                            </li>
                        </ul>
                        <ul class="navbar-nav">
                            <li class="nav-item">
                                <a class="nav-link" href="logout.php">
                                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                                </a>
                            </li>
                        </ul>
                    <?php else: ?>
                        <!-- Public Navigation -->
                        <ul class="navbar-nav me-auto">
                            <li class="nav-item">
                                <a class="nav-link <?php echo $currentPage === 'index.php' ? 'active' : ''; ?>" href="index.php">
                                    <i class="fas fa-home me-1"></i>Home
                                </a>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="competitionsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-flag-checkered me-1"></i>Competitions
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="competitionsDropdown">
                                    <li><a class="dropdown-item" href="competitions.php?status=active">Active Competitions</a></li>
                                    <li><a class="dropdown-item" href="competitions.php?status=upcoming">Upcoming Competitions</a></li>
                                    <li><a class="dropdown-item" href="competitions.php?status=completed">Past Competitions</a></li>
                                </ul>
                            </li>
                        </ul>
                        <ul class="navbar-nav">
                            <li class="nav-item">
                                <a class="nav-link" href="admin/login.php">
                                    <i class="fas fa-user-shield me-1"></i>Admin
                                </a>
                            </li>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>
    
    <main class="container py-4">
        <?php
        // Display flash message if any
        $flashMessage = getFlashMessage();
        if ($flashMessage) {
            $alertClass = 'alert-info';
            switch ($flashMessage['type']) {
                case 'success':
                    $alertClass = 'alert-success';
                    break;
                case 'error':
                    $alertClass = 'alert-danger';
                    break;
                case 'warning':
                    $alertClass = 'alert-warning';
                    break;
            }
            echo '<div class="alert ' . $alertClass . ' alert-dismissible fade show" role="alert">';
            echo $flashMessage['message'];
            echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
            echo '</div>';
        }
        ?>