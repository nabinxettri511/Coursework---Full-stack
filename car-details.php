
<?php
require_once "auth.php";
require_once "db.php";

// Check login status
$loggedIn = isLoggedIn();

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

// Prepared statement prevents SQL injection
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

    <title>
        <?= escape($car['car_name']) ?> | My Cars System
    </title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <style>

        body {
            background: #f5f7fa;
        }

        .details-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        }

        .detail-label {
            font-weight: 600;
            color: #555;
        }

        .car-title {
            color: #198754;
            font-weight: bold;
        }

        .description {
            line-height: 1.8;
            overflow-wrap: anywhere;
        }

        .rating-box {
            background: #edf7f0;
            border-radius: 8px;
            padding: 15px;
        }

    </style>

</head>

<body>

<?php include "navbar.php"; ?>

<div class="container py-5">

    <div class="row justify-content-center">

        <div class="col-md-8">

            <div class="details-card">

                <p class="text-muted mb-2">

                    Car Details

                </p>

                <h1 class="car-title mb-4">

                    <?= escape($car['car_name']) ?>

                </h1>

                <hr>

                <!-- Car ID -->

                <div class="mb-4">

                    <div class="detail-label">

                        Car ID

                    </div>

                    <p class="mb-0">

                        <?= (int)$car['car_id'] ?>

                    </p>

                </div>

                <!-- Car description -->

                <div class="mb-4">

                    <div class="detail-label">

                        Car Description

                    </div>

                    <p class="description">

                        <?= nl2br(
                            escape($car['car_description'])
                        ) ?>

                    </p>

                </div>

                <!-- Established date -->

                <div class="mb-4">

                    <div class="detail-label">

                        Established Date

                    </div>

                    <p class="mb-0">

                        <?= escape($car['established_date']) ?>

                    </p>

                </div>

                <!-- Car rating -->

                <div class="rating-box mb-4">

                    <div class="detail-label">

                        Car Rating

                    </div>

                    <h3 class="text-success mt-2">

                        <?= escape($car['rating']) ?> / 10

                    </h3>

                    <div class="progress"
                         role="progressbar"
                         aria-label="Car rating"
                         aria-valuenow="<?= (int)$car['rating'] ?>"
                         aria-valuemin="0"
                         aria-valuemax="10">

                        <div class="progress-bar bg-success"
                             style="width: <?= max(0, min(100, (int)$car['rating'] * 10)) ?>%">

                        </div>

                    </div>

                </div>

                <!-- Action buttons -->

                <div class="d-flex flex-wrap gap-2">

                    <a href="list-cars.php"
                       class="btn btn-secondary">

                        Back to Cars

                    </a>

                    <?php if ($loggedIn): ?>

                        <a href="edit-car-form.php?id=<?= (int)$car['car_id'] ?>"
                           class="btn btn-warning">

                            Edit Car

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
                                    class="btn btn-danger">

                                Delete Car

                            </button>

                        </form>

                    <?php endif; ?>

                </div>

            </div>

        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>
