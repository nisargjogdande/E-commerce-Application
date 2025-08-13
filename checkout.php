<?php
// FILE: checkout.php

// 1. Initialize
require_once __DIR__ . '/common/init.php';

// --- AJAX for Placing Order ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'place_order') {
    header('Content-Type: application/json');
    $user_id = $_SESSION['user_id'] ?? 0;
    $address = trim($_POST['address'] ?? '');

    if (empty($address)) { echo json_encode(['status' => 'error', 'message' => 'Shipping address is required.']); exit(); }
    if (empty($_SESSION['cart'])) { echo json_encode(['status' => 'error', 'message' => 'Your cart is empty.']); exit(); }
    if (!$user_id) { echo json_encode(['status' => 'error', 'message' => 'You must be logged in.']); exit(); }

    try {
        $conn->beginTransaction();
        
        $total_price = 0;
        $product_ids = array_keys($_SESSION['cart']);
        $placeholders = rtrim(str_repeat('?,', count($product_ids)), ',');
        $stmt = $conn->prepare("SELECT id, price, stock FROM products WHERE id IN ($placeholders)");
        $stmt->execute($product_ids);
        $products = $stmt->fetchAll(PDO::FETCH_UNIQUE);

        foreach ($_SESSION['cart'] as $pid => $quantity) {
            if (!isset($products[$pid]) || $products[$pid]['stock'] < $quantity) {
                 throw new Exception('Product ' . ($products[$pid]['name'] ?? 'ID:'.$pid) . ' is out of stock.');
            }
            $total_price += $products[$pid]['price'] * $quantity;
        }

        $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, shipping_address, status) VALUES (?, ?, ?, 'Placed')");
        $stmt->execute([$user_id, $total_price, $address]);
        $order_id = $conn->lastInsertId();

        $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stock_stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");

        foreach ($_SESSION['cart'] as $pid => $quantity) {
            $item_stmt->execute([$order_id, $pid, $quantity, $products[$pid]['price']]);
            $stock_stmt->execute([$quantity, $pid]);
        }
        
        $user_addr_stmt = $conn->prepare("UPDATE users SET address = ? WHERE id = ? AND (address IS NULL OR address = '')");
        $user_addr_stmt->execute([$address, $user_id]);

        $conn->commit();
        unset($_SESSION['cart']);

        echo json_encode(['status' => 'success', 'message' => 'Order placed successfully!', 'redirect' => 'order.php?highlight=' . $order_id]);

    } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Failed to place order: ' . $e->getMessage()]);
    }
    exit();
}

// --- Page Load Logic (THE FIX IS HERE) ---

// 2. Security Checks before any HTML
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if (empty($_SESSION['cart'])) {
    header('Location: cart.php'); // Redirect to cart if it's empty
    exit();
}

// 3. Presentation
include __DIR__ . '/common/header.php';
include __DIR__ . '/common/sidebar.php';

// --- Data Fetching for Page Display ---
$user_stmt = $conn->prepare("SELECT name, phone, email, address FROM users WHERE id = ?");
$user_stmt->execute([$_SESSION['user_id']]);
$user = $user_stmt->fetch(PDO::FETCH_ASSOC);

?>
<main class="p-4 pb-40">
    <h1 class="text-2xl font-bold mb-6 text-white">Checkout</h1>

    <div class="bg-gray-800 p-6 rounded-2xl shadow-lg border border-gray-700 space-y-6">
        <!-- Shipping Info -->
        <div>
            <h2 class="text-lg font-semibold border-b border-gray-700 pb-2 mb-4 text-white">
                <i class="fas fa-shipping-fast mr-2 text-indigo-400"></i>Shipping Information
            </h2>
            <form id="checkout-form" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-400">Name</label>
                    <input type="text" value="<?= htmlspecialchars($user['name']) ?>" class="mt-1 block w-full bg-gray-900 p-3 border border-gray-600 rounded-lg text-gray-300" readonly>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-400">Phone</label>
                    <input type="text" value="<?= htmlspecialchars($user['phone']) ?>" class="mt-1 block w-full bg-gray-900 p-3 border border-gray-600 rounded-lg text-gray-300" readonly>
                </div>
                <div>
                    <label for="address" class="block text-sm font-medium text-gray-400">Shipping Address</label>
                    <textarea id="address" name="address" rows="4" required class="mt-1 block w-full p-3 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"><?= htmlspecialchars($user['address']) ?></textarea>
                </div>
            </form>
        </div>

        <!-- Payment Method -->
        <div>
            <h2 class="text-lg font-semibold border-b border-gray-700 pb-2 mb-4 text-white">
                <i class="fas fa-wallet mr-2 text-indigo-400"></i>Payment Method
            </h2>
            <div class="bg-gray-700 border border-gray-600 p-4 rounded-lg flex items-center">
                <i class="fas fa-money-bill-wave text-3xl text-green-400"></i>
                <div class="ml-4">
                    <p class="font-bold text-white">Cash on Delivery (COD)</p>
                    <p class="text-sm text-gray-400">Pay with cash when your order arrives.</p>
                </div>
            </div>
        </div>
        <div id="form-message" class="text-center min-h-[24px]"></div>
    </div>
</main>

<!-- Place Order Footer -->
<div class="fixed bottom-16 left-0 right-0 bg-gray-800/80 backdrop-blur-sm p-4 border-t border-gray-700">
    <button id="place-order-btn" class="w-full bg-green-500 text-white font-bold py-3 px-6 rounded-lg hover:bg-green-600 transition-transform active:scale-95">
        <i class="fas fa-check-circle mr-2"></i>Place Order
    </button>
</div>

<script>
document.getElementById('place-order-btn').addEventListener('click', async () => {
    const form = document.getElementById('checkout-form');
    const messageDiv = document.getElementById('form-message');
    
    // Simple client-side validation
    if (form.address.value.trim() === '') {
        messageDiv.innerHTML = '<p class="text-red-400 font-medium">Please enter your shipping address.</p>';
        form.address.focus();
        return;
    }

    const formData = new FormData(form);
    formData.append('action', 'place_order');

    const result = await ajaxRequest('checkout.php', formData);
    
    if (result.status === 'success') {
        messageDiv.innerHTML = `<p class="text-green-400 font-medium">${result.message}</p>`;
        alert('Order Placed! You will be redirected to your orders page.');
        window.location.href = result.redirect;
    } else {
        messageDiv.innerHTML = `<p class="text-red-400 font-medium">${result.message}</p>`;
    }
});
</script>
<?php include __DIR__ . '/common/bottom.php'; ?>