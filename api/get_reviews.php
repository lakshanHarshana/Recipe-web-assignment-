<?php
// api/get_reviews.php - Fetch Reviews and Average Rating for a Recipe
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$recipe_id = filter_var($_GET['recipe_id'] ?? 0, FILTER_VALIDATE_INT);

if (!$recipe_id) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid recipe ID']);
    exit;
}

try {
    // Fetch individual reviews
    $stmt = $pdo->prepare("SELECT r.*, u.username FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.recipe_id = :recipe_id ORDER BY r.created_at DESC");
    $stmt->execute([':recipe_id' => $recipe_id]);
    $reviews = $stmt->fetchAll();

    // Fetch average rating
    $avg_stmt = $pdo->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as review_count FROM reviews WHERE recipe_id = :recipe_id");
    $avg_stmt->execute([':recipe_id' => $recipe_id]);
    $stats = $avg_stmt->fetch();

    echo json_encode([
        'status' => 'success',
        'avg_rating' => $stats['avg_rating'] ? round($stats['avg_rating'], 1) : 0,
        'review_count' => (int)$stats['review_count'],
        'reviews' => $reviews
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
