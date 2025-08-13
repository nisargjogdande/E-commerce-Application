<?php
// FILE: login.php

// 1. Initialize
require_once __DIR__ . '/common/init.php';

// --- LOGIC ---

// Handle Logout
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy();
    header('Location: login.php');
    exit();
}

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Handle AJAX form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $action = $_POST['action'] ?? '';
    
    // --- LOGIN ACTION ---
    if ($action === 'login') {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            echo json_encode(['status' => 'error', 'message' => 'Email and password are required.']);
            exit();
        }

        $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name']; // Store name for personalization
            echo json_encode(['status' => 'success', 'redirect' => 'index.php']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid email or password.']);
        }
        exit();
    }
    
    // --- SIGNUP ACTION (REVISED AND CHECKED) ---
    if ($action === 'signup') {
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // Server-side validation
        if (empty($name) || empty($phone) || empty($email) || empty($password)) {
            echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
            exit();
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid email format.']);
            exit();
        }

        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            echo json_encode(['status' => 'error', 'message' => 'This email is already registered.']);
            exit();
        }

        // Hash password and insert new user
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (name, phone, email, password) VALUES (?, ?, ?, ?)");
        
        if ($stmt->execute([$name, $phone, $email, $hashed_password])) {
            // Automatically log in the new user
            $_SESSION['user_id'] = $conn->lastInsertId();
            $_SESSION['user_name'] = $name;
            echo json_encode(['status' => 'success', 'redirect' => 'index.php']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Registration failed due to a server error.']);
        }
        exit();
    }

    // Fallback for any other action
    echo json_encode(['status' => 'error', 'message' => 'Invalid form action.']);
    exit();
}

// --- PRESENTATION ---
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Welcome - Nisu Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
    <style> body { -webkit-tap-highlight-color: transparent; } .tab-active { border-color: #818cf8; color: #e0e7ff; } .input-field { background-color: #374151; border-color: #4b5563; color: #f3f4f6; } .input-field:focus { border-color: #818cf8; box-shadow: 0 0 0 2px #4338ca; } @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap'); body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-gray-900 flex items-center justify-center min-h-screen font-sans select-none p-4">
    <div id="loading-modal" class="hidden fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center"><div class="w-12 h-12 border-4 border-t-transparent border-indigo-400 rounded-full animate-spin"></div></div>
    <main class="bg-gray-800 p-6 sm:p-8 rounded-2xl shadow-2xl w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-white">Nisu<span class="text-indigo-400">Store</span></h1>
            <p class="text-gray-400 mt-2">Your fast lane to shopping.</p>
        </div>
        <div class="flex border-b border-gray-700 mb-6"><button id="login-tab-btn" class="flex-1 py-3 text-center font-semibold text-lg border-b-2 tab-active transition-colors duration-300" onclick="showTab('login')">Login</button><button id="signup-tab-btn" class="flex-1 py-3 text-center font-semibold text-lg text-gray-500 border-b-2 border-transparent hover:text-gray-300 transition-colors duration-300" onclick="showTab('signup')">Sign Up</button></div>
        <div id="message-area" class="text-center mb-4 min-h-[24px]"></div>
        <form id="login-form" class="space-y-6"><input type="hidden" name="action" value="login"><div><label for="login-email" class="block text-sm font-medium text-gray-300 mb-1">Email</label><input type="email" id="login-email" name="email" required class="mt-1 block w-full px-4 py-3 rounded-lg shadow-sm focus:outline-none input-field transition-colors duration-300"></div><div><label for="login-password" class="block text-sm font-medium text-gray-300 mb-1">Password</label><input type="password" id="login-password" name="password" required class="mt-1 block w-full px-4 py-3 rounded-lg shadow-sm focus:outline-none input-field transition-colors duration-300"></div><button type="submit" class="w-full bg-indigo-600 text-white py-3 px-4 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-800 focus:ring-indigo-500 font-semibold text-lg transition-transform duration-200 active:scale-95">Login</button></form>
        <form id="signup-form" class="hidden space-y-5"><input type="hidden" name="action" value="signup"><div><label for="signup-name" class="block text-sm font-medium text-gray-300 mb-1">Full Name</label><input type="text" id="signup-name" name="name" required class="mt-1 block w-full px-4 py-3 rounded-lg shadow-sm focus:outline-none input-field transition-colors duration-300"></div><div><label for="signup-phone" class="block text-sm font-medium text-gray-300 mb-1">Phone</label><input type="tel" id="signup-phone" name="phone" required class="mt-1 block w-full px-4 py-3 rounded-lg shadow-sm focus:outline-none input-field transition-colors duration-300"></div><div><label for="signup-email" class="block text-sm font-medium text-gray-300 mb-1">Email</label><input type="email" id="signup-email" name="email" required class="mt-1 block w-full px-4 py-3 rounded-lg shadow-sm focus:outline-none input-field transition-colors duration-300"></div><div><label for="signup-password" class="block text-sm font-medium text-gray-300 mb-1">Password</label><input type="password" id="signup-password" name="password" required class="mt-1 block w-full px-4 py-3 rounded-lg shadow-sm focus:outline-none input-field transition-colors duration-300"></div><button type="submit" class="w-full bg-indigo-600 text-white py-3 px-4 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-800 focus:ring-indigo-500 font-semibold text-lg transition-transform duration-200 active:scale-95">Create Account</button></form>
    </main>
<script>
document.addEventListener('contextmenu', event => event.preventDefault());
const loginTabBtn = document.getElementById('login-tab-btn'), signupTabBtn = document.getElementById('signup-tab-btn'), loginForm = document.getElementById('login-form'), signupForm = document.getElementById('signup-form'), messageArea = document.getElementById('message-area'), loadingModal = document.getElementById('loading-modal');
function showTab(tabName) { messageArea.innerHTML = ''; if (tabName === 'login') { loginForm.classList.remove('hidden'); signupForm.classList.add('hidden'); loginTabBtn.classList.add('tab-active', 'text-e0e7ff'); loginTabBtn.classList.remove('text-gray-500'); signupTabBtn.classList.remove('tab-active', 'text-e0e7ff'); signupTabBtn.classList.add('text-gray-500'); } else { loginForm.classList.add('hidden'); signupForm.classList.remove('hidden'); signupTabBtn.classList.add('tab-active', 'text-e0e7ff'); signupTabBtn.classList.remove('text-gray-500'); loginTabBtn.classList.remove('tab-active', 'text-e0e7ff'); loginTabBtn.classList.add('text-gray-500'); } }
function showLoader(show) { loadingModal.classList.toggle('hidden', !show); }
function showMessage(type, message) { const color = type === 'success' ? 'text-green-400' : 'text-red-400'; messageArea.innerHTML = `<p class="${color} font-medium">${message}</p>`; }
async function handleFormSubmit(event) {
    event.preventDefault(); showLoader(true); messageArea.innerHTML = ''; const form = event.target; const formData = new FormData(form);
    try {
        const response = await fetch('login.php', { method: 'POST', body: formData, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const result = await response.json();
        if (result.status === 'success') { showMessage('success', 'Success! Redirecting...'); window.location.href = result.redirect; } else { showMessage('error', result.message); }
    } catch (error) { showMessage('error', 'An unexpected error occurred. Please try again.'); console.error('Fetch error:', error); } finally { showLoader(false); }
}
loginForm.addEventListener('submit', handleFormSubmit); signupForm.addEventListener('submit', handleFormSubmit);
</script>
</body></html>