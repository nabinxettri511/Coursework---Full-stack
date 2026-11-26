
<?php
require_once "auth.php";
require_once "db.php";

$loggedIn = isLoggedIn();

$csrf_token = generateCsrfToken();

// Prevent XSS
function escape($value) {
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

// Validate input types
$fields = ['name', 'rating', 'from_date', 'to_date'];

foreach ($fields as $field) {

    if (isset($_GET[$field]) && !is_string($_GET[$field])) {

        http_response_code(400);
        exit("Invalid search input.");

    }

}

// Filter search input
$name = trim($_GET['name'] ?? '');

$rating = trim($_GET['rating'] ?? '');

$from_date = trim($_GET['from_date'] ?? '');

$to_date = trim($_GET['to_date'] ?? '');

// Validate name
if (mb_strlen($name) > 150) {

    http_response_code(400);
    exit("Search name is too long.");

}

// Validate rating
if ($rating !== '') {

    $validRating = filter_var(
        $rating,
        FILTER_VALIDATE_INT,
        [
            'options' => [
                'min_range' => 1,
                'max_range' => 10
            ]
        ]
    );

    if ($validRating === false) {

        http_response_code(400);
        exit("Invalid rating.");

    }

}

// Validate dates
foreach ([$from_date, $to_date] as $dateValue) {

    if ($dateValue !== '') {

        $date = DateTime::createFromFormat(
            '!Y-m-d',
            $dateValue
        );

        if (!$date ||
            $date->format('Y-m-d') !== $dateValue) {

            http_response_code(400);
            exit("Invalid date.");

        }

    }

}

// Validate date range
if ($from_date !== '' &&
    $to_date !== '' &&
    $from_date > $to_date) {

    http_response_code(400);
    exit("From date cannot be after To date.");

}

// Dynamic search query
$sql = "SELECT * FROM Cars WHERE 1=1";

$params = [];

$types = "";

// Search by car name
if ($name !== '') {

    $sql .= " AND car_name LIKE ?";

    $params[] = "%" . $name . "%";

    $types .= "s";

}

// Search by rating
if ($rating !== '') {

    $sql .= " AND rating = ?";

    $params[] = (int)$rating;

    $types .= "i";

}

// Search by starting date
if ($from_date !== '') {

    $sql .= " AND established_date >= ?";

    $params[] = $from_date;

    $types .= "s";

}

// Search by ending date
if ($to_date !== '') {

    $sql .= " AND established_date <= ?";

    $params[] = $to_date;

    $types .= "s";

}

$sql .= " ORDER BY established_date";

// Prepared statement prevents SQL injection
$stmt = $mysqli->prepare($sql);

if (!empty($params)) {

    $stmt->bind_param($types, ...$params);

}

$stmt->execute();

$results = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <title>Advanced Search | My Cars System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <style>

        body {
            background: #f5f7fa;
        }

        .card {
            border: none;
            border-radius: 12px;
        }

        .table thead th {
            background: #198754;
            color: white;
        }

        .table td {
            vertical-align: middle;
        }

        .table a {
            text-decoration: none;
        }

    </style>

</head>

<body>

<?php include "navbar.php"; ?>

<div class="container py-4">

    <div class="card shadow-sm mb-4">

        <div class="card-header bg-dark text-white">

            <h2 class="mb-0">Advanced Car Search</h2>

        </div>

        <div class="card-body p-4">

            <p class="text-muted">

                Search for cars using one or several
                criteria simultaneously.

            </p>

            <form method="get"
                  action="search-car.php"
                  class="row g-3">

                <div class="col-md-4">

                    <label for="name" class="form-label">
                        Car Name
                    </label>

                    <input type="text"
                           id="name"
                           name="name"
                           class="form-control"
                           placeholder="Enter car name"
                           maxlength="150"
                           value="<?= escape($name) ?>">

                </div>

                <div class="col-md-2">

                    <label for="rating" class="form-label">
                        Rating
                    </label>

                    <input type="number"
                           id="rating"
                           name="rating"
                           class="form-control"
                           min="1"
                           max="10"
                           step="1"
                           placeholder="1–10"
                           value="<?= escape($rating) ?>">

                </div>

                <div class="col-md-3">

                    <label for="from_date" class="form-label">
                        From Date
                    </label>

                    <input type="date"
                           id="from_date"
                           name="from_date"
                           class="form-control"
                           value="<?= escape($from_date) ?>">

                </div>

                <div class="col-md-3">

                    <label for="to_date" class="form-label">
                        To Date
                    </label>

                    <input type="date"
                           id="to_date"
                           name="to_date"
                           class="form-control"
                           value="<?= escape($to_date) ?>">

                </div>

                <div class="col-12">

                    <button type="submit"
                            class="btn btn-success">

                        Search

                    </button>

                    <a href="search-car.php"
                       class="btn btn-secondary">

                        Reset

                    </a>

                    <a href="list-cars.php"
                       class="btn btn-outline-dark">

                        Back to Cars

                    </a>

                </div>

            </form>

        </div>

    </div>

    <div class="card shadow-sm">

        <div class="card-body p-4">

            <h5 class="mb-3">

                Search Results:

                <span class="badge bg-success">

                    <?= (int)$results->num_rows ?>

                </span>

            </h5>

            <div class="table-responsive">

                <table class="table table-striped table-bordered">

                    <thead>

                        <tr>

                            <th>Car Name</th>

                            <th>Rating</th>

                            <th>Established Date</th>

                            <?php if ($loggedIn): ?>

                                <th>Actions</th>

                            <?php endif; ?>

                        </tr>

                    </thead>

                    <tbody>

                    <?php if ($results->num_rows === 0): ?>

                        <tr>

                            <td colspan="<?= $loggedIn ? 4 : 3 ?>"
                                class="text-center text-muted">

                                No cars found.

                            </td>

                        </tr>

                    <?php endif; ?>

                    <?php while ($car = $results->fetch_assoc()): ?>

                        <tr>

                            <td>

                                <a href="car-details.php?id=<?= (int)$car['car_id'] ?>">

                                    <?= escape($car['car_name']) ?>

                                </a>

                            </td>

                            <td>

                                <?= escape($car['rating']) ?>

                            </td>

                            <td>

                                <?= escape($car['established_date']) ?>

                            </td>

                            <?php if ($loggedIn): ?>

                                <td>

                                    <a href="edit-car-form.php?id=<?= (int)$car['car_id'] ?>"
                                       class="btn btn-warning btn-sm">

                                        Edit

                                    </a>

                                    <form action="delete-car.php"
                                          method="post"
                                          class="d-inline"
                                          onsubmit="return confirm('Are you sure you want to delete this car?');">

                                        <input type="hidden"
                                               name="id"
                                               value="<?= (int)$car['car_id'] ?>">

                                        <input type="hidden"
                                               name="csrf_token"
                                               value="<?= escape($csrf_token) ?>">

                                        <button type="submit"
                                                class="btn btn-danger btn-sm">

                                            Delete

                                        </button>

                                    </form>

                                </td>

                            <?php endif; ?>

                        </tr>

                    <?php endwhile; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
