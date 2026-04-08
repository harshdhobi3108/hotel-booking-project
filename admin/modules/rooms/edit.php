<?php
require_once '../../includes/db.php';

$id = $_GET['id'];
$result = $conn->query("SELECT * FROM rooms WHERE id=$id");
$row = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $name = $_POST['name'];
  $price = $_POST['price'];
  $image = $_POST['image'];
  $rating = $_POST['rating'];
  $location = $_POST['location'];
  $features = $_POST['features'];

  $conn->query("UPDATE rooms SET 
    name='$name',
    price='$price',
    image='$image',
    rating='$rating',
    location='$location',
    features='$features'
    WHERE id=$id");

  header("Location: list.php");
}
?>

<h2>Edit Room</h2>

<form method="POST">
  <input type="text" name="name" value="<?= $row['name'] ?>"><br><br>
  <input type="number" name="price" value="<?= $row['price'] ?>"><br><br>
  <input type="text" name="image" value="<?= $row['image'] ?>"><br><br>
  <input type="text" name="rating" value="<?= $row['rating'] ?>"><br><br>
  <input type="text" name="location" value="<?= $row['location'] ?>"><br><br>
  <input type="text" name="features" value="<?= $row['features'] ?>"><br><br>

  <button type="submit">Update</button>
</form>