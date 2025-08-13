<?php
// FILE: admin/common/header.php
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Admin - Nisu Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
      tailwind.config = { darkMode: 'class', theme: { extend: { fontFamily: { sans: ['Inter', 'sans-serif'] } } } }
    </script>
    <style>
        body { -webkit-tap-highlight-color: transparent; }
    </style>
</head>
<body class="bg-gray-900 text-gray-200 font-sans antialiased select-none">
    <div id="loading-modal" class="hidden fixed inset-0 bg-black bg-opacity-60 z-[100] flex items-center justify-center">
        <div class="w-12 h-12 border-4 border-t-transparent border-indigo-400 rounded-full animate-spin"></div>
    </div>
    
    <div class="flex min-h-screen">
        <?php require_once __DIR__ . '/sidebar.php'; ?>
        <!-- Main content area -->
        <div class="flex-1 flex flex-col transition-all duration-300 lg:ml-64">
            <header class="bg-gray-800 shadow-md p-4 flex justify-between items-center sticky top-0 z-30 border-b border-gray-700">
                <!-- This button's JS is in bottom.php -->
                <button id="admin-menu-btn" class="lg:hidden text-2xl text-gray-300 hover:text-white">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="text-xl font-bold text-white">Admin Panel</h1>
                <a href="login.php?action=logout" class="text-red-400 hover:text-red-300 flex items-center gap-2">
                    <i class="fas fa-sign-out-alt"></i>
                    <span class="hidden sm:inline">Logout</span>
                </a>
            </header>
            <main class="flex-1 p-4 md:p-6 lg:p-8">