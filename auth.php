<?php
// Auth helper functions. Call session_start() in the page that uses these.

// Is user logged in?
function isLoggedIn(): bool {
    return isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
}

// Protect a page
function requireAuth(): void {
    if (!isLoggedIn()) {
        header('Location: login-form.php');
        exit;
    }
}

// Simple user validation (demo only!)
function validateUser(string $username, string $password): bool {
    // Hardcoded credentials for demo
    if ($username === 'admin' && $password === 'admin123') {
        return true;
    }
    return false;
}

// Generate CSRF token
function generateCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Validate CSRF token
function validateCsrfToken(?string $token): bool {
    return isset($_SESSION['csrf_token']) &&
           $token !== null &&
           hash_equals($_SESSION['csrf_token'], $token);
}
?>
