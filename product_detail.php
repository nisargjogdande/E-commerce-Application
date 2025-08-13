<?php
// FILE: product_detail.php

// 1. Initialize
require_once __DIR__ . '/common/init.php';

// --- AJAX for Add to Cart ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_to_cart') {
    header('Content-Type: application/json');
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);

    if ($product_id && $quantity > 0) {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        // If product already in cart, update quantity. Else, add it.
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id] += $quantity;
        } else {
            $_SESSION['cart'][$product_id] = $quantity;
        }
        echo json_encode(['status' => 'success', 'message' => 'Product added to cart!', 'cart_count' => count($_SESSION['cart'])]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid product or quantity.']);
    }
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

// --- Data Fetching ---
$product_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$product = null;
$related_products = [];

if ($product_id) {
    try {
        // Fetch product details - THIS IS THE FIX
        $stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.cat_id = c.id WHERE p.id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC); // Correct PDO method

        if ($product) {
            // Fetch related products
            $related_stmt = $conn->prepare("SELECT * FROM products WHERE cat_id = ? AND id != ? ORDER BY RAND() LIMIT 4");
            $related_stmt->execute([$product['cat_id'], $product_id]);
            $related_products = $related_stmt->fetchAll(PDO::FETCH_ASSOC); // Correct PDO method
        }
    } catch (PDOException $e) {
        // Handle potential DB errors gracefully
        echo "<main class='p-4 text-red-400'>Database error: " . $e->getMessage() . "</main>";
        include __DIR__ . '/common/bottom.php';
        exit();
    }
}

if (!$product) {
    echo "<main class='p-4 text-center'><p class='text-red-400 text-xl mt-10'>Product not found.</p><a href='index.php' class='mt-4 inline-block bg-indigo-600 text-white font-bold py-2 px-6 rounded-lg'>Go Home</a></main>";
    include __DIR__ . '/common/bottom.php';
    exit();
}
?>

<main class="pb-24"> <!-- Padding bottom to clear the fixed action bar -->
    <!-- Product Image Slider -->
    <div class="w-full h-80 bg-gray-800">
        <img src="uploads/products/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="w-full h-full object-contain">
    </div>

    <!-- Product Info -->
    <div class="p-4 bg-gray-900">
        <h1 class="text-2xl font-bold text-white"><?= htmlspecialchars($product['name']) ?></h1>
        <p class="text-gray-400 text-sm mb-3">In <a href="product.php?cat_id=<?= $product['cat_id'] ?>" class="text-indigo-400 hover:underline"><?= htmlspecialchars($product['category_name']) ?></a></p>
        
        <div class="flex items-center justify-between mb-4">
            <span class="text-3xl font-bold text-indigo-400"><?= format_inr($product['price']) ?></span>
            <span class="text-sm font-medium px-3 py-1 rounded-full <?= $product['stock'] > 0 ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400' ?>">
                <?= $product['stock'] > 0 ? 'In Stock' : 'Out of Stock' ?>
            </span>
        </div>
        
        <div class="border-t border-gray-700 pt-4">
            <h2 class="font-bold text-lg mb-2 text-white">Description</h2>
            <p class="text-gray-300 text-base leading-relaxed">
                <?= nl2br(htmlspecialchars($product['description'])) ?>
            </p>
        </div>
    </div>

    <!-- Related Products -->
    <?php if ($related_products): ?>
    <section class="p-4 mt-4">
        <h2 class="text-xl font-bold mb-4 text-white">Related Products</h2>
        <div class="grid grid-cols-2 gap-4">
            <?php foreach ($related_products as $rel_product): ?>
            <a href="product_detail.php?id=<?= $rel_product['id'] ?>" class="bg-gray-800 rounded-lg shadow-lg overflow-hidden group border border-gray-700">
                <div class="w-full h-32 overflow-hidden">
                  <img src="uploads/products/<?= htmlspecialchars($rel_product['image']) ?>" alt="<?= htmlspecialchars($rel_product['name']) ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                </div>
                <div class="p-2">
                    <h3 class="font-semibold text-sm truncate text-white"><?= htmlspecialchars($rel_product['name']) ?></h3>
                    <span class="font-bold text-indigo-400 text-md"><?= format_inr($rel_product['price']) ?></span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</main>

<!-- Action Bar (Fixed at bottom) -->
<div class="fixed bottom-16 left-0 right-0 bg-gray-800/80 backdrop-blur-sm p-3 border-t border-gray-700 flex items-center justify-between space-x-4">
    <div class="flex items-center border border-gray-600 rounded-md bg-gray-700">
        <button id="qty-minus" class="px-4 py-2 text-lg font-bold text-gray-300 transition-transform active:scale-90">-</button>
        <input id="quantity" type="text" value="1" readonly class="w-12 text-center font-bold bg-gray-700 text-white border-l border-r border-gray-600">
        <button id="qty-plus" class="px-4 py-2 text-lg font-bold text-gray-300 transition-transform active:scale-90">+</button>
    </div>
    <button id="add-to-cart-btn" <?= $product['stock'] <= 0 ? 'disabled' : '' ?> class="flex-1 bg-indigo-600 text-white font-bold py-3 rounded-lg hover:bg-indigo-700 disabled:bg-gray-500 disabled:cursor-not-allowed transition-transform active:scale-95">
        <i class="fas fa-cart-plus mr-2"></i> Add to Cart
    </button>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const qtyMinus = document.getElementById('qty-minus');
    const qtyPlus = document.getElementById('qty-plus');
    const quantityInput = document.getElementById('quantity');
    const maxStock = <?= (int)$product['stock'] ?>;
    const addToCartBtn = document.getElementById('add-to-cart-btn');

    qtyMinus.addEventListener('click', () => {
        let currentQty = parseInt(quantityInput.value);
        if (currentQty > 1) { quantityInput.value = currentQty - 1; }
    });

    qtyPlus.addEventListener('click', () => {
        let currentQty = parseInt(quantityInput.value);
        if (currentQty < maxStock) { quantityInput.value = currentQty + 1; }
    });

    addToCartBtn.addEventListener('click', async () => {
        const formData = new FormData();
        formData.append('action', 'add_to_cart');
        formData.append('product_id', '<?= $product_id ?>');
        formData.append('quantity', quantityInput.value);

        const result = await ajaxRequest('product_detail.php', formData);
        
        if (result.status === 'success') {
            alert(result.message); // A simple feedback mechanism
            // Update cart count in header
            const cartLink = document.querySelector('header a[href="cart.php"]');
            let cartCountSpan = cartLink.querySelector('span');
            if (!cartCountSpan) {
                cartCountSpan = document.createElement('span');
                cartCountSpan.className = 'absolute -top-2 -right-3 bg-indigo-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center';
                cartLink.appendChild(cartCountSpan);
            }
            cartCountSpan.textContent = result.cart_count;
        } else {
            alert(result.message || 'Could not add to cart.');
        }
    });
});
</script>

<?php include __DIR__ . '/common/bottom.php'; ?>