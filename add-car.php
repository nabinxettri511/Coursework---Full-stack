
<?php
require_once "auth.php";

requireAuth();

require_once "db.php";

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: add-car-form.php");
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
    'CarName',
    'CarDescription',
    'DateEstablished',
    'CarRating'
];

foreach ($fields as $field) {
    if (!isset($_POST[$field]) || !is_string($_POST[$field])) {
        http_response_code(400);
        exit("Invalid form input.");
    }
}

// Read and filter input
$car_name = trim($_POST['CarName']);
$car_description = trim($_POST['CarDescription']);
$established_date = trim($_POST['DateEstablished']);

$rating = filter_var(
    $_POST['CarRating'],
    FILTER_VALIDATE_INT,
    [
        'options' => [
            'min_range' => 1,
            'max_range' => 10
        ]
    ]
);

// Validate required fields
if ($car_name === '' ||
    $car_description === '' ||
    $established_date === '') {

    http_response_code(400);
    exit("Please complete all fields.");
}

// Validate car name length
if (mb_strlen($car_name) > 150) {
    http_response_code(400);
    exit("Car name is too long.");
}

// Validate description length
if (mb_strlen($car_description) > 2000) {
    http_response_code(400);
    exit("Description is too long.");
}

// Validate rating
if ($rating === false) {
    http_response_code(400);
    exit("Rating must be an integer between 1 and 10.");
}

// Validate date
$date = DateTime::createFromFormat(
    '!Y-m-d',
    $established_date
);

if (!$date || $date->format('Y-m-d') !== $established_date) {
    http_response_code(400);
    exit("Invalid established date.");
}

// Prepared statement prevents SQL injection
try {

    $sql = "INSERT INTO Cars
            (car_name, car_description, established_date, rating)
            VALUES (?, ?, ?, ?)";

    $stmt = $mysqli->prepare($sql);

    $stmt->bind_param(
        "sssi",
        $car_name,
        $car_description,
        $established_date,
        $rating
    );

    if (!$stmt->execute()) {
        throw new RuntimeException("Insert failed.");
    }

    $stmt->close();

    header("Location: list-cars.php");
    exit;

} catch (Throwable $e) {

    error_log($e->getMessage());

    http_response_code(500);

    exit("Unable to add car. Please try again.");
}
