
<?php
require_once "auth.php";

requireAuth();

require_once "db.php";

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit("Invalid request method.");
}

// Validate CSRF token
$token = $_POST['csrf_token'] ?? '';

if (!is_string($token) || !validateCsrfToken($token)) {
    http_response_code(403);
    exit("Invalid security token.");
}

// Validate car ID
$id = filter_input(
    INPUT_POST,
    'id',
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

if (!$id) {
    http_response_code(400);
    exit("Invalid car ID.");
}

try {

    // Prepared statement prevents SQL injection
    $sql = "DELETE FROM Cars WHERE car_id = ?";

    $stmt = $mysqli->prepare($sql);

    $stmt->bind_param("i", $id);

    if (!$stmt->execute()) {
        throw new RuntimeException("Delete failed.");
    }

    if ($stmt->affected_rows === 0) {
        $stmt->close();

        http_response_code(404);
        exit("Car not found.");
    }

    $stmt->close();

    $_SESSION['success_message'] =
        "Car deleted successfully.";

    header("Location: list-cars.php");
    exit;

} catch (Throwable $e) {

    error_log($e->getMessage());

    http_response_code(500);

    exit("Unable to delete car. Please try again.");
}
