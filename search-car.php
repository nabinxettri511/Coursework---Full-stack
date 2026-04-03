<?php
include("db.php");

// Get values safely
$name = trim($_GET['name'] ?? '');
$rating = trim($_GET['rating'] ?? '');
$from_date = trim($_GET['from_date'] ?? '');
$to_date = trim($_GET['to_date'] ?? '');

// Build dynamic SQL
$sql = "SELECT * FROM Cars WHERE 1=1";
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

if ($from_date !== '') {
    $sql .= " AND established_date >= ?";
    $params[] = $from_date;
    $types .= "s";
}

if ($to_date !== '') {
    $sql .= " AND established_date <= ?";
    $params[] = $to_date;
    $types .= "s";
}

$sql .= " ORDER BY established_date";

$stmt = $mysqli->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$results = $stmt->get_result();
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Search Cars</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="container py-4">

<h1 class="mb-4">Advanced Search</h1>

<form method="get" action="search-car.php" class="row g-3 mb-4">

  <div class="col-md-4">
    <label class="form-label">Car name</label>
    <input type="text" name="name" class="form-control"
           value="<?= htmlspecialchars($name) ?>">
  </div>

  <div class="col-md-2">
    <label class="form-label">Rating</label>
    <input type="number" name="rating" class="form-control"
           min="1" max="10"
           value="<?= htmlspecialchars($rating) ?>">
  </div>

  <div class="col-md-3">
    <label class="form-label">From date</label>
    <input type="date" name="from_date" class="form-control"
           value="<?= htmlspecialchars($from_date) ?>">
  </div>

  <div class="col-md-3">
    <label class="form-label">To date</label>
    <input type="date" name="to_date" class="form-control"
           value="<?= htmlspecialchars($to_date) ?>">
  </div>

  <div class="col-12">
    <button type="submit" class="btn btn-primary">Search</button>
    <a href="search-car.php" class="btn btn-secondary">Reset</a>
    <a href="list-cars.php" class="btn btn-outline-dark">Back</a>
  </div>

</form>

<table class="table table-bordered table-striped">

  <thead class="table-dark">
    <tr>
      <th>Car</th>
      <th>Rating</th>
      <th>Established Date</th>
      <th>Actions</th>
    </tr>
  </thead>

  <tbody>
    <?php if ($results->num_rows === 0): ?>
      <tr>
        <td colspan="4">No results found.</td>
      </tr>
    <?php else: ?>
      <?php while ($row = $results->fetch_assoc()): ?>
        <tr>
          <td>
            <a href="car-details.php?id=<?= (int)$row['car_id'] ?>">
              <?= htmlspecialchars($row['car_name']) ?>
            </a>
          </td>
          <td><?= htmlspecialchars($row['rating']) ?></td>
          <td><?= htmlspecialchars($row['established_date']) ?></td>
          <td>
            <a class="btn btn-sm btn-warning"
               href="edit-car-form.php?id=<?= (int)$row['car_id'] ?>">Edit</a>

            <a class="btn btn-sm btn-danger"
               href="delete-car.php?id=<?= (int)$row['car_id'] ?>"
               onclick="return confirm('Delete this car?')">Delete</a>
          </td>
        </tr>
      <?php endwhile; ?>
    <?php endif; ?>
  </tbody>

</table>

</body>
</html>
