<?php
// dashboard.php - Authenticated User Dashboard & Recipe Submission Manager
$page_title = "User Dashboard";
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';

// Force login check
require_login();

$user = current_user();
$errors = [];
$success_msg = '';

// Handle Recipe Creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_recipe') {
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

    if ($prep_time === false || $prep_time < 0) {
        $errors['prep_time'] = 'Please enter a valid prep time in minutes.';
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

    // Default placeholder image if left empty
    if (empty($image_url)) {
        $image_url = 'https://images.unsplash.com/photo-1495521821757-a1efb6729352?auto=format&fit=crop&w=800&q=80';
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
                ':image_url' => $image_url
            ]);

            set_flash('success', 'Recipe "' . sanitize($title) . '" published successfully!');
            header("Location: dashboard.php");
            exit;
        } catch (PDOException $e) {
            $errors['general'] = 'Failed to publish recipe: ' . $e->getMessage();
        }
    }
}

// Handle Recipe Deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_recipe') {
    $recipe_id = filter_var($_POST['recipe_id'] ?? 0, FILTER_VALIDATE_INT);
    if ($recipe_id) {
        try {
            $stmt = $pdo->prepare("DELETE FROM recipes WHERE id = :id AND user_id = :user_id");
            $stmt->execute([':id' => $recipe_id, ':user_id' => $user['id']]);
            set_flash('info', 'Recipe deleted successfully.');
            header("Location: dashboard.php");
            exit;
        } catch (PDOException $e) {
            $errors['general'] = 'Failed to delete recipe: ' . $e->getMessage();
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

                <form id="addRecipeForm" action="dashboard.php" method="POST" novalidate>
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

                    <div class="mb-3">
                        <label for="image_url" class="form-label fw-medium">Recipe Image URL</label>
                        <input type="url" class="form-control" id="image_url" name="image_url" placeholder="https://images.unsplash.com/..." value="<?php echo sanitize($_POST['image_url'] ?? ''); ?>">
                        <small class="text-muted">Paste an online image URL. Leave blank for default placeholder.</small>
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
                                    <div>
                                        <form action="dashboard.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this recipe?');">
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
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
