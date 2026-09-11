<?php
// dashboard.php - Authenticated User Dashboard & Recipe Submission Manager
$page_title = "User Dashboard";
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// Force Chef role check
require_chef();

$user = current_user();
$errors = [];
$success_msg = '';

// Handle Recipe Creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_recipe') {
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

    // Server-Side Validation
    if (empty($title) || strlen($title) < 3) {
        $errors['title'] = 'Recipe title must be at least 3 characters.';
    }

    if (empty($category)) {
        $errors['category'] = 'Please select a recipe category.';
    }

    if ($prep_time === false || $prep_time <= 0) {
        $errors['prep_time'] = 'Prep time must be a positive number greater than 0.';
    }

    if ($cook_time === false || $cook_time < 0) {
        $errors['cook_time'] = 'Please enter a valid cook time in minutes.';
    }

    if ($servings === false || $servings < 1) {
        $errors['servings'] = 'Servings must be at least 1.';
    }

    if (empty($ingredients) || strlen($ingredients) < 10) {
        $errors['ingredients'] = 'Please provide ingredients list (at least 10 characters).';
    }

    if (empty($instructions) || strlen($instructions) < 15) {
        $errors['instructions'] = 'Please provide step-by-step instructions (at least 15 characters).';
    }

    // File Upload Handling (.jpg, .png, .webp, .gif)
    $uploaded_image_url = '';
    if (isset($_FILES['recipe_image']) && $_FILES['recipe_image']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['recipe_image']['tmp_name'];
        $file_name = $_FILES['recipe_image']['name'];
        $file_size = $_FILES['recipe_image']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        $allowed_exts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        if (!in_array($file_ext, $allowed_exts)) {
            $errors['recipe_image'] = 'Invalid file format. Allowed: .jpg, .jpeg, .png, .webp, .gif';
        } elseif ($file_size > 5 * 1024 * 1024) {
            $errors['recipe_image'] = 'Uploaded file exceeds maximum size of 5MB.';
        } else {
            $check_img = @getimagesize($file_tmp);
            if ($check_img === false) {
                $errors['recipe_image'] = 'Uploaded file is not a valid image.';
            } else {
                $upload_dir = __DIR__ . '/images/recipes/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }

                $new_filename = 'recipe_' . time() . '_' . uniqid() . '.' . $file_ext;
                $destination = $upload_dir . $new_filename;

                if (move_uploaded_file($file_tmp, $destination)) {
                    $uploaded_image_url = 'images/recipes/' . $new_filename;
                } else {
                    $errors['recipe_image'] = 'Failed to upload image file. Please try again.';
                }
            }
        }
    }

    // Determine final image path: Uploaded File > Image URL Input > Default Placeholder
    if (!empty($uploaded_image_url)) {
        $final_image_url = $uploaded_image_url;
    } elseif (!empty($image_url)) {
        $final_image_url = $image_url;
    } else {
        $final_image_url = 'https://images.unsplash.com/photo-1495521821757-a1efb6729352?auto=format&fit=crop&w=800&q=80';
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO recipes (user_id, title, category, prep_time, cook_time, servings, difficulty, ingredients, instructions, image_url) 
                                   VALUES (:user_id, :title, :category, :prep_time, :cook_time, :servings, :difficulty, :ingredients, :instructions, :image_url)");
            $stmt->execute([
                ':user_id' => $user['id'],
                ':title' => $title,
                ':category' => $category,
                ':prep_time' => $prep_time,
                ':cook_time' => $cook_time,
                ':servings' => $servings,
                ':difficulty' => $difficulty,
                ':ingredients' => $ingredients,
                ':instructions' => $instructions,
                ':image_url' => $final_image_url
            ]);

            set_flash('success', 'Recipe "' . sanitize($title) . '" published successfully!');
            header("Location: dashboard.php");
            exit;
        } catch (PDOException $e) {
            $errors['general'] = handle_db_error($e, 'Failed to publish recipe.');
        }
    }
}

// Handle Recipe Deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_recipe') {
    if (!verify_csrf_token()) {
        set_flash('danger', 'Invalid security token.');
        header("Location: dashboard.php");
        exit;
    }

    $recipe_id = filter_var($_POST['recipe_id'] ?? 0, FILTER_VALIDATE_INT);
    if ($recipe_id) {
        try {
            $stmt = $pdo->prepare("DELETE FROM recipes WHERE id = :id AND user_id = :user_id");
            $stmt->execute([':id' => $recipe_id, ':user_id' => $user['id']]);
            set_flash('info', 'Recipe deleted successfully.');
            header("Location: dashboard.php");
            exit;
        } catch (PDOException $e) {
            $errors['general'] = handle_db_error($e, 'Failed to delete recipe.');
        }
    }
}

