<?php
// Set headers to allow cross-origin requests and specify JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Database connection parameters
define('DB_HOST', 'localhost');
define('DB_NAME', 'freelancima');
define('DB_USER', 'root');
define('DB_PASSWORD', '');

// Current timestamp and user
$CURRENT_TIMESTAMP = "2025-05-07 12:46:36";
$CURRENT_USER = "souhail4real";

/**
 * Creates a database connection
 * @return PDO|null Database connection or null on failure
 */
function createConnection() {
    try {
        $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch(PDOException $e) {
        return null;
    }
}

/**
 * Get all freelancers from database grouped by category
 * @param PDO $conn Database connection
 * @return array Freelancers grouped by category
 */
function getAllFreelancers($conn) {
    // SQL query to get all freelancers
    $sql = "
    SELECT 
        id, username, profile_link, profile_image, rating, reviews, 
        short_description, price, category 
    FROM 
        freelancers 
    ORDER BY category ASC, id DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    // Initialize categories
    $categories = [
        'web-development' => [],
        'mobile-development' => [],
        'data-science-ml' => [],
        'cybersecurity' => [],
        'cloud-devops' => []
    ];
    
    // Group freelancers by category
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $category = $row['category'];
        if (isset($categories[$category])) {
            $categories[$category][] = $row;
        }
    }
    
    return $categories;
}

/**
 * Get freelancers by category
 * @param PDO $conn Database connection
 * @param string $category Category to filter by
 * @return array Freelancers in the specified category
 */
function getFreelancersByCategory($conn, $category) {
    // SQL query with parameterized query for category
    $sql = "
    SELECT 
        id, username, profile_link, profile_image, rating, reviews, 
        short_description, price, category 
    FROM 
        freelancers 
    WHERE 
        category = :category
    ORDER BY id DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':category', $category, PDO::PARAM_STR);
    $stmt->execute();
    
    $freelancers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return [$category => $freelancers];
}

/**
 * Search freelancers by keyword
 * @param PDO $conn Database connection
 * @param string $keyword Search keyword
 * @return array Matching freelancers grouped by category
 */
function searchFreelancers($conn, $keyword) {
    // SQL query with parameterized search using LIKE
    $sql = "
    SELECT 
        id, username, profile_link, profile_image, rating, reviews, 
        short_description, price, category 
    FROM 
        freelancers 
    WHERE 
        username LIKE :keyword OR 
        short_description LIKE :keyword
    ORDER BY category ASC, id DESC";
    
    $stmt = $conn->prepare($sql);
    $searchTerm = '%' . $keyword . '%';
    $stmt->bindParam(':keyword', $searchTerm, PDO::PARAM_STR);
    $stmt->execute();
    
    // Initialize categories
    $categories = [
        'web-development' => [],
        'mobile-development' => [],
        'data-science-ml' => [],
        'cybersecurity' => [],
        'cloud-devops' => []
    ];
    
    // Group results by category
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $category = $row['category'];
        if (isset($categories[$category])) {
            $categories[$category][] = $row;
        }
    }
    
    return $categories;
}

/**
 * Get metadata from database
 * @param PDO $conn Database connection
 * @return array Metadata information
 */
function getMetadata($conn) {
    $sql = "
    SELECT 
        last_updated, updated_by, record_count
    FROM 
        metadata
    ORDER BY id DESC
    LIMIT 1";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $metadata = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($metadata) {
        return [
            'last_updated' => $metadata['last_updated'],
            'updated_by' => $metadata['updated_by']
        ];
    }
    
    // Return default metadata if none found
    global $CURRENT_TIMESTAMP, $CURRENT_USER;
    return [
        'last_updated' => $CURRENT_TIMESTAMP,
        'updated_by' => $CURRENT_USER
    ];
}

// Main execution
try {
    $conn = createConnection();
    if (!$conn) {
        throw new Exception("Database connection failed");
    }
    
    // Get parameters
    $action = isset($_GET['action']) ? $_GET['action'] : 'all';
    $category = isset($_GET['category']) ? $_GET['category'] : '';
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    
    // Get metadata
    $metadata = getMetadata($conn);
    
    // Process based on action
    switch ($action) {
        case 'category':
            $categories = getFreelancersByCategory($conn, $category);
            break;
        case 'search':
            $categories = searchFreelancers($conn, $search);
            break;
        default:
            $categories = getAllFreelancers($conn);
            break;
    }
    
    // Build response
    $response = [
        'metadata' => $metadata,
        'categories' => $categories
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    // Handle errors
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}
?>