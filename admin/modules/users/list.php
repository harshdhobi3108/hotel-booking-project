<?php
require_once("../../includes/auth_check.php");
require_once("../../includes/db.php");

$result = $conn->query("SELECT * FROM users");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Users</title>

    <style>
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: #f4f6f9;
        }

        /* MAIN */
        .main {
            margin-left: 240px;
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

        /* CENTER WRAPPER */
        .center-box {
            display: flex;
            justify-content: center;
        }

        /* CARD */
        .card {
            width: 100%;
            max-width: 900px; /* CONTROL WIDTH HERE */
            background: #fff;
            padding: 25px;
            border-radius: 14px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }

        /* TABLE */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #f1f3f6;
        }

        th, td {
            padding: 14px;
            text-align: left;
        }

        th {
            font-size: 14px;
            color: #555;
        }

        td {
            font-size: 14px;
            color: #333;
        }

        tr {
            border-bottom: 1px solid #eee;
        }

        tr:hover {
            background: #f9f9ff;
        }

        /* DELETE BUTTON */
        .btn-delete {
            padding: 6px 14px;
            background: #ff4757;
            color: white;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
            transition: 0.3s;
        }

        .btn-delete:hover {
            background: #e84118;
        }

    </style>
</head>

<body>

<?php include("../../includes/sidebar.php"); ?>

<div class="main">

    <div class="page-header">
        <h1>Users</h1>
    </div>

    <div class="center-box">
        <div class="card">

            <table>
                <thead>
                    <tr>
                        <th style="width:80px;">ID</th>
                        <th style="width:200px;">Name</th>
                        <th>Email</th>
                        <th style="width:120px;">Action</th>
                    </tr>
                </thead>

                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
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
                </tbody>
            </table>

        </div>
    </div>

</div>

</body>
</html>