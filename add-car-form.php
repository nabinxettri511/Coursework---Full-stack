
<?php
require_once "auth.php";

// Only logged-in users can add cars
requireAuth();

// Generate CSRF security token
$csrf_token = generateCsrfToken();
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <title>Add Car | My Cars System</title>

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

                <h2 class="mb-2">Add New Car</h2>

                <p class="text-muted mb-4">

                    Enter the details of the new car below.

                </p>

                <form action="add-car.php"
                      method="post">

                    <!-- CSRF protection -->

                    <input type="hidden"
                           name="csrf_token"
                           value="<?= htmlspecialchars(
                               $csrf_token,
                               ENT_QUOTES | ENT_SUBSTITUTE,
                               'UTF-8'
                           ) ?>">

                    <!-- Car name -->

                    <div class="mb-3">

                        <label for="CarName"
                               class="form-label">

                            Car Name

                        </label>

                        <input type="text"
                               id="CarName"
                               name="CarName"
                               class="form-control"
                               placeholder="Enter car name"
                               maxlength="150"
                               required>

                    </div>

                    <!-- Car description -->

                    <div class="mb-3">

                        <label for="CarDescription"
                               class="form-label">

                            Car Description

                        </label>

                        <textarea id="CarDescription"
                                  name="CarDescription"
                                  class="form-control"
                                  rows="5"
                                  maxlength="2000"
                                  placeholder="Enter car description"
                                  required></textarea>

                    </div>

                    <!-- Established date -->

                    <div class="mb-3">

                        <label for="DateEstablished"
                               class="form-label">

                            Established Date

                        </label>

                        <input type="date"
                               id="DateEstablished"
                               name="DateEstablished"
                               class="form-control"
                               required>

                    </div>

                    <!-- Car rating -->

                    <div class="mb-4">

                        <label for="CarRating"
                               class="form-label">

                            Car Rating

                        </label>

                        <input type="number"
                               id="CarRating"
                               name="CarRating"
                               class="form-control"
                               min="1"
                               max="10"
                               step="1"
                               placeholder="Enter rating from 1 to 10"
                               required>

                        <div class="form-text">

                            Rating must be between 1 and 10.

                        </div>

                    </div>

                    <!-- Form buttons -->

                    <div class="d-flex flex-wrap gap-2">

                        <button type="submit"
                                class="btn btn-success">

                            Add Car

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
