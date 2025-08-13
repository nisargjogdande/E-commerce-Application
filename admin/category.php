<?php
// FILE: admin/category.php

// 1. Initialize the application (starts session, connects to DB)
require_once __DIR__ . '/../common/init.php';

// 2. Perform Admin-specific security check
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// --- AJAX CRUD Handling ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action'])) {
    header('Content-Type: application/json');
    $action = $_POST['action'];
    
    // ADD/UPDATE a category
    if ($action === 'save') {
        try {
            $id = $_POST['id'] ?? null;
            $name = trim($_POST['name'] ?? '');
            $image_name = $_POST['current_image'] ?? '';

            if (empty($name)) {
                throw new Exception('Category name is required.');
            }

            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                // Securely handle file uploads
                $target_dir = __DIR__ . "/../uploads/categories/";
                $extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
                $image_name = "cat_" . time() . '.' . $extension;
                $target_file = $target_dir . $image_name;
                if (!move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    throw new Exception('Failed to upload image.');
                }
            }

            if ($id) { // Update existing category
                $stmt = $conn->prepare("UPDATE categories SET name = ?, image = ? WHERE id = ?");
                $stmt->execute([$name, $image_name, $id]);
            } else { // Add new category
                $stmt = $conn->prepare("INSERT INTO categories (name, image) VALUES (?, ?)");
                $stmt->execute([$name, $image_name]);
            }
            echo json_encode(['status' => 'success', 'message' => 'Category saved successfully!']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    } 
    // DELETE a category
    elseif ($action === 'delete') {
        try {
            $id = $_POST['id'] ?? 0;
            if (!$id) throw new Exception('Invalid ID.');
            $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['status' => 'success', 'message' => 'Category deleted.']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    exit();
}

// --- Page Display Logic ---
require_once __DIR__ . '/common/header.php';

// Fetch all categories for display
$categories = $conn->query("SELECT * FROM categories ORDER BY name")->fetchAll();
?>

<h2 class="text-2xl font-bold mb-6 text-white">Manage Categories</h2>

<!-- Add/Edit Form -->
<form id="category-form" class="bg-gray-800 p-6 rounded-lg shadow-md mb-8 border border-gray-700">
    <h3 id="form-title" class="text-xl font-semibold mb-4 text-white">Add New Category</h3>
    <input type="hidden" name="id" id="category-id">
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="current_image" id="current-image-name">
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-2">
            <label for="name" class="block text-sm font-medium text-gray-300">Category Name</label>
            <input type="text" name="name" id="name" required class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm text-white focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <div>
            <label for="image" class="block text-sm font-medium text-gray-300">Image</label>
            <input type="file" name="image" id="image" accept="image/*" class="mt-1 block w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-gray-700 file:text-gray-300 hover:file:bg-gray-600">
            <img id="image-preview" src="" class="hidden w-20 h-20 mt-4 object-cover rounded-md border border-gray-600">
        </div>
    </div>
    <div id="form-message" class="mt-4 text-sm"></div>
    <div class="mt-6 flex items-center gap-4">
        <button type="submit" class="bg-indigo-600 text-white font-bold py-2 px-4 rounded-md hover:bg-indigo-500 transition-colors">Save Category</button>
        <button type="button" id="cancel-edit" class="hidden bg-gray-600 text-gray-200 py-2 px-4 rounded-md hover:bg-gray-500">Cancel Edit</button>
    </div>
</form>

<!-- Category List -->
<div class="bg-gray-800 p-4 rounded-lg shadow-md overflow-x-auto border border-gray-700">
    <table class="w-full text-left">
        <thead>
            <tr class="border-b border-gray-700">
                <th class="p-3 text-gray-300">Image</th>
                <th class="p-3 text-gray-300">Name</th>
                <th class="p-3 text-right text-gray-300">Actions</th>
            </tr>
        </thead>
        <tbody id="category-list">
            <?php foreach ($categories as $cat): ?>
            <tr id="cat-row-<?= $cat['id'] ?>" class="border-b border-gray-700 hover:bg-gray-700/50">
                <td class="p-3"><img src="../uploads/categories/<?= htmlspecialchars($cat['image']) ?>" class="w-12 h-12 object-cover rounded-md bg-gray-700"></td>
                <td class="p-3 font-medium text-white"><?= htmlspecialchars($cat['name']) ?></td>
                <td class="p-3 text-right">
                    <button onclick='editCategory(<?= htmlspecialchars(json_encode($cat), ENT_QUOTES, "UTF-8") ?>)' class="text-blue-400 hover:text-blue-300 mr-2 text-lg"><i class="fas fa-edit"></i></button>
                    <button onclick="deleteCategory(<?= $cat['id'] ?>)" class="text-red-400 hover:text-red-300 text-lg"><i class="fas fa-trash"></i></button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
const form = document.getElementById('category-form');
const formTitle = document.getElementById('form-title');
const formMessage = document.getElementById('form-message');
const catIdInput = document.getElementById('category-id');
const catNameInput = document.getElementById('name');
const imagePreview = document.getElementById('image-preview');
const imageInput = document.getElementById('image');
const currentImageName = document.getElementById('current-image-name');
const cancelBtn = document.getElementById('cancel-edit');

function showMessage(type, message) {
    const color = type === 'success' ? 'text-green-400' : 'text-red-400';
    formMessage.innerHTML = `<p class="${color}">${message}</p>`;
    setTimeout(() => { formMessage.innerHTML = ''; }, 4000);
}

// Reset form to its initial state
function resetForm() {
    form.reset();
    formTitle.textContent = 'Add New Category';
    catIdInput.value = '';
    currentImageName.value = '';
    imagePreview.classList.add('hidden');
    cancelBtn.classList.add('hidden');
    formMessage.innerHTML = '';
}

form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const result = await adminAjaxRequest('category.php', new FormData(form));
    showMessage(result.status, result.message);
    if(result.status === 'success') {
        setTimeout(() => location.reload(), 1500);
    }
});

function editCategory(cat) {
    resetForm();
    formTitle.textContent = 'Edit Category: ' + cat.name;
    catIdInput.value = cat.id;
    catNameInput.value = cat.name;
    currentImageName.value = cat.image;
    if (cat.image) {
        imagePreview.src = `../uploads/categories/${cat.image}`;
        imagePreview.classList.remove('hidden');
    }
    cancelBtn.classList.remove('hidden');
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

cancelBtn.addEventListener('click', resetForm);

async function deleteCategory(id) {
    if(confirm('Are you sure? Deleting a category will also delete all products within it! This action cannot be undone.')) {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', id);
        const result = await adminAjaxRequest('category.php', formData);
        if(result.status === 'success') {
             document.getElementById(`cat-row-${id}`).remove();
        } else {
            alert('Error: ' + result.message);
        }
    }
}

// Show image preview when a new file is selected
imageInput.addEventListener('change', () => {
    const file = imageInput.files[0];
    if (file) {
        imagePreview.src = URL.createObjectURL(file);
        imagePreview.classList.remove('hidden');
    }
});
</script>

<?php require_once __DIR__ . '/common/bottom.php'; ?>