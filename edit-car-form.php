<?php
include 'db.php';

$id = $_GET['id'];

// Get the specific game info
$sql = "SELECT * FROM Cars WHERE car_id = $id";
$result = mysqli_query($mysqli, $sql);
$row = mysqli_fetch_assoc($result);
?>

<!doctype html>
<html lang="en">
<body>

<h1>Update Car</h1>

<form action="update-car.php" method="post">
  <input type="hidden" name="car_id" value="<?=$row['car_id']?>">

  <label>Car Name:</label><br>
  <input type="text" name="car_name" value="<?=$row['car_name']?>" required><br><br>
  
  <label>Description:</label><br>
  <textarea name="car_description" rows="5" cols="40" required><?=$row['car_description']?></textarea><br><br>

  <label>Rating:</label><br>
  <input type="number" name="rating" value="<?=$row['rating']?>" required><br><br>

  <label>Establish Date:</label><br>
  <input type="date" name="established_date" value="<?=$row['established_date']?>" required><br><br>

  <input class="btn" type="submit" value="Update Car">
</form>

</body>
</html>
