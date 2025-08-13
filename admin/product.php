<?php
// FILE: admin/product.php

// 1. Initialize
require_once __DIR__ . '/../common/init.php';

// 2. Security Check
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// --- AJAX CRUD Handling ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // All AJAX logic from the previous correct response remains unchanged.
    // It's already functional. We are only fixing the initial page load.
    header('Content-Type: application/json');
    $action = $_POST['action'] ?? '';
    if ($action === 'save') {
        $id = $_POST['id'] ?? null;
        $cat_id = $_POST['cat_id'];
        $name = $_POST['name'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $stock = $_POST['stock'];
        $image_name = $_POST['current_image'] ?? '';
        if (empty($cat_id) || empty($name) || empty($price) || !isset($stock)) { echo json_encode(['status' => 'error', 'message' => 'Please fill all required fields.']); exit(); }
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $target_dir = __DIR__ . "/../uploads/products/";
            $image_name = time() . '_' . basename($_FILES["image"]["name"]);
            if (!move_uploaded_file($_FILES["image"]["tmp_name"], $target_dir . $image_name)) { echo json_encode(['status' => 'error', 'message' => 'Failed to upload image.']); exit(); }
        }
        if ($id) {
            $stmt = $conn->prepare("UPDATE products SET cat_id=?, name=?, description=?, price=?, stock=?, image=? WHERE id=?");
            $stmt->execute([$cat_id, $name, $description, $price, $stock, $image_name, $id]);
        } else {
            $stmt = $conn->prepare("INSERT INTO products (cat_id, name, description, price, stock, image) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$cat_id, $name, $description, $price, $stock, $image_name]);
        }
        echo json_encode(['status' => 'success', 'message' => 'Product saved successfully!']);
    } elseif ($action === 'delete') {
        $id = $_POST['id'];
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['status' => 'success', 'message' => 'Product deleted.']);
    }
    exit();
}

// 3. Presentation
require_once __DIR__ . '/common/header.php';

// --- ROBUST DATA FETCHING ---
// We fetch categories and products for the page display.
// This is the most critical section for fixing the bug.
try {
    $products = $conn->query("SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.cat_id = c.id ORDER BY p.created_at DESC")->fetchAll();
    $categories = $conn->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();
} catch (PDOException $e) {
    // If there's a database error, we'll know.
    $products = [];
    $categories = [];
    echo "<div class='bg-red-500 text-white p-4'>Database Error: " . $e->getMessage() . "</div>";
}
?>

<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold text-white">Manage Products</h2>
    <button id="add-product-btn" class="bg-indigo-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-indigo-500 transition-colors">
        <i class="fas fa-plus mr-2"></i>Add New Product
    </button>
</div>

<!-- Add/Edit Form Modal -->
<div id="product-modal" class="hidden fixed inset-0 bg-black bg-opacity-70 z-50 flex justify-center items-center p-4">
    <form id="product-form" novalidate class="bg-gray-800 p-6 rounded-2xl shadow-xl w-full max-w-2xl max-h-full overflow-y-auto border border-gray-700">
        <div class="flex justify-between items-center mb-4">
            <h3 id="form-title" class="text-xl font-semibold text-white">Add New Product</h3>
            <button type="button" id="close-modal-btn" class="text-gray-400 hover:text-white text-2xl">×</button>
        </div>

        <div id="form-message" class="mb-4 text-center min-h-[24px]"></div>

        <input type="hidden" name="id" id="product-id">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="current_image" id="current-image-name">
        
        <div class="space-y-4">
            <input type="text" name="name" id="name" placeholder="Product Name" required class="w-full p-3 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            
            <!-- CATEGORY DROPDOWN - REVISED LOGIC -->
            <select name="cat_id" id="cat_id" required class="w-full p-3 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">-- Select a Category --</option>
                <?php if (empty($categories)): ?>
                    <option value="" disabled>No categories found. Please add one first.</option>
                <?php else: ?>
                    <?php foreach($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat['id']) ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
            
            <textarea name="description" id="description" placeholder="Description" rows="4" class="w-full p-3 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="number" step="0.01" name="price" id="price" placeholder="Price (₹)" required class="w-full p-3 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                <input type="number" name="stock" id="stock" placeholder="Stock Quantity" required class="w-full p-3 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label for="image" class="block text-sm font-medium text-gray-400">Product Image</label>
                <input type="file" name="image" id="image" class="mt-1 block w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-600 file:text-white hover:file:bg-indigo-500">
                <img id="image-preview" src="" class="hidden w-24 h-24 mt-2 object-cover rounded-md">
            </div>
        </div>
        <div class="mt-6">
            <button type="submit" class="w-full bg-indigo-600 text-white font-bold py-3 rounded-lg hover:bg-indigo-500 transition-colors">Save Product</button>
        </div>
    </form>
