// FILENAME: common/bottom.php
// --- CONTENT ---
</div> <!-- Closing main-container -->
<nav class="fixed bottom-0 left-0 right-0 bg-white shadow-[0_-1px_3px_rgba(0,0,0,0.1)] z-30 flex justify-around">
    <a href="index.php" class="flex flex-col items-center justify-center text-slate-600 hover:text-indigo-600 py-2 w-full">
        <i class="fas fa-home text-xl"></i>
        <span class="text-xs mt-1">Home</span>
    </a>
    <a href="cart.php" class="flex flex-col items-center justify-center text-slate-600 hover:text-indigo-600 py-2 w-full relative">
        <i class="fas fa-shopping-cart text-xl"></i>
        <span class="text-xs mt-1">Cart</span>
        <span id="cart-count-badge-bottom" class="absolute top-1 right-[25%] bg-red-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center">0</span>
    </a>
    <a href="profile.php" class="flex flex-col items-center justify-center text-slate-600 hover:text-indigo-600 py-2 w-full">
        <i class="fas fa-user text-xl"></i>
        <span class="text-xs mt-1">Profile</span>
    </a>
</nav>
<script>
// Update bottom nav cart badge as well
function updateBottomCartBadge() {
    const cart = JSON.parse(localStorage.getItem('quickEditCart')) || [];
    const totalItems = cart.reduce((sum, item) => sum + item.qty, 0);
    const badge = document.getElementById('cart-count-badge-bottom');
    if (badge) {
        badge.textContent = totalItems;
        badge.style.display = totalItems > 0 ? 'flex' : 'none';
    }
}
document.addEventListener('DOMContentLoaded', updateBottomCartBadge);
// Overwrite the global function to update both badges
const originalUpdateCartBadge = window.updateCartBadge;
window.updateCartBadge = function() {
    originalUpdateCartBadge();
    updateBottomCartBadge();
}
</script>
</body>
</html>