
<?php
require_once "auth.php";

if (isLoggedIn()) {

    header("Location: list-cars.php");

} else {

    header("Location: login.php");

}

exit;
