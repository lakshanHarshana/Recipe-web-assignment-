<?php
// auth/logout.php - Logout Logic
require_once __DIR__ . '/../includes/functions.php';

session_unset();
session_destroy();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_start();
set_flash('info', 'You have been successfully logged out.');
header("Location: ../index.php");
exit;
?>
