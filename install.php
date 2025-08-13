<?php
// --- EDIT YOUR DATABASE CREDENTIALS HERE ---
// This MUST match your local MySQL server setup.
$db_host = '127.0.0.1'; // Usually '127.0.0.1' or 'localhost'
$db_user = 'root';      // Your MySQL username
$db_pass = 'root';      // Your MySQL password. Leave blank if you don't have one: ''
$db_name = 'quick_edit_db';

// --- DEFAULT ADMIN CREDENTIALS ---
$admin_user = 'admin';
$admin_pass = 'password123'; // The default password for the admin panel

// --- INSTALLATION LOGIC ---
// Stop execution if the app is already installed to prevent accidental data loss.
if (file_exists('common/config.php')) {
    header('Content-Type: text/html');
    echo '<!DOCTYPE html><html><head><title>Already Installed</title><script src="https://cdn.tailwindcss.com"></script></head><body class="bg-gray-100 flex items-center justify-center h-screen"><div class="text-center bg-white p-8 rounded-lg shadow-lg">';
    echo '<h1 class="text-2xl font-bold text-red-600">Installation Locked</h1>';
    echo '<p class="mt-2 text-gray-700">Quick Edit seems to be already installed.</p>';
    echo '<p class="text-gray-500 text-sm mt-1">To prevent data loss, the installer is locked. If you wish to re-install, you must first <strong>manually delete the `common/config.php` file</strong> from your project directory.</p>';
    echo '<a href="index.php" class="mt-4 inline-block bg-indigo-600 text-white font-bold py-2 px-6 rounded-lg">Go to Homepage</a>';
    echo '</div></body></html>';
    exit();
}

$error = '';
$success = '';

try {
    // Step 1: Connect to MySQL Server (without selecting a database yet)
    $pdo = new PDO("mysql:host=$db_host", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Step 2: Create Database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$db_name`");
    $success .= "Database '$db_name' created or already exists.<br>";

    // Step 3: Create All Tables (Full Schema from previous response)
    $pdo->exec("CREATE TABLE IF NOT EXISTS `users` (`id` INT AUTO_INCREMENT PRIMARY KEY,`name` VARCHAR(100) NOT NULL,`phone` VARCHAR(20) NOT NULL,`email` VARCHAR(100) NOT NULL UNIQUE,`password` VARCHAR(255) NOT NULL,`address` TEXT,`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB;");
    $pdo->exec("CREATE TABLE IF NOT EXISTS `admin` (`id` INT AUTO_INCREMENT PRIMARY KEY,`username` VARCHAR(50) NOT NULL UNIQUE,`password` VARCHAR(255) NOT NULL) ENGINE=InnoDB;");
    $pdo->exec("CREATE TABLE IF NOT EXISTS `categories` (`id` INT AUTO_INCREMENT PRIMARY KEY,`name` VARCHAR(100) NOT NULL,`image` VARCHAR(255)) ENGINE=InnoDB;");
    $pdo->exec("CREATE TABLE IF NOT EXISTS `products` (`id` INT AUTO_INCREMENT PRIMARY KEY,`cat_id` INT NOT NULL,`name` VARCHAR(255) NOT NULL,`description` TEXT,`price` DECIMAL(10, 2) NOT NULL,`stock` INT NOT NULL DEFAULT 0,`image` VARCHAR(255),`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY (`cat_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE) ENGINE=InnoDB;");
    $pdo->exec("CREATE TABLE IF NOT EXISTS `orders` (`id` INT AUTO_INCREMENT PRIMARY KEY,`user_id` INT NOT NULL,`total_amount` DECIMAL(10, 2) NOT NULL,`shipping_address` TEXT NOT NULL,`status` VARCHAR(50) NOT NULL DEFAULT 'Placed',`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE) ENGINE=InnoDB;");
    $pdo->exec("CREATE TABLE IF NOT EXISTS `order_items` (`id` INT AUTO_INCREMENT PRIMARY KEY,`order_id` INT NOT NULL,`product_id` INT NOT NULL,`quantity` INT NOT NULL,`price` DECIMAL(10, 2) NOT NULL,FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE) ENGINE=InnoDB;");
    $success .= "All tables created successfully.<br>";

    // Step 4: Insert Default Admin User (check if exists first)
    $stmt = $pdo->prepare("SELECT id FROM admin WHERE username = ?");
    $stmt->execute([$admin_user]);
    if ($stmt->fetch()) {
        $success .= "Admin user '$admin_user' already exists.<br>";
    } else {
        $hashed_password = password_hash($admin_pass, PASSWORD_DEFAULT);
        $stmt_insert = $pdo->prepare("INSERT INTO `admin` (username, password) VALUES (?, ?)");
        $stmt_insert->execute([$admin_user, $hashed_password]);
        $success .= "Default admin user created (username: <strong>$admin_user</strong>, password: <strong>$admin_pass</strong>).<br>";
    }
    
    // Step 5: Create Uploads Directories
    if (!is_dir('common')) mkdir('common', 0777, true);
    if (!is_dir('uploads')) mkdir('uploads', 0777, true);
    if (!is_dir('uploads/categories')) mkdir('uploads/categories', 0777, true);
    if (!is_dir('uploads/products')) mkdir('uploads/products', 0777, true);
    $success .= "Required directories created.<br>";
    
    // Step 6: Create the config.php file
    $config_content = "<?php
// THIS FILE IS AUTO-GENERATED BY install.php
// DO NOT EDIT MANUALLY UNLESS YOU KNOW WHAT YOU ARE DOING

// DATABASE CONNECTION
define('DB_HOST', '{$db_host}');
define('DB_USER', '{$db_user}');
define('DB_PASS', '{$db_pass}');
define('DB_NAME', '{$db_name}');

// GLOBAL SITE SETTINGS
define('CURRENCY_SYMBOL', '₹');

// --- DATABASE CONNECTION OBJECT ---
try {
    \$conn = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
    \$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    \$conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException \$e) {
    die('<h1>Database Connection Failed</h1><p>The application could not connect to the database. Please check credentials in <strong>common/config.php</strong> or run <strong>install.php</strong> again.</p><p><strong>Error:</strong> ' . \$e->getMessage() . '</p>');
}

// --- HELPER FUNCTIONS ---
function format_inr(\$amount) {
    if (!is_numeric(\$amount)) { \$amount = 0; }
    return CURRENCY_SYMBOL . number_format(\$amount, 2);
}
?>";
    file_put_contents('common/config.php', $config_content);
    $success .= "'common/config.php' created. Installation complete!<br><strong>Redirecting to login page...</strong>";

} catch (PDOException $e) {
    // This will catch connection errors, like 'Access Denied'
    $error = "Installation failed: " . $e->getMessage() . "<br><br><strong>Please check your database credentials at the top of the `install.php` file and try again.</strong>";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nisu Store- Installation</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen font-sans">
    <div class="bg-white p-8 rounded-lg shadow-lg max-w-lg w-full text-center">
        <h1 class="text-3xl font-bold text-gray-800 mb-4">Quick Edit Installer</h1>
        <?php if (!empty($error)): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 text-left" role="alert">
                <p class="font-bold">Error!</p>
                <p><?= $error ?></p>
            </div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 text-left" role="alert">
                <p class="font-bold">Success!</p>
                <div class="text-sm mt-2"><?= $success ?></div>
            </div>
            <script>
                setTimeout(() => {
                    window.location.href = 'login.php';
                }, 4000);
            </script>
        <?php endif; ?>
    </div>
</body>
</html>