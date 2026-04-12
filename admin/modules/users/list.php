<?php
require_once("../../includes/auth_check.php");
require_once("../../includes/db.php");

/* ===== SEARCH ===== */
$search = $_GET['search'] ?? '';

$where = "";
if ($search) {
    $searchSafe = $conn->real_escape_string($search);
    $where = "WHERE name LIKE '%$searchSafe%' OR email LIKE '%$searchSafe%'";
}

/* ===== QUERY ===== */
$result = $conn->query("SELECT * FROM users $where ORDER BY id DESC");
?>

<?php include("../../includes/header.php"); ?>

<style>

/* ===== TOP BAR ===== */
.top-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

/* ===== SEARCH ===== */
.search-box input {
    padding: 8px 12px;
    border-radius: 8px;
    border: 1px solid #ddd;
    outline: none;
    width: 220px;
}

/* ===== CARD ===== */
.card {
    background: #fff;
    border-radius: 14px;
    padding: 20px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.05);
}

/* ===== TABLE ===== */
.table {
    width: 100%;
    border-collapse: collapse;
}

.table th {
    text-align: left;
    padding: 14px;
    background: #f9fafb;
    font-size: 14px;
}

.table td {
    padding: 14px;
    border-top: 1px solid #eee;
}

/* Hover */
.table tr:hover {
    background: #f7f9fc;
}

/* ===== USER AVATAR ===== */
.user {
    display: flex;
    align-items: center;
    gap: 10px;
}

.avatar {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    background: linear-gradient(135deg, #7b2cbf, #9d4edd);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
}

/* ===== BUTTON ===== */
.btn-delete {
    padding: 6px 12px;
    background: #e74c3c;
    color: white;
    border-radius: 6px;
    text-decoration: none;
    font-size: 13px;
}

/* ===== RESPONSIVE ===== */
@media(max-width: 768px) {
    .table th, .table td {
        font-size: 12px;
        padding: 10px;
    }
}

</style>

<!-- ===== HEADER ===== -->
<div class="top-bar">
    <h2>Users</h2>

    <div class="search-box">
        <form method="GET">
            <input 
                type="text" 
                name="search" 
                placeholder="Search users..." 
                value="<?= htmlspecialchars($search) ?>"
                onkeyup="this.form.submit()"
            >
        </form>
    </div>
</div>

<!-- ===== TABLE ===== -->
<div class="card">

    <table class="table">
        <tr>
            <th>ID</th>
            <th>User</th>
            <th>Email</th>
            <th>Action</th>
        </tr>

        <?php while($row = $result->fetch_assoc()): ?>
        <tr>

            <td><?= $row['id'] ?></td>

            <td>
                <div class="user">
                    <div class="avatar">
                        <?= strtoupper(substr($row['name'], 0, 1)) ?>
                    </div>

                    <div>
                        <?= htmlspecialchars($row['name']) ?>
                    </div>
                </div>
            </td>

            <td><?= htmlspecialchars($row['email']) ?></td>

            <td>
                <a class="btn-delete"
                   href="delete.php?id=<?= $row['id'] ?>"
                   onclick="return confirm('Delete this user?')">
                   Delete
                </a>
            </td>

        </tr>
        <?php endwhile; ?>

    </table>

</div>

<?php include("../../includes/footer.php"); ?>