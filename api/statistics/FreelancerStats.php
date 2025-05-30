<?php
require_once __DIR__ . '/../config.php';

class FreelancerStats {
    private $pdo;
    private $startDate;
    private $endDate;
    private $currentUser;
    public $tableName = 'freelancers'; 
    
    public function __construct($startDate = '2024-05-27 00:00:00', $endDate = '2025-05-28 11:29:25', $currentUser = 'souhail4real') {
        $this->pdo = createConnection(); // Using your existing connection function
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->currentUser = $currentUser;
    }
    
    public function getBasicStats() {
        // Get total count of freelancers (no date filter)
        $queryTotal = "SELECT COUNT(*) as total_freelancers FROM {$this->tableName}";
        $stmtTotal = $this->pdo->prepare($queryTotal);
        $stmtTotal->execute();
        $totalCount = $stmtTotal->fetch(PDO::FETCH_ASSOC);
        
        // Get other stats with date filter
        $query = "
            SELECT 
                ROUND(AVG(rating), 2) as average_rating,
                ROUND(AVG(price), 2) as average_price,
                SUM(reviews) as total_reviews,
                MIN(price) as min_price,
                MAX(price) as max_price
            FROM {$this->tableName}
            WHERE created_at BETWEEN :start_date AND :end_date
        ";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':start_date', $this->startDate);
        $stmt->bindParam(':end_date', $this->endDate);
        $stmt->execute();
        
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Combine results
        return [
            'total_freelancers' => $totalCount['total_freelancers'],
            'average_rating' => $stats['average_rating'],
            'average_price' => $stats['average_price'],
            'total_reviews' => $stats['total_reviews'],
            'min_price' => $stats['min_price'],
            'max_price' => $stats['max_price']
        ];
    }
    
