<?php
// api/get_recipes.php - JSON API Endpoint for Live Recipe Search and Category Filtering
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : 'All';

try {
    $sql = "SELECT r.*, u.username 
            FROM recipes r 
            LEFT JOIN users u ON r.user_id = u.id 
            WHERE 1=1";
    
    $params = [];

    // Category Filter
    if (!empty($category) && $category !== 'All') {
        $sql .= " AND r.category = :category";
        $params[':category'] = $category;
    }

    // Keyword Search (searches title, category, ingredients, and instructions)
    if (!empty($search_query)) {
        $sql .= " AND (r.title LIKE :query OR r.category LIKE :query OR r.ingredients LIKE :query OR r.instructions LIKE :query)";
        $params[':query'] = '%' . $search_query . '%';
    }

    $sql .= " ORDER BY r.created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $recipes = $stmt->fetchAll();

    echo json_encode([
        'status' => 'success',
        'count' => count($recipes),
        'recipes' => $recipes
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch recipes: ' . $e->getMessage()
    ]);
}
?>
