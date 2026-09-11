<?php
// includes/functions.php - Common helper functions and session management

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Sanitize string output for preventing XSS attacks
 */
function sanitize($data) {
    if (is_null($data)) return '';
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Check if a user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current logged in user details
 */
function current_user() {
    if (is_logged_in()) {
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'] ?? 'User',
            'email' => $_SESSION['user_email'] ?? '',
            'role' => $_SESSION['user_role'] ?? 'Customer'
        ];
    }
    return null;
}

/**
 * Role check helpers
 */
function is_chef() {
    return is_logged_in() && ($_SESSION['user_role'] ?? '') === 'Chef';
}

function is_customer() {
    return is_logged_in() && ($_SESSION['user_role'] ?? '') === 'Customer';
}

/**
 * Require login for protected routes
 */
function require_login() {
    if (!is_logged_in()) {
        set_flash('danger', 'Please log in to access this page.');
        header('Location: ' . get_base_url() . 'auth/login.php');
        exit;
    }
}

/**
 * Require Chef role for recipe creation/editing routes
 */
function require_chef() {
    require_login();
    if (!is_chef()) {
        set_flash('warning', 'Only registered Chefs can submit or edit recipes.');
        header('Location: ' . get_base_url() . 'customer_dashboard.php');
        exit;
    }
}

/**
 * Set a session flash message
 */
function set_flash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type, // success, danger, warning, info
        'message' => $message
    ];
}

/**
 * Get and clear session flash message
 */
function display_flash() {
    if (isset($_SESSION['flash'])) {
        $type = sanitize($_SESSION['flash']['type']);
        $message = sanitize($_SESSION['flash']['message']);
        unset($_SESSION['flash']);
        echo "<div class='alert alert-{$type} alert-dismissible fade show container mt-3' role='alert'>
                <i class='bi bi-info-circle-fill me-2'></i>{$message}
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
              </div>";
    }
}

/**
 * Helper to get site base URL
 */
function get_base_url() {
    $script_dir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
    // Normalize path separators for Windows / subfolder execution
    $base = rtrim(str_replace('\\', '/', $script_dir), '/');
    if (str_ends_with($base, '/auth')) {
        $base = substr($base, 0, -5);
    }
    return $base . '/';
}

/**
 * Helper to format difficulty badge HTML
 */
function get_difficulty_badge($difficulty) {
    switch (strtolower($difficulty)) {
        case 'easy':
            return '<span class="badge bg-success"><i class="bi bi-reception-1 me-1"></i>Easy</span>';
        case 'medium':
            return '<span class="badge bg-warning text-dark"><i class="bi bi-reception-2 me-1"></i>Medium</span>';
        case 'hard':
            return '<span class="badge bg-danger"><i class="bi bi-reception-4 me-1"></i>Hard</span>';
        default:
            return '<span class="badge bg-secondary">' . sanitize($difficulty) . '</span>';
    }
}
?>
