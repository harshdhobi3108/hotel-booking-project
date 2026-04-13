<?php
require_once("../../includes/auth_check.php");
require_once("../../includes/db.php");

$result = $conn->query("SELECT * FROM users ORDER BY id DESC");
?>

<?php include("../../includes/header.php"); ?>

<style>

/* SAME YOUR DESIGN (unchanged) */
.top-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.search-box input {
    padding: 10px 14px;
    border-radius: 10px;
    border: 1px solid #ddd;
    outline: none;
    width: 240px;
}

.card {
    background: #fff;
    border-radius: 14px;
    padding: 20px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.05);
}

.table {
    width: 100%;
    border-collapse: collapse;
}

.table th {
    text-align: left;
    padding: 14px;
    background: #f9fafb;
}

.table td {
    padding: 14px;
    border-top: 1px solid #eee;
}

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
}

.btn-delete {
    padding: 6px 12px;
    background: #e74c3c;
    color: white;
    border-radius: 6px;
    text-decoration: none;
}

.empty {
    text-align: center;
    padding: 20px;
    color: #888;
}

/* ================== ✅ RESPONSIVE ONLY ADDED ================== */

/* TABLE SCROLL */
.table-wrapper {
    width: 100%;
    overflow-x: auto;
}

/* MOBILE */
@media (max-width: 768px) {

    /* TOP BAR STACK */
    .top-bar {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }

    /* SEARCH FULL WIDTH */
    .search-box input {
        width: 100%;
    }

    /* PREVENT TABLE SHRINK */
    .table {
        min-width: 600px;
    }

    /* BUTTON FULL WIDTH */
    .btn-delete {
        display: block;
        width: 100%;
        text-align: center;
    }
}

/* EXTRA SMALL */
@media (max-width: 480px) {
    .table {
        min-width: 500px;
    }
}

</style>

<div class="top-bar">
    <h2>Users</h2>

    <div class="search-box">
        <input 
            type="text" 
            id="searchInput"
            placeholder="Search users..."
        >
    </div>
</div>

<div class="card">

    <!-- ✅ WRAPPER ADDED -->
    <div class="table-wrapper">

    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Email</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody id="userTable">

            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>

                <td>
                    <div class="user">
                        <div class="avatar">
                            <?= strtoupper(substr($row['name'], 0, 1)) ?>
                        </div>
                        <?= htmlspecialchars($row['name']) ?>
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

        </tbody>

    </table>

    </div>

</div>

<script>
const input = document.getElementById("searchInput");
const table = document.getElementById("userTable");

let timer;

input.addEventListener("keyup", () => {
    clearTimeout(timer);

    timer = setTimeout(() => {
        const query = input.value;

        fetch(`search.php?search=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(data => {

                table.innerHTML = "";

                if (data.length === 0) {
                    table.innerHTML = `
                        <tr>
                            <td colspan="4" class="empty">No users found</td>
                        </tr>
                    `;
                    return;
                }

                data.forEach(user => {
                    table.innerHTML += `
                        <tr>
                            <td>${user.id}</td>

                            <td>
                                <div class="user">
                                    <div class="avatar">
                                        ${user.name.charAt(0).toUpperCase()}
                                    </div>
                                    ${user.name}
                                </div>
                            </td>

                            <td>${user.email}</td>

                            <td>
                                <a class="btn-delete"
                                   href="delete.php?id=${user.id}"
                                   onclick="return confirm('Delete this user?')">
                                   Delete
                                </a>
                            </td>
                        </tr>
                    `;
                });

            });

    }, 300);
});
</script>

<?php include("../../includes/footer.php"); ?>