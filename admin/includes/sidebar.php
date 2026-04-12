<?php
function isActive($keyword) {
    return strpos($_SERVER['PHP_SELF'], $keyword) !== false ? 'active' : '';
}
?>

<div class="sidebar">

    <div>
        <h2 class="logo">HotelLux</h2>

        <div class="nav-menu">

            <a href="/hotel-booking/admin/dashboard.php"
               class="nav-link <?= isActive('dashboard.php') ?>">
               🏠 Dashboard
            </a>

            <a href="/hotel-booking/admin/modules/rooms/list.php"
               class="nav-link <?= isActive('/rooms/') ?>">
               🏨 Rooms
            </a>

            <a href="/hotel-booking/admin/modules/bookings/list.php"
               class="nav-link <?= isActive('/bookings/') ?>">
               📅 Bookings
            </a>

            <a href="/hotel-booking/admin/modules/users/list.php"
               class="nav-link <?= isActive('/users/') ?>">
               👤 Users
            </a>

        </div>
    </div>

    <!-- 🔥 LOGOUT BUTTON -->
    <a href="/hotel-booking/admin/logout.php"
       class="nav-link logout-btn"
       onclick="return confirm('Are you sure you want to logout?')">
       🚪 Logout
    </a>

</div>

<style>
.sidebar {
    width: 240px;
    height: 100vh;
    background: linear-gradient(180deg, #1e293b, #0f172a);
    color: white;
    position: fixed;
    left: 0;
    top: 0;
    padding: 25px 15px;

    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

/* LOGO */
.logo {
    font-size: 22px;
    font-weight: 700;
    margin-bottom: 30px;
    text-align: center;
}

/* MENU */
.nav-menu {
    display: flex;
    flex-direction: column;
}

/* LINKS */
.nav-link {
    padding: 12px;
    margin: 6px 0;
    border-radius: 10px;
    text-decoration: none;
    color: #cbd5e1;
    transition: 0.3s;
}

.nav-link:hover {
    background: rgba(255,255,255,0.08);
    color: #fff;
    transform: translateX(5px);
}

.nav-link.active {
    background: linear-gradient(135deg, #6c2bd9, #9333ea);
    color: white;
    font-weight: 600;
}

/* LOGOUT */
.logout-btn {
    margin-top: auto;
    background: rgba(255,255,255,0.1);
    color: #ff6b6b;
    text-align: center;
}

.logout-btn:hover {
    background: rgba(255, 107, 107, 0.2);
    color: #fff;
}
</style>