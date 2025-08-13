<?php
// FILE: admin/setting.php

// 1. Initialize the application
require_once __DIR__ . '/../common/init.php';

// 2. Security Check
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// 3. Page Logic & Presentation
$message = '';
$message_type = '';

// Handle form submission to update settings
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $admin_id = $_SESSION['admin_id'];
        $new_username = trim($_POST['username'] ?? '');
        $new_password = $_POST['password'] ?? '';

        if (empty($new_username)) {
            throw new Exception("Username cannot be empty.");
        }

        if (!empty($new_password)) {
            // Update both username and password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE admin SET username = ?, password = ? WHERE id = ?");
            $stmt->execute([$new_username, $hashed_password, $admin_id]);
            $_SESSION['admin_username'] = $new_username; // Update session
            $message = 'Settings and password updated successfully!';
        } else {
            // Update username only
            $stmt = $conn->prepare("UPDATE admin SET username = ? WHERE id = ?");
            $stmt->execute([$new_username, $admin_id]);
            $_SESSION['admin_username'] = $new_username; // Update session
            $message = 'Username updated successfully!';
        }
        $message_type = 'success';
    } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        $message_type = 'error';
    }
}

require_once __DIR__ . '/common/header.php';

// Fetch current admin info for the form
$admin = $conn->query("SELECT username FROM admin WHERE id = {$_SESSION['admin_id']}")->fetch();

?>

<h2 class="text-2xl font-bold mb-6 text-white">Admin Settings</h2>

<div class="bg-gray-800 p-8 rounded-lg shadow-md max-w-lg mx-auto border border-gray-700">
    <?php if ($message): ?>
    <div class="p-4 mb-4 text-sm rounded-lg <?php echo $message_type === 'success' ? 'bg-green-200 text-green-800' : 'bg-red-200 text-red-800'; ?>" role="alert">
        <span class="font-medium"><?= htmlspecialchars($message) ?></span>
    </div>
    <?php endif; ?>

    <form method="POST" action="setting.php" class="space-y-6">
        <div>
            <label for="username" class="block text-sm font-medium text-gray-300">Admin Username</label>
            <input type="text" id="username" name="username" value="<?= htmlspecialchars($admin['username']) ?>" required class="mt-1 block w-full p-3 bg-gray-700 border-gray-600 rounded-md shadow-sm text-white focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <div>
            <label for="password" class="block text-sm font-medium text-gray-300">New Password</label>
            <input type="password" id="password" name="password" placeholder="Leave blank to keep current" class="mt-1 block w-full p-3 bg-gray-700 border-gray-600 rounded-md shadow-sm text-white focus:ring-indigo-500 focus:border-indigo-500">
            <p class="mt-2 text-xs text-gray-400">If you set a new password, you will be required to log in again.</p>
        </div>
        <button type="submit" class="w-full bg-indigo-600 text-white py-3 px-4 rounded-md hover:bg-indigo-500 font-semibold transition-colors">
            Update Settings
        </button>
    </form>
</div>

<?php require_once __DIR__ . '/common/bottom.php'; ?>