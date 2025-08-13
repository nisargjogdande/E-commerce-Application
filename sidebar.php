// FILENAME: common/sidebar.php
// --- CONTENT ---
<?php
// This check is a failsafe for direct access, but should be included from a page with config.php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
$isLoggedIn = isset($_SESSION['user_id']);
?>
<!-- Sidebar Overlay -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden transition-opacity duration-300"></div>

<!-- Sidebar -->
<aside id="sidebar" class="fixed top-0 left-0 w-64 h-full bg-white shadow-lg z-40 transform -translate-x-full transition-transform duration-300 ease-in-out">
    <div class="p-4 flex justify-between items-center border-b">
        <h2 class="text-xl font-bold text-indigo-600">Menu</h2>
        <button id="close-sidebar" class="text-slate-500 hover:text-slate-800">
            <i class="fas fa-times text-xl"></i>
        </button>
    </div>
    <nav class="mt-4">
        <a href="index.php" class="flex items-center px-4 py-3 text-slate-700 hover:bg-slate-100">
            <i class="fas fa-home w-6 text-center"></i>
            <span class="ml-3">Home</span>
        </a>
        <a href="order.php" class="flex items-center px-4 py-3 text-slate-700 hover:bg-slate-100">
            <i class="fas fa-box w-6 text-center"></i>
            <span class="ml-3">My Orders</span>
        </a>
        <a href="profile.php" class="flex items-center px-4 py-3 text-slate-700 hover:bg-slate-100">
            <i class="fas fa-user-circle w-6 text-center"></i>
            <span class="ml-3">My Profile</span>
        </a>
        <hr class="my-2">
        <?php if ($isLoggedIn): ?>
            <a href="profile.php?action=logout" class="flex items-center px-4 py-3 text-red-500 hover:bg-red-50">
                <i class="fas fa-sign-out-alt w-6 text-center"></i>
                <span class="ml-3">Logout</span>
            </a>
        <?php else: ?>
            <a href="login.php" class="flex items-center px-4 py-3 text-green-600 hover:bg-green-50">
                <i class="fas fa-sign-in-alt w-6 text-center"></i>
                <span class="ml-3">Login / Sign Up</span>
            </a>
        <?php endif; ?>
    </nav>
</aside>