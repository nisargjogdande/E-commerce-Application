<?php
// FILE: admin/common/bottom.php
?>
            </main>
        </div>
    </div>
<script>
    // Global Admin JS
    document.addEventListener('contextmenu', event => event.preventDefault());

    // --- Sidebar Functionality ---
    const menuBtn = document.getElementById('admin-menu-btn');
    const sidebar = document.getElementById('admin-sidebar');
    const overlay = document.getElementById('admin-sidebar-overlay');

    function toggleAdminSidebar() {
        // Toggle the class that moves the sidebar on/off screen
        sidebar.classList.toggle('-translate-x-full');
        // Show/hide the dark overlay for mobile
        overlay.classList.toggle('hidden');
    }
    
    // Ensure all elements exist before adding listeners
    if (menuBtn && sidebar && overlay) {
        // When hamburger icon is clicked
        menuBtn.addEventListener('click', toggleAdminSidebar);
        // When the dark overlay is clicked (to close the menu)
        overlay.addEventListener('click', toggleAdminSidebar);
    }
    
    // --- Loader Functions ---
    const loadingModal = document.getElementById('loading-modal');
    function showLoader() { if (loadingModal) loadingModal.classList.remove('hidden'); }
    function hideLoader() { if (loadingModal) loadingModal.classList.add('hidden'); }

    // --- Generic AJAX Function ---
    async function adminAjaxRequest(url, formData) {
        showLoader();
        try {
            const response = await fetch(url, { 
                method: 'POST', 
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
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