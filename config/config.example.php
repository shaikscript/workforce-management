<?php
/**
 * AI SQUARE HUB Configuration Template
 * Copy this file to config.php and update the values
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database_name');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_CHARSET', 'utf8mb4');

define('BASE_URL', 'https://yourdomain.rf.gd/'); // Change for local: 'http://localhost/'
define('SITE_NAME', 'AI SQUARE HUB');
define('COMPANY_NAME', 'AI SQUARE IT SOLUTIONS');
define('COMPANY_TAGLINE', 'Build • Evaluate • Grow Together');

// Timezone configuration (InfinityFree uses UTC, override as needed)
date_default_timezone_set('Asia/Kolkata');

// Security settings
define('SESSION_TIMEOUT', 1800); // 30 minutes
define('CSRF_TOKEN_LENGTH', 32);
define('MAX_UPLOAD_SIZE', 8388608); // 8 MB for InfinityFree compatibility

// SMTP Configuration (Default - will be overridden from database)
define('SMTP_HOST', 'smtp.example.com');
define('SMTP_PORT', 587);
define('SMTP_ENCRYPTION', 'tls');
define('SMTP_USER', 'email@example.com');
define('SMTP_PASS', 'password');
define('SMTP_FROM', 'noreply@aisquare.com');
define('SMTP_FROM_NAME', 'AI Square Hub');

// Logo and Branding (Default)
define('DEFAULT_LOGO_PATH', 'assets/img/logo.svg');
define('DEFAULT_DESIGNATION', 'AI Evaluator');

// File upload paths
define('UPLOAD_PATH', 'uploads/');
define('AVATAR_PATH', UPLOAD_PATH . 'avatars/');
define('COMPANY_LOGO_PATH', UPLOAD_PATH . 'company/');
define('TEST_MEDIA_PATH', UPLOAD_PATH . 'tests/');

// Allowed file types
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml']);
define('ALLOWED_AUDIO_TYPES', ['audio/mpeg', 'audio/wav', 'audio/ogg']);
define('ALLOWED_DOCUMENT_TYPES', ['application/pdf', 'text/csv', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);

?>
