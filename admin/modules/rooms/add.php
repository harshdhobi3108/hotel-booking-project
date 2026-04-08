<?php
require_once '../../includes/db.php';
require_once '../../includes/auth_check.php';
?>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $name = $_POST['name'];
  $price = $_POST['price'];
  $image = $_POST['image'];
  $rating = $_POST['rating'];
  $location = $_POST['location'];
  $features = $_POST['features'];

  // Secure insert
  $stmt = $conn->prepare("INSERT INTO rooms 
    (name, price, image, rating, location, features, status) 
    VALUES (?, ?, ?, ?, ?, ?, 'available')");

  $stmt->bind_param("sdssss", $name, $price, $image, $rating, $location, $features);
  $stmt->execute();

  header("Location: list.php");
  exit();
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Add Room</title>

  <style>
    body {
      margin: 0;
      font-family: 'Poppins', sans-serif;
      background: #f4f6f9;
    }

    /* LAYOUT */
    .container {
      display: flex;
    }

    /* MAIN */
    .main {
      flex: 1;
      padding: 40px;
    }

    /* HEADER */
    .page-header {
      margin-bottom: 25px;
    }

    .page-header h1 {
      margin: 0;
      font-size: 26px;
      color: #333;
    }

    /* CENTER */
    .form-wrapper {
      display: flex;
      justify-content: center;
    }

    /* CARD */
    .card {
      width: 100%;
      max-width: 600px;
      background: #fff;
      padding: 30px;
      border-radius: 14px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    }

    /* FORM */
    .form-group {
      margin-bottom: 18px;
    }

    label {
      font-size: 14px;
      color: #555;
      display: block;
      margin-bottom: 6px;
    }

    input {
      width: 100%;
      padding: 12px;
      border: 1px solid #ddd;
      border-radius: 8px;
      outline: none;
      font-size: 14px;
      transition: 0.3s;
    }

    input:focus {
      border-color: #6c5ce7;
      box-shadow: 0 0 0 2px rgba(108,92,231,0.1);
    }

    /* BUTTON */
    .btn {
      width: 100%;
      padding: 14px;
      background: linear-gradient(135deg, #6c5ce7, #5a4bdc);
      border: none;
      color: white;
      font-weight: 600;
      border-radius: 8px;
      cursor: pointer;
      transition: 0.3s;
    }

    .btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(108,92,231,0.2);
    }

  </style>
</head>

<body>

<div class="container">

  <!-- SIDEBAR -->
  <?php include '../../includes/sidebar.php'; ?>

  <!-- MAIN CONTENT -->
  <div class="main">

    <div class="page-header">
      <h1>Add New Room</h1>
    </div>

    <div class="form-wrapper">
      <div class="card">

        <form method="POST">

          <div class="form-group">
            <label>Room Name</label>
            <input type="text" name="name" required>
          </div>

          <div class="form-group">
            <label>Price (₹)</label>
            <input type="number" name="price" required>
          </div>

          <div class="form-group">
            <label>Image Filename</label>
            <input type="text" name="image" placeholder="room1.jpg" required>
          </div>

          <div class="form-group">
            <label>Rating</label>
            <input type="text" name="rating">
          </div>

          <div class="form-group">
            <label>Location</label>
            <input type="text" name="location">
          </div>

          <div class="form-group">
            <label>Features</label>
            <input type="text" name="features">
          </div>

          <button type="submit" class="btn">Add Room</button>

        </form>

      </div>
    </div>

  </div>

</div>

</body>
</html>