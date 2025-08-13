// FILENAME: common/config.php
// --- CONTENT ---
<?php
// Start session on all pages
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Database Credentials
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_NAME', 'quick_edit_db');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error . ". Please run install.php first.");
}

// Set charset
$conn->set_charset("utf8mb4");

// Function to format price
function format_price($price) {
    return '₹' . number_format($price, 2);
}

// Path for uploads
define('UPLOAD_PATH', '/quick-edit/uploads/');
?>