    public function getCategoryStats() {
        // First query to get counts of ALL freelancers by category (no date filter)
        $queryTotal = "
            SELECT 
                category,
                COUNT(*) as freelancer_count
            FROM {$this->tableName}
            GROUP BY category
        ";
        
        $stmtTotal = $this->pdo->prepare($queryTotal);
        $stmtTotal->execute();
        $totalCounts = $stmtTotal->fetchAll(PDO::FETCH_ASSOC);
        
        // Convert to associative array for easy lookup
        $categoryTotals = [];
        foreach ($totalCounts as $count) {
            $categoryTotals[$count['category']] = $count['freelancer_count'];
        }
        
        // Second query to get average price, rating, and reviews within the date range
        $queryStats = "
            SELECT 
                category,
                ROUND(AVG(price), 2) as average_price,
                ROUND(AVG(rating), 2) as average_rating,
                SUM(reviews) as total_reviews
            FROM {$this->tableName}
            WHERE created_at BETWEEN :start_date AND :end_date
            GROUP BY category
        ";
        
        $stmtStats = $this->pdo->prepare($queryStats);
        $stmtStats->bindParam(':start_date', $this->startDate);
        $stmtStats->bindParam(':end_date', $this->endDate);
        $stmtStats->execute();
        $categoryStats = $stmtStats->fetchAll(PDO::FETCH_ASSOC);
        
        // Combine the results
        $result = [];
        foreach ($categoryStats as $stat) {
            $category = $stat['category'];
            $result[] = [
                'category' => $category,
                'freelancer_count' => $categoryTotals[$category] ?? 0, // Use total count regardless of date
                'average_price' => $stat['average_price'],
                'average_rating' => $stat['average_rating'],
                'total_reviews' => $stat['total_reviews']
            ];
        }
        
        // Add any categories that are missing from the date-filtered results
        foreach ($categoryTotals as $category => $count) {
            $found = false;
            foreach ($result as $stat) {
                if ($stat['category'] === $category) {
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                // Query to get avg price, rating for this category regardless of date
                $queryCatStats = "
                    SELECT 
                        ROUND(AVG(price), 2) as average_price,
                        ROUND(AVG(rating), 2) as average_rating,
                        SUM(reviews) as total_reviews
                    FROM {$this->tableName}
                    WHERE category = :category
                ";
                
                $stmtCatStats = $this->pdo->prepare($queryCatStats);
                $stmtCatStats->bindParam(':category', $category);
                $stmtCatStats->execute();
                $catStats = $stmtCatStats->fetch(PDO::FETCH_ASSOC);
                
                $result[] = [
                    'category' => $category,
                    'freelancer_count' => $count,
                    'average_price' => $catStats['average_price'] ?? 0,
                    'average_rating' => $catStats['average_rating'] ?? 0,
                    'total_reviews' => $catStats['total_reviews'] ?? 0
                ];
            }
        }
        
        // Sort by category order
        usort($result, function($a, $b) {
            $order = [
                'data-science-ml' => 1,
                'mobile-development' => 2,
                'cloud-devops' => 3,
                'cybersecurity' => 4
            ];
            
            $orderA = isset($order[$a['category']]) ? $order[$a['category']] : 5;
            $orderB = isset($order[$b['category']]) ? $order[$b['category']] : 5;
            
            return $orderA - $orderB;
        });
        
        return $result;
    }
    
    public function getPriceRangeStats() {
        $query = "
            SELECT 
                CASE 
                    WHEN price < 20 THEN 'Below $20'
                    WHEN price >= 20 AND price < 30 THEN '$20-$29'
                    WHEN price >= 30 AND price < 40 THEN '$30-$39'
                    WHEN price >= 40 AND price < 50 THEN '$40-$49'
                    ELSE '$50 and above'
                END as price_range,
                COUNT(*) as freelancer_count,
                ROUND(AVG(rating), 2) as average_rating
            FROM {$this->tableName}
            WHERE created_at BETWEEN :start_date AND :end_date
            GROUP BY price_range
            ORDER BY 
                CASE 
                    WHEN price_range = 'Below $20' THEN 1
                    WHEN price_range = '$20-$29' THEN 2
                    WHEN price_range = '$30-$39' THEN 3
                    WHEN price_range = '$40-$49' THEN 4
                    WHEN price_range = '$50 and above' THEN 5
                END
        ";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':start_date', $this->startDate);
        $stmt->bindParam(':end_date', $this->endDate);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getMonthlyTrends() {
        $query = "
            SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                COUNT(*) as new_freelancers,
                ROUND(AVG(price), 2) as average_price,
                ROUND(AVG(rating), 2) as average_rating
            FROM {$this->tableName}
            WHERE created_at BETWEEN :start_date AND :end_date
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month
        ";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':start_date', $this->startDate);
        $stmt->bindParam(':end_date', $this->endDate);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getRatingDistribution() {
        $query = "
            SELECT 
                CASE 
                    WHEN rating < 4.0 THEN 'Below 4.0'
                    WHEN rating >= 4.0 AND rating < 4.5 THEN '4.0-4.4'
                    WHEN rating >= 4.5 AND rating < 4.8 THEN '4.5-4.7'
                    WHEN rating >= 4.8 AND rating < 5.0 THEN '4.8-4.9'
                    ELSE '5.0'
                END as rating_range,
                COUNT(*) as freelancer_count,
                ROUND(AVG(price), 2) as average_price
            FROM {$this->tableName}
            WHERE created_at BETWEEN :start_date AND :end_date
            GROUP BY rating_range
            ORDER BY 
                CASE 
                    WHEN rating_range = 'Below 4.0' THEN 1
                    WHEN rating_range = '4.0-4.4' THEN 2
                    WHEN rating_range = '4.5-4.7' THEN 3
                    WHEN rating_range = '4.8-4.9' THEN 4
                    WHEN rating_range = '5.0' THEN 5
                END
        ";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':start_date', $this->startDate);
        $stmt->bindParam(':end_date', $this->endDate);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>