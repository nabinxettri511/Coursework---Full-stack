
<?php
require_once __DIR__ . "/auth.php";

$navLoggedIn = isLoggedIn();

$navCsrfToken = generateCsrfToken();
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">

    <div class="container">

        <a class="navbar-brand fw-bold"
           href="list-cars.php">

            My Cars System

        </a>

        <button class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#mainNavbar"
                aria-controls="mainNavbar"
                aria-expanded="false"
                aria-label="Toggle navigation">

            <span class="navbar-toggler-icon"></span>

        </button>

        <div class="collapse navbar-collapse"
             id="mainNavbar">

            <ul class="navbar-nav me-auto">

                <li class="nav-item">

                    <a class="nav-link"
                       href="list-cars.php">

                        Home

                    </a>

                </li>

                <li class="nav-item">

                    <a class="nav-link"
                       href="search-car.php">

                        Advanced Search

                    </a>

                </li>

                <!-- AJAX Features -->

                <li class="nav-item dropdown">

                    <a class="nav-link dropdown-toggle"
                       href="#"
                       role="button"
                       data-bs-toggle="dropdown"
                       aria-expanded="false">

                        AJAX Features

                    </a>

                    <ul class="dropdown-menu">

                        <li>

                            <a class="dropdown-item"
                               href="bootstrap-ajax-dropdown.html">

                                AJAX Car Search

                            </a>

                        </li>

                        <li>

                            <a class="dropdown-item"
                               href="bootstrap-ajax-modal.html">

                                AJAX Car Details

                            </a>

                        </li>

                    </ul>

                </li>

                <?php if ($navLoggedIn): ?>

                    <li class="nav-item">

                        <a class="nav-link"
                           href="add-car-form.php">

                            Add Car

                        </a>

                    </li>

                <?php endif; ?>

            </ul>

            <div class="d-flex flex-wrap
                        align-items-center gap-2">

                <?php if ($navLoggedIn): ?>

                    <span class="text-white">

                        Welcome,
                        <?= htmlspecialchars(
                            $_SESSION['username'] ?? '',
                            ENT_QUOTES | ENT_SUBSTITUTE,
                            'UTF-8'
                        ) ?>

                    </span>

                    <!-- Secure logout -->

                    <form action="logout.php"
                          method="post"
                          class="d-inline">

                        <input type="hidden"
                               name="csrf_token"
                               value="<?= htmlspecialchars(
                                   $navCsrfToken,
                                   ENT_QUOTES | ENT_SUBSTITUTE,
                                   'UTF-8'
                               ) ?>">

                        <button type="submit"
                                class="btn btn-danger btn-sm">

                            Logout

                        </button>

                    </form>

                <?php else: ?>

                    <a href="login.php"
                       class="btn btn-outline-light btn-sm">

                        Login

                    </a>

                    <a href="register.php"
                       class="btn btn-success btn-sm">

                        Register

                    </a>

                <?php endif; ?>

            </div>

        </div>

    </div>

</nav>
