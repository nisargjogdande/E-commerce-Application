<?php
// FILE: common/sidebar.php
?>
<!-- Sidebar -->
<div id="sidebar-overlay" class="hidden fixed inset-0 bg-black bg-opacity-60 z-40 transition-opacity duration-300 lg:hidden"></div>
<aside id="sidebar" class="fixed top-0 left-0 h-full w-64 bg-gray-800 shadow-xl z-50 transform -translate-x-full transition-transform duration-300 ease-in-out border-r border-gray-700">
    <div class="p-5 border-b border-gray-700 flex justify-between items-center">
        <h2 class="text-xl font-bold text-white">Menu</h2>
        <button id="close-sidebar-btn" class="text-gray-400 hover:text-white lg:hidden"><i class="fas fa-times"></i></button>
    </div>
    <nav class="mt-4">
        <a href="index.php" class="flex items-center px-5 py-3 text-gray-300 hover:bg-gray-700 hover:text-white rounded-md mx-2">
            <i class="fas fa-home w-6 text-indigo-400"></i>
            <span class="ml-3">Home</span>
        </a>
        <a href="product.php" class="flex items-center px-5 py-3 text-gray-300 hover:bg-gray-700 hover:text-white rounded-md mx-2">
            <i class="fas fa-box-open w-6 text-indigo-400"></i>
            <span class="ml-3">All Products</span>
        </a>
        <a href="order.php" class="flex items-center px-5 py-3 text-gray-300 hover:bg-gray-700 hover:text-white rounded-md mx-2">
            <i class="fas fa-receipt w-6 text-indigo-400"></i>
            <span class="ml-3">My Orders</span>
        </a>
        <a href="profile.php" class="flex items-center px-5 py-3 text-gray-300 hover:bg-gray-700 hover:text-white rounded-md mx-2">
            <i class="fas fa-user-circle w-6 text-indigo-400"></i>
            <span class="ml-3">Profile</span>
        </a>
        <div class="border-t border-gray-700 my-4"></div>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="login.php?action=logout" class="flex items-center px-5 py-3 text-red-400 hover:bg-red-900/50 hover:text-red-300 rounded-md mx-2">
                <i class="fas fa-sign-out-alt w-6"></i>
                <span class="ml-3">Logout</span>
            </a>
        <?php else: ?>
             <a href="login.php" class="flex items-center px-5 py-3 text-green-400 hover:bg-green-900/50 hover:text-green-300 rounded-md mx-2">
                <i class="fas fa-sign-in-alt w-6"></i>
                <span class="ml-3">Login</span>
            </a>
        <?php endif; ?>
    </nav>
</aside>