<?php
// admin/auth_check.php
// Centralized authentication and authorization check for administrative pages

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if not logged in or if the user doesn't have an admin/reviewer role
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'reviewer'])) {
    header("Location: ../login.php");
    exit;
}
?>