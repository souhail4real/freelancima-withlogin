<?php
// Start the session
session_start();
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
    <link rel="stylesheet" href="styles.css">
     
</head>

<body>
    <!-- Header -->
    <header class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center space-x-8">
                <a href="index.php" class="text-2xl font-bold site-logo">FreeLanci.ma</a>
            </div>

            <div class="flex items-center space-x-4">
                <!-- Auth Navigation -->
                <div class="hidden md:flex items-center space-x-4">
                    <?php if(isset($_SESSION['user_id'])): ?>
    <div class="flex items-center">
        <span class="text-gray-700 mr-2">Welcome, <span class="font-semibold"><?php echo htmlspecialchars($_SESSION['username']); ?></span></span>
        <a href="auth/logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded transition">
            Logout
        </a>
    </div>
<?php else: ?>
    <a href="auth/login.php" class="text-gray-700 hover:text-green-600 transition">Login</a>
    <a href="auth/register.php" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded transition">Register</a>
<?php endif; ?>
                </div>

                <button class="md:hidden hamburger-menu text-gray-700 text-2xl">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero-gradient text-white py-16 md:py-24">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-3xl md:text-5xl font-bold mb-4">Find the perfect freelance services for your business</h1>
            <p class="text-xl md:text-2xl mb-8 max-w-3xl mx-auto">Get work done by talented freelancers from Morocco and around the world</p>
    
            <div class="max-w-2xl mx-auto relative">
                <input type="text" id="hero-search-input" placeholder="What service are you looking for?"
                    class="w-full py-4 px-6 rounded-full text-gray-800 focus:outline-none focus:ring-2 focus:ring-green-500">
                <button id="hero-search-button"
                    class="absolute right-2 top-2 bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-full transition">
                    Search
                </button>
                
                <!-- Advanced Filters Toggle Button - Enhanced styling -->
                <div class="mt-4 flex justify-center">
                    <button id="toggle-filters" class="flex items-center">
                        <i class="fas fa-filter"></i> Advanced Filters
                        <i class="fas fa-chevron-down"></i>
                    </button>
                </div>
            </div>
    
            <!-- Advanced Filters Panel -->
            <div id="advanced-filters" class="mt-6" style="display: none;">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Price Range Filter -->
                    <div class="filter-group">
                        <label>Price Range</label>
                        <div class="flex items-center">
                            <span class="text-gray-600">$</span>
                            <input type="number" id="price-min" placeholder="Min" min="0">
                            <span class="text-gray-600">-</span>
                            <input type="number" id="price-max" placeholder="Max" min="0">
                        </div>
                    </div>
    
                    <!-- Category Filter -->
                    <div class="filter-group">
                        <label>Category</label>
                        <select id="category-filter">
                            <option value="">All Categories</option>
                            <option value="web-development">Web Development</option>
                            <option value="mobile-development">Mobile Development</option>
                            <option value="data-science-ml">Data Science & ML</option>
                            <option value="cybersecurity">Cybersecurity</option>
                            <option value="cloud-devops">Cloud & DevOps</option>
                        </select>
                    </div>
    
                    <!-- Skills Filter -->
                    <div class="filter-group">
                        <label>Skills</label>
                        <input type="text" id="skills-filter" placeholder="e.g. JavaScript, Python">
                    </div>
                </div>
    
                <!-- Filter Actions -->
                <div class="mt-4 flex justify-end">
                    <button id="clear-filters">Clear Filters</button>
                    <button id="apply-filters" class="ml-2">Apply Filters</button>
                </div>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="py-12 bg-white">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12 section-title">Popular Categories</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6 category-container">
                <!-- Category 1 -->
                <div class="category-card bg-white rounded-lg p-6 text-center cursor-pointer border border-gray-200 selected-category"
                    data-category="web-development">
                    <div class="text-4xl mb-4 category-icon">
                        <i class="fas fa-code"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Web Development</h3>
                    <p class="text-gray-600">HTML, CSS, JavaScript, React, PHP</p>
                </div>

                <!-- Category 2 -->
                <div class="category-card bg-white rounded-lg p-6 text-center cursor-pointer border border-gray-200"
                    data-category="mobile-development">
                    <div class="text-4xl mb-4 category-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Mobile Development</h3>
                    <p class="text-gray-600">Flutter, Kotlin, Swift, React Native</p>
                </div>

                <!-- Category 3 -->
                <div class="category-card bg-white rounded-lg p-6 text-center cursor-pointer border border-gray-200"
                    data-category="data-science-ml">
                    <div class="text-4xl mb-4 category-icon">
                        <i class="fas fa-brain"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Data Science & ML</h3>
                    <p class="text-gray-600">Python, Pandas, TensorFlow, Scikit-learn</p>
                </div>

                <!-- Category 4 -->
                <div class="category-card bg-white rounded-lg p-6 text-center cursor-pointer border border-gray-200"
                    data-category="cybersecurity">
                    <div class="text-4xl mb-4 category-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Cybersecurity</h3>
                    <p class="text-gray-600">Ethical hacking, cryptography, firewalls</p>
                </div>

                <!-- Category 5 -->
                <div class="category-card bg-white rounded-lg p-6 text-center cursor-pointer border border-gray-200"
                    data-category="cloud-devops">
                    <div class="text-4xl mb-4 category-icon">
                        <i class="fas fa-cloud"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Cloud & DevOps</h3>
                    <p class="text-gray-600">AWS, Azure, Docker, Kubernetes, CI/CD</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Freelancers Section -->
    <section class="py-12 freelancer-section">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12 section-title">Top Freelancers</h2>

            <div id="search-results" class="hidden">
                <h3 class="text-xl font-bold mb-4">Search Results</h3>
                <div id="search-results-container"
                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                    <!-- Search results will be displayed here -->
                </div>
                <button id="clear-search"
                    class="mt-6 mb-8 px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition">
                    Clear Search
                </button>
            </div>
            <!-- Add this right before your freelancer-container div -->
            <div id="active-filter-tags" class="mb-4 flex flex-wrap gap-2 hidden"></div>
            <div id="freelancer-container"
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8 freelancer-container">
                <!-- Freelancers will be loaded here dynamically -->
            </div>

            <div id="pagination-container" class="text-center mt-8 pagination-container">
                <!-- Pagination controls will be rendered here dynamically -->
            </div>

            <div id="loading-spinner" class="loading-spinner hidden"></div>

            <div class="text-center mt-12">

            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 footer-columns">
                <!-- Column 1 -->
                <div>
                    <h3 class="text-xl font-bold mb-4">FreeLanci.ma</h3>
                    <p class="text-gray-400">Connecting businesses with top freelance talent from Morocco and around the
                        world since 2023.</p>
                </div>

                <!-- Column 2 -->
                <div>
                    <h4 class="text-lg font-semibold mb-4">Categories</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Web Development</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Mobile Development</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Data Science & ML</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Cybersecurity</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Cloud & DevOps</a></li>
                    </ul>
                </div>

                <!-- Column 3 -->
                <div>
                    <h4 class="text-lg font-semibold mb-4">About</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Careers</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Press</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Partnerships</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Privacy Policy</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Terms of Service</a></li>
                    </ul>
                </div>

                <!-- Column 4 -->
                <div>
                    <h4 class="text-lg font-semibold mb-4">Support</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Help Center</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Community</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Contact Us</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">FAQs</a></li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-gray-800 mt-8 pt-8 flex flex-col md:flex-row justify-between items-center">
                <div class="text-gray-400 mb-4 md:mb-0">
                    &copy; 2025 FreeLanci.ma. All rights reserved.
                </div>

                <div class="flex space-x-4">
                    <a href="#" class="text-gray-400 hover:text-white transition">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                </div>
            </div>
        </div>

        <script src="script.js"></script>
    </footer>

    <!-- Mobile Menu JavaScript -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const hamburgerMenu = document.querySelector('.hamburger-menu');
        
        if (hamburgerMenu) {
            hamburgerMenu.addEventListener('click', function() {
                // Check if mobile menu exists
                let mobileMenu = document.getElementById('mobile-menu');
                
                if (!mobileMenu) {
                    // Create mobile menu
                    mobileMenu = document.createElement('div');
                    mobileMenu.id = 'mobile-menu';
                    mobileMenu.className = 'fixed inset-0 bg-gray-800 bg-opacity-75 z-50 flex items-center justify-center';
                    
                    // Create menu content container
                    const menuContent = document.createElement('div');
                    menuContent.className = 'bg-white rounded-lg w-11/12 max-w-md p-4 relative';
                    
                    // Add close button
                    const closeButton = document.createElement('button');
                    closeButton.className = 'absolute top-4 right-4 text-gray-500 hover:text-gray-700';
                    closeButton.innerHTML = '<i class="fas fa-times text-xl"></i>';
                    closeButton.addEventListener('click', function() {
                        mobileMenu.classList.add('hidden');
                    });
                    
                    // Check if user is logged in by looking for the logout link
                    const isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
                    
                    // Menu items based on authentication status
                    let menuItems = '';
                    
                    if (isLoggedIn) {
                        const username = "<?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'User'; ?>";
                        
                        menuItems += `
                            <div class="border-b border-gray-200 pb-4 mb-4">
                                <p class="font-semibold text-lg mb-2">Welcome, ${username}</p>
                                <a href="auth/logout.php" class="block w-full text-center bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded transition">
                                    Logout
                                </a>
                            </div>
                        `;
                    } else {
                        menuItems += `
                            <div class="border-b border-gray-200 pb-4 mb-4 flex flex-col space-y-2">
                                <a href="auth/login.php" class="block w-full text-center text-gray-700 hover:text-green-600 py-2 transition">Login</a>
                                <a href="auth/register.php" class="block w-full text-center bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded transition">Register</a>
                            </div>
                        `;
                    }
                    
                    // Add other menu items
                    menuItems += `
                        <div class="space-y-2">
                            <a href="index.php" class="block py-2 text-gray-700 hover:text-green-600">Home</a>
                            <a href="#" class="block py-2 text-gray-700 hover:text-green-600">Categories</a>
                            <a href="#" class="block py-2 text-gray-700 hover:text-green-600">How It Works</a>
                            <a href="#" class="block py-2 text-gray-700 hover:text-green-600">About Us</a>
                            <a href="#" class="block py-2 text-gray-700 hover:text-green-600">Contact</a>
                        </div>
                    `;
                    
                    // Set menu content
                    menuContent.innerHTML = menuItems;
                    menuContent.prepend(closeButton);
                    
                    // Add menu content to mobile menu
                    mobileMenu.appendChild(menuContent);
                    
                    // Add mobile menu to body
                    document.body.appendChild(mobileMenu);
                } else {
                    // Toggle existing mobile menu
                    mobileMenu.classList.toggle('hidden');
                }
            });
        }
    });
    </script>
</body>

</html>
