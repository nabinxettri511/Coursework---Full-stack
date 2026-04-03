<?php
session_start();
require "db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: list-cars.php");
    exit;
}

$car_id = $_POST['car_id'] ?? '';
$car_name = trim($_POST['car_name'] ?? '');
$car_description = trim($_POST['car_description'] ?? '');
$rating = trim($_POST['rating'] ?? '');
$established_date = trim($_POST['established_date'] ?? '');

if (!ctype_digit($car_id)) {
    die("Invalid car ID.");
}

if ($car_name === '' || $car_description === '' || $rating === '' || $established_date === '') {
    die("Please fill in all fields.");
}

if (!is_numeric($rating) || $rating < 1 || $rating > 10) {
    die("Rating must be between 1 and 10.");
}

$sql = "UPDATE Cars
        SET car_name = ?, car_description = ?, rating = ?, established_date = ?
        WHERE car_id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("ssisi", $car_name, $car_description, $rating, $established_date, $car_id);

if (!$stmt->execute()) {
    die("Database error: " . htmlspecialchars($stmt->error));
}

header("Location: list-cars.php");
exit;
?>