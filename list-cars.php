
<?php
require_once "auth.php";

requireAuth();

require_once "db.php";

$loggedIn = isLoggedIn();

$csrf_token = generateCsrfToken();

$sql = "SELECT * FROM Cars ORDER BY established_date";

$results = $mysqli->query($sql);

function escape($value) {
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}
?>

<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <title>My Cars System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <style>

        body {
            background: #f5f7fa;
        }

        .card-box {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        }

        .cars-table {
            width: 100%;
            background: white;
        }

        .cars-table th {
            background: #198754;
            color: white;
            padding: 14px;
        }

        .cars-table td {
            padding: 14px;
            border-bottom: 1px solid #ddd;
        }

        .cars-table tr:hover {
            background: #edf7f0;
        }

        .cars-table a {
            text-decoration: none;
        }

    </style>

</head>

<body>

<?php include "navbar.php"; ?>

<div class="container my-4">

    <?php if (!empty($_SESSION['success_message'])): ?>

        <div class="alert alert-success">

            <?= escape($_SESSION['success_message']) ?>

        </div>

        <?php unset($_SESSION['success_message']); ?>

    <?php endif; ?>

    <div class="card-box mb-4">

        <div class="d-flex justify-content-between
                    align-items-center mb-3">

            <h1 class="h3 mb-0">My Cars</h1>

            <?php if ($loggedIn): ?>

                <a href="add-car-form.php"
                   class="btn btn-success">

                    + Add Car

                </a>

            <?php endif; ?>

        </div>

        <div class="row g-3">

            <div class="col-md-5">

                <label class="form-label" for="liveName">
                    Live Search by Name
                </label>

                <input type="text"
                       id="liveName"
                       class="form-control"
                       placeholder="Enter car name">

            </div>

            <div class="col-md-3">

                <label class="form-label" for="liveRating">
                    Filter by Rating
                </label>

                <input type="number"
                       id="liveRating"
                       class="form-control"
                       min="1"
                       max="10"
                       placeholder="1 to 10">

            </div>

            <div class="col-md-4 d-flex align-items-end gap-2">

                <button type="button"
                        class="btn btn-secondary"
                        onclick="resetFilters()">

                    Reset

                </button>

                <a href="search-car.php"
                   class="btn btn-primary">

                    Advanced Search

                </a>

            </div>

        </div>

    </div>

    <div class="card-box">

        <h2 class="h5 mb-3">Cars List</h2>

        <div id="ajaxResults" class="table-responsive">

            <table class="cars-table">

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

                        <td colspan="<?= $loggedIn ? 4 : 3 ?>">

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

<script>

// Check login status
const loggedIn = <?= json_encode($loggedIn) ?>;

// CSRF security token
const csrfToken = <?= json_encode($csrf_token) ?>;

// Prevent XSS in AJAX results
function escapeHtml(value) {

    return String(value)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");

}

let latestRequest = 0;

// AJAX live search
function loadCars() {

    const requestId = ++latestRequest;

    const name =
        document.getElementById('liveName').value;

    const rating =
        document.getElementById('liveRating').value;

    const url = 'ajax.php?name='
        + encodeURIComponent(name)
        + '&rating='
        + encodeURIComponent(rating);

    fetch(url)

        .then(response => {

            if (!response.ok) {

                throw new Error('Request failed');

            }

            return response.json();

        })

        .then(data => {

            if (requestId !== latestRequest) {

                return;

            }

            let html = `

                <table class="cars-table">

                    <thead>

                        <tr>

                            <th>Car Name</th>

                            <th>Rating</th>

                            <th>Established Date</th>

                            ${loggedIn ? '<th>Actions</th>' : ''}

                        </tr>

                    </thead>

                    <tbody>

            `;

            if (data.length === 0) {

                html += `

                    <tr>

                        <td colspan="${loggedIn ? 4 : 3}">

                            No cars found.

                        </td>

                    </tr>

                `;

            } else {

                data.forEach(car => {

                    const id = Number(car.car_id);

                    if (!Number.isSafeInteger(id) || id < 1) {

                        return;

                    }

                    html += `

                        <tr>

                            <td>

                                <a href="car-details.php?id=${id}">

                                    ${escapeHtml(car.car_name)}

                                </a>

                            </td>

                            <td>

                                ${escapeHtml(car.rating)}

                            </td>

                            <td>

                                ${escapeHtml(car.established_date)}

                            </td>

                    `;

                    if (loggedIn) {

                        html += `

                            <td>

                                <a href="edit-car-form.php?id=${id}"
                                   class="btn btn-warning btn-sm">

                                    Edit

                                </a>

                                <form action="delete-car.php"
                                      method="post"
                                      class="d-inline"
                                      onsubmit="return confirm('Are you sure you want to delete this car?');">

                                    <input type="hidden"
                                           name="id"
                                           value="${id}">

                                    <input type="hidden"
                                           name="csrf_token"
                                           value="${escapeHtml(csrfToken)}">

                                    <button type="submit"
                                            class="btn btn-danger btn-sm">

                                        Delete

                                    </button>

                                </form>

                            </td>

                        `;

                    }

                    html += '</tr>';

                });

            }

            html += '</tbody></table>';

            document.getElementById('ajaxResults').innerHTML = html;

        })

        .catch(() => {

            if (requestId !== latestRequest) {

                return;

            }

            document.getElementById('ajaxResults').innerHTML =
                '<div class="alert alert-danger">Unable to load cars.</div>';

        });

}

// Reset AJAX filters
function resetFilters() {

    document.getElementById('liveName').value = '';

    document.getElementById('liveRating').value = '';

    loadCars();

}

// Listen for search input
document.getElementById('liveName')
    .addEventListener('input', loadCars);

// Listen for rating input
document.getElementById('liveRating')
    .addEventListener('input', loadCars);

</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
