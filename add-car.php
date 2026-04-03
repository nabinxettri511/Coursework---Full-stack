<?php
session_start();
require "db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: add-car-form.php");
    exit;
}

$car_name = trim($_POST['CarName'] ?? '');
$car_description = trim($_POST['CarDescription'] ?? '');
$car_establish_date = trim($_POST['DateEstablished'] ?? '');
$car_rating = trim($_POST['CarRating'] ?? '');

if ($car_name === '' || $car_description === '' || $car_establish_date === '' || $car_rating === '') {
    die("Please fill in all fields.");
}

if (!is_numeric($car_rating) || $car_rating < 1 || $car_rating > 10) {
    die("Rating must be between 1 and 10.");
}

$sql = "INSERT INTO Cars (car_name, car_description, established_date, rating)
        VALUES (?, ?, ?, ?)";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("sssi", $car_name, $car_description, $car_establish_date, $car_rating);

if (!$stmt->execute()) {
    die("Database error: " . htmlspecialchars($stmt->error));
}

header("Location: list-cars.php");
exit;
?>