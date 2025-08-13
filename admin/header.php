// FILENAME: admin/common/header.php
// --- CONTENT ---
<?php
// Include the main config file
include_once __DIR__ . '/../../common/config.php';

// Security Check: Redirect to login if not logged in as admin
if (!isset($_SESSION['admin_id'])) {
    // Get the current path to redirect back after login
    $current_path = basename($_SERVER['PHP_SELF']);
    if ($current_path !== 'login.php') {
        header('Location: login.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Admin - Nisu Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        body { -webkit-user-select: none; -ms-user-select: none; user-select: none; -webkit-tap-highlight-color: transparent; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .loader-dots div { animation: bounce 1.4s infinite ease-in-out both; }
        .loader-dots div:nth-child(1) { animation-delay: -0.32s; }
        .loader-dots div:nth-child(2) { animation-delay: -0.16s; }
        @keyframes bounce { 0%, 80%, 100% { transform: scale(0); } 40% { transform: scale(1.0); } }
        /* Style for active sidebar link */
        .sidebar-link.active { background-color: #eef2ff; color: #4f46e5; font-weight: 600; }
    </style>
</head>
<body class="antialiased font-sans select-none" oncontextmenu="return false;">
    <div class="flex h-screen bg-slate-100">
        <?php 
        // Don't show sidebar on login page
        if (basename($_SERVER['PHP_SELF']) !== 'login.php') {
            include_once 'sidebar.php';
        }
        ?>

        <div class="flex-1 flex flex-col overflow-hidden">
            <?php 
            // Don't show header on login page
            if (basename($_SERVER['PHP_SELF']) !== 'login.php'): ?>
            <header class="bg-white shadow-sm z-10">
                <div class="px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center justify-between h-16">
                        <!-- Mobile menu button -->
                        <button id="mobile-menu-button" class="text-slate-500 focus:outline-none focus:text-slate-600 md:hidden">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                        <div class="hidden md:block">
                           <h1 class="text-lg font-semibold text-slate-700">Admin Dashboard</h1>
                        </div>
                        <div class="flex items-center">
                           <a href="setting.php" class="text-slate-500 hover:text-indigo-600">
                                <i class="fas fa-cog text-xl"></i>
                           </a>
                        </div>
                    </div>
                </div>
            </header>
            <?php endif; ?>

            <!-- Main content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-slate-100">
                <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-4">

    <!-- Loading Modal -->
    <div id="loading-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div class="loader-dots flex space-x-2">
            <div class="w-3 h-3 bg-indigo-600 rounded-full"></div>
            <div class="w-3 h-3 bg-indigo-600 rounded-full"></div>
            <div class="w-3 h-3 bg-indigo-600 rounded-full"></div>
        </div>
    </div>
    <!-- Simple Toast Notification -->
    <div id="toast-notification" class="hidden fixed top-5 right-5 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 animate-pulse">
        <span id="toast-message"></span>
    </div>
<script>
    // Global Admin JS
    const showLoader = () => document.getElementById('loading-modal').classList.remove('hidden');
    const hideLoader = () => document.getElementById('loading-modal').classList.add('hidden');

    function showToast(message, isError = false) {
        const toast = document.getElementById('toast-notification');
        const toastMessage = document.getElementById('toast-message');
        
        toastMessage.textContent = message;
        toast.className = 'fixed top-5 right-5 text-white px-6 py-3 rounded-lg shadow-lg z-50';
        toast.classList.add(isError ? 'bg-red-500' : 'bg-green-500');
        
        toast.classList.remove('hidden');
        setTimeout(() => toast.classList.add('hidden'), 3000);
    }
</script>