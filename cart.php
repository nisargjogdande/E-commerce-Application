<?php
// FILE: cart.php

// 1. Initialize
require_once __DIR__ . '/common/init.php';

// --- AJAX for Cart Actions ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    
    if ($_POST['action'] === 'update_qty' && $product_id) {
        $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
        if ($quantity > 0) {
            $_SESSION['cart'][$product_id] = $quantity;
        } else { // Remove if quantity is 0 or less
            unset($_SESSION['cart'][$product_id]);
        }
    } elseif ($_POST['action'] === 'remove_item' && $product_id) {
        unset($_SESSION['cart'][$product_id]);
    }

    // Recalculate total for the JSON response
    $total_price = 0;
    if (!empty($_SESSION['cart'])) {
        $product_ids = array_keys($_SESSION['cart']);
        $placeholders = rtrim(str_repeat('?,', count($product_ids)), ',');
        $stmt = $conn->prepare("SELECT id, price FROM products WHERE id IN ($placeholders)");
        $stmt->execute($product_ids);
        $products_info = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        foreach ($_SESSION['cart'] as $pid => $quantity) {
             if (isset($products_info[$pid])) {
                $total_price += $products_info[$pid] * $quantity;
            }
        }
    }
    
    echo json_encode([
        'status' => 'success', 
        'cart_count' => count($_SESSION['cart']), 
        'total_price_formatted' => format_inr($total_price)
    ]);
    exit();
}

// --- Page Load Logic ---

// 2. Security Check
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// 3. Presentation
include __DIR__ . '/common/header.php';
include __DIR__ . '/common/sidebar.php';

// --- Data Fetching for Page Display ---
$cart_items = [];
$total_price = 0;
if (!empty($_SESSION['cart'])) {
    $product_ids = array_keys($_SESSION['cart']);
    if (count($product_ids) > 0) {
        $placeholders = rtrim(str_repeat('?,', count($product_ids)), ',');
        $stmt = $conn->prepare("SELECT id, name, price, image, stock FROM products WHERE id IN ($placeholders)");
        $stmt->execute($product_ids);
        $products = $stmt->fetchAll(PDO::FETCH_UNIQUE);

        foreach ($_SESSION['cart'] as $pid => $quantity) {
             if (isset($products[$pid])) {
                $product = $products[$pid];
                $product['quantity'] = $quantity;
                $cart_items[] = $product;
                $total_price += $product['price'] * $quantity;
            } else {
                // Product might have been deleted, auto-remove from cart
                unset($_SESSION['cart'][$pid]);
            }
        }
    }
}
?>

<main class="p-4 pb-40"> <!-- Padding bottom for checkout footer -->
    <h1 class="text-2xl font-bold mb-6 text-white">My Cart</h1>

    <div id="cart-container" class="space-y-4">
        <?php if (count($cart_items) > 0): ?>
            <?php foreach ($cart_items as $item): ?>
            <div id="cart-item-<?= $item['id'] ?>" class="flex bg-gray-800 p-4 rounded-lg shadow-md items-center border border-gray-700">
                <img src="uploads/products/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="w-20 h-20 rounded-md object-cover">
                <div class="flex-1 ml-4">
                    <h3 class="font-semibold text-md text-white"><?= htmlspecialchars($item['name']) ?></h3>
                    <p class="text-indigo-400 font-bold mt-1"><?= format_inr($item['price']) ?></p>
                    <div class="flex items-center mt-3">
                        <label class="text-sm text-gray-400 mr-2">Qty:</label>
                        <input type="number" value="<?= $item['quantity'] ?>" min="1" max="<?= $item['stock'] ?>" class="w-16 text-center bg-gray-700 border border-gray-600 rounded-md py-1 text-white" onchange="updateQty(<?= $item['id'] ?>, this.value)">
                    </div>
                </div>
                <button onclick="removeItem(<?= $item['id'] ?>)" class="ml-4 text-red-400 hover:text-red-300 text-xl">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div id="empty-cart-message" class="text-center py-20">
                <i class="fas fa-shopping-cart text-6xl text-gray-600"></i>
                <p class="text-gray-400 mt-4 text-xl">Your cart is empty.</p>
                <a href="index.php" class="mt-6 inline-block bg-indigo-600 text-white font-bold py-3 px-8 rounded-lg hover:bg-indigo-700">Shop Now</a>
            </div>
        <?php endif; ?>
    </div>
</main>

<!-- Checkout Footer -->
<div id="checkout-footer" class="fixed bottom-16 left-0 right-0 bg-gray-800/80 backdrop-blur-sm p-4 border-t border-gray-700 flex items-center justify-between <?= empty($cart_items) ? 'hidden' : '' ?>">
    <div>
        <span class="text-gray-400">Total:</span>
        <span id="total-price" class="text-2xl font-bold text-white ml-2"><?= format_inr($total_price) ?></span>
    </div>
    <a href="checkout.php" class="bg-green-500 text-white font-bold py-3 px-6 rounded-lg hover:bg-green-600">
        Proceed to Checkout
    </a>
</div>

<script>
async function updateCart(action, productId, quantity = 1) {
    const formData = new FormData();
    formData.append('action', action);
    formData.append('product_id', productId);
    formData.append('quantity', quantity);

    const result = await ajaxRequest('cart.php', formData);

    if (result.status === 'success') {
        const totalPriceEl = document.getElementById('total-price');
        const checkoutFooter = document.getElementById('checkout-footer');
        
        if (totalPriceEl) {
            totalPriceEl.textContent = result.total_price_formatted;
        }

        if (result.cart_count === 0) {
            document.getElementById('cart-container').innerHTML = `
            <div id="empty-cart-message" class="text-center py-20">
                <i class="fas fa-shopping-cart text-6xl text-gray-600"></i>
                <p class="text-gray-400 mt-4 text-xl">Your cart is empty.</p>
                <a href="index.php" class="mt-6 inline-block bg-indigo-600 text-white font-bold py-3 px-8 rounded-lg hover:bg-indigo-700">Shop Now</a>
            </div>`;
            if(checkoutFooter) checkoutFooter.classList.add('hidden');
        } else {
             if(checkoutFooter) checkoutFooter.classList.remove('hidden');
        }
    } else {
        alert('Could not update cart.');
    }
}

function updateQty(productId, quantity) {
    updateCart('update_qty', productId, quantity);
}

function removeItem(productId) {
    if (confirm('Are you sure you want to remove this item?')) {
        document.getElementById(`cart-item-${productId}`).remove();
        updateCart('remove_item', productId);
    }
}
</script>

<?php include __DIR__ . '/common/bottom.php'; ?>