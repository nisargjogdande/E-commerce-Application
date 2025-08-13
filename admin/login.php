<?php
// FILE: admin/login.php

// Use __DIR__ to go up one directory and find the main init file.
// This starts the session and connects to the database.
require_once __DIR__ . '/../common/init.php';

// --- LOGIC ---

// Handle Logout action
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    unset($_SESSION['admin_id']);
    session_destroy();
    header('Location: login.php');
    exit();
}

// If admin is already logged in, redirect to the dashboard
if (isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit();
}

$error = '';
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Username and password are required.';
    } else {
        $stmt = $conn->prepare("SELECT id, password FROM admin WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            // Login successful
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $username;
            header('Location: index.php');
            exit();
        } else {
            // Login failed
            $error = 'Invalid username or password.';
        }
    }
}

// The connection will be closed automatically when the script ends.
// No need for $conn = null; or $conn->close(); here.

// --- PRESENTATION ---
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Admin Login - Nisu Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-200 flex items-center justify-center h-screen font-sans select-none">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-sm">
        <h1 class="text-2xl font-bold text-center text-gray-800 mb-2">Admin Login</h1>
        <p class="text-center text-gray-500 mb-6">Welcome back to Nisu Store</p>
        
        <?php if ($error): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded mb-4" role="alert">
            <p><?= htmlspecialchars($error) ?></p>
        </div>
        <?php endif; ?>

        <form method="POST" action="login.php" class="space-y-4">
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                <input type="text" id="username" name="username" required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" id="password" name="password" required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <button type="submit" class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 font-semibold">
                Login
            </button>
        </form>
    </div>
    <script> document.addEventListener('contextmenu', event => event.preventDefault()); </script>
</body>
</html>