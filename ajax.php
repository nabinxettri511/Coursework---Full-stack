<?php
include("db.php");

$name = trim($_GET['name'] ?? '');
$rating = trim($_GET['rating'] ?? '');

$sql = "SELECT car_id, car_name, rating, established_date FROM Cars WHERE 1=1";
$params = [];
$types = "";

if ($name !== '') {
    $sql .= " AND car_name LIKE ?";
    $params[] = "%" . $name . "%";
    $types .= "s";
}

if ($rating !== '') {
    $sql .= " AND rating = ?";
    $params[] = $rating;
    $types .= "i";
}

$sql .= " ORDER BY established_date";

$stmt = $mysqli->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

header('Content-Type: application/json');
echo json_encode($data);
?>