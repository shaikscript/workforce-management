<?php
/**
 * Logout Script
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

// Log audit before destroying session
if (isLoggedIn()) {
    $userId = $_SESSION[USER_SESSION_KEY]['id'] ?? null;
    $portal = $_SESSION[PORTAL_KEY] ?? 'unknown';
    logAudit('logout', "User logged out from $portal portal", $userId);
}

// Destroy session and redirect
logout();
?>