// Fetch user's submitted recipes
try {
    $stmt = $pdo->prepare("SELECT * FROM recipes WHERE user_id = :user_id ORDER BY created_at DESC");
    $stmt->execute([':user_id' => $user['id']]);
    $my_recipes = $stmt->fetchAll();
} catch (PDOException $e) {
    $my_recipes = [];
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container mt-4 mb-5">
    
    <!-- User Welcome Header -->
    <div class="bg-dark text-white p-4 p-md-5 rounded-4 shadow-sm mb-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <div>
            <span class="badge bg-accent mb-2 px-3 py-2">Chef Portal</span>
            <h1 class="font-heading fw-bold mb-1">Welcome, <?php echo sanitize($user['username']); ?>!</h1>
            <p class="text-light-50 mb-0"><i class="bi bi-envelope me-1"></i><?php echo sanitize($user['email']); ?></p>
        </div>
        <div class="mt-3 mt-md-0 text-md-end">
            <span class="fs-4 fw-bold text-accent"><?php echo count($my_recipes); ?></span>
            <span class="d-block small text-secondary">Published Recipes</span>
        </div>
    </div>

    <div class="row g-4">
        
        <!-- Left Column: Add Recipe Form (Requirement: User input via form with validation) -->
        <div class="col-lg-7">
            <div class="bg-white p-4 p-md-5 rounded-4 shadow-sm border">
                <h3 class="font-heading fw-bold text-dark mb-3"><i class="bi bi-plus-circle-fill text-accent me-2"></i>Submit New Recipe</h3>
                <p class="text-muted small mb-4">Share your favorite culinary creations with the FlavorCraft community.</p>

                <?php if (!empty($errors['general'])): ?>
                    <div class="alert alert-danger mb-4"><?php echo sanitize($errors['general']); ?></div>
                <?php endif; ?>

                <form id="addRecipeForm" action="dashboard.php" method="POST" enctype="multipart/form-data" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <input type="hidden" name="action" value="add_recipe">

                    <div class="mb-3">
                        <label for="title" class="form-label fw-medium">Recipe Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?php echo isset($errors['title']) ? 'is-invalid-custom' : ''; ?>" id="title" name="title" placeholder="e.g. Creamy Tuscan Garlic Chicken" value="<?php echo sanitize($_POST['title'] ?? ''); ?>" required>
                        <?php if (isset($errors['title'])): ?>
                            <div class="invalid-feedback-custom d-block"><?php echo sanitize($errors['title']); ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="category" class="form-label fw-medium">Category <span class="text-danger">*</span></label>
                            <select class="form-select <?php echo isset($errors['category']) ? 'is-invalid-custom' : ''; ?>" id="category" name="category" required>
                                <option value="" selected disabled>Select Category</option>
                                <option value="Breakfast" <?php echo (($_POST['category'] ?? '') === 'Breakfast') ? 'selected' : ''; ?>>Breakfast</option>
                                <option value="Italian" <?php echo (($_POST['category'] ?? '') === 'Italian') ? 'selected' : ''; ?>>Italian</option>
                                <option value="Asian" <?php echo (($_POST['category'] ?? '') === 'Asian') ? 'selected' : ''; ?>>Asian</option>
                                <option value="Dessert" <?php echo (($_POST['category'] ?? '') === 'Dessert') ? 'selected' : ''; ?>>Dessert</option>
                                <option value="Healthy" <?php echo (($_POST['category'] ?? '') === 'Healthy') ? 'selected' : ''; ?>>Healthy</option>
                                <option value="Quick & Easy" <?php echo (($_POST['category'] ?? '') === 'Quick & Easy') ? 'selected' : ''; ?>>Quick & Easy</option>
                            </select>
                            <?php if (isset($errors['category'])): ?>
                                <div class="invalid-feedback-custom d-block"><?php echo sanitize($errors['category']); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-6">
                            <label for="difficulty" class="form-label fw-medium">Difficulty Level</label>
                            <select class="form-select" id="difficulty" name="difficulty">
                                <option value="Easy" <?php echo (($_POST['difficulty'] ?? '') === 'Easy') ? 'selected' : ''; ?>>Easy</option>
                                <option value="Medium" <?php echo (($_POST['difficulty'] ?? 'Medium') === 'Medium') ? 'selected' : ''; ?>>Medium</option>
                                <option value="Hard" <?php echo (($_POST['difficulty'] ?? '') === 'Hard') ? 'selected' : ''; ?>>Hard</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label for="prep_time" class="form-label fw-medium">Prep Time (mins) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control <?php echo isset($errors['prep_time']) ? 'is-invalid-custom' : ''; ?>" id="prep_time" name="prep_time" min="0" placeholder="15" value="<?php echo sanitize($_POST['prep_time'] ?? '15'); ?>" required>
                            <?php if (isset($errors['prep_time'])): ?>
                                <div class="invalid-feedback-custom d-block"><?php echo sanitize($errors['prep_time']); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-4">
                            <label for="cook_time" class="form-label fw-medium">Cook Time (mins) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control <?php echo isset($errors['cook_time']) ? 'is-invalid-custom' : ''; ?>" id="cook_time" name="cook_time" min="0" placeholder="20" value="<?php echo sanitize($_POST['cook_time'] ?? '20'); ?>" required>
                            <?php if (isset($errors['cook_time'])): ?>
                                <div class="invalid-feedback-custom d-block"><?php echo sanitize($errors['cook_time']); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-4">
                            <label for="servings" class="form-label fw-medium">Servings <span class="text-danger">*</span></label>
                            <input type="number" class="form-control <?php echo isset($errors['servings']) ? 'is-invalid-custom' : ''; ?>" id="servings" name="servings" min="1" placeholder="4" value="<?php echo sanitize($_POST['servings'] ?? '4'); ?>" required>
                            <?php if (isset($errors['servings'])): ?>
                                <div class="invalid-feedback-custom d-block"><?php echo sanitize($errors['servings']); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-3 p-3 bg-light rounded-3 border">
                        <label class="form-label fw-bold text-dark mb-2"><i class="bi bi-image text-accent me-2"></i>Recipe Image</label>
                        
                        <div class="mb-3">
                            <label for="recipe_image" class="form-label small fw-medium text-secondary mb-1">
                                <i class="bi bi-upload me-1"></i>Upload Image File (.jpg, .png, .webp, .gif)
                            </label>
                            <input type="file" class="form-control <?php echo isset($errors['recipe_image']) ? 'is-invalid-custom' : ''; ?>" id="recipe_image" name="recipe_image" accept="image/jpeg,image/png,image/webp,image/gif">
                            <?php if (isset($errors['recipe_image'])): ?>
                                <div class="invalid-feedback-custom d-block"><?php echo sanitize($errors['recipe_image']); ?></div>
                            <?php endif; ?>
                        </div>

                        <div>
                            <label for="image_url" class="form-label small fw-medium text-secondary mb-1">
                                <i class="bi bi-link-45deg me-1"></i>OR Paste Web Image URL
                            </label>
                            <input type="url" class="form-control" id="image_url" name="image_url" placeholder="https://images.unsplash.com/..." value="<?php echo sanitize($_POST['image_url'] ?? ''); ?>">
                        </div>
                        <small class="text-muted d-block mt-2 fs-7">Choose a photo from your computer OR paste an image link. Leave blank for default dish photo.</small>
                    </div>

                    <div class="mb-3">
                        <label for="ingredients" class="form-label fw-medium">Ingredients (One per line) <span class="text-danger">*</span></label>
                        <textarea class="form-control <?php echo isset($errors['ingredients']) ? 'is-invalid-custom' : ''; ?>" id="ingredients" name="ingredients" rows="4" placeholder="400g Pasta&#10;2 tbsp Olive Oil&#10;3 Cloves Garlic, minced" required><?php echo sanitize($_POST['ingredients'] ?? ''); ?></textarea>
                        <?php if (isset($errors['ingredients'])): ?>
                            <div class="invalid-feedback-custom d-block"><?php echo sanitize($errors['ingredients']); ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-4">
                        <label for="instructions" class="form-label fw-medium">Step-by-Step Instructions (One per line) <span class="text-danger">*</span></label>
                        <textarea class="form-control <?php echo isset($errors['instructions']) ? 'is-invalid-custom' : ''; ?>" id="instructions" name="instructions" rows="5" placeholder="1. Boil water in a large pot.&#10;2. Saute garlic in olive oil until fragrant.&#10;3. Combine pasta and sauce." required><?php echo sanitize($_POST['instructions'] ?? ''); ?></textarea>
                        <?php if (isset($errors['instructions'])): ?>
                            <div class="invalid-feedback-custom d-block"><?php echo sanitize($errors['instructions']); ?></div>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="btn btn-accent rounded-pill px-5 py-2 fw-medium w-100">
                        <i class="bi bi-cloud-arrow-up-fill me-2"></i>Publish Recipe
                    </button>
                </form>
            </div>
        </div>

        <!-- Right Column: My Submitted Recipes List -->
        <div class="col-lg-5">
            <div class="bg-white p-4 rounded-4 shadow-sm border h-100">
                <h4 class="font-heading fw-bold text-dark mb-3"><i class="bi bi-journal-bookmark-fill text-accent me-2"></i>My Recipes</h4>
                <p class="text-muted small mb-4">View or remove recipes you have published.</p>

                <?php if (empty($my_recipes)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-journal-x fs-1 text-muted"></i>
                        <p class="text-muted mt-2">You haven't submitted any recipes yet.<br>Use the form to publish your first dish!</p>
                    </div>
                <?php else: ?>
                    <div class="vstack gap-3">
                        <?php foreach ($my_recipes as $rec): ?>
                            <div class="card border rounded-3 p-3 shadow-sm hover-shadow transition">
                                <div class="d-flex gap-3 align-items-center">
                                    <img src="<?php echo !empty($rec['image_url']) ? sanitize($rec['image_url']) : 'https://images.unsplash.com/photo-1495521821757-a1efb6729352?auto=format&fit=crop&w=800&q=80'; ?>" alt="Recipe Thumbnail" class="rounded-3 object-fit-cover" style="width: 70px; height: 70px;">
                                    <div class="flex-grow-1 min-w-0">
                                        <span class="badge bg-light text-dark border mb-1"><?php echo sanitize($rec['category']); ?></span>
                                        <h6 class="fw-bold text-dark text-truncate mb-1"><?php echo sanitize($rec['title']); ?></h6>
                                        <small class="text-muted"><i class="bi bi-clock me-1"></i><?php echo (int)$rec['prep_time'] + (int)$rec['cook_time']; ?> mins | <?php echo sanitize($rec['difficulty']); ?></small>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a href="edit_recipe.php?id=<?php echo (int)$rec['id']; ?>" class="btn btn-outline-primary btn-sm rounded-circle" title="Edit Recipe">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                        <form action="dashboard.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this recipe?');">
                                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                            <input type="hidden" name="action" value="delete_recipe">
                                            <input type="hidden" name="recipe_id" value="<?php echo (int)$rec['id']; ?>">
                                            <button type="submit" class="btn btn-outline-danger btn-sm rounded-circle" title="Delete Recipe">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            </div>

            <!-- Contact Messages Inbox Panel -->
            <?php
            try {
                $msg_stmt = $pdo->prepare("SELECT * FROM messages WHERE recipient_id = :user_id OR recipient_id IS NULL ORDER BY created_at DESC");
                $msg_stmt->execute([':user_id' => $user['id']]);
                $user_messages = $msg_stmt->fetchAll();
            } catch (PDOException $e) {
                $user_messages = [];
            }
            ?>
            <div class="bg-white p-4 rounded-4 shadow-sm border mt-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="font-heading fw-bold text-dark mb-0"><i class="bi bi-inbox-fill text-accent me-2"></i>Contact Submissions</h5>
                    <span class="badge bg-secondary rounded-pill"><?php echo count($user_messages); ?> Inquiry</span>
                </div>
                <p class="text-muted small mb-3">Messages submitted by visitors via Contact Us page.</p>

                <?php if (empty($user_messages)): ?>
                    <p class="text-muted small text-center py-3 mb-0">No messages received yet.</p>
                <?php else: ?>
                    <div class="vstack gap-2" style="max-height: 300px; overflow-y: auto;">
                        <?php foreach ($user_messages as $m): ?>
                            <div class="p-3 bg-light rounded-3 border">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <strong class="text-dark small"><i class="bi bi-person me-1"></i><?php echo sanitize($m['name']); ?></strong>
                                    <small class="text-muted fs-8"><?php echo date('M d, H:i', strtotime($m['created_at'])); ?></small>
                                </div>
                                <div class="small text-secondary mb-1">
                                    <i class="bi bi-envelope me-1"></i><?php echo sanitize($m['email']); ?> | <strong><?php echo sanitize($m['subject']); ?></strong>
                                </div>
                                <p class="small text-muted mb-0 bg-white p-2 rounded border"><?php echo sanitize($m['message']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        </div>

    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
