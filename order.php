<?php
// FILE: order.php

// 1. Initialize
require_once __DIR__ . '/common/init.php';

// --- Page Load Logic ---

// 2. Security Check before any HTML
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// 3. Presentation
include __DIR__ . '/common/header.php';
include __DIR__ . '/common/sidebar.php';

// --- Data Fetching for Page Display ---
$user_id = $_SESSION['user_id'];
$orders = [];
try {
    $stmt = $conn->prepare("
        SELECT 
            o.id, o.total_amount, o.status, o.created_at,
            (SELECT p.name FROM products p JOIN order_items oi ON p.id = oi.product_id WHERE oi.order_id = o.id LIMIT 1) as product_name,
            (SELECT p.image FROM products p JOIN order_items oi ON p.id = oi.product_id WHERE oi.order_id = o.id LIMIT 1) as product_image,
            (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) as item_count
        FROM orders o
        WHERE o.user_id = ?
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<p class='text-red-400 p-4'>Database error: " . $e->getMessage() . "</p>";
}

// Separate orders into active and history - UNIVERSAL SYNTAX FIX
$active_orders = array_filter($orders, function($o) {
    return in_array($o['status'], ['Placed', 'Dispatched']);
});
$history_orders = array_filter($orders, function($o) {
    return in_array($o['status'], ['Delivered', 'Cancelled']);
});


// Function to render the progress tracker
function render_progress_tracker($status) {
    $stages = [
        'Placed' => ['icon' => 'fa-box', 'complete' => false, 'active' => false],
        'Dispatched' => ['icon' => 'fa-truck-fast', 'complete' => false, 'active' => false],
        'Delivered' => ['icon' => 'fa-check-circle', 'complete' => false, 'active' => false]
    ];
    $status_lower = strtolower($status);

    if ($status_lower == 'placed') $stages['Placed']['active'] = true;
    if ($status_lower == 'dispatched') { $stages['Placed']['complete'] = true; $stages['Dispatched']['active'] = true; }
    if ($status_lower == 'delivered') { $stages['Placed']['complete'] = true; $stages['Dispatched']['complete'] = true; $stages['Delivered']['active'] = true; $stages['Delivered']['complete'] = true; }
    if ($status_lower == 'cancelled') return '<div class="text-center text-red-400 font-bold p-4 border-t border-gray-700">Order Cancelled</div>';

    $html = '<div class="flex items-center w-full px-4 pt-4 pb-2">';
    $keys = array_keys($stages);
    $is_first = true;
    foreach ($stages as $key => $stage) {
        if (!$is_first) {
            $line_bg = $stage['complete'] || $stage['active'] ? 'bg-green-400' : 'bg-gray-600';
            $html .= "<div class='flex-auto h-0.5 mt-[-1.2rem] {$line_bg}'></div>";
        }
        $icon_color = $stage['active'] || $stage['complete'] ? 'text-green-400' : 'text-gray-500';
        $text_color = $stage['active'] ? 'text-green-300 font-semibold' : 'text-gray-400';
        $html .= "<div class='flex-shrink-0 text-center z-10'>";
        $html .= "<div class='w-10 h-10 {$icon_color} mx-auto rounded-full flex items-center justify-center text-xl " . ($stage['active'] || $stage['complete'] ? 'bg-green-500/20' : 'bg-gray-700') . "'><i class='fas {$stage['icon']}'></i></div>";
        $html .= "<p class='text-xs mt-1 {$text_color}'>$key</p></div>";
        $is_first = false;
    }
    $html .= '</div>';
    return $html;
}

// Function to render an order card
function render_order_card($order) {
    $status_color = 'text-orange-400';
    if ($order['status'] == 'Delivered') $status_color = 'text-green-400';
    if ($order['status'] == 'Cancelled') $status_color = 'text-red-400';

    $card_content = "
    <div class='bg-gray-800 rounded-lg shadow-md overflow-hidden border border-gray-700'>
        <div class='p-4'>
            <div class='flex justify-between items-start'>
                <div>
                    <h3 class='font-bold text-white'>Order #{$order['id']}</h3>
                    <p class='text-sm text-gray-400'>" . date('d M Y, h:i A', strtotime($order['created_at'])) . "</p>
                </div>
                <span class='font-bold {$status_color}'>{$order['status']}</span>
            </div>
            <div class='flex items-center mt-4 pt-4 border-t border-gray-700'>
                <img src='uploads/products/" . htmlspecialchars($order['product_image'] ?? 'default.png') . "' class='w-16 h-16 rounded-md object-cover mr-4'>
                <div class='flex-1'>
                    <p class='font-semibold text-white'>" . htmlspecialchars($order['product_name']) . "</p>
                    " . ($order['item_count'] > 1 ? "<p class='text-sm text-gray-400'>+ " . ($order['item_count'] - 1) . " more item(s)</p>" : "") . "
                </div>
                 <div class='text-right'>
                    <p class='text-sm text-gray-400'>Total</p>
                    <p class='font-bold text-white'>" . format_inr($order['total_amount']) . "</p>
                 </div>
            </div>
        </div>";

    if ($order['status'] !== 'Cancelled') {
         $card_content .= render_progress_tracker($order['status']);
    }

    $card_content .= "</div>";
    return $card_content;
}
?>
<main class="p-4 pb-24">
    <h1 class="text-2xl font-bold mb-4 text-white">My Orders</h1>
    <div class="flex border-b border-gray-700 mb-4">
        <button id="active-tab-btn" class="flex-1 py-3 font-semibold border-b-2 tab-active transition-colors duration-300" onclick="showOrderTab('active')">Active Orders</button>
        <button id="history-tab-btn" class="flex-1 py-3 font-semibold text-gray-500 border-b-2 border-transparent hover:text-gray-300 transition-colors duration-300" onclick="showOrderTab('history')">Order History</button>
    </div>
    <div id="active-orders" class="space-y-4">
        <?php if (count($active_orders) > 0): ?>
            <?php foreach ($active_orders as $order) echo render_order_card($order); ?>
        <?php else: ?>
            <p class="text-center text-gray-500 py-16">No active orders.</p>
        <?php endif; ?>
    </div>
    <div id="history-orders" class="hidden space-y-4">
         <?php if (count($history_orders) > 0): ?>
            <?php foreach ($history_orders as $order) echo render_order_card($order); ?>
        <?php else: ?>
            <p class="text-center text-gray-500 py-16">No past orders found.</p>
        <?php endif; ?>
    </div>
</main>
<style>.tab-active { border-color: #818cf8; color: #e0e7ff; }</style>
<script>
    const activeTabBtn = document.getElementById('active-tab-btn');
    const historyTabBtn = document.getElementById('history-tab-btn');
    const activeOrdersDiv = document.getElementById('active-orders');
    const historyOrdersDiv = document.getElementById('history-orders');

    function showOrderTab(tabName) {
        if (tabName === 'active') {
            activeOrdersDiv.classList.remove('hidden');
            historyOrdersDiv.classList.add('hidden');
            activeTabBtn.classList.add('tab-active');
            historyTabBtn.classList.remove('tab-active', 'text-gray-300');
            historyTabBtn.classList.add('text-gray-500');
        } else {
            activeOrdersDiv.classList.add('hidden');
            historyOrdersDiv.classList.remove('hidden');
            historyTabBtn.classList.add('tab-active', 'text-e0e7ff');
            activeTabBtn.classList.remove('tab-active');
            activeTabBtn.classList.add('text-gray-500');
        }
    }
</script>
<?php include __DIR__ . '/common/bottom.php'; ?>