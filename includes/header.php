<?php
// includes/header.php - Responsive Navigation and HTML Header
require_once __DIR__ . '/functions.php';

// Determine active page for navigation highlight
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? sanitize($page_title) . ' | FlavorCraft Digital Recipe Book' : 'FlavorCraft - Digital Recipe Book'; ?></title>
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts: Playfair Display & Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <?php
    $css_prefix = ($current_page == 'register.php' || $current_page == 'login.php') ? '../' : '';
    ?>
    <link rel="stylesheet" href="<?php echo $css_prefix; ?>css/style.css">
</head>
<body>

    <!-- Main Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark-theme sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="<?php echo get_base_url(); ?>index.php">
                <span class="brand-icon me-2"><i class="bi bi-journal-bookmark-fill"></i></span>
                <span class="brand-text">Flavor<span class="text-accent">Craft</span></span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>" href="<?php echo get_base_url(); ?>index.php">
                            <i class="bi bi-house-door-fill me-1"></i> Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo get_base_url(); ?>index.php#recipes-section">
                            <i class="bi bi-egg-fried me-1"></i> Browse Recipes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'contact.php') ? 'active' : ''; ?>" href="<?php echo get_base_url(); ?>contact.php">
                            <i class="bi bi-envelope-paper-fill me-1"></i> Contact Us
                        </a>
                    </li>
                    <?php if (is_chef()): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>" href="<?php echo get_base_url(); ?>dashboard.php">
                                <i class="bi bi-journal-plus me-1"></i> Chef Portal
                            </a>
                        </li>
                    <?php elseif (is_customer()): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($current_page == 'customer_dashboard.php') ? 'active' : ''; ?>" href="<?php echo get_base_url(); ?>customer_dashboard.php">
                                <i class="bi bi-heart-fill text-danger me-1"></i> My Portal
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
                
                <div class="d-flex align-items-center gap-2">
                    <!-- Theme Toggle Button -->
                    <button id="themeToggleBtn" class="btn btn-outline-light btn-sm rounded-circle px-2 py-1 me-1" title="Toggle Theme">
                        <i class="bi bi-moon-stars-fill"></i>
                    </button>

                    <?php if (is_logged_in()): ?>
                        <span class="badge <?php echo is_chef() ? 'bg-accent' : 'bg-success'; ?> d-none d-md-inline me-1">
                            <?php echo is_chef() ? '👨‍🍳 Chef' : '🍽️ Customer'; ?>
                        </span>
                        <a href="<?php echo get_base_url(); ?>auth/profile.php" class="text-light me-2 d-none d-md-inline text-decoration-none text-hover-accent" title="View Profile">
                            <i class="bi bi-person-circle text-accent me-1"></i> <?php echo sanitize($_SESSION['username']); ?>
                        </a>
                        <a href="<?php echo get_base_url(); ?>auth/logout.php" class="btn btn-outline-light btn-sm rounded-pill px-3">
                            <i class="bi bi-box-arrow-right me-1"></i> Logout
                        </a>
                    <?php else: ?>
                        <a href="<?php echo get_base_url(); ?>auth/login.php" class="btn btn-outline-light btn-sm rounded-pill px-3">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Login
                        </a>
                        <a href="<?php echo get_base_url(); ?>auth/register.php" class="btn btn-accent btn-sm rounded-pill px-3">
                            <i class="bi bi-person-plus-fill me-1"></i> Register
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Global Flash Alerts Container -->
    <?php display_flash(); ?>
