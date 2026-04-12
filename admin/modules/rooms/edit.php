<?php
require_once("../../includes/auth_check.php");
require_once("../../includes/db.php");

/* ===== GET ROOM ===== */
$id = $_GET['id'];

$stmt = $conn->prepare("SELECT * FROM rooms WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$room = $stmt->get_result()->fetch_assoc();

/* ===== UPDATE ===== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name     = $_POST['name'];
    $price    = $_POST['price'];
    $rating   = $_POST['rating'];
    $location = $_POST['location'];
    $features = $_POST['features'];

    // IMAGE (optional update)
    if (!empty($_FILES['image']['name'])) {

        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/hotel-booking/uploads/rooms/';

        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $imageName = time() . '.' . $ext;

        move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $imageName);

        $imagePath = "uploads/rooms/" . $imageName;

        $stmt = $conn->prepare("
            UPDATE rooms 
            SET name=?, price=?, image=?, rating=?, location=?, features=? 
            WHERE id=?
        ");
        $stmt->bind_param("sdssssi", $name, $price, $imagePath, $rating, $location, $features, $id);

    } else {

        $stmt = $conn->prepare("
            UPDATE rooms 
            SET name=?, price=?, rating=?, location=?, features=? 
            WHERE id=?
        ");
        $stmt->bind_param("sdsssi", $name, $price, $rating, $location, $features, $id);
    }

    $stmt->execute();

    header("Location: list.php");
    exit();
}
?>

<?php include("../../includes/header.php"); ?>

<style>

/* ===== HEADER ===== */
.page-header {
    margin-bottom: 25px;
}

/* ===== FORM CARD ===== */
.form-wrapper {
    display: flex;
    justify-content: center;
}

.card {
    width: 100%;
    max-width: 600px;
}

/* ===== INPUTS ===== */
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
}

input:focus {
    border-color: #7b2cbf;
    box-shadow: 0 0 0 2px rgba(123,44,191,0.1);
}

/* ===== IMAGE PREVIEW ===== */
.preview {
    margin-top: 10px;
}

.preview img {
    width: 100%;
    max-height: 200px;
    object-fit: cover;
    border-radius: 10px;
}

/* ===== BUTTON ===== */
.btn {
    width: 100%;
    padding: 14px;
    background: linear-gradient(135deg, #7b2cbf, #9d4edd);
    border: none;
    color: white;
    font-weight: 600;
    border-radius: 8px;
    cursor: pointer;
}

.btn:hover {
    transform: translateY(-2px);
}

</style>

<div class="page-header">
    <h2>Edit Room</h2>
</div>

<div class="form-wrapper">
    <div class="card">

        <form method="POST" enctype="multipart/form-data">

            <div class="form-group">
                <label>Room Name</label>
                <input type="text" name="name" value="<?= htmlspecialchars($room['name']) ?>" required>
            </div>

            <div class="form-group">
                <label>Price (₹)</label>
                <input type="number" name="price" value="<?= $room['price'] ?>" required>
            </div>

            <div class="form-group">
                <label>Change Image</label>
                <input type="file" name="image" id="imageInput">
                
                <div class="preview">
                    <img id="previewImg" src="/hotel-booking/<?= $room['image'] ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Rating</label>
                <input type="text" name="rating" value="<?= $room['rating'] ?>">
            </div>

            <div class="form-group">
                <label>Location</label>
                <input type="text" name="location" value="<?= htmlspecialchars($room['location']) ?>">
            </div>

            <div class="form-group">
                <label>Features</label>
                <input type="text" name="features" value="<?= htmlspecialchars($room['features']) ?>">
            </div>

            <button class="btn">Update Room</button>

        </form>

    </div>
</div>

<script>
// 🔥 LIVE IMAGE PREVIEW
document.getElementById("imageInput").addEventListener("change", function(e) {
    const file = e.target.files[0];
    if (file) {
        document.getElementById("previewImg").src = URL.createObjectURL(file);
    }
});
</script>

<?php include("../../includes/footer.php"); ?>