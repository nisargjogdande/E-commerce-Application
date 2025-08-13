<?php
// FILE: admin/index.php

// 1. Initialize the application
require_once __DIR__ . '/../common/init.php';

// 2. Perform Admin-specific security check
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// 3. Start printing the page
require_once __DIR__ . '/common/header.php';

// --- Page content starts here ---

// Fetch stats for the dashboard
$total_users = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_orders = $conn->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$total_revenue = $conn->query("SELECT SUM(total_amount) FROM orders WHERE status = 'Delivered'")->fetchColumn();
$active_products = $conn->query("SELECT COUNT(*) FROM products")->fetchColumn();
?>

<!-- Stat Cards Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
    <!-- Stat Card: Total Revenue -->
    <div class="bg-gradient-to-br from-green-500 to-green-700 text-white p-6 rounded-2xl shadow-lg transform hover:scale-105 transition duration-300">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-sm font-medium text-green-200">Total Revenue</p>
                <p class="text-3xl font-bold mt-1"><?= format_inr($total_revenue ?? 0) ?></p>
            </div>
            <i class="fas fa-rupee-sign text-4xl text-white/30"></i>
        </div>
    </div>
    
    <!-- Stat Card: Total Orders -->
    <div class="bg-gradient-to-br from-blue-500 to-blue-700 text-white p-6 rounded-2xl shadow-lg transform hover:scale-105 transition duration-300">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-sm font-medium text-blue-200">Total Orders</p>
                <p class="text-3xl font-bold mt-1"><?= $total_orders ?></p>
            </div>
            <i class="fas fa-receipt text-4xl text-white/30"></i>
        </div>
    </div>
    
    <!-- Stat Card: Total Users -->
    <div class="bg-gradient-to-br from-purple-500 to-purple-700 text-white p-6 rounded-2xl shadow-lg transform hover:scale-105 transition duration-300">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-sm font-medium text-purple-200">Total Users</p>
                <p class="text-3xl font-bold mt-1"><?= $total_users ?></p>
            </div>
            <i class="fas fa-users text-4xl text-white/30"></i>
        </div>
    </div>
    
    <!-- Stat Card: Active Products -->
    <div class="bg-gradient-to-br from-yellow-500 to-yellow-700 text-white p-6 rounded-2xl shadow-lg transform hover:scale-105 transition duration-300">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-sm font-medium text-yellow-200">Active Products</p>
                <p class="text-3xl font-bold mt-1"><?= $active_products ?></p>
            </div>
            <i class="fas fa-box-open text-4xl text-white/30"></i>
        </div>
    </div>
</div>

<!-- Quick Actions Panel -->
<div class="mt-8 bg-gray-800 p-6 rounded-2xl shadow-md border border-gray-700">
    <h2 class="text-xl font-bold mb-4 text-white">Quick Actions</h2>
    <div class="flex flex-wrap gap-4">
        <a href="product.php" class="bg-indigo-600 text-white font-bold py-2 px-5 rounded-lg hover:bg-indigo-500 transition-colors">
            <i class="fas fa-plus mr-2"></i>Add Product
        </a>
        <a href="order.php" class="bg-gray-700 text-white font-bold py-2 px-5 rounded-lg hover:bg-gray-600 transition-colors">
            <i class="fas fa-list-alt mr-2"></i>Manage Orders
        </a>
        <a href="user.php" class="bg-gray-700 text-white font-bold py-2 px-5 rounded-lg hover:bg-gray-600 transition-colors">
            <i class="fas fa-user-edit mr-2"></i>Manage Users
        </a>
    </div>
</div>

<?php 
// Use absolute path for robustness
require_once __DIR__ . '/common/bottom.php'; 
?>