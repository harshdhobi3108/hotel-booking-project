<?php
function isActive($keyword) {
    return strpos($_SERVER['PHP_SELF'], $keyword) !== false ? 'active' : '';
}
?>

<div class="sidebar">

    <!-- LOGO -->
    <div class="sidebar-top">
        <h2 class="logo">HotelLux</h2>

        <div class="nav-menu">

            <a href="/hotel-booking/admin/dashboard.php"
               class="nav-link <?= isActive('dashboard.php') ?>">
               <span class="icon">🏠</span>
               <span>Dashboard</span>
            </a>

            <a href="/hotel-booking/admin/modules/rooms/list.php"
               class="nav-link <?= isActive('/rooms/') ?>">
               <span class="icon">🏨</span>
               <span>Rooms</span>
            </a>

            <a href="/hotel-booking/admin/modules/bookings/list.php"
               class="nav-link <?= isActive('/bookings/') ?>">
               <span class="icon">📅</span>
               <span>Bookings</span>
            </a>

            <a href="/hotel-booking/admin/modules/users/list.php"
               class="nav-link <?= isActive('/users/') ?>">
               <span class="icon">👤</span>
               <span>Users</span>
            </a>

        </div>
    </div>

    <!-- LOGOUT -->
    <div class="sidebar-bottom">
        <a href="/hotel-booking/admin/logout.php"
           class="nav-link logout-btn"
           onclick="return confirm('Are you sure you want to logout?')">
           <span class="icon">🚪</span>
           <span>Logout</span>
        </a>
    </div>

</div>

<style>

/* ================= SIDEBAR ================= */
.sidebar {
    width: 250px;
    height: 100vh;
    background: linear-gradient(180deg, #0f172a, #020617);
    color: white;
    position: fixed;
    left: 0;
    top: 0;
    padding: 20px 15px;

    display: flex;
    flex-direction: column;
    justify-content: space-between;

    transition: 0.3s;
    z-index: 1000;
}

/* ================= LOGO ================= */
.logo {
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 25px;
    text-align: center;
    letter-spacing: 1px;
}

/* ================= MENU ================= */
.nav-menu {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

/* ================= LINK ================= */
.nav-link {
    display: flex;
    align-items: center;
    gap: 12px;

    padding: 12px 14px;
    border-radius: 12px;
    text-decoration: none;
    color: #94a3b8;
    font-size: 14px;
    transition: 0.25s;
}

/* ICON */
.icon {
    font-size: 16px;
}

/* HOVER */
.nav-link:hover {
    background: rgba(255,255,255,0.05);
    color: #fff;
    transform: translateX(4px);
}

/* ACTIVE */
.nav-link.active {
    background: linear-gradient(135deg, #7c3aed, #9333ea);
    color: #fff;
    font-weight: 600;
    box-shadow: 0 6px 15px rgba(124,58,237,0.3);
}

/* ================= LOGOUT ================= */
.sidebar-bottom {
    margin-top: 20px;
}

.logout-btn {
    background: rgba(255,255,255,0.05);
    color: #f87171;
}

.logout-btn:hover {
    background: rgba(248,113,113,0.2);
    color: #fff;
}

/* ===== MOBILE SIDEBAR FIX ===== */
@media (max-width: 768px) {

    .sidebar {
        transform: translateX(-100%) !important;
    }

    .sidebar.active {
        transform: translateX(0) !important;
    }

}

</style>