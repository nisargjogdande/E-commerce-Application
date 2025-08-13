<?php
// FILE: common/header.php

$show_splash = !isset($_SESSION['splash_shown']);
if ($show_splash) {
    $_SESSION['splash_shown'] = true;
}
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>NisuStore</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
      tailwind.config = { darkMode: 'class', theme: { extend: { fontFamily: { sans: ['Inter', 'sans-serif'] } } } }
    </script>
    <style>
        body { -webkit-tap-highlight-color: transparent; }
        .no-scrollbar::-webkit-scrollbar { display: none; }

        /* --- SIMPLIFIED SPLASH SCREEN STYLES --- */
        #splash-screen {
            position: fixed;
            inset: 0;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #111827; /* bg-gray-900 */
            transition: opacity 0.5s ease-out, visibility 0.5s;
        }
        #splash-screen.hidden {
            opacity: 0;
            visibility: hidden;
        }
        .splash-logo {
            font-size: 3rem; font-weight: 800; color: white;
            animation: pulse-in 1.5s ease-in-out;
        }
        .splash-logo .highlight { color: #818cf8; }
        
        @keyframes pulse-in {
            0% { transform: scale(0.8); opacity: 0; }
            50% { transform: scale(1.05); opacity: 1; }
            100% { transform: scale(1); opacity: 1; }
        }
        /* --- END SPLASH STYLES --- */
    </style>
</head>
<body class="bg-gray-900 text-gray-200 font-sans antialiased overflow-x-hidden select-none">

<!-- Splash Screen HTML (if needed) -->
<?php if ($show_splash): ?>
<div id="splash-screen">
    <h1 class="splash-logo">Nisu<span class="highlight">Store</span></h1>
</div>
<?php endif; ?>

<!-- Main App Container -->
<div class="relative min-h-screen pb-20">
    <!-- Loading Modal -->
    <div id="loading-modal" class="hidden fixed inset-0 bg-black bg-opacity-60 z-[100] flex items-center justify-center">
        <div class="w-12 h-12 border-4 border-t-transparent border-indigo-400 rounded-full animate-spin"></div>
    </div>
    
    <!-- Header -->
    <header class="bg-gray-800/80 backdrop-blur-sm shadow-md sticky top-0 z-40 px-4 py-3 flex items-center justify-between border-b border-gray-700">
        <button id="menu-btn" class="text-xl text-gray-300 hover:text-white"><i class="fas fa-bars"></i></button>
        <a href="index.php" class="text-2xl font-bold text-white">Nisu<span class="text-indigo-400">Store</span></a>
        <a href="cart.php" class="relative text-xl text-gray-300 hover:text-white"><i class="fas fa-shopping-cart"></i>
            <?php $cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; if ($cart_count > 0) { echo "<span class='absolute -top-2 -right-3 bg-indigo-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center'>$cart_count</span>"; } ?>
        </a>
    </header>