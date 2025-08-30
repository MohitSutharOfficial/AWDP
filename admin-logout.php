<?php
/**
 * Admin Logout Script
 * Safely logs out admin users and clears session data
 */

session_start();

// Log the logout action
if (isset($_SESSION['admin_username'])) {
    error_log('Admin logout: ' . $_SESSION['admin_username'] . ' from IP: ' . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown'));
}

// Clear all session variables
$_SESSION = [];

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
}

// Destroy the session
session_destroy();

// Redirect to login page with success message
header('Location: admin-login.php?logged_out=1');
exit();
?>
