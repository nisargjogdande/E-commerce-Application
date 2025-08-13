<?php
// FILE: index.php

// 1. Initialize the application (start session, connect to DB)
require_once 'common/init.php';

// 2. Perform page-specific logic BEFORE any HTML is printed
// Redirect to login if the user is not logged in. This is the crucial fix.
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit(); // Always call exit() after a header redirect
}

// 3. Now that all logic is done, we can safely start printing the page
include 'common/header.php';
include 'common/sidebar.php';

// --- Page content starts here ---

// Fetch categories
$cat_stmt = $conn->query("SELECT * FROM categories ORDER BY name LIMIT 8");
$categories = $cat_stmt->fetchAll();

// Fetch featured products
$prod_stmt = $conn->query("SELECT p.id, p.name, p.price, p.image, c.name as category_name FROM products p JOIN categories c ON p.cat_id = c.id ORDER BY p.created_at DESC LIMIT 10");
$products = $prod_stmt->fetchAll();

?>

<main class="p-4">
    <!-- Search Bar -->
    <div class="mb-6">
        <div class="relative">
            <input type="text" placeholder="Search for products..." class="w-full pl-10 pr-4 py-3 bg-white border border-gray-200 rounded-full focus:outline-none focus:ring-2 focus:ring-indigo-300">
            <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
        </div>
    </div>

    <!-- Top Categories -->
    <section class="mb-8">
        <h2 class="text-xl font-bold mb-4">Categories</h2>
        <div class="flex space-x-4 overflow-x-auto pb-4 no-scrollbar">
            <?php foreach ($categories as $category): ?>
            <a href="product.php?cat_id=<?= $category['id'] ?>" class="flex-shrink-0 text-center">
                <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center overflow-hidden">
                    <img src="uploads/categories/<?= htmlspecialchars($category['image']) ?>" alt="<?= htmlspecialchars($category['name']) ?>" class="w-full h-full object-cover">
                </div>
                <span class="mt-2 text-sm font-medium text-gray-700 block"><?= htmlspecialchars($category['name']) ?></span>
            </a>
            <?php endforeach; ?>
             <a href="product.php" class="flex-shrink-0 text-center">
                <div class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center">
                    <i class="fas fa-ellipsis-h text-gray-600"></i>
                </div>
                <span class="mt-2 text-sm font-medium text-gray-700 block">All</span>
            </a>
        </div>
    </section>

    <!-- Featured Products -->
    <section>
        <h2 class="text-xl font-bold mb-4">Featured Products</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
            <?php foreach ($products as $product): ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <a href="product_detail.php?id=<?= $product['id'] ?>">
                    <img src="uploads/products/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="w-full h-32 object-cover">
                </a>
                <div class="p-3">
                    <h3 class="font-semibold text-md truncate"><?= htmlspecialchars($product['name']) ?></h3>
                    <p class="text-gray-500 text-sm"><?= htmlspecialchars($product['category_name']) ?></p>
                    <div class="flex items-center justify-between mt-2">
                        <span class="font-bold text-indigo-600 text-lg"><?= format_inr($product['price']) ?></span>
                        <a href="product_detail.php?id=<?= $product['id'] ?>" class="bg-indigo-500 text-white rounded-full h-8 w-8 flex items-center justify-center hover:bg-indigo-600 transition-colors">
                            <i class="fas fa-plus"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
</main>

<?php include 'common/bottom.php'; ?>