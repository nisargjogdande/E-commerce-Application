<?php
// FILE: admin/order.php

// 1. Initialize the application
require_once __DIR__ . '/../common/init.php';

// 2. Security Check
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// 3. Page Logic & Presentation
require_once __DIR__ . '/common/header.php';

// Fetch all orders with user details
$orders = $conn->query("
    SELECT o.*, u.name as user_name 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC
")->fetchAll();

function getStatusBadgeClass($status) {
    switch ($status) {
        case 'Delivered':
            return 'bg-green-200 text-green-800';
        case 'Dispatched':
            return 'bg-blue-200 text-blue-800';
        case 'Cancelled':
            return 'bg-red-200 text-red-800';
        case 'Placed':
        default:
            return 'bg-yellow-200 text-yellow-800';
    }
}
?>

<h2 class="text-2xl font-bold mb-6 text-white">Manage Orders</h2>

<!-- Mobile Card Layout -->
<div class="space-y-4 lg:hidden">
    <?php foreach ($orders as $order): ?>
    <div class="bg-gray-800 rounded-lg shadow-md p-4 border border-gray-700">
        <div class="flex justify-between items-start">
            <div>
                <h3 class="font-bold text-white">Order #<?= $order['id'] ?></h3>
                <p class="text-sm text-gray-400">by <?= htmlspecialchars($order['user_name']) ?></p>
            </div>
            <span class="px-2 py-1 text-xs font-semibold rounded-full <?= getStatusBadgeClass($order['status']) ?>">
                <?= htmlspecialchars($order['status']) ?>
            </span>
        </div>
        <div class="mt-4 pt-3 border-t border-gray-700 flex justify-between items-center">
            <div>
                <p class="text-gray-300"><?= date('d M Y, H:i', strtotime($order['created_at'])) ?></p>
                <p class="text-lg font-semibold text-indigo-400 mt-1"><?= format_inr($order['total_amount']) ?></p>
            </div>
            <a href="order_detail.php?id=<?= $order['id'] ?>" class="bg-indigo-600 text-white font-bold py-2 px-4 rounded-md hover:bg-indigo-500">
                View
            </a>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Desktop Table Layout -->
<div class="hidden lg:block bg-gray-800 p-4 rounded-lg shadow-md overflow-x-auto border border-gray-700">
    <table class="w-full text-left">
        <thead>
            <tr class="border-b border-gray-700">
                <th class="p-3 text-gray-300">Order ID</th>
                <th class="p-3 text-gray-300">User</th>
                <th class="p-3 text-gray-300">Amount</th>
                <th class="p-3 text-gray-300">Status</th>
                <th class="p-3 text-gray-300">Date</th>
                <th class="p-3 text-right text-gray-300">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
                <tr class="border-b border-gray-700 hover:bg-gray-700/50">
                    <td class="p-3 font-mono text-white">#<?= $order['id'] ?></td>
                    <td class="p-3 text-white"><?= htmlspecialchars($order['user_name']) ?></td>
                    <td class="p-3 font-semibold text-indigo-400"><?= format_inr($order['total_amount']) ?></td>
                    <td class="p-3">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full <?= getStatusBadgeClass($order['status']) ?>">
                            <?= htmlspecialchars($order['status']) ?>
                        </span>
                    </td>
                    <td class="p-3 text-sm text-gray-400"><?= date('d M Y', strtotime($order['created_at'])) ?></td>
                    <td class="p-3 text-right">
                        <a href="order_detail.php?id=<?= $order['id'] ?>" class="text-indigo-400 hover:underline">View Details</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/common/bottom.php'; ?>