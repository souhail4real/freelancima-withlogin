<?php require_once './includes/header.php'; ?>
<?php
require_once 'api/statistics/FreelancerStats.php';

// Set the date range (default is one year)
$startDate = '2024-05-27 00:00:00';
$endDate = '2025-05-28 11:29:25'; // Using your provided timestamp

// You can make these dates dynamic based on user input
if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
    $startDate = $_GET['start_date'] . ' 00:00:00';
    $endDate = $_GET['end_date'] . ' 23:59:59';
}

// Initialize the statistics class
$stats = new FreelancerStats($startDate, $endDate, 'souhail4real');

// Get all the statistics data
$basicStats = $stats->getBasicStats();
$categoryStats = $stats->getCategoryStats();
$priceRangeStats = $stats->getPriceRangeStats();
$monthlyTrends = $stats->getMonthlyTrends();
$ratingDistribution = $stats->getRatingDistribution();

// Format the date range for display
$displayStartDate = date('M d, Y', strtotime($startDate));
$displayEndDate = date('M d, Y', strtotime($endDate));

// Check if the user is logged in
$userLoggedIn = isset($_SESSION['user_id']) || CURRENT_USER !== '';
$currentUser = CURRENT_USER;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FreeLanci.ma - Statistics Dashboard</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Heroicons (optional replacement for Font Awesome) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@heroicons/react/solid/icons.css">
    <!-- Chart.js (kept as is) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Configure Tailwind theme colors -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#3498db',
                        secondary: '#2ecc71',
                        accent: '#f39c12',
                        textColor: '#34495e',
                        lightGray: '#f5f5f5',
                    }
                }
            }
        }
    </script>
