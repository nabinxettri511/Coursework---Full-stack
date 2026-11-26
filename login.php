<?php
session_start();
require "db.php";

$message = "";

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Generate CAPTCHA
if (!isset($_SESSION['captcha_num1'], $_SESSION['captcha_num2'])) {
    $_SESSION['captcha_num1'] = random_int(1, 9);
    $_SESSION['captcha_num2'] = random_int(1, 9);
}

// Login attempt limit
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}

if (($_SESSION['blocked_until'] ?? 0) <= time()) {
    if (isset($_SESSION['blocked_until'])) {
        $_SESSION['login_attempts'] = 0;
        unset($_SESSION['blocked_until']);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $captcha = $_POST['captcha'] ?? '';
    $token = $_POST['csrf_token'] ?? '';

    if (($_SESSION['blocked_until'] ?? 0) > time()) {

        $message = "Too many attempts. Try again in 5 minutes.";

    } elseif (!hash_equals($_SESSION['csrf_token'], $token)) {

        $message = "Invalid security token.";

    } elseif ($username === '' || $password === '') {

        $message = "Please complete all fields.";

    } elseif ($captcha !== (string)(
        $_SESSION['captcha_num1'] + $_SESSION['captcha_num2']
    )) {

        $message = "Incorrect CAPTCHA answer.";

    } else {

        $sql = "SELECT user_id, username, password
                FROM users WHERE username = ?";

        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();

        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        $stmt->close();

        if ($user && password_verify($password, $user['password'])) {

            session_regenerate_id(true);

            $_SESSION['loggedin'] = true;
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['login_attempts'] = 0;
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

            unset($_SESSION['blocked_until']);
            unset($_SESSION['captcha_num1']);
            unset($_SESSION['captcha_num2']);

            header("Location: list-cars.php");
            exit;

        } else {

            $_SESSION['login_attempts']++;

            $message = "Invalid username or password.";

            if ($_SESSION['login_attempts'] >= 5) {
                $_SESSION['blocked_until'] = time() + 300;
                $message = "Too many attempts. Try again in 5 minutes.";
            }
        }
    }

    // Refresh CAPTCHA
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

    <title>Login | My Cars System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

</head>

<body class="bg-light">

<div class="container mt-5" style="max-width: 450px;">

    <div class="card shadow-sm">

        <div class="card-body p-4">

            <h2 class="text-center mb-2">Login</h2>

            <p class="text-center text-muted mb-4">
                My Cars Management System
            </p>

            <?php if ($message !== ''): ?>

                <div class="alert alert-danger">
                    <?= htmlspecialchars($message) ?>
                </div>

            <?php endif; ?>

            <form method="post" action="login.php">

                <input type="hidden" name="csrf_token"
                       value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                <div class="mb-3">

                    <label class="form-label">Username</label>

                    <input type="text"
                           name="username"
                           class="form-control"
                           required>

                </div>

                <div class="mb-3">

                    <label class="form-label">Password</label>

                    <input type="password"
                           name="password"
                           class="form-control"
                           required>

                </div>

                <div class="mb-3">

                    <label class="form-label">

                        CAPTCHA:
                        <?= $_SESSION['captcha_num1'] ?>
                        +
                        <?= $_SESSION['captcha_num2'] ?>
                        = ?

                    </label>

                    <input type="number"
                           name="captcha"
                           class="form-control"
                           required>

                </div>

                <button type="submit"
                        class="btn btn-primary w-100">

                    Login

                </button>

            </form>

            <p class="text-center mt-3">

                Don't have an account?

                <a href="register.php">Register here</a>

            </p>

            <div class="text-center">

                <a href="list-cars.php">Back to Cars</a>

            </div>

        </div>

    </div>

</div>

</body>
</html>
