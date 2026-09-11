<?php
// api/toggle_favorite.php - Database-backed favorites toggler
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Please log in to save favorites to your account.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = current_user();
    $recipe_id = filter_var($_POST['recipe_id'] ?? 0, FILTER_VALIDATE_INT);

    if (!$recipe_id) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid recipe ID specified.']);
        exit;
    }

    try {
        // Check if favorite already exists
        $stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = :user_id AND recipe_id = :recipe_id");
        $stmt->execute([':user_id' => $user['id'], ':recipe_id' => $recipe_id]);
        $fav = $stmt->fetch();

        if ($fav) {
            // Remove favorite
            $del = $pdo->prepare("DELETE FROM favorites WHERE id = :id");
            $del->execute([':id' => $fav['id']]);
            echo json_encode([
                'status' => 'success',
                'is_favorite' => false,
                'message' => 'Recipe removed from account favorites.'
            ]);
        } else {
            // Add favorite
            $ins = $pdo->prepare("INSERT INTO favorites (user_id, recipe_id) VALUES (:user_id, :recipe_id)");
            $ins->execute([':user_id' => $user['id'], ':recipe_id' => $recipe_id]);
            echo json_encode([
                'status' => 'success',
                'is_favorite' => true,
                'message' => 'Recipe saved to account favorites!'
            ]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => handle_db_error($e, 'Failed to update favorites.')
        ]);
    }
}
?>
