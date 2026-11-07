<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php echo getCurrentTheme(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> | <?php echo COMPANY_NAME; ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons & Lucide Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/app.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?php echo DEFAULT_LOGO_PATH; ?>">
    
    <!-- Theme Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const theme = localStorage.getItem('theme') || '<?php echo getCurrentTheme(); ?>';
            document.documentElement.setAttribute('data-bs-theme', theme);
            
            // Initialize theme toggle
            const themeToggle = document.getElementById('theme-toggle');
            if (themeToggle) {
                themeToggle.addEventListener('click', function() {
                    const currentTheme = document.documentElement.getAttribute('data-bs-theme');
                    const newTheme = currentTheme === 'light' ? 'dark' : 'light';
                    document.documentElement.setAttribute('data-bs-theme', newTheme);
                    localStorage.setItem('theme', newTheme);
                });
            }
        });
    </script>
    
    <?php
    // Additional head content can be added here
    ?>
</head>
<body class="bg-body-tertiary">
    <!-- Shared Header Content -->
    <header class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="<?php echo BASE_URL; ?>">
                <img src="<?php echo DEFAULT_LOGO_PATH; ?>" alt="<?php echo COMPANY_NAME; ?>" class="logo-sm me-2">
                <span class="fw-bold"><?php echo SITE_NAME; ?></span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (isLoggedIn()): ?>
                        <!-- User Profile & Notifications -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-bell-fill me-2"></i>
                                <span class="badge bg-danger rounded-pill notification-count" id="notification-count">
                                    <?php 
                                    if (isset($_SESSION[USER_SESSION_KEY])) {
                                        $portal = $_SESSION[PORTAL_KEY] ?? 'employee';
                                        $userId = $_SESSION[USER_SESSION_KEY]['id'] ?? 0;
                                        // Simplified count - implement actual query
                                        echo '0'; 
                                    }
                                    ?>
                                </span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                <li><h6 class="dropdown-header">Notifications</h6></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#">No new notifications</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#">View All</a></li>
                            </ul>
                        </li>
                        
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle me-2"></i>
                                <?php echo $_SESSION[USER_SESSION_KEY]['name'] ?? 'User'; ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i> Profile</a></li>
                                <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i> Settings</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                            </ul>
                        </li>
                        
                        <!-- Theme Toggle -->
                        <li class="nav-item">
                            <button class="btn btn-link nav-link" id="theme-toggle" title="Toggle Theme">
                                <i class="bi bi-circle-half"></i>
                            </button>
                        </li>
                    <?php else: ?>
                        <!-- Login Links -->
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?portal=admin">Admin</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?portal=employee">Employee</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?portal=candidate">Candidate</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </header>

    <!-- Flash Messages -->
    <div class="container mt-3">
        <?php displayFlashMessage(); ?>
    </div>

    <!-- Main Content Container (to be filled by individual pages) -->
    <main class="container mt-4 mb-5">
        <!-- Content will be injected here -->
