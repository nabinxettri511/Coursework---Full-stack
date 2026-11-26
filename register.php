<?php
session_start();
require "db.php";

$message = "";
$success = false;

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Generate CAPTCHA
if (!isset($_SESSION['captcha_num1'], $_SESSION['captcha_num2'])) {
    $_SESSION['captcha_num1'] = random_int(1, 9);
    $_SESSION['captcha_num2'] = random_int(1, 9);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $captcha = $_POST['captcha'] ?? '';
    $token = $_POST['csrf_token'] ?? '';

    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $message = "Invalid security token.";
    } elseif ($username === '' || $email === '' || $password === '') {
        $message = "Please complete all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email address.";
    } elseif (strlen($password) < 8) {
        $message = "Password must contain at least 8 characters.";
    } elseif ($password !== $confirm) {
        $message = "Passwords do not match.";
    } elseif ($captcha !== (string)(
        $_SESSION['captcha_num1'] + $_SESSION['captcha_num2']
    )) {
        $message = "Incorrect CAPTCHA answer.";
    } else {

        $hashedPassword = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        try {
            $stmt = $mysqli->prepare(
                "INSERT INTO users (username, email, password)
                 VALUES (?, ?, ?)"
            );

            $stmt->bind_param(
                "sss",
                $username,
                $email,
                $hashedPassword
            );

            $stmt->execute();
            $stmt->close();

            $message = "Registration successful!";
            $success = true;

        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) {
                $message = "Username or email already exists.";
            } else {
                $message = "Registration failed. Please try again.";
            }
        }
    }

    $_SESSION['captcha_num1'] = random_int(1, 9);
    $_SESSION['captcha_num2'] = random_int(1, 9);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <title>Register | My Cars System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5" style="max-width: 500px;">

    <div class="card shadow-sm">

        <div class="card-body p-4">

            <h2 class="text-center mb-2">Create Account</h2>

            <p class="text-center text-muted mb-4">
                My Cars Management System
            </p>

            <?php if ($message !== ''): ?>

                <div class="alert alert-<?= $success ? 'success' : 'danger' ?>">
                    <?= htmlspecialchars($message) ?>
                </div>

            <?php endif; ?>

            <form method="post" action="register.php">

                <input type="hidden" name="csrf_token"
                       value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                <div class="mb-3">
                    <label class="form-label">Username</label>

                    <input type="text" name="username"
                           class="form-control"
                           maxlength="100" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>

                    <input type="email" name="email"
                           class="form-control"
                           maxlength="150" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Password</label>

                    <input type="password" name="password"
                           class="form-control"
                           minlength="8" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Confirm Password</label>

                    <input type="password" name="confirm_password"
                           class="form-control" required>
                </div>

                <div class="mb-3">

                    <label class="form-label">
                        CAPTCHA:
                        <?= $_SESSION['captcha_num1'] ?>
                        +
                        <?= $_SESSION['captcha_num2'] ?>
                        = ?
                    </label>

                    <input type="number" name="captcha"
                           class="form-control" required>

                </div>

                <button type="submit"
                        class="btn btn-success w-100">
                    Register
                </button>

            </form>

            <p class="text-center mt-3">
                Already have an account?
                <a href="login.php">Login here</a>
            </p>

            <div class="text-center">
                <a href="list-cars.php">Back to Cars</a>
            </div>

        </div>

    </div>

</div>

</body>
</html>
