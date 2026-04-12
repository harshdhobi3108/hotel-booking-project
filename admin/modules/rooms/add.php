<?php
require_once '../../includes/db.php';
require_once '../../includes/auth_check.php';

// ================= HANDLE FORM =================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name     = trim($_POST['name']);
    $price    = $_POST['price'];
    $rating   = $_POST['rating'];
    $location = $_POST['location'];
    $features = $_POST['features'];

    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== 0) {
        die("Image upload is required.");
    }

    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/hotel-booking/uploads/rooms/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $originalName = $_FILES['image']['name'];
    $tmpName      = $_FILES['image']['tmp_name'];
    $fileSize     = $_FILES['image']['size'];

    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($ext, $allowed)) {
        die("Only JPG, JPEG, PNG, WEBP files allowed.");
    }

    if ($fileSize > 5 * 1024 * 1024) {
        die("File size must be less than 5MB.");
    }

    $baseName = strtolower(pathinfo($originalName, PATHINFO_FILENAME));
    $baseName = preg_replace('/[^a-z0-9-]/', '-', $baseName);
    $baseName = preg_replace('/-+/', '-', $baseName);

    $imageName = $baseName . '-' . time() . '.' . $ext;
    $destination = $uploadDir . $imageName;

    if (!move_uploaded_file($tmpName, $destination)) {
        die("Failed to upload image.");
    }

    $imagePath = "uploads/rooms/" . $imageName;

    $stmt = $conn->prepare("
        INSERT INTO rooms 
        (name, price, image, rating, location, features, status) 
        VALUES (?, ?, ?, ?, ?, ?, 'available')
    ");

    $stmt->bind_param("sdssss", $name, $price, $imagePath, $rating, $location, $features);
    $stmt->execute();

    header("Location: list.php");
    exit();
}
?>

<?php include '../../includes/header.php'; ?>

<style>

.page-header {
    margin-bottom: 25px;
}

.page-header h1 {
    font-size: 26px;
    color: #333;
}

.form-wrapper {
    display: flex;
    justify-content: center;
}

.card {
    width: 100%;
    max-width: 600px;
}

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
}

input:focus {
    border-color: #6c5ce7;
    box-shadow: 0 0 0 2px rgba(108,92,231,0.1);
}

/* 🔥 IMAGE PREVIEW */
.preview {
    margin-top: 10px;
    display: none;
}

.preview img {
    width: 100%;
    max-height: 220px;
    object-fit: cover;
    border-radius: 10px;
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
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(108,92,231,0.2);
}

</style>

<div class="page-header">
    <h1>Add New Room</h1>
</div>

<div class="form-wrapper">
    <div class="card">

        <form method="POST" enctype="multipart/form-data">

            <div class="form-group">
                <label>Room Name</label>
                <input type="text" name="name" required>
            </div>

            <div class="form-group">
                <label>Price (₹)</label>
                <input type="number" name="price" required>
            </div>

            <div class="form-group">
                <label>Upload Image</label>
                <input type="file" name="image" id="imageInput" accept="image/*" required>

                <!-- 🔥 PREVIEW -->
                <div class="preview" id="previewBox">
                    <img id="previewImg" src="">
                </div>
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

<script>
// 🔥 IMAGE PREVIEW LOGIC
const input = document.getElementById("imageInput");
const previewBox = document.getElementById("previewBox");
const previewImg = document.getElementById("previewImg");

input.addEventListener("change", function() {
    const file = this.files[0];

    if (file) {
        previewImg.src = URL.createObjectURL(file);
        previewBox.style.display = "block";
    }
});
</script>

<?php include '../../includes/footer.php'; ?>