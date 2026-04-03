<?php

  $mysqli = new mysqli("localhost","2440804","Uttamsingh@123","db2440804");

  if ($mysqli -> connect_errno) {
    echo "Failed to connect to MySQL: " . $mysqli -> connect_error;
    exit();
  }
?>
