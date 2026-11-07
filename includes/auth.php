<?php
/**
 * Authentication, CSRF, RBAC & Anti-IDOR Guards
 */

session_start();

// Security constants
const CSRF_TOKEN_KEY = 'csrf_token';
const USER_SESSION_KEY = 'user_session';
const PORTAL_KEY = 'portal'; // 'admin', 'employee', 'candidate'

// Helper functions
function generateCsrfToken() {
    if (!isset($_SESSION[CSRF_TOKEN_KEY])) {
        $_SESSION[CSRF_TOKEN_KEY] = bin2hex(random_bytes(CSRF_TOKEN_LENGTH / 2));
    }
    return $_SESSION[CSRF_TOKEN_KEY];
}

function validateCsrfToken($token) {
    return isset($_SESSION[CSRF_TOKEN_KEY]) && hash_equals($_SESSION[CSRF_TOKEN_KEY], $token);
}

function isLoggedIn($portal = null) {
    if (!isset($_SESSION[USER_SESSION_KEY])) {
        return false;
    }
    
    if ($portal && isset($_SESSION[PORTAL_KEY])) {
        return $_SESSION[PORTAL_KEY] === $portal;
    }
    
    return true;
}

function redirect($url) {
    header("Location: " . BASE_URL . ltrim($url, '/'));
    exit();
}

function logout() {
    session_unset();
    session_destroy();
    redirect('index.php');
}

function checkAccess($requiredRole = null, $portal = null) {
    if (!isLoggedIn($portal)) {
        redirect('index.php?error=unauthorized');
    }
    
    if ($requiredRole && isset($_SESSION[USER_SESSION_KEY]['role'])) {
        // Role-based access control logic
        $userRole = $_SESSION[USER_SESSION_KEY]['role'];
        // Implement role hierarchy checking here
        // For now, basic check
        return true; // Implement actual RBAC logic
    }
    
    return true;
}

function preventIdor($expectedId, $providedId) {
    // Anti-IDOR protection: ensure user can only access their own data
    // Implement specific checks based on portal and context
    return $expectedId === $providedId;
}

function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function escapeSql($value) {
    // PDO prepared statements handle this, but helper for legacy code
    global $db;
    return $db->getConnection()->quote($value);
}

// Session regeneration on privilege changes
function regenerateSession() {
    session_regenerate_id(true);
}

// Audit logging helper
function logAudit($action, $details, $userId = null) {
    global $db;
    $portal = isset($_SESSION[PORTAL_KEY]) ? $_SESSION[PORTAL_KEY] : 'unknown';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    $sql = "INSERT INTO admin_audit_logs (admin_id, action, details, portal, ip_address, user_agent, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())";
    
    $db->query($sql, [$userId, $action, $details, $portal, $ip, $userAgent]);
}

// CSRF token injection for forms
function csrfField() {
    return '<input type="hidden" name="_csrf" value="' . generateCsrfToken() . '">';
}

// Check CSRF on POST requests
function checkCsrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['_csrf'] ?? '';
        if (!validateCsrfToken($token)) {
            die("CSRF token validation failed. Please refresh and try again.");
        }
    }
}

?>
