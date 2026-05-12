<?php
/**
 * Security Utilities for Relational Lens
 * Implements CSRF Protection
 */

/**
 * Generate a CSRF token and store it in the session if one doesn't exist
 * @return string The token
 */
function generate_csrf_token() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate a CSRF token against the session
 * @param string $token The token to validate
 * @return bool True if valid
 */
function validate_csrf_token($token) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Outputs a hidden input field with the CSRF token
 */
function csrf_input() {
    echo '<input type="hidden" name="csrf_token" value="' . generate_csrf_token() . '">';
}

/**
 * Verifies CSRF token for POST or GET requests
 * Terminate script if validation fails
 */
function verify_csrf() {
    $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
    if (!validate_csrf_token($token)) {
        die("Security Validation Failed: CSRF Token Invalid or Missing. Please refresh the page and try again.");
    }
}
?>