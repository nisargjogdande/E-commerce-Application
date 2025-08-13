// FILENAME: admin/common/sidebar.php
// --- CONTENT ---
<?php $currentPage = basename($_SERVER['PHP_SELF']); ?>

<!-- Sidebar Overlay -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-20 hidden md:hidden"></div>

<!-- Sidebar -->
<div id="sidebar" class="fixed inset-y-0 left-0 bg-white shadow-lg w-64 transform -translate-x-full md:relative md:translate-x-0 transition-transform duration-200 ease-in-out z-30">
    <div class="flex items-center justify-center h-16 bg-white border-b">
        <h1 class="text-2xl font-bold text-indigo-600">Nisu Store</h1>
    </div>
    <nav class="mt-4">
        <a href="index.php" class="sidebar-link flex items-center py-3 px-6 text-slate-600 hover:bg-slate-50 hover:text-indigo-600 transition-colors duration-200 <?= $currentPage == 'index.php' ? 'active' : '' ?>">
            <i class="fas fa-tachometer-alt w-6"></i><span class="ml-3">Dashboard</span>
        </a>
        <a href="category.php" class="sidebar-link flex items-center py-3 px-6 text-slate-600 hover:bg-slate-50 hover:text-indigo-600 transition-colors duration-200 <?= $currentPage == 'category.php' ? 'active' : '' ?>">
            <i class="fas fa-tags w-6"></i><span class="ml-3">Categories</span>
        </a>
        <a href="product.php" class="sidebar-link flex items-center py-3 px-6 text-slate-600 hover:bg-slate-50 hover:text-indigo-600 transition-colors duration-200 <?= $currentPage == 'product.php' ? 'active' : '' ?>">
            <i class="fas fa-box-open w-6"></i><span class="ml-3">Products</span>
        </a>
        <a href="order.php" class="sidebar-link flex items-center py-3 px-6 text-slate-600 hover:bg-slate-50 hover:text-indigo-600 transition-colors duration-200 <?= $currentPage == 'order.php' || $currentPage == 'order_detail.php' ? 'active' : '' ?>">
            <i class="fas fa-shopping-cart w-6"></i><span class="ml-3">Orders</span>
        </a>
        <a href="user.php" class="sidebar-link flex items-center py-3 px-6 text-slate-600 hover:bg-slate-50 hover:text-indigo-600 transition-colors duration-200 <?= $currentPage == 'user.php' ? 'active' : '' ?>">
            <i class="fas fa-users w-6"></i><span class="ml-3">Users</span>
        </a>
        <a href="setting.php" class="sidebar-link flex items-center py-3 px-6 text-slate-600 hover:bg-slate-50 hover:text-indigo-600 transition-colors duration-200 <?= $currentPage == 'setting.php' ? 'active' : '' ?>">
            <i class="fas fa-cogs w-6"></i><span class="ml-3">Settings</span>
        </a>
        <hr class="my-4">
        <a href="login.php?action=logout" class="flex items-center py-3 px-6 text-red-500 hover:bg-red-50 hover:text-red-600 transition-colors duration-200">
            <i class="fas fa-sign-out-alt w-6"></i><span class="ml-3">Logout</span>
        </a>
    </nav>
</div>
<script>
// Mobile Sidebar Toggle
document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    const menuButton = document.getElementById('mobile-menu-button');

    const toggleSidebar = () => {
        sidebar.classList.toggle('-translate-x-full');
        overlay.classList.toggle('hidden');
    };

    if (menuButton) menuButton.addEventListener('click', toggleSidebar);
    if (overlay) overlay.addEventListener('click', toggleSidebar);
});
</script>