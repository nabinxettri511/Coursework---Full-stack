
<?php
require_once "auth.php";

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header("Allow: POST");

    http_response_code(405);

    exit("Method not allowed.");

}

// Check whether user is logged in
requireAuth();

// Validate CSRF token
$token = $_POST['csrf_token'] ?? null;

if (!is_string($token) ||
    !validateCsrfToken($token)) {

    http_response_code(403);

    exit("Invalid security token.");

}

// Remove all session variables
$_SESSION = [];

// Delete session cookie
if (ini_get("session.use_cookies")) {

    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );

}

// Destroy session
session_destroy();

// Redirect to login page
header("Location: login.php");

exit;
