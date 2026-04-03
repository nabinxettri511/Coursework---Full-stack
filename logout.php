<?php
session_start();
session_unset();
session_destroy();

echo "<script>
    sessionStorage.removeItem('tabLoggedIn');
    window.location.href = 'login-form.php';
</script>";
exit;
