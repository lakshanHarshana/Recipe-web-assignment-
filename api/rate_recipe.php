<?php
// api/rate_recipe.php - Submit Star Rating & Review
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Please log in to leave a review.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token()) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Invalid or expired security token. Please refresh the page and try again.']);
        exit;
    }

    $user = current_user();
    $recipe_id = filter_var($_POST['recipe_id'] ?? 0, FILTER_VALIDATE_INT);
    $rating = filter_var($_POST['rating'] ?? 5, FILTER_VALIDATE_INT);
    $comment = trim($_POST['comment'] ?? '');

    if (!$recipe_id || $rating < 1 || $rating > 5) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid recipe or rating score.']);
        exit;
    }

    try {
        // Verify target recipe actually exists in database
        $recipeCheck = $pdo->prepare("SELECT id FROM recipes WHERE id = :id");
        $recipeCheck->execute([':id' => $recipe_id]);
        if (!$recipeCheck->fetch()) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Recipe not found.']);
            exit;
        }

        // Upsert review (update if user already reviewed this recipe, else insert)
        $stmt = $pdo->prepare("SELECT id FROM reviews WHERE recipe_id = :recipe_id AND user_id = :user_id");
        $stmt->execute([':recipe_id' => $recipe_id, ':user_id' => $user['id']]);
        $existing = $stmt->fetch();

        if ($existing) {
            $update = $pdo->prepare("UPDATE reviews SET rating = :rating, comment = :comment, created_at = NOW() WHERE id = :id");
            $update->execute([':rating' => $rating, ':comment' => $comment, ':id' => $existing['id']]);
        } else {
            $insert = $pdo->prepare("INSERT INTO reviews (recipe_id, user_id, rating, comment) VALUES (:recipe_id, :user_id, :rating, :comment)");
            $insert->execute([':recipe_id' => $recipe_id, ':user_id' => $user['id'], ':rating' => $rating, ':comment' => $comment]);
        }

        // Fetch updated average rating and count
        $avg_stmt = $pdo->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as review_count FROM reviews WHERE recipe_id = :recipe_id");
        $avg_stmt->execute([':recipe_id' => $recipe_id]);
        $stats = $avg_stmt->fetch();

        echo json_encode([
            'status' => 'success',
            'message' => 'Thank you for your rating!',
            'avg_rating' => round($stats['avg_rating'], 1),
            'review_count' => (int)$stats['review_count']
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => handle_db_error($e, 'Failed to save review.')]);
    }
}
?>
