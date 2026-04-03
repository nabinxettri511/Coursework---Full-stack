<?php
session_start();
include("db.php");

// Run SQL query
$sql = "SELECT * FROM Cars ORDER BY established_date";
$results = mysqli_query($mysqli, $sql);

if (!$results) {
    die("Database error: " . htmlspecialchars(mysqli_error($mysqli)));
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Cars</title>

  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
  >

  <style>
    body {
      background: #f5f7fa;
    }

    .cars-table {
      font-family: Arial, sans-serif;
      border-collapse: collapse;
      width: 100%;
      background: #ffffff;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    .cars-table thead {
      background: #198754;
      color: white;
    }

    .cars-table th,
    .cars-table td {
      padding: 14px;
      text-align: left;
      font-size: 14px;
    }

    .cars-table td {
      border-bottom: 1px solid #e9ecef;
    }

    .cars-table tr:nth-child(even) {
      background-color: #f8f9fa;
    }

    .cars-table tbody tr:hover {
      background-color: #edf7f0;
      transition: 0.2s;
    }

    .cars-table a {
      color: #0d6efd;
      text-decoration: none;
      font-weight: 600;
    }

    .cars-table a:hover {
      text-decoration: underline;
    }

    .card-box {
      background: #ffffff;
      border-radius: 12px;
      padding: 20px;
      box-shadow: 0 3px 10px rgba(0,0,0,0.08);
    }
  </style>
</head>
<body>

  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
      <a class="navbar-brand fw-bold" href="list-cars.php">My Cars System</a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
              data-bs-target="#mainNavbar" aria-controls="mainNavbar"
              aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="mainNavbar">
        <div class="ms-auto d-flex align-items-center gap-2">

          <a class="btn btn-sm btn-outline-light" href="add-car-form.php">Add Car</a>
          <a class="btn btn-sm btn-outline-light" href="search-car.php">Advanced Search</a>

          <?php if (!empty($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
            <span class="navbar-text text-white ms-2">
              Logged in as: <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>
            </span>
            <a class="btn btn-sm btn-danger ms-2" href="logout.php">Logout</a>
          <?php else: ?>
            <a class="btn btn-sm btn-outline-light ms-2" href="login-form.php">Login</a>
          <?php endif; ?>

        </div>
      </div>
    </div>
  </nav>

  <div class="container my-4">

    <div class="card-box mb-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">My Cars</h1>
        <a href="add-car-form.php" class="btn btn-success btn-sm">+ Add a car</a>
      </div>

      <div class="row g-3">
        <div class="col-md-5">
          <label class="form-label">Live search by name</label>
          <input type="text" id="liveName" class="form-control" placeholder="Enter car name">
        </div>

        <div class="col-md-3">
          <label class="form-label">Live filter by rating</label>
          <input type="number" id="liveRating" class="form-control" placeholder="1 to 10" min="1" max="10">
        </div>

        <div class="col-md-4 d-flex align-items-end">
          <button type="button" class="btn btn-secondary me-2" onclick="resetFilters()">Reset</button>
          <a href="search-car.php" class="btn btn-primary">Open advanced search</a>
        </div>
      </div>
    </div>

    <div class="card-box">
      <h2 class="h5 mb-3">Cars List</h2>

      <div id="ajaxResults">
        <table class="cars-table">
          <thead>
            <tr>
              <th>Car</th>
              <th>Rating</th>
              <th>Established Date</th>
              <th style="width: 220px;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($a_row = mysqli_fetch_assoc($results)): ?>
              <tr>
                <td>
                  <a href="car-details.php?id=<?= (int)$a_row['car_id'] ?>">
                    <?= htmlspecialchars($a_row['car_name']) ?>
                  </a>
                </td>
                <td><?= htmlspecialchars($a_row['rating']) ?></td>
                <td><?= htmlspecialchars($a_row['established_date']) ?></td>
                <td>
                  <a class="btn btn-warning btn-sm"
                     href="edit-car-form.php?id=<?= (int)$a_row['car_id'] ?>">
                    Edit
                  </a>
                  <a class="btn btn-outline-danger btn-sm ms-1"
                     href="delete-car.php?id=<?= (int)$a_row['car_id'] ?>"
                     onclick="return confirm('Are you sure you want to delete this car?');">
                    Delete
                  </a>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>

  <script>
    function loadCars() {
      const name = document.getElementById('liveName').value;
      const rating = document.getElementById('liveRating').value;

      fetch('ajax.php?name=' + encodeURIComponent(name) + '&rating=' + encodeURIComponent(rating))
        .then(response => response.json())
        .then(data => {
          let html = `
            <table class="cars-table">
              <thead>
                <tr>
                  <th>Car</th>
                  <th>Rating</th>
                  <th>Established Date</th>
                  <th style="width: 220px;">Actions</th>
                </tr>
              </thead>
              <tbody>
          `;

          if (data.length === 0) {
            html += `<tr><td colspan="4">No cars found.</td></tr>`;
          } else {
            data.forEach(car => {
              html += `
                <tr>
                  <td><a href="car-details.php?id=${car.car_id}">${escapeHtml(car.car_name)}</a></td>
                  <td>${escapeHtml(car.rating)}</td>
                  <td>${escapeHtml(car.established_date)}</td>
                  <td>
                    <a class="btn btn-warning btn-sm" href="edit-car-form.php?id=${car.car_id}">Edit</a>
                    <a class="btn btn-outline-danger btn-sm ms-1"
                       href="delete-car.php?id=${car.car_id}"
                       onclick="return confirm('Are you sure you want to delete this car?');">
                      Delete
                    </a>
                  </td>
                </tr>
              `;
            });
          }

          html += `</tbody></table>`;
          document.getElementById('ajaxResults').innerHTML = html;
        });
    }

    function resetFilters() {
      document.getElementById('liveName').value = '';
      document.getElementById('liveRating').value = '';
      loadCars();
    }

    function escapeHtml(value) {
      return String(value)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
    }

    document.getElementById('liveName').addEventListener('keyup', loadCars);
    document.getElementById('liveRating').addEventListener('keyup', loadCars);
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>