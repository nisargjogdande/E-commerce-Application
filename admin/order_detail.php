<?php
// FILE: admin/order_detail.php

// 1. Initialize - THIS IS THE CRITICAL FIX
require_once __DIR__ . '/../common/init.php';

// --- Page Load & AJAX Logic ---
$order_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// 2. Security Check
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Handle AJAX for status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    header('Content-Type: application/json');
    $status = $_POST['status'];
    if ($order_id && !empty($status)) {
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        if ($stmt->execute([$status, $order_id])) {
            echo json_encode(['status' => 'success', 'message' => 'Order status updated!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update status.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid data.']);
    }
    exit();
}

// 3. Presentation
require_once __DIR__ . '/common/header.php';

// --- Data Fetching for Page Display ---
$order = null;
$items = [];
if ($order_id) {
    // Fetch order details
    $stmt = $conn->prepare("SELECT o.*, u.name, u.email, u.phone FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($order) {
        // Fetch order items
        $item_stmt = $conn->prepare("SELECT oi.*, p.name as product_name, p.image as product_image FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
        $item_stmt->execute([$order_id]);
        $items = $item_stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

if (!$order) {
    echo "<p class='text-red-400 p-4'>Order not found.</p>";
    require_once __DIR__ . '/common/bottom.php';
    exit();
}
?>
<div class="mb-4">
    <a href="order.php" class="text-indigo-400 hover:underline">
        <i class="fas fa-arrow-left mr-2"></i>Back to All Orders
    </a>
</div>
<h2 class="text-2xl font-bold mb-6 text-white">Order Details #<?= $order_id ?></h2>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <!-- Order Items Card -->
        <div class="bg-gray-800 p-6 rounded-2xl shadow-md border border-gray-700">
            <h3 class="text-xl font-semibold mb-4 text-white">Items Ordered</h3>
            <div class="space-y-4">
                <?php foreach($items as $item): ?>
                <div class="flex items-center border-b border-gray-700 pb-4 last:border-b-0 last:pb-0">
                    <img src="../uploads/products/<?= htmlspecialchars($item['product_image']) ?>" class="w-16 h-16 rounded-md object-cover mr-4">
                    <div class="flex-1">
                        <p class="font-semibold text-white"><?= htmlspecialchars($item['product_name']) ?></p>
                        <p class="text-sm text-gray-400">Qty: <?= $item['quantity'] ?> @ <?= format_inr($item['price']) ?></p>
                    </div>
                    <p class="font-bold text-white"><?= format_inr($item['quantity'] * $item['price']) ?></p>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="text-right mt-4 pt-4 border-t border-gray-700">
                <p class="text-gray-400">Grand Total:</p>
                <p class="text-2xl font-bold text-indigo-400"><?= format_inr($order['total_amount']) ?></p>
            </div>
        </div>
    </div>
    
    <div class="space-y-6">
        <!-- Status Update Card -->
        <div class="bg-gray-800 p-6 rounded-2xl shadow-md border border-gray-700">
            <h3 class="text-xl font-semibold mb-4 text-white">Update Status</h3>
            <div id="status-message" class="mb-2"></div>
            <select id="status-select" class="w-full p-3 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-indigo-500">
                <option value="Placed" <?= $order['status'] == 'Placed' ? 'selected' : '' ?>>Placed</option>
                <option value="Dispatched" <?= $order['status'] == 'Dispatched' ? 'selected' : '' ?>>Dispatched</option>
                <option value="Delivered" <?= $order['status'] == 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                <option value="Cancelled" <?= $order['status'] == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
            </select>
            <button id="update-status-btn" class="mt-4 w-full bg-indigo-600 text-white font-bold py-2 rounded-lg hover:bg-indigo-500">Update Status</button>
        </div>
        
        <!-- Customer Info Card -->
        <div class="bg-gray-800 p-6 rounded-2xl shadow-md border border-gray-700">
            <h3 class="text-xl font-semibold mb-4 text-white">Customer Information</h3>
            <p class="text-gray-300"><strong class="text-gray-400 font-medium">Name:</strong> <?= htmlspecialchars($order['name']) ?></p>
            <p class="text-gray-300"><strong class="text-gray-400 font-medium">Email:</strong> <?= htmlspecialchars($order['email']) ?></p>
            <p class="text-gray-300"><strong class="text-gray-400 font-medium">Phone:</strong> <?= htmlspecialchars($order['phone']) ?></p>
            <p class="mt-2 text-gray-300"><strong class="text-gray-400 font-medium">Shipping Address:</strong><br><?= nl2br(htmlspecialchars($order['shipping_address'])) ?></p>
        </div>
    </div>
</div>

<script>
document.getElementById('update-status-btn').addEventListener('click', async () => {
    const status = document.getElementById('status-select').value;
    const formData = new FormData();
    formData.append('action', 'update_status');
    formData.append('status', status);

    const result = await adminAjaxRequest('order_detail.php?id=<?= $order_id ?>', formData);
    const msgDiv = document.getElementById('status-message');
    
    const color = result.status === 'success' ? 'text-green-400' : 'text-red-400';
    msgDiv.innerHTML = `<p class="${color} font-medium">${result.message}</p>`;

    if (result.status === 'success') {
        setTimeout(() => location.reload(), 1500);
    }
});
</script>

<?php require_once __DIR__ . '/common/bottom.php'; ?>