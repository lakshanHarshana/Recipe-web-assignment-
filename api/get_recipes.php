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

    // Category or Favorites Filter
    if (!empty($category) && $category !== 'All') {
        if ($category === 'Favorites') {
            $fav_ids = [];
            // If logged in, fetch favorites from database table
            if (is_logged_in()) {
                $user = current_user();
                $fav_stmt = $pdo->prepare("SELECT recipe_id FROM favorites WHERE user_id = :user_id");
                $fav_stmt->execute([':user_id' => $user['id']]);
                $fav_ids = $fav_stmt->fetchAll(PDO::FETCH_COLUMN);
            }
            
            // Fallback to URL fav_ids parameter if provided
            if (empty($fav_ids) && isset($_GET['fav_ids'])) {
                $fav_ids_raw = trim($_GET['fav_ids']);
                $fav_ids = array_filter(array_map('intval', explode(',', $fav_ids_raw)), function($v) { return $v > 0; });
            }
            
            if (!empty($fav_ids)) {
                $in_clause = implode(',', array_map('intval', $fav_ids));
                $sql .= " AND r.id IN ($in_clause)";
            } else {
                $sql .= " AND 1=0";
            }
        } else {
            $sql .= " AND r.category = :category";
            $params[':category'] = $category;
        }
    }

    // Keyword Search (searches title, category, ingredients, and instructions)
    if (!empty($search_query)) {
        $sql .= " AND (r.title LIKE :q1 OR r.category LIKE :q2 OR r.ingredients LIKE :q3 OR r.instructions LIKE :q4)";
        $searchTerm = '%' . $search_query . '%';
        $params[':q1'] = $searchTerm;
        $params[':q2'] = $searchTerm;
        $params[':q3'] = $searchTerm;
        $params[':q4'] = $searchTerm;
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
        'message' => handle_db_error($e, 'Failed to fetch recipes.')
    ]);
}
?>
