<?php
// edit_recipe.php - Edit Existing Submitted Recipe
$page_title = "Edit Recipe";
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

require_chef();
$user = current_user();
$recipe_id = filter_var($_GET['id'] ?? ($_POST['recipe_id'] ?? 0), FILTER_VALIDATE_INT);

if (!$recipe_id) {
    set_flash('danger', 'Invalid recipe specified.');
    header("Location: dashboard.php");
    exit;
}

// Fetch existing recipe owned by current user
$stmt = $pdo->prepare("SELECT * FROM recipes WHERE id = :id AND user_id = :user_id");
$stmt->execute([':id' => $recipe_id, ':user_id' => $user['id']]);
$recipe = $stmt->fetch();

if (!$recipe) {
    set_flash('danger', 'Recipe not found or access denied.');
    header("Location: dashboard.php");
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token()) {
        $errors['general'] = 'Invalid or expired security token. Please try submitting again.';
    }

    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $prep_time = filter_var($_POST['prep_time'] ?? 0, FILTER_VALIDATE_INT);
    $cook_time = filter_var($_POST['cook_time'] ?? 0, FILTER_VALIDATE_INT);
    $servings = filter_var($_POST['servings'] ?? 1, FILTER_VALIDATE_INT);
    $difficulty = trim($_POST['difficulty'] ?? 'Medium');
    $ingredients = trim($_POST['ingredients'] ?? '');
    $instructions = trim($_POST['instructions'] ?? '');
    $image_url = trim($_POST['image_url'] ?? '');

    if (empty($title) || strlen($title) < 3) $errors['title'] = 'Recipe title must be at least 3 characters.';
    if (empty($category)) $errors['category'] = 'Please select a category.';
    if ($prep_time === false || $prep_time <= 0) $errors['prep_time'] = 'Prep time must be a positive number greater than 0.';
    if ($cook_time === false || $cook_time < 0) $errors['cook_time'] = 'Valid cook time required.';
    if ($servings === false || $servings < 1) $errors['servings'] = 'Servings must be at least 1.';
    if (empty($ingredients) || strlen($ingredients) < 10) $errors['ingredients'] = 'Ingredients required.';
    if (empty($instructions) || strlen($instructions) < 15) $errors['instructions'] = 'Instructions required.';

    // File Upload Handling
    $final_image_url = $recipe['image_url'];

    if (isset($_FILES['recipe_image']) && $_FILES['recipe_image']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['recipe_image']['tmp_name'];
        $file_name = $_FILES['recipe_image']['name'];
        $file_size = $_FILES['recipe_image']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        if (!in_array($file_ext, $allowed_exts)) {
            $errors['recipe_image'] = 'Invalid file format. Allowed: .jpg, .jpeg, .png, .webp, .gif';
        } elseif ($file_size > 5 * 1024 * 1024) {
            $errors['recipe_image'] = 'File size exceeds 5MB limit.';
        } else {
            $check_img = @getimagesize($file_tmp);
            if ($check_img === false) {
                $errors['recipe_image'] = 'Uploaded file is not a valid image.';
            } else {
                $upload_dir = __DIR__ . '/images/recipes/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                $new_filename = 'recipe_' . time() . '_' . uniqid() . '.' . $file_ext;
                if (move_uploaded_file($file_tmp, $upload_dir . $new_filename)) {
                    $final_image_url = 'images/recipes/' . $new_filename;
                } else {
                    $errors['recipe_image'] = 'Failed to save uploaded image.';
                }
            }
        }
    } elseif (!empty($image_url)) {
        $final_image_url = $image_url;
    }

    if (empty($errors)) {
        try {
            $upd = $pdo->prepare("UPDATE recipes SET title = :t, category = :c, prep_time = :pt, cook_time = :ct, servings = :s, difficulty = :d, ingredients = :ing, instructions = :ins, image_url = :img WHERE id = :id AND user_id = :uid");
            $upd->execute([
                ':t' => $title,
                ':c' => $category,
                ':pt' => $prep_time,
                ':ct' => $cook_time,
                ':s' => $servings,
                ':d' => $difficulty,
                ':ing' => $ingredients,
                ':ins' => $instructions,
                ':img' => $final_image_url,
                ':id' => $recipe['id'],
                ':uid' => $user['id']
            ]);

            set_flash('success', 'Recipe "' . sanitize($title) . '" updated successfully!');
            header("Location: dashboard.php");
            exit;
        } catch (PDOException $e) {
            $errors['general'] = handle_db_error($e, 'Failed to update recipe.');
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container mt-4 mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="bg-white p-4 p-md-5 rounded-4 shadow-sm border">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="font-heading fw-bold text-dark mb-0"><i class="bi bi-pencil-square text-accent me-2"></i>Edit Recipe</h3>
                    <a href="dashboard.php" class="btn btn-outline-secondary btn-sm rounded-pill"><i class="bi bi-arrow-left me-1"></i>Back to Dashboard</a>
                </div>

                <?php if (!empty($errors['general'])): ?>
                    <div class="alert alert-danger mb-4"><?php echo sanitize($errors['general']); ?></div>
                <?php endif; ?>

                <form id="addRecipeForm" action="edit_recipe.php?id=<?php echo (int)$recipe['id']; ?>" method="POST" enctype="multipart/form-data" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <input type="hidden" name="recipe_id" value="<?php echo (int)$recipe['id']; ?>">

                    <div class="mb-3">
                        <label for="title" class="form-label fw-medium">Recipe Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" value="<?php echo sanitize($_POST['title'] ?? $recipe['title']); ?>" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="category" class="form-label fw-medium">Category <span class="text-danger">*</span></label>
                            <select class="form-select" id="category" name="category" required>
                                <?php 
                                $cats = ['Breakfast', 'Italian', 'Asian', 'Dessert', 'Healthy', 'Quick & Easy'];
                                foreach ($cats as $c) {
                                    $sel = (($_POST['category'] ?? $recipe['category']) === $c) ? 'selected' : '';
                                    echo "<option value='$c' $sel>$c</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="difficulty" class="form-label fw-medium">Difficulty Level</label>
                            <select class="form-select" id="difficulty" name="difficulty">
                                <?php 
                                $diffs = ['Easy', 'Medium', 'Hard'];
                                foreach ($diffs as $d) {
                                    $sel = (($_POST['difficulty'] ?? $recipe['difficulty']) === $d) ? 'selected' : '';
                                    echo "<option value='$d' $sel>$d</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label for="prep_time" class="form-label fw-medium">Prep Time (mins)</label>
                            <input type="number" class="form-control" id="prep_time" name="prep_time" value="<?php echo sanitize($_POST['prep_time'] ?? $recipe['prep_time']); ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label for="cook_time" class="form-label fw-medium">Cook Time (mins)</label>
                            <input type="number" class="form-control" id="cook_time" name="cook_time" value="<?php echo sanitize($_POST['cook_time'] ?? $recipe['cook_time']); ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label for="servings" class="form-label fw-medium">Servings</label>
                            <input type="number" class="form-control" id="servings" name="servings" value="<?php echo sanitize($_POST['servings'] ?? $recipe['servings']); ?>" required>
                        </div>
                    </div>

                    <div class="mb-3 p-3 bg-light rounded-3 border">
                        <label class="form-label fw-bold text-dark mb-2">Current Recipe Image</label>
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <img src="<?php echo sanitize($recipe['image_url']); ?>" alt="Current Image" class="rounded-3 object-fit-cover shadow-sm" style="width: 90px; height: 90px;">
                            <small class="text-muted">Upload a new file below to replace this image.</small>
                        </div>

                        <div class="mb-3">
                            <label for="recipe_image" class="form-label small fw-medium text-secondary mb-1">Upload New Image File (.jpg, .png, .webp)</label>
                            <input type="file" class="form-control" id="recipe_image" name="recipe_image" accept="image/*">
                        </div>

                        <div>
                            <label for="image_url" class="form-label small fw-medium text-secondary mb-1">OR Change Web Image URL</label>
                            <input type="url" class="form-control" id="image_url" name="image_url" value="<?php echo sanitize($_POST['image_url'] ?? $recipe['image_url']); ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="ingredients" class="form-label fw-medium">Ingredients (One per line) <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="ingredients" name="ingredients" rows="4" required><?php echo sanitize($_POST['ingredients'] ?? $recipe['ingredients']); ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label for="instructions" class="form-label fw-medium">Step-by-Step Instructions (One per line) <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="instructions" name="instructions" rows="5" required><?php echo sanitize($_POST['instructions'] ?? $recipe['instructions']); ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-accent rounded-pill px-5 py-2 fw-medium w-100">
                        <i class="bi bi-check-circle-fill me-2"></i>Save Recipe Changes
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
