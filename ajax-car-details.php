
<?php

header("Content-Type: application/json; charset=UTF-8");
header("X-Content-Type-Options: nosniff");

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {

    http_response_code(405);

    echo json_encode([
        "error" => "Method not allowed."
    ]);

    exit;

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

    echo json_encode([
        "error" => "Invalid car ID."
    ]);

    exit;

}

try {

    require_once "db.php";

    // Prepared statement prevents SQL injection
    $stmt = $mysqli->prepare(
        "SELECT car_id,
                car_name,
                car_description,
                established_date,
                rating
         FROM Cars
         WHERE car_id = ?"
    );

    $stmt->bind_param("i", $id);

    $stmt->execute();

    $result = $stmt->get_result();

    $car = $result->fetch_assoc();

    if (!$car) {

        http_response_code(404);

        echo json_encode([
            "error" => "Car not found."
        ]);

        exit;

    }

    // Return car information as JSON
    echo json_encode(
        $car,
        JSON_INVALID_UTF8_SUBSTITUTE
    );

    $stmt->close();

} catch (Throwable $e) {

    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        "error" => "Unable to retrieve car details."
    ]);

}
