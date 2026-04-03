<?php
session_start();
require "db.php";

$id = $_GET['id'] ?? '';

if (!ctype_digit($id)) {
    die("Invalid car ID.");
}

$sql = "DELETE FROM Cars WHERE car_id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $id);

if (!$stmt->execute()) {
    die("Database error: " . htmlspecialchars($stmt->error));
}

header("Location: list-cars.php");
exit;
?>