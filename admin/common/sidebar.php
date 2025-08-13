<?php
// FILE: admin/common/sidebar.php
?>
<!-- Mobile Overlay -->
<div id="admin-sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-60 z-40 hidden lg:hidden"></div>

<!-- Sidebar -->
<aside id="admin-sidebar" class="bg-gray-800 text-gray-100 w-64 space-y-2 py-4 fixed inset-y-0 left-0 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out z-50 border-r border-gray-700">
    <a href="index.php" class="flex items-center space-x-2 px-4 py-2">
        <i class="fas fa-rocket text-2xl text-indigo-400"></i>
        <span class="text-2xl font-extrabold text-white">Nisu<span class="text-indigo-400">Store</span></span>
    </a>
    
    <nav class="px-2 mt-4 space-y-1">
        <a href="index.php" class="flex items-center space-x-3 p-3 rounded-md hover:bg-gray-700 text-gray-300 hover:text-white">
            <i class="fas fa-tachometer-alt w-6"></i><span>Dashboard</span>
        </a>
        <a href="category.php" class="flex items-center space-x-3 p-3 rounded-md hover:bg-gray-700 text-gray-300 hover:text-white">
            <i class="fas fa-tags w-6"></i><span>Categories</span>
        </a>
        <a href="product.php" class="flex items-center space-x-3 p-3 rounded-md hover:bg-gray-700 text-gray-300 hover:text-white">
            <i class="fas fa-box-open w-6"></i><span>Products</span>
        </a>
        <a href="order.php" class="flex items-center space-x-3 p-3 rounded-md hover:bg-gray-700 text-gray-300 hover:text-white">
            <i class="fas fa-receipt w-6"></i><span>Orders</span>
        </a>
        <a href="user.php" class="flex items-center space-x-3 p-3 rounded-md hover:bg-gray-700 text-gray-300 hover:text-white">
            <i class="fas fa-users w-6"></i><span>Users</span>
        </a>
        <a href="setting.php" class="flex items-center space-x-3 p-3 rounded-md hover:bg-gray-700 text-gray-300 hover:text-white">
            <i class="fas fa-cog w-6"></i><span>Settings</span>
        </a>
    </nav>
 </aside>