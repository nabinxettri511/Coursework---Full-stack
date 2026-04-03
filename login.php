<?php
session_start();
require "db.php";

// -------------------------------
// SECURITY HARDENING
// -------------------------------

// 1. Too many failed attempts → block temporarily
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}
if ($_SESSION['login_attempts'] >= 5) {
    die("Too many failed login attempts. Try again later.");
}

// 2. Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login-form.php");
    exit;
}

// 3. Read username/password
$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($username === '' || $password === '') {
    $_SESSION['login_error'] = "Please fill in all fields.";
    header("Location: login-form.php");
    exit;
}

// -------------------------------
// LOOKUP USER IN DATABASE
// -------------------------------
$sql = "SELECT id, username, password_hash FROM users WHERE username = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    $_SESSION['login_attempts']++;
    $_SESSION['login_error'] = "Invalid username or password.";
    header("Location: login-form.php");
    exit;
}

// -------------------------------
// VERIFY PASSWORD
// -------------------------------
if (!password_verify($password, $user['password_hash'])) {
    $_SESSION['login_attempts']++;
    $_SESSION['login_error'] = "Invalid username or password.";
    header("Location: login-form.php");
    exit;
}

// -------------------------------
// LOGIN SUCCESS
// -------------------------------
$_SESSION['loggedin'] = true;
$_SESSION['username'] = $user['username'];
$_SESSION['user_id'] = $user['id'];
$_SESSION['agent'] = $_SERVER['HTTP_USER_AGENT']; // session hijack prevention

session_regenerate_id(true); // secure session refresh

// Reset failed login counter
$_SESSION['login_attempts'] = 0;

// -------------------------------
// PER-TAB LOGIN (IMPORTANT)
// -------------------------------
echo "<script>
    sessionStorage.setItem('tabLoggedIn', 'true');  
    window.location.href = 'index.php';
</script>";
exit;
?>
