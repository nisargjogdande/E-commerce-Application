<?php
// FILE: profile.php

// 1. Initialize
require_once __DIR__ . '/common/init.php';

// --- AJAX for Profile Updates ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $user_id = $_SESSION['user_id'] ?? 0;
    if (!$user_id) { echo json_encode(['status' => 'error', 'message' => 'Authentication error.']); exit(); }
    
    // UPDATE PROFILE ACTION
    if ($_POST['action'] === 'update_profile') {
        $name = trim($_POST['name']);
        $phone = trim($_POST['phone']);
        $address = trim($_POST['address']);
        
        $stmt = $conn->prepare("UPDATE users SET name = ?, phone = ?, address = ? WHERE id = ?");
        if ($stmt->execute([$name, $phone, $address, $user_id])) {
            echo json_encode(['status' => 'success', 'message' => 'Profile updated successfully!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update profile.']);
        }
    } 
    // CHANGE PASSWORD ACTION
    elseif ($_POST['action'] === 'change_password') {
        $current_pass = $_POST['current_password'];
        $new_pass = $_POST['new_password'];

        if (empty($current_pass) || empty($new_pass)) {
             echo json_encode(['status' => 'error', 'message' => 'All password fields are required.']);
             exit();
        }

        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($current_pass, $user['password'])) {
            $hashed_new_pass = password_hash($new_pass, PASSWORD_DEFAULT);
            $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            if ($update_stmt->execute([$hashed_new_pass, $user_id])) {
                echo json_encode(['status' => 'success', 'message' => 'Password changed successfully!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to change password.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Incorrect current password.']);
        }
    }
    exit();
}

// --- Page Load Logic (THE FIX IS HERE) ---

// 2. Security Check
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// 3. Presentation
include __DIR__ . '/common/header.php';
include __DIR__ . '/common/sidebar.php';

// --- Data Fetching for Page Display ---
$stmt = $conn->prepare("SELECT name, email, phone, address FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<main class="p-4 pb-24">
    <h1 class="text-2xl font-bold mb-6 text-white">My Profile</h1>

    <div id="profile-message" class="mb-4 min-h-[48px]"></div>

    <!-- Edit Profile Form -->
    <div class="bg-gray-800 p-6 rounded-2xl shadow-lg border border-gray-700 mb-8">
        <h2 class="text-lg font-semibold mb-4 text-white border-b border-gray-700 pb-2">
            <i class="fas fa-user-edit mr-2 text-indigo-400"></i>Personal Information
        </h2>
        <form id="profile-form" class="space-y-4">
            <input type="hidden" name="action" value="update_profile">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-400">Name</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required class="mt-1 w-full input-style">
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-400">Email (cannot be changed)</label>
                <input type="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" readonly class="mt-1 w-full bg-gray-900 p-3 border border-gray-600 rounded-lg text-gray-500 cursor-not-allowed">
            </div>
            <div>
                <label for="phone" class="block text-sm font-medium text-gray-400">Phone</label>
                <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" required class="mt-1 w-full input-style">
            </div>
             <div>
                <label for="address" class="block text-sm font-medium text-gray-400">Address</label>
                <textarea id="address" name="address" rows="3" class="mt-1 w-full input-style"><?= htmlspecialchars($user['address']) ?></textarea>
            </div>
            <button type="submit" class="w-full bg-indigo-600 text-white font-bold py-3 rounded-lg hover:bg-indigo-500 transition-colors">Save Changes</button>
        </form>
    </div>

    <!-- Change Password Form -->
    <div class="bg-gray-800 p-6 rounded-2xl shadow-lg border border-gray-700">
        <h2 class="text-lg font-semibold mb-4 text-white border-b border-gray-700 pb-2">
            <i class="fas fa-key mr-2 text-indigo-400"></i>Change Password
        </h2>
        <form id="password-form" class="space-y-4">
            <input type="hidden" name="action" value="change_password">
            <div>
                <label for="current_password" class="block text-sm font-medium text-gray-400">Current Password</label>
                <input type="password" id="current_password" name="current_password" required class="mt-1 w-full input-style">
            </div>
            <div>
                <label for="new_password" class="block text-sm font-medium text-gray-400">New Password</label>
                <input type="password" id="new_password" name="new_password" required class="mt-1 w-full input-style">
            </div>
            <button type="submit" class="w-full bg-orange-500 text-white font-bold py-3 rounded-lg hover:bg-orange-600 transition-colors">Update Password</button>
        </form>
    </div>
    
    <div class="mt-8 text-center">
        <a href="login.php?action=logout" class="text-red-400 font-semibold hover:underline">
            <i class="fas fa-sign-out-alt mr-2"></i>Logout
        </a>
    </div>
</main>

<style>
.input-style {
    @apply block w-full p-3 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500;
}
</style>

<script>
const profileForm = document.getElementById('profile-form');
const passwordForm = document.getElementById('password-form');
const messageDiv = document.getElementById('profile-message');

function showMessage(type, message) {
    const bgColor = type === 'success' ? 'bg-green-500/20' : 'bg-red-500/20';
    const textColor = type === 'success' ? 'text-green-300' : 'text-red-300';
    messageDiv.innerHTML = `<div class="${bgColor} ${textColor} p-3 rounded-md font-medium">${message}</div>`;
    setTimeout(() => { messageDiv.innerHTML = ''; }, 4000);
}

profileForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(profileForm);
    const result = await ajaxRequest('profile.php', formData);
    showMessage(result.status, result.message);
});

passwordForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(passwordForm);
    const result = await ajaxRequest('profile.php', formData);
    showMessage(result.status, result.message);
    if(result.status === 'success') {
        passwordForm.reset();
    }
});
</script>

<?php include __DIR__ . '/common/bottom.php'; ?>