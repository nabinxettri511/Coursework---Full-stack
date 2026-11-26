
<?php
require_once "db.php";

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

// Validate input types
if (
    (isset($_GET['name']) && !is_string($_GET['name'])) ||
    (isset($_GET['rating']) && !is_string($_GET['rating']))
) {

    http_response_code(400);

    echo json_encode([
        "error" => "Invalid input."
    ]);

    exit;

}

// Retrieve search values
$name = trim($_GET['name'] ?? '');

$rating = trim($_GET['rating'] ?? '');

// Validate name length
if (mb_strlen($name) > 150) {

    http_response_code(400);

    echo json_encode([
        "error" => "Car name is too long."
    ]);

    exit;

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

        echo json_encode([
            "error" => "Rating must be between 1 and 10."
        ]);

        exit;

    }

}

try {

    // Build search query
    $sql = "SELECT car_id,
                   car_name,
                   rating,
                   established_date
            FROM Cars
            WHERE 1=1";

    $params = [];

    $types = "";

    // Search by name
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

    $sql .= " ORDER BY established_date";

    // Prevent SQL injection
    $stmt = $mysqli->prepare($sql);

    if (!empty($params)) {

        $stmt->bind_param($types, ...$params);

    }

    $stmt->execute();

    $result = $stmt->get_result();

    $cars = [];

    while ($row = $result->fetch_assoc()) {

        $cars[] = $row;

    }

    // Return JSON data
    echo json_encode(
        $cars,
        JSON_INVALID_UTF8_SUBSTITUTE
    );

    $stmt->close();

} catch (Throwable $e) {

    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        "error" => "Unable to retrieve cars."
    ]);

}
