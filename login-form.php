<?php
session_start();

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Secure Login</title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>
<body class="bg-light">

<div class="container mt-5" style="max-width: 400px;">

    <h2 class="text-center mb-4">Login</h2>

    <?php if (!empty($_SESSION['login_error'])): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($_SESSION['login_error']); ?>
        </div>
        <?php unset($_SESSION['login_error']); ?>
    <?php endif; ?>

    <form method="post" action="login.php">

        <input type="hidden" name="csrf_token"
               value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">

        <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="username"
                   class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password"
                   class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary w-100">
            Login
        </button>
    </form>

</div>

</body>
</html>
