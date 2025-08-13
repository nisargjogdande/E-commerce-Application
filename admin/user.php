<?php
// FILE: admin/user.php

// 1. Initialize the application
require_once __DIR__ . '/../common/init.php';

// 2. Security Check
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// 3. Page Logic & Presentation
require_once __DIR__ . '/common/header.php';

// Fetch all registered users
$users = $conn->query("SELECT id, name, email, phone, created_at FROM users ORDER BY created_at DESC")->fetchAll();

?>

<h2 class="text-2xl font-bold mb-6 text-white">Manage Users</h2>

<!-- Mobile Card Layout -->
<div class="space-y-4 lg:hidden">
    <?php foreach ($users as $user): ?>
    <div class="bg-gray-800 rounded-lg shadow-md p-4 border border-gray-700">
        <div class="flex justify-between items-start">
            <div>
                <h3 class="font-bold text-white"><?= htmlspecialchars($user['name']) ?></h3>
                <p class="text-sm text-indigo-400"><?= htmlspecialchars($user['email']) ?></p>
            </div>
            <span class="text-xs text-gray-500">ID: <?= $user['id'] ?></span>
        </div>
        <div class="mt-3">
            <p class="text-gray-300"><i class="fas fa-phone-alt w-4 mr-2 text-gray-400"></i><?= htmlspecialchars($user['phone']) ?></p>
            <p class="text-gray-300 text-sm mt-1"><i class="fas fa-calendar-alt w-4 mr-2 text-gray-400"></i>Joined: <?= date('d M Y', strtotime($user['created_at'])) ?></p>
        </div>
    </div>
    <?php endforeach; ?>
</div>


<!-- Desktop Table Layout -->
<div class="hidden lg:block bg-gray-800 p-4 rounded-lg shadow-md overflow-x-auto border border-gray-700">
    <table class="w-full text-left">
        <thead>
            <tr class="border-b border-gray-700">
                <th class="p-3 text-gray-300">ID</th>
                <th class="p-3 text-gray-300">Name</th>
                <th class="p-3 text-gray-300">Email</th>
                <th class="p-3 text-gray-300">Phone</th>
                <th class="p-3 text-gray-300">Registered On</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr class="border-b border-gray-700 hover:bg-gray-700/50">
                    <td class="p-3 text-white"><?= $user['id'] ?></td>
                    <td class="p-3 font-medium text-white"><?= htmlspecialchars($user['name']) ?></td>
                    <td class="p-3 text-indigo-400"><?= htmlspecialchars($user['email']) ?></td>
                    <td class="p-3 text-white"><?= htmlspecialchars($user['phone']) ?></td>
                    <td class="p-3 text-sm text-gray-400"><?= date('d M Y, H:i', strtotime($user['created_at'])) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/common/bottom.php'; ?>