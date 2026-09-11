<?php
// index.php - Main Landing Page & Recipe Book Catalog
$page_title = "Home - Explore Culinary Delights";
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';

// Fetch initial recipes from MySQL database
try {
    $stmt = $pdo->query("SELECT r.*, u.username 
                         FROM recipes r 
                         LEFT JOIN users u ON r.user_id = u.id 
                         ORDER BY r.created_at DESC");
    $recipes = $stmt->fetchAll();
} catch (PDOException $e) {
    $recipes = [];
}

// Fetch available categories for filter pills
$categories = ['All', 'Breakfast', 'Italian', 'Asian', 'Dessert', 'Healthy', 'Quick & Easy'];
?>

<div class="container mt-4 mb-5">

    <!-- Hero Image Slider Section (Requirement 2.3: Interactive Image Slider) -->
    <div id="heroSliderContainer" class="hero-slider-section mb-5">
        
        <!-- Slide 1 -->
        <div class="hero-slide" style="background-image: url('https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=1200&q=80');">
            <div class="hero-overlay p-4 p-md-5">
                <div class="hero-content">
                    <span class="badge bg-accent mb-2 px-3 py-2 text-uppercase fs-6">Featured Recipe</span>
                    <h1 class="display-4 font-heading fw-bold mb-3">Master the Art of Authentic Cooking</h1>
                    <p class="lead mb-4">Discover handcrafted recipes curated by passionate food lovers across the world.</p>
                    <a href="#recipes-section" class="btn btn-accent rounded-pill px-4 py-2 fw-medium">
                        <i class="bi bi-compass me-2"></i>Explore Recipes
                    </a>
                </div>
            </div>
        </div>

        <!-- Slide 2 -->
        <div class="hero-slide" style="background-image: url('https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=1200&q=80');">
            <div class="hero-overlay p-4 p-md-5">
                <div class="hero-content">
                    <span class="badge bg-warning text-dark mb-2 px-3 py-2 text-uppercase fs-6">Healthy Living</span>
                    <h1 class="display-4 font-heading fw-bold mb-3">Fresh & Wholesome Meal Bowls</h1>
                    <p class="lead mb-4">Nourish your body with quick, vibrant, and nutrient-dense recipes made in under 20 minutes.</p>
                    <a href="#recipes-section" class="btn btn-light rounded-pill px-4 py-2 fw-medium text-dark">
                        <i class="bi bi-egg-fried me-2"></i>View Healthy Recipes
                    </a>
                </div>
            </div>
        </div>

        <!-- Slide 3 -->
        <div class="hero-slide" style="background-image: url('https://images.unsplash.com/photo-1578985545062-69928b1d9587?auto=format&fit=crop&w=1200&q=80');">
            <div class="hero-overlay p-4 p-md-5">
                <div class="hero-content">
                    <span class="badge bg-accent mb-2 px-3 py-2 text-uppercase fs-6">Sweet Temptations</span>
                    <h1 class="display-4 font-heading fw-bold mb-3">Indulgent Artisan Desserts</h1>
                    <p class="lead mb-4">From rich lava cakes to fresh berry tarts, satisfy your sweet tooth effortlessly.</p>
                    <a href="#recipes-section" class="btn btn-accent rounded-pill px-4 py-2 fw-medium">
                        <i class="bi bi-heart-fill me-2"></i>Browse Desserts
                    </a>
                </div>
            </div>
        </div>

        <!-- Interactive Slider Controls (Manual Next/Prev & Auto-Play Pause) -->
        <div class="slider-controls">
            <button type="button" class="slider-prev" aria-label="Previous Slide" title="Previous Slide">
                <i class="bi bi-chevron-left"></i>
            </button>
            <button type="button" class="slider-pause" aria-label="Pause Auto-play" title="Toggle Auto-play">
                <i class="bi bi-pause-fill"></i>
            </button>
            <button type="button" class="slider-next" aria-label="Next Slide" title="Next Slide">
                <i class="bi bi-chevron-right"></i>
            </button>
        </div>

        <!-- Slider Indicators -->
        <div class="slider-indicators position-absolute bottom-0 start-50 translate-middle-x mb-3 d-flex gap-2 z-3"></div>
    </div>

    <!-- Quick Stats Banner -->
    <div class="row g-4 mb-5 text-center">
        <div class="col-6 col-md-3">
            <div class="p-3 bg-white rounded-4 shadow-sm border">
                <i class="bi bi-journal-check text-accent fs-1 mb-2"></i>
                <h3 class="fw-bold mb-0"><?php echo count($recipes); ?>+</h3>
                <small class="text-muted">Tested Recipes</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="p-3 bg-white rounded-4 shadow-sm border">
                <i class="bi bi-people-fill text-accent fs-1 mb-2"></i>
                <h3 class="fw-bold mb-0">150+</h3>
                <small class="text-muted">Community Chefs</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="p-3 bg-white rounded-4 shadow-sm border">
                <i class="bi bi-grid-fill text-accent fs-1 mb-2"></i>
                <h3 class="fw-bold mb-0"><?php echo count($categories); ?></h3>
                <small class="text-muted">Cuisine Categories</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="p-3 bg-white rounded-4 shadow-sm border">
                <i class="bi bi-star-fill text-warning fs-1 mb-2"></i>
                <h3 class="fw-bold mb-0">4.9/5</h3>
                <small class="text-muted">User Satisfaction</small>
            </div>
        </div>
    </div>

    <!-- Recipe Search & Category Filter Section (Requirement 2.2 & 2.3: Dynamic Search) -->
    <section id="recipes-section" class="mb-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div>
                <h2 class="font-heading fw-bold text-dark mb-1">Explore Recipes</h2>
                <p class="text-muted mb-0">Search by dish name, ingredient keyword, or filter by category.</p>
            </div>
            <div>
                <span id="recipeCountBadge" class="badge bg-secondary px-3 py-2 fs-6 rounded-pill">
                    <?php echo count($recipes); ?> Recipes Available
                </span>
            </div>
        </div>

        <!-- Interactive Search Container -->
        <div class="search-container mb-4">
            <div class="row g-3 align-items-center">
                <div class="col-md-7">
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" id="searchInput" class="form-control border-start-0 ps-0" placeholder="Search recipes by title, ingredients (e.g. Avocado, Chicken, Pasta)...">
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                        <?php foreach ($categories as $index => $cat): ?>
                            <button type="button" class="category-filter-btn <?php echo ($index === 0) ? 'active' : ''; ?>" data-category="<?php echo $cat; ?>">
                                <?php echo $cat; ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Dynamic Recipe Grid Container (Populated via PHP & dynamic JS Search) -->
    <div class="row g-4" id="recipeGridContainer">
        <?php if (empty($recipes)): ?>
            <div class="col-12 text-center py-5">
                <i class="bi bi-emoji-frown fs-1 text-muted"></i>
                <h4 class="mt-3">No recipes found in database</h4>
                <p class="text-muted">Import <code>database.sql</code> or submit your first recipe via the dashboard!</p>
            </div>
        <?php else: ?>
            <?php foreach ($recipes as $recipe): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card recipe-card h-100 shadow-sm" onclick='openRecipeModal(<?php echo json_encode($recipe, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>)'>
                        <div class="recipe-card-img-wrapper">
                            <span class="category-pill"><?php echo sanitize($recipe['category']); ?></span>
                            <img src="<?php echo !empty($recipe['image_url']) ? sanitize($recipe['image_url']) : 'https://images.unsplash.com/photo-1495521821757-a1efb6729352?auto=format&fit=crop&w=800&q=80'; ?>" alt="<?php echo sanitize($recipe['title']); ?>" loading="lazy">
                        </div>
                        <div class="card-body d-flex flex-column p-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="recipe-meta"><i class="bi bi-clock me-1 text-accent"></i><?php echo (int)$recipe['prep_time'] + (int)$recipe['cook_time']; ?> mins</span>
                                <?php echo get_difficulty_badge($recipe['difficulty']); ?>
                            </div>
                            <h5 class="card-title font-heading fw-bold text-dark mb-2"><?php echo sanitize($recipe['title']); ?></h5>
                            <p class="card-text text-muted small flex-grow-1">
                                <?php echo sanitize(substr($recipe['ingredients'], 0, 95)); ?>...
                            </p>
                            <div class="pt-3 border-top d-flex justify-content-between align-items-center mt-auto">
                                <small class="text-secondary"><i class="bi bi-person me-1"></i><?php echo sanitize($recipe['username'] ?? 'Chef'); ?></small>
                                <button class="btn btn-sm btn-outline-accent rounded-pill">
                                    View Recipe <i class="bi bi-arrow-right ms-1"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Call to Action Banner -->
    <div class="bg-dark text-white rounded-4 p-5 mt-5 text-center position-relative overflow-hidden shadow">
        <div class="position-relative z-2 max-w-600 mx-auto">
            <h2 class="font-heading display-6 fw-bold mb-3">Have a Unique Recipe to Share?</h2>
            <p class="lead mb-4 text-light-50">Join our growing community of food enthusiasts! Submit your signature recipes, inspire home cooks, and manage your culinary collection.</p>
            <?php if (is_logged_in()): ?>
                <a href="<?php echo get_base_url(); ?>dashboard.php" class="btn btn-accent rounded-pill btn-lg px-5">
                    <i class="bi bi-plus-circle-fill me-2"></i>Add Your Recipe Now
                </a>
            <?php else: ?>
                <a href="<?php echo get_base_url(); ?>auth/register.php" class="btn btn-accent rounded-pill btn-lg px-5">
                    <i class="bi bi-person-plus-fill me-2"></i>Create Free Account
                </a>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
