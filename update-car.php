
<?php
require_once "auth.php";

requireAuth();

require_once "db.php";

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: list-cars.php");
    exit;
}

// Validate CSRF token
$token = $_POST['csrf_token'] ?? '';

if (!is_string($token) || !validateCsrfToken($token)) {
    http_response_code(403);
    exit("Invalid security token.");
}

// Validate input types
$fields = [
    'car_id',
    'car_name',
    'car_description',
    'established_date',
    'rating'
];

foreach ($fields as $field) {

    if (!isset($_POST[$field]) ||
        !is_string($_POST[$field])) {

        http_response_code(400);
        exit("Invalid form input.");
    }
}

// Validate car ID
$car_id = filter_var(
    $_POST['car_id'],
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

// Filter input
$car_name = trim($_POST['car_name']);

$car_description = trim($_POST['car_description']);

$established_date = trim($_POST['established_date']);

// Validate rating
$rating = filter_var(
    $_POST['rating'],
    FILTER_VALIDATE_INT,
    [
        'options' => [
            'min_range' => 1,
            'max_range' => 10
        ]
    ]
);

// Check car ID
if ($car_id === false) {
    http_response_code(400);
    exit("Invalid car ID.");
}

// Check required fields
if ($car_name === '' ||
    $car_description === '' ||
    $established_date === '') {

    http_response_code(400);
    exit("Please complete all fields.");
}

// Check name length
if (mb_strlen($car_name) > 150) {
    http_response_code(400);
    exit("Car name is too long.");
}

// Check description length
if (mb_strlen($car_description) > 2000) {
    http_response_code(400);
    exit("Description is too long.");
}

// Check rating
if ($rating === false) {
    http_response_code(400);
    exit("Rating must be an integer between 1 and 10.");
}

// Validate date
$date = DateTime::createFromFormat(
    '!Y-m-d',
    $established_date
);

if (!$date ||
    $date->format('Y-m-d') !== $established_date) {

    http_response_code(400);
    exit("Invalid established date.");
}

try {

    // Check that the car exists
    $check = $mysqli->prepare(
        "SELECT car_id FROM Cars WHERE car_id = ?"
    );

    $check->bind_param("i", $car_id);
    $check->execute();

    $result = $check->get_result();

    if ($result->num_rows === 0) {
        $check->close();

        http_response_code(404);
        exit("Car not found.");
    }

    $check->close();

    // Prepared statement prevents SQL injection
    $sql = "UPDATE Cars
            SET car_name = ?,
                car_description = ?,
                established_date = ?,
                rating = ?
            WHERE car_id = ?";

    $stmt = $mysqli->prepare($sql);

    $stmt->bind_param(
        "sssii",
        $car_name,
        $car_description,
        $established_date,
        $rating,
        $car_id
    );

    if (!$stmt->execute()) {
        throw new RuntimeException("Update failed.");
    }

    $stmt->close();

    header("Location: list-cars.php");
    exit;

} catch (Throwable $e) {

    error_log($e->getMessage());

    http_response_code(500);

    exit("Unable to update car. Please try again.");
}
