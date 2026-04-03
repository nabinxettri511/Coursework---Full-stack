<?php
require "db.php";

$id = $_GET['id'] ?? '';

if (!ctype_digit($id)) {
    die("Invalid car ID.");
}

$sql = "SELECT * FROM Cars WHERE car_id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$a_row = $result->fetch_assoc();

if (!$a_row) {
    die("Car not found.");
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($a_row['car_name']) ?></title>
</head>
<body>
  <h1><?= htmlspecialchars($a_row['car_name']) ?></h1>
  <p><?= nl2br(htmlspecialchars($a_row['car_description'])) ?></p>
  <p><strong>Rating:</strong> <?= htmlspecialchars($a_row['rating']) ?></p>
  <p><strong>Established date:</strong> <?= htmlspecialchars($a_row['established_date']) ?></p>

  <a href="list-cars.php">&lt;&lt; Back to list</a>
</body>
</html>