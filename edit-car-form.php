
<?php
require_once "auth.php";
require_once "db.php";

// Only logged-in users can edit cars
requireAuth();

// Generate CSRF token
$csrf_token = generateCsrfToken();

// Prevent XSS attacks
function escape($value) {
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

// Validate car ID
$id = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT,
    [
        'options' => [
            'min_range' => 1
        ]
    ]
);

if (!$id) {

    http_response_code(400);
    exit("Invalid car ID.");

}

// Retrieve car using prepared statement
$stmt = $mysqli->prepare(
    "SELECT * FROM Cars WHERE car_id = ?"
);

$stmt->bind_param("i", $id);

$stmt->execute();

$result = $stmt->get_result();

$car = $result->fetch_assoc();

// Check whether car exists
if (!$car) {

    http_response_code(404);
    exit("Car not found.");

}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <title>Edit Car | My Cars System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <style>

        body {
            background: #f5f7fa;
        }

        .form-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        }

        .form-label {
            font-weight: 600;
        }

    </style>

</head>

<body>

<?php include "navbar.php"; ?>

<div class="container py-5">

    <div class="row justify-content-center">

        <div class="col-md-8">

            <div class="form-card">

                <h2 class="mb-2">Edit Car</h2>

                <p class="text-muted mb-4">

                    Update the information for
                    <?= escape($car['car_name']) ?>.

                </p>

                <form action="update-car.php"
                      method="post">

                    <!-- Hidden car ID -->

                    <input type="hidden"
                           name="car_id"
                           value="<?= (int)$car['car_id'] ?>">

                    <!-- CSRF protection -->

                    <input type="hidden"
                           name="csrf_token"
                           value="<?= escape($csrf_token) ?>">

                    <!-- Car name -->

                    <div class="mb-3">

                        <label for="car_name"
                               class="form-label">

                            Car Name

                        </label>

                        <input type="text"
                               id="car_name"
                               name="car_name"
                               class="form-control"
                               maxlength="150"
                               value="<?= escape($car['car_name']) ?>"
                               required>

                    </div>

                    <!-- Car description -->

                    <div class="mb-3">

                        <label for="car_description"
                               class="form-label">

                            Car Description

                        </label>

                        <textarea id="car_description"
                                  name="car_description"
                                  class="form-control"
                                  rows="5"
                                  maxlength="2000"
                                  required><?= escape($car['car_description']) ?></textarea>

                    </div>

                    <!-- Established date -->

                    <div class="mb-3">

                        <label for="established_date"
                               class="form-label">

                            Established Date

                        </label>

                        <input type="date"
                               id="established_date"
                               name="established_date"
                               class="form-control"
                               value="<?= escape($car['established_date']) ?>"
                               required>

                    </div>

                    <!-- Car rating -->

                    <div class="mb-4">

                        <label for="rating"
                               class="form-label">

                            Car Rating

                        </label>

                        <input type="number"
                               id="rating"
                               name="rating"
                               class="form-control"
                               min="1"
                               max="10"
                               step="1"
                               value="<?= escape($car['rating']) ?>"
                               required>

                        <div class="form-text">

                            Rating must be between 1 and 10.

                        </div>

                    </div>

                    <!-- Form buttons -->

                    <div class="d-flex flex-wrap gap-2">

                        <button type="submit"
                                class="btn btn-success">

                            Update Car

                        </button>

                        <button type="reset"
                                class="btn btn-secondary">

                            Reset

                        </button>

                        <a href="list-cars.php"
                           class="btn btn-outline-dark">

                            Cancel

                        </a>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>