</div>

<!-- Product List Table -->
<div class="bg-gray-800 p-4 rounded-2xl shadow-md overflow-x-auto border border-gray-700">
    <table class="w-full text-left">
        <thead class="border-b border-gray-700">
            <tr><th class="p-3 text-sm font-semibold text-gray-400">Image</th><th class="p-3 text-sm font-semibold text-gray-400">Name</th><th class="p-3 text-sm font-semibold text-gray-400">Category</th><th class="p-3 text-sm font-semibold text-gray-400">Price</th><th class="p-3 text-sm font-semibold text-gray-400">Stock</th><th class="p-3 text-sm font-semibold text-gray-400 text-right">Actions</th></tr>
        </thead>
        <tbody>
            <?php if (empty($products)): ?>
                <tr><td colspan="6" class="text-center p-6 text-gray-400">No products found. Click "Add New Product" to start.</td></tr>
            <?php else: ?>
                <?php foreach ($products as $p): ?>
                <tr id="prod-row-<?= $p['id'] ?>" class="border-b border-gray-700 hover:bg-gray-700/50">
                    <td class="p-3"><img src="../uploads/products/<?= htmlspecialchars($p['image']) ?>" class="w-12 h-12 object-cover rounded-md"></td>
                    <td class="p-3 font-medium text-white"><?= htmlspecialchars($p['name']) ?></td>
                    <td class="p-3 text-gray-400"><?= htmlspecialchars($p['cat_name']) ?></td>
                    <td class="p-3 font-semibold text-indigo-400"><?= format_inr($p['price']) ?></td>
                    <td class="p-3 text-white"><?= $p['stock'] ?></td>
                    <td class="p-3 text-right">
                        <button onclick='editProduct(<?= htmlspecialchars(json_encode($p), ENT_QUOTES, 'UTF-8') ?>)' class="text-blue-400 hover:text-blue-300 mr-2 text-lg"><i class="fas fa-edit"></i></button>
                        <button onclick="deleteProduct(<?= $p['id'] ?>)" class="text-red-400 hover:text-red-300 text-lg"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- All JavaScript remains the same -->
<script>
const modal = document.getElementById('product-modal'); const addBtn = document.getElementById('add-product-btn'); const closeBtn = document.getElementById('close-modal-btn'); const form = document.getElementById('product-form'); const formMessage = document.getElementById('form-message');
function showMessage(type, message) { const color = type === 'success' ? 'text-green-400' : 'text-red-400'; formMessage.innerHTML = `<p class="${color} font-medium">${message}</p>`; }
function toggleModal(show) { modal.classList.toggle('hidden', !show); form.reset(); document.getElementById('image-preview').classList.add('hidden'); formMessage.innerHTML = ''; }
addBtn.addEventListener('click', () => { document.getElementById('form-title').textContent = 'Add New Product'; toggleModal(true); }); closeBtn.addEventListener('click', () => toggleModal(false));
form.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (document.getElementById('cat_id').value === '') { showMessage('error', 'You must select a category.'); return; }
    const formData = new FormData(form); const result = await adminAjaxRequest('product.php', formData);
    showMessage(result.status, result.message); if(result.status === 'success') { setTimeout(() => location.reload(), 1500); }
});
function editProduct(p) {
    document.getElementById('form-title').textContent = 'Edit Product'; document.getElementById('product-id').value = p.id; document.getElementById('name').value = p.name; document.getElementById('cat_id').value = p.cat_id; document.getElementById('description').value = p.description; document.getElementById('price').value = p.price; document.getElementById('stock').value = p.stock; document.getElementById('current-image-name').value = p.image; const preview = document.getElementById('image-preview');
    if (p.image) { preview.src = `../uploads/products/${p.image}`; preview.classList.remove('hidden'); }
    toggleModal(true);
}
async function deleteProduct(id) {
    if(confirm('Are you sure?')) {
        const formData = new FormData(); formData.append('action', 'delete'); formData.append('id', id);
        const result = await adminAjaxRequest('product.php', formData);
        if(result.status === 'success') { document.getElementById(`prod-row-${id}`).remove(); } else { alert(result.message || 'Failed to delete.'); }
    }
}
</script>

<?php require_once __DIR__ . '/common/bottom.php'; ?>