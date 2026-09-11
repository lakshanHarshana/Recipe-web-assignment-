<?php
// customer_dashboard.php - Dashboard for Food Lovers / Customers
$page_title = "Customer Portal";
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';

require_login();
$user = current_user();

// If a Chef visits customer_dashboard.php, redirect to Chef Dashboard
if (is_chef()) {
    header("Location: dashboard.php");
    exit;
}

// Fetch user's reviews
try {
    $rev_stmt = $pdo->prepare("SELECT r.*, rec.title, rec.image_url, rec.category 
                               FROM reviews r 
                               JOIN recipes rec ON r.recipe_id = rec.id 
                               WHERE r.user_id = :uid 
                               ORDER BY r.created_at DESC");
    $rev_stmt->execute([':uid' => $user['id']]);
    $my_reviews = $rev_stmt->fetchAll();
} catch (PDOException $e) {
    $my_reviews = [];
}
?>

<div class="container mt-4 mb-5">
    
    <!-- Customer Welcome Banner -->
    <div class="bg-dark text-white p-4 p-md-5 rounded-4 shadow-sm mb-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <div>
            <span class="badge bg-success mb-2 px-3 py-2"><i class="bi bi-heart-fill me-1"></i>Food Lover Portal</span>
            <h1 class="font-heading fw-bold mb-1">Welcome, <?php echo sanitize($user['username']); ?>!</h1>
            <p class="text-light-50 mb-0"><i class="bi bi-envelope me-1"></i><?php echo sanitize($user['email']); ?></p>
        </div>
        <div class="mt-3 mt-md-0 text-md-end">
            <span class="fs-4 fw-bold text-warning"><?php echo count($my_reviews); ?></span>
            <span class="d-block small text-secondary">Reviews Contributed</span>
        </div>
    </div>

    <div class="row g-4">
        
        <!-- Left Column: Quick Recipe Browser & Bookmarks -->
        <div class="col-lg-7">
            <div class="bg-white p-4 rounded-4 shadow-sm border mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="font-heading fw-bold text-dark mb-0"><i class="bi bi-heart-fill text-danger me-2"></i>My Saved Favorites</h4>
                    <a href="index.php?category=Favorites#recipes-section" class="btn btn-outline-danger btn-sm rounded-pill">View All Favorites</a>
                </div>
                <p class="text-muted small">Access your bookmarked signature dishes and quick weeknight meals anytime.</p>
                <div id="customerFavoritesGrid" class="row g-3">
                    <div class="col-12 text-center py-4">
                        <a href="index.php#recipes-section" class="btn btn-accent rounded-pill px-4 py-2"><i class="bi bi-compass me-2"></i>Explore Recipe Catalog</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: My Reviews & Ratings -->
        <div class="col-lg-5">
            <div class="bg-white p-4 rounded-4 shadow-sm border h-100">
                <h4 class="font-heading fw-bold text-dark mb-3"><i class="bi bi-star-fill text-warning me-2"></i>My Posted Reviews</h4>
                <p class="text-muted small mb-4">Feedback and ratings you have shared on community recipes.</p>

                <?php if (empty($my_reviews)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-chat-square-text fs-1 text-muted"></i>
                        <p class="text-muted mt-2">You haven't posted any reviews yet.<br>Click any recipe on the homepage to rate it!</p>
                    </div>
                <?php else: ?>
                    <div class="vstack gap-3" style="max-height: 450px; overflow-y: auto;">
                        <?php foreach ($my_reviews as $rev): ?>
                            <div class="p-3 bg-light rounded-3 border">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <strong class="text-dark"><i class="bi bi-journal-bookmark me-1 text-accent"></i><?php echo sanitize($rev['title']); ?></strong>
                                    <span class="small text-warning"><?php echo str_repeat('⭐', (int)$rev['rating']); ?></span>
                                </div>
                                <p class="small text-muted mb-1 bg-white p-2 rounded border"><?php echo sanitize($rev['comment']); ?></p>
                                <small class="text-secondary fs-8"><i class="bi bi-clock me-1"></i><?php echo date('M d, Y', strtotime($rev['created_at'])); ?></small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