</head>
<body class="font-sans bg-gray-100 text-gray-800">
    <!-- Dashboard Header -->
    <div class="bg-gradient-to-r from-blue-500 to-green-500 text-white py-8 mb-8 rounded-b-3xl relative">
        <div class="max-w-7xl mx-auto px-4">
            <h1 class="mb-2 text-center text-3xl font-bold">FreeLanci.ma Statistics Dashboard</h1>
            
            <?php if ($userLoggedIn): ?>
                <p class="text-center text-lg opacity-90 mt-2">Welcome, <span class="font-semibold"><?php echo htmlspecialchars($_SESSION['username']); ?></span></p>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="max-w-7xl mx-auto px-4 pb-20">
        <!-- Date Range Filter -->
        <div class="mb-8">
            <div class="mx-auto max-w-2xl">
                <div class="bg-white rounded-xl shadow-md">
                    <div class="p-6">
                        <form method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-4">
                            <div class="md:col-span-5">
                                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                                <input type="date" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" id="start_date" name="start_date" value="<?= date('Y-m-d', strtotime($startDate)) ?>">
                            </div>
                            <div class="md:col-span-5">
                                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                                <input type="date" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" id="end_date" name="end_date" value="<?= date('Y-m-d', strtotime($endDate)) ?>">
                            </div>
                            <div class="md:col-span-2 flex items-end">
                                <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-md transition duration-200 ease-in-out">Filter</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Basic Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            <div class="bg-blue-500 text-white rounded-xl shadow-md overflow-hidden transform transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
                <div class="p-6 text-center">
                    <h5 class="text-lg font-semibold mb-2">Total Freelancers</h5>
                    <h2 class="text-4xl font-bold"><?= $basicStats['total_freelancers'] ?></h2>
                </div>
            </div>
            <div class="bg-green-500 text-white rounded-xl shadow-md overflow-hidden transform transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
                <div class="p-6 text-center">
                    <h5 class="text-lg font-semibold mb-2">Average Rating</h5>
                    <h2 class="text-4xl font-bold"><?= $basicStats['average_rating'] ?></h2>
                </div>
            </div>
            <div class="bg-amber-500 text-white rounded-xl shadow-md overflow-hidden transform transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
                <div class="p-6 text-center">
                    <h5 class="text-lg font-semibold mb-2">Average Price</h5>
                    <h2 class="text-4xl font-bold">$<?= $basicStats['average_price'] ?></h2>
                </div>
            </div>
        </div>
        
        <!-- Additional Stats Row -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">
            <div class="bg-white rounded-xl shadow-md overflow-hidden transform transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
                <div class="p-6 text-center">
                    <h5 class="text-lg text-gray-500 font-semibold mb-2">Total Reviews</h5>
                    <h3 class="text-2xl font-bold"><?= number_format($basicStats['total_reviews']) ?></h3>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-md overflow-hidden transform transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
                <div class="p-6 text-center">
                    <h5 class="text-lg text-gray-500 font-semibold mb-2">Price Range</h5>
                    <h3 class="text-2xl font-bold">$<?= $basicStats['min_price'] ?> - $<?= $basicStats['max_price'] ?></h3>
                </div>
            </div>
        </div>
        
        <!-- Category Distribution Chart -->
        <div class="mb-10">
            <div class="bg-white rounded-xl shadow-md overflow-hidden transform transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h5 class="text-xl font-semibold">Freelancers by Category</h5>
                </div>
                <div class="p-6">
                    <div class="h-80 mb-6">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Price Range & Rating Distribution Charts -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">
            <div class="bg-white rounded-xl shadow-md overflow-hidden transform transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h5 class="text-xl font-semibold">Price Range Distribution</h5>
                </div>
                <div class="p-6">
                    <div class="h-80 mb-6">
                        <canvas id="priceRangeChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-md overflow-hidden transform transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h5 class="text-xl font-semibold">Rating Distribution</h5>
                </div>
                <div class="p-6">
                    <div class="h-80 mb-6">
                        <canvas id="ratingChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Monthly Trends Chart -->
        <div class="mb-10">
            <div class="bg-white rounded-xl shadow-md overflow-hidden transform transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h5 class="text-xl font-semibold">Monthly Trends</h5>
                </div>
                <div class="p-6">
                    <div class="h-80 mb-6">
                        <canvas id="monthlyTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Data Tables -->
        <div>
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h5 class="text-xl font-semibold">Category Details</h5>
                </div>
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Freelancers</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avg. Price</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avg. Rating</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Reviews</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($categoryStats as $category): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($category['category']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $category['freelancer_count'] ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">$<?= $category['average_price'] ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $category['average_rating'] ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $category['total_reviews'] ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Category Chart
        const categoryChart = new Chart(
            document.getElementById('categoryChart'),
            {
                type: 'bar',
                data: {
                    labels: <?= json_encode(array_column($categoryStats, 'category')) ?>,
                    datasets: [
                        {
                            label: 'Number of Freelancers',
                            data: <?= json_encode(array_column($categoryStats, 'freelancer_count')) ?>,
                            backgroundColor: 'rgba(59, 130, 246, 0.5)',
                            borderColor: 'rgb(59, 130, 246)',
                            borderWidth: 1
                        },
                        {
                            label: 'Average Price ($)',
                            data: <?= json_encode(array_column($categoryStats, 'average_price')) ?>,
                            backgroundColor: 'rgba(239, 68, 68, 0.5)',
                            borderColor: 'rgb(239, 68, 68)',
                            borderWidth: 1,
                            type: 'line',
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Number of Freelancers'
                            }
                        },
                        y1: {
                            beginAtZero: true,
                            position: 'right',
                            grid: {
                                drawOnChartArea: false
                            },
                            title: {
                                display: true,
                                text: 'Average Price ($)'
                            }
                        }
                    }
                }
            }
        );

        // Price Range Chart
        const priceRangeChart = new Chart(
            document.getElementById('priceRangeChart'),
            {
                type: 'pie',
                data: {
                    labels: <?= json_encode(array_column($priceRangeStats, 'price_range')) ?>,
                    datasets: [{
                        data: <?= json_encode(array_column($priceRangeStats, 'freelancer_count')) ?>,
                        backgroundColor: [
                            'rgba(239, 68, 68, 0.7)',
                            'rgba(59, 130, 246, 0.7)',
                            'rgba(250, 204, 21, 0.7)',
                            'rgba(16, 185, 129, 0.7)',
                            'rgba(139, 92, 246, 0.7)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right'
                        }
                    }
                }
            }
        );
        
        // Rating Distribution Chart
        const ratingChart = new Chart(
            document.getElementById('ratingChart'),
            {
                type: 'doughnut',
                data: {
                    labels: <?= json_encode(array_column($ratingDistribution, 'rating_range')) ?>,
                    datasets: [{
                        data: <?= json_encode(array_column($ratingDistribution, 'freelancer_count')) ?>,
                        backgroundColor: [
                            'rgba(239, 68, 68, 0.7)',
                            'rgba(59, 130, 246, 0.7)',
                            'rgba(250, 204, 21, 0.7)',
                            'rgba(16, 185, 129, 0.7)',
                            'rgba(139, 92, 246, 0.7)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right'
                        }
                    }
                }
            }
        );

        // Monthly Trend Chart
        const monthlyTrendChart = new Chart(
            document.getElementById('monthlyTrendChart'),
            {
                type: 'line',
                data: {
                    labels: <?= json_encode(array_column($monthlyTrends, 'month')) ?>,
                    datasets: [
                        {
                            label: 'New Freelancers',
                            data: <?= json_encode(array_column($monthlyTrends, 'new_freelancers')) ?>,
                            backgroundColor: 'rgba(16, 185, 129, 0.5)',
                            borderColor: 'rgb(16, 185, 129)',
                            borderWidth: 2,
                            tension: 0.2
                        },
                        {
                            label: 'Average Price',
                            data: <?= json_encode(array_column($monthlyTrends, 'average_price')) ?>,
                            backgroundColor: 'rgba(251, 146, 60, 0.5)',
                            borderColor: 'rgb(251, 146, 60)',
                            borderWidth: 2,
                            tension: 0.2,
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'New Freelancers'
                            }
                        },
                        y1: {
                            beginAtZero: true,
                            position: 'right',
                            grid: {
                                drawOnChartArea: false
                            },
                            title: {
                                display: true,
                                text: 'Average Price ($)'
                            }
                        }
                    }
                }
            }
        );
    </script>
</body>
</html>
<?php require_once './includes/footer.php'; ?>