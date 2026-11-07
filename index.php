# Unified Login Gateway - AI SQUARE HUB
<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

// Check if already logged in
if (isLoggedIn()) {
    $portal = $_SESSION[PORTAL_KEY] ?? 'employee';
    redirect($portal . '/dashboard.php');
}

// Handle login submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();
    
    $portal = sanitizeInput($_POST['portal'] ?? 'employee');
    $identifier = sanitizeInput($_POST['identifier'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validate inputs
    if (empty($identifier) || empty($password)) {
        handleError('Please enter both identifier and password', 'danger');
    } else {
        // Portal-specific authentication
        $success = false;
        $userId = 0;
        $userName = '';
        $userRole = '';
        
        try {
            if ($portal === 'admin') {
                $stmt = $db->query("SELECT * FROM admins WHERE email = ? OR username = ? LIMIT 1", [$identifier, $identifier]);
                $user = $stmt->fetch();
                
                if ($user && password_verify($password, $user['password_hash'])) {
                    $success = true;
                    $userId = $user['id'];
                    $userName = $user['name'];
                    $userRole = $user['role'];
                }
                
            } elseif ($portal === 'employee') {
                $stmt = $db->query("SELECT * FROM employees WHERE employee_id = ? OR email = ? LIMIT 1", [$identifier, $identifier]);
                $user = $stmt->fetch();
                
                if ($user && password_verify($password, $user['password_hash'])) {
                    $success = true;
                    $userId = $user['id'];
                    $userName = $user['full_name'];
                    $userRole = 'employee';
                }
                
            } elseif ($portal === 'candidate') {
                $stmt = $db->query("SELECT * FROM candidates WHERE candidate_id = ? OR email = ? LIMIT 1", [$identifier, $identifier]);
                $user = $stmt->fetch();
                
                if ($user && password_verify($password, $user['password_hash'])) {
                    $success = true;
                    $userId = $user['id'];
                    $userName = $user['full_name'];
                    $userRole = 'candidate';
                }
            }
            
            if ($success) {
                // Regenerate session ID for security
                regenerateSession();
                
                // Set session data
                $_SESSION[USER_SESSION_KEY] = [
                    'id' => $userId,
                    'name' => $userName,
                    'role' => $userRole,
                    'portal' => $portal
                ];
                $_SESSION[PORTAL_KEY] = $portal;
                $_SESSION['last_activity'] = time();
                
                // Log audit
                logAudit('login', "User logged in via $portal portal", $userId);
                
                // Redirect to portal dashboard
                redirect($portal . '/dashboard.php');
            } else {
                handleError('Invalid identifier or password', 'danger');
                logAudit('login_failed', "Failed login attempt for $identifier via $portal portal", null);
            }
            
        } catch (PDOException $e) {
            handleError('Database error during login', 'danger');
            error_log("Login error: " . $e->getMessage());
        }
    }
}

// Get portal from URL parameter or default to employee
$portal = sanitizeInput($_GET['portal'] ?? 'employee');
$portals = [
    'admin' => 'Admin Hub',
    'employee' => 'Employee Hub',
    'candidate' => 'Candidate Hub'
];
$selectedPortal = $portals[$portal] ?? 'Employee Hub';
$portalClass = $portal === 'admin' ? 'bg-danger' : ($portal === 'candidate' ? 'bg-success' : 'bg-primary');

// Company settings
$companySettings = getCompanySettings();
$logoPath = $companySettings['logo_path'] ?? DEFAULT_LOGO_PATH;
$companyName = $companySettings['company_name'] ?? COMPANY_NAME;
$tagline = $companySettings['tagline'] ?? COMPANY_TAGLINE;

?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php echo getCurrentTheme(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo SITE_NAME; ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/app.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?php echo $logoPath; ?>">
    
    <style>
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #2563EB 0%, #0F172A 100%);
            color: white;
        }
        
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            color: #0F172A;
            border: none;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 450px;
            overflow: hidden;
        }
        
        .login-header {
            background: linear-gradient(135deg, #2563EB, #0F172A);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        
        .logo-container {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: white;
            border-radius: 50%;
            padding: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
        
        .logo-container img {
            max-width: 100%;
            max-height: 100%;
        }
        
        .portal-switcher {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            justify-content: center;
        }
        
        .portal-btn {
            padding: 10px 20px;
            border-radius: 20px;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            color: white;
        }
        
        .portal-btn.active {
            background: white;
            color: #0F172A;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
        
        .login-form {
            padding: 30px;
        }
        
        .form-control {
            border-radius: 8px;
            border: 1px solid #E2E8F0;
            padding: 12px;
            margin-bottom: 15px;
        }
        
        .btn-login {
            background: linear-gradient(135deg, #2563EB, #0F172A);
            color: white;
            border: none;
            padding: 12px;
            width: 100%;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.4);
        }
        
        .footer-text {
            text-align: center;
            margin-top: 30px;
            color: #64748B;
            font-size: 0.9rem;
        }
        
        .theme-toggle-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 8px;
            border-radius: 50%;
            cursor: pointer;
            backdrop-filter: blur(5px);
        }
    </style>
</head>
<body class="login-container">
    <!-- Theme Toggle -->
    <button class="theme-toggle-btn" id="theme-toggle" title="Toggle Theme">
        <i class="bi bi-circle-half"></i>
    </button>
    
    <div class="login-card">
        <!-- Header -->
        <div class="login-header">
            <div class="logo-container">
                <img src="<?php echo $logoPath; ?>" alt="<?php echo $companyName; ?>">
            </div>
            <h1 class="fw-bold mb-2"><?php echo SITE_NAME; ?></h1>
            <p class="mb-0" style="opacity: 0.9; font-size: 0.9rem;"><?php echo $tagline; ?></p>
        </div>
        
        <!-- Portal Switcher -->
        <div class="portal-switcher mt-3">
            <?php foreach ($portals as $key => $label): ?>
                <button type="button" class="portal-btn <?php echo $portal === $key ? 'active' : ''; ?>" 
                        onclick="window.location.href='index.php?portal=<?php echo $key; ?>'">
                    <?php echo $label; ?>
                </button>
            <?php endforeach; ?>
        </div>
        
        <!-- Login Form -->
        <form class="login-form" method="POST" action="index.php?portal=<?php echo $portal; ?>">
            <?php csrfField(); ?>
            <input type="hidden" name="portal" value="<?php echo $portal; ?>">
            
            <div class="mb-3">
                <label class="form-label fw-bold">
                    <?php echo $portal === 'admin' ? 'Admin Email/Username' : ($portal === 'candidate' ? 'Candidate ID/Email' : 'Employee ID/Email'); ?>
                </label>
                <input type="text" class="form-control" name="identifier" placeholder="Enter <?php echo $portal === 'admin' ? 'email/username' : 'ID/email'; ?>" required autofocus>
            </div>
            
            <div class="mb-4">
                <label class="form-label fw-bold">Password</label>
                <input type="password" class="form-control" name="password" placeholder="Enter password" required>
            </div>
            
            <?php displayFlashMessage(); ?>
            
            <button type="submit" class="btn btn-login">
                <i class="bi bi-box-arrow-in-right me-2"></i> Sign In
            </button>
        </form>
        
        <!-- Footer -->
        <div class="footer-text">
            <p class="mb-1">&copy; <?php echo date('Y'); ?> <?php echo $companyName; ?></p>
            <p class="mb-0"><?php echo $tagline; ?></p>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="assets/js/app.js"></script>
    
    <script>
        // Initialize theme switcher
        initThemeSwitcher();
    </script>
</body>
</html>
