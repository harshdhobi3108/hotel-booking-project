<?php
require_once("../../includes/auth_check.php");
require_once("../../includes/db.php");
include("../../includes/sidebar.php");

$result = $conn->query("SELECT * FROM rooms");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Rooms</title>

    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI';
            background: #f4f6f9;
        }

        /* MAIN CONTENT */
        .main-content {
            margin-left: 240px;
            padding: 30px;
        }

        h2 {
            margin-bottom: 20px;
        }

        /* TOP BAR */
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        /* BUTTONS */
        .btn {
            padding: 8px 14px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            color: white;
            font-size: 13px;
        }

        .add { background: #2ecc71; }
        .edit { background: #3498db; }
        .delete { background: #e74c3c; }

        .btn:hover {
            opacity: 0.9;
        }

        /* TABLE */
        table {
            width: 100%;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            border-collapse: collapse;
            box-shadow: 0 6px 18px rgba(0,0,0,0.08);
        }

        th, td {
            padding: 14px;
            border-bottom: 1px solid #eee;
            text-align: center;
        }

        th {
            background: #f9fafb;
            font-weight: 600;
        }

        tr:hover {
            background: #f4f6f9;
        }

        /* STATUS BADGE */
        .status {
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 12px;
        }

        .available {
            background: #e6fffa;
            color: #0f766e;
        }

        .booked {
            background: #fee2e2;
            color: #b91c1c;
        }

    </style>
</head>

<body>

<div class="main-content">

    <div class="top-bar">
        <h2>Manage Rooms</h2>
        <a href="add.php" class="btn add">+ Add Room</a>
    </div>

    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Price</th>
            <th>Status</th>
            <th>Action</th>
        </tr>

        <?php while($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= $row['name'] ?></td>
            <td>₹<?= $row['price'] ?></td>
            <td>
                <span class="status <?= $row['status'] ?>">
                    <?= ucfirst($row['status']) ?>
                </span>
            </td>
            <td>
                <a href="edit.php?id=<?= $row['id'] ?>" class="btn edit">Edit</a>
                <a href="delete.php?id=<?= $row['id'] ?>" 
                   class="btn delete"
                   onclick="return confirm('Delete this room?')">
                   Delete
                </a>
            </td>
        </tr>
        <?php } ?>

    </table>

</div>

</body>
</html>