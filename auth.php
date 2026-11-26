<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check login status
function isLoggedIn(): bool {
    return isset($_SESSION['loggedin'], $_SESSION['user_id'])
        && $_SESSION['loggedin'] === true;
}

// Protect private pages
function requireAuth(): void {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit;
    }
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
    return isset($_SESSION['csrf_token'])
        && $token !== null
        && hash_equals($_SESSION['csrf_token'], $token);
}
?>