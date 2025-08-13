<?php
// FILE: product.php

// 1. Initialize
require_once __DIR__ . '/common/init.php';

// 2. Security Check
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// 3. Presentation
include __DIR__ . '/common/header.php';
include __DIR__ . '/common/sidebar.php';

// --- Data Fetching Logic ---
$page_title = "All Products";
$sql = "SELECT p.id, p.name, p.price, p.image, c.name as category_name FROM products p JOIN categories c ON p.cat_id = c.id";
$params = [];
$where_clauses = [];

// Category Filter
$cat_id = filter_input(INPUT_GET, 'cat_id', FILTER_VALIDATE_INT);
if ($cat_id) {
    $where_clauses[] = "p.cat_id = ?";
    $params[] = $cat_id;
    
    $cat_stmt = $conn->prepare("SELECT name FROM categories WHERE id = ?");
    $cat_stmt->execute([$cat_id]);
    $category = $cat_stmt->fetch(PDO::FETCH_ASSOC);
    if ($category) {
        $page_title = htmlspecialchars($category['name']);
    }
}

if (count($where_clauses) > 0) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}

// Sorting
$sort = $_GET['sort'] ?? 'new';
if ($sort == 'price_asc') {
    $sql .= " ORDER BY p.price ASC";
} elseif ($sort == 'price_desc') {
    $sql .= " ORDER BY p.price DESC";
} else {
    $sql .= " ORDER BY p.created_at DESC";
}

// Prepare and execute the final query - THIS IS THE FIX
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC); // Correct PDO method to get all results

?>
<main class="p-4 pb-24">
    <h1 class="text-2xl font-bold mb-4 text-white"><?= $page_title ?></h1>

    <!-- Filters -->
    <div class="bg-gray-800 p-3 rounded-lg shadow-sm mb-6 border border-gray-700">
        <form id="filter-form" method="GET" class="flex items-center justify-between">
            <input type="hidden" name="cat_id" value="<?= htmlspecialchars($cat_id ?? '') ?>">
            <div>
                <label for="sort" class="font-medium text-gray-400">Sort by:</label>
                <select name="sort" id="sort" class="ml-2 border-gray-600 bg-gray-700 text-white rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" onchange="document.getElementById('filter-form').submit()">
                    <option value="new" <?= $sort == 'new' ? 'selected' : '' ?>>Newest</option>
                    <option value="price_asc" <?= $sort == 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
                    <option value="price_desc" <?= $sort == 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
                </select>
            </div>
        </form>
    </div>

    <!-- Product Grid -->
    <div class="grid grid-cols-2 gap-4">
        <?php if (count($products) > 0): ?>
            <?php foreach ($products as $product): ?>
            <a href="product_detail.php?id=<?= $product['id'] ?>" class="bg-gray-800 rounded-lg shadow-lg overflow-hidden group border border-gray-700">
                <div class="w-full h-40 overflow-hidden">
                  <img src="uploads/products/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                </div>
                <div class="p-3">
                    <h3 class="font-semibold text-md truncate text-white"><?= htmlspecialchars($product['name']) ?></h3>
                    <p class="text-gray-400 text-sm"><?= htmlspecialchars($product['category_name']) ?></p>
                    <div class="flex items-baseline justify-between mt-2">
                        <span class="font-bold text-lg text-indigo-400"><?= format_inr($product['price']) ?></span>
                        <div class="bg-indigo-600 text-white rounded-full h-8 w-8 flex items-center justify-center group-hover:bg-indigo-500 transition-colors">
                            <i class="fas fa-arrow-right text-sm"></i>
                        </div>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-span-2 text-center py-16 bg-gray-800 rounded-lg border border-gray-700">
                <i class="fas fa-search-dollar text-4xl text-gray-500"></i>
                <p class="mt-4 text-gray-400">No products found in this category.</p>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include __DIR__ . '/common/bottom.php'; ?>