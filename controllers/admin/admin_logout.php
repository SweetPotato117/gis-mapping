<?php
// Logout controller: destroy session and redirect to admin login
session_start();

// Unset all session variables
$_SESSION = array();

// If there's a session cookie, remove it
if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'], $params['secure'], $params['httponly']
        );
}

// Destroy the session
session_destroy();

// Clear any client-side storage hints via a small JS redirect fallback, but prefer PHP header
$redirect = '../../admin/index.php?msg=' . urlencode('You have been logged out successfully.');
header('Location: ' . $redirect);
exit();