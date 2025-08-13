// FILENAME: common/header.php
// --- CONTENT ---
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Nisu Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        /* Disable text selection and other user interactions */
        body {
            -webkit-user-select: none; /* Safari */
            -ms-user-select: none; /* IE 10+ */
            user-select: none; /* Standard syntax */
            -webkit-tap-highlight-color: transparent; /* Remove tap highlight on mobile */
        }
        /* Custom scrollbar for webkit browsers */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        /* Dotted loader animation */
        .loader-dots div {
            animation-name: bounce;
            animation-duration: 1.4s;
            animation-iteration-count: infinite;
            animation-timing-function: ease-in-out;
            background-color: #4f46e5; /* indigo-600 */
        }
        .loader-dots div:nth-child(2) { animation-delay: -0.16s; }
        .loader-dots div:nth-child(3) { animation-delay: -0.32s; }
        @keyframes bounce {
            0%, 80%, 100% { transform: scale(0); }
            40% { transform: scale(1.0); }
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased font-sans select-none h-full" oncontextmenu="return false;">
    <!-- Loading Modal -->
    <div id="loading-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div class="loader-dots relative w-20 h-5">
            <div class="absolute top-0 w-3 h-3 rounded-full"></div>
            <div class="absolute top-0 w-3 h-3 rounded-full" style="left: 25px;"></div>
            <div class="absolute top-0 w-3 h-3 rounded-full" style="left: 50px;"></div>
        </div>
    </div>

    <!-- Main container -->
    <div id="main-container" class="relative min-h-screen pb-20"> <!-- Padding-bottom to avoid overlap with bottom nav -->
        <!-- Sidebar -->
        <?php include_once 'common/sidebar.php'; ?>

        <!-- App Header -->
        <header class="sticky top-0 bg-white shadow-sm z-30 px-4 py-3 flex items-center justify-between">
            <button id="sidebar-toggle" class="text-xl text-slate-600">
                <i class="fas fa-bars"></i>
            </button>
            <a href="index.php" class="text-2xl font-bold text-indigo-600">QuickEdit</a>
            <a href="cart.php" class="text-xl text-slate-600 relative">
                <i class="fas fa-shopping-bag"></i>
                <span id="cart-count-badge" class="absolute -top-1 -right-2 bg-red-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center">0</span>
            </a>
        </header>

<script>
    // Global JavaScript
    document.addEventListener('DOMContentLoaded', function () {
        // Disable zoom
        document.addEventListener('touchmove', function (event) {
            if (event.scale !== 1) { event.preventDefault(); }
        }, { passive: false });

        document.addEventListener('dblclick', function(event) {
            event.preventDefault();
        }, { passive: false });

        // Sidebar Toggle
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebar-overlay');
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const closeSidebarBtn = document.getElementById('close-sidebar');

        const toggleSidebar = () => {
            sidebar.classList.toggle('-translate-x-full');
            sidebarOverlay.classList.toggle('hidden');
        };

        if(sidebarToggle) sidebarToggle.addEventListener('click', toggleSidebar);
        if(sidebarOverlay) sidebarOverlay.addEventListener('click', toggleSidebar);
        if(closeSidebarBtn) closeSidebarBtn.addEventListener('click', toggleSidebar);

        updateCartBadge();
    });
    
    // --- Reusable Functions ---
    const showLoader = () => document.getElementById('loading-modal').classList.remove('hidden');
    const hideLoader = () => document.getElementById('loading-modal').classList.add('hidden');
    
    // Cart management in localStorage
    const getCart = () => JSON.parse(localStorage.getItem('quickEditCart')) || [];
    const saveCart = (cart) => localStorage.setItem('quickEditCart', JSON.stringify(cart));
    
    const addToCart = (productId, quantity = 1) => {
        let cart = getCart();
        const existingProduct = cart.find(item => item.id === productId);
        if (existingProduct) {
            existingProduct.qty += quantity;
        } else {
            cart.push({ id: productId, qty: quantity });
        }
        saveCart(cart);
        updateCartBadge();
        showToast('Item added to cart!');
    };
    
    const updateCartBadge = () => {
        const cart = getCart();
        const totalItems = cart.reduce((sum, item) => sum + item.qty, 0);
        const badge = document.getElementById('cart-count-badge');
        if (badge) {
            badge.textContent = totalItems;
            badge.style.display = totalItems > 0 ? 'flex' : 'none';
        }
    };

    // Simple toast notification
    const showToast = (message) => {
        const toast = document.createElement('div');
        toast.className = 'fixed bottom-24 left-1/2 -translate-x-1/2 bg-slate-800 text-white px-4 py-2 rounded-full text-sm shadow-lg animate-fade-in-out';
        toast.textContent = message;
        document.body.appendChild(toast);

        const animStyle = document.createElement('style');
        animStyle.innerHTML = `@keyframes fade-in-out { 0% { opacity: 0; transform: translate(-50%, 20px); } 10% { opacity: 1; transform: translate(-50%, 0); } 90% { opacity: 1; transform: translate(-50%, 0); } 100% { opacity: 0; transform: translate(-50%, 20px); } } .animate-fade-in-out { animation: fade-in-out 3s ease-in-out forwards; }`;
        document.head.appendChild(animStyle);

        setTimeout(() => {
            toast.remove();
            animStyle.remove();
        }, 3000);
    };

</script>