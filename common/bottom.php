<?php
// FILE: common/bottom.php
?>
    <!-- Bottom Navigation -->
    <nav class="fixed bottom-0 left-0 right-0 bg-gray-800/80 backdrop-blur-sm border-t border-gray-700 z-30 flex justify-around text-gray-400">
        <a href="index.php" class="flex flex-col items-center justify-center p-3 w-full hover:bg-gray-700 text-indigo-400"><i class="fas fa-home text-xl"></i><span class="text-xs mt-1">Home</span></a>
        <a href="cart.php" class="flex flex-col items-center justify-center p-3 w-full hover:bg-gray-700"><i class="fas fa-shopping-cart text-xl"></i><span class="text-xs mt-1">Cart</span></a>
        <a href="profile.php" class="flex flex-col items-center justify-center p-3 w-full hover:bg-gray-700"><i class="fas fa-user text-xl"></i><span class="text-xs mt-1">Profile</span></a>
    </nav>

</div> <!-- End of main container -->

<script>
    // --- SIMPLIFIED SPLASH SCREEN SCRIPT ---
    document.addEventListener('DOMContentLoaded', () => {
        const splashScreen = document.getElementById('splash-screen');
        if (splashScreen) {
            // Wait a moment after animation ends, then hide it
            setTimeout(() => {
                splashScreen.classList.add('hidden');
            }, 1800); // Total time splash is visible
        }
    });


    // --- GLOBAL APP JAVASCRIPT ---
    document.addEventListener('contextmenu', event => event.preventDefault());
    
    // Sidebar toggle functionality
    const menuBtn = document.getElementById('menu-btn');
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebar-overlay');
    const closeSidebarBtn = document.getElementById('close-sidebar-btn');

    function toggleSidebar() { sidebar.classList.toggle('-translate-x-full'); sidebarOverlay.classList.toggle('hidden'); }
    if (menuBtn && sidebar && sidebarOverlay && closeSidebarBtn) {
        menuBtn.addEventListener('click', toggleSidebar);
        sidebarOverlay.addEventListener('click', toggleSidebar);
        closeSidebarBtn.addEventListener('click', toggleSidebar);
    }
    
    // Loader functions
    const loadingModal = document.getElementById('loading-modal');
    function showLoader() { if (loadingModal) loadingModal.classList.remove('hidden'); }
    function hideLoader() { if (loadingModal) loadingModal.classList.add('hidden'); }

    // Generic AJAX function
    async function ajaxRequest(url, formData) {
        showLoader();
        try {
            const response = await fetch(url, { method: 'POST', body: formData, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            return await response.json();
        } catch (error) {
            console.error('AJAX Error:', error);
            alert('An unexpected network error occurred.');
            return { status: 'error', message: 'An unexpected error occurred.' };
        } finally {
            hideLoader();
        }
    }
</script>
</body>
</html>