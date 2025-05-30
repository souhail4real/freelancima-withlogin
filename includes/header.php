<?php
// Include config to ensure session is started
require_once __DIR__ . '/../api/config.php';

// Define a base URL that points to the root of your project
$base_url = "/freelanciv2";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FreeLanci.ma - Moroccan Freelance Marketplace</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/styles.css">
</head>
<body>
    <!-- Header -->
    <header class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center space-x-8">
                <a href="<?php echo $base_url; ?>/index.php" class="text-2xl font-bold site-logo">FreeLanci.ma</a>
            </div>

            <div class="flex items-center space-x-4">
                <!-- Auth Navigation -->
                <div class="hidden md:flex items-center space-x-4">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <div class="flex items-center">
                            <span class="text-gray-700 mr-2">Welcome, <span class="font-semibold"><?php echo htmlspecialchars($_SESSION['username']); ?></span></span>
                            <a href="<?php echo $base_url; ?>/auth/logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded transition">
                                Logout
                            </a>
                        </div>
                    <?php else: ?>
                        <a href="<?php echo $base_url; ?>/auth/login.php" class="text-gray-700 hover:text-green-600 transition">Login</a>
                        <a href="<?php echo $base_url; ?>/auth/register.php" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded transition">Register</a>
                    <?php endif; ?>
                </div>

                <button class="md:hidden hamburger-menu text-gray-700 text-2xl">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </header>