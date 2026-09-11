<?php
// includes/footer.php - Footer component and JavaScript imports
$js_prefix = (basename($_SERVER['PHP_SELF']) == 'register.php' || basename($_SERVER['PHP_SELF']) == 'login.php') ? '../' : '';
?>
    <!-- Global Recipe Details Modal Popup -->
    <div class="modal fade" id="recipeDetailModal" tabindex="-1" aria-labelledby="recipeModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header border-0 bg-dark text-white p-4 position-relative">
                    <div class="pe-4">
                        <span id="modalCategoryBadge" class="badge bg-accent mb-2">Category</span>
                        <h3 class="modal-title font-heading fw-bold" id="recipeModalTitle">Recipe Title</h3>
                        <p class="text-light-50 small mb-0"><i class="bi bi-person me-1"></i> Submitted by <span id="modalAuthor">Author</span></p>
                    </div>
                    <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-4">
                        <div class="col-md-5">
                            <img id="modalImage" src="" alt="Recipe Image" class="img-fluid rounded-3 shadow-sm w-100 object-fit-cover" style="max-height: 280px;">
                            
                            <div class="recipe-meta-grid mt-3 p-3 bg-light rounded-3">
                                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                                    <span><i class="bi bi-clock text-accent me-2"></i>Prep Time</span>
                                    <strong id="modalPrepTime">15 mins</strong>
                                </div>
                                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                                    <span><i class="bi bi-fire text-accent me-2"></i>Cook Time</span>
                                    <strong id="modalCookTime">20 mins</strong>
                                </div>
                                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                                    <span><i class="bi bi-people text-accent me-2"></i>Servings</span>
                                    <strong id="modalServings">4 Servings</strong>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-bar-chart text-accent me-2"></i>Difficulty</span>
                                    <span id="modalDifficulty">Easy</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <h5 class="fw-bold border-bottom pb-2 mb-3 text-dark"><i class="bi bi-basket-fill text-accent me-2"></i>Ingredients</h5>
                            <ul id="modalIngredientsList" class="list-group list-group-flush mb-4">
                                <!-- Populated dynamically by JavaScript -->
                            </ul>

                            <h5 class="fw-bold border-bottom pb-2 mb-3 text-dark"><i class="bi bi-journal-text text-accent me-2"></i>Step-by-Step Instructions</h5>
                            <ol id="modalInstructionsList" class="ps-3 mb-0 text-muted lh-lg">
                                <!-- Populated dynamically by JavaScript -->
                            </ol>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light px-4 py-3">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-accent rounded-pill px-4" onclick="window.print()"><i class="bi bi-printer me-1"></i> Print Recipe</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Back To Top Smooth Scroll Button -->
    <button id="backToTopBtn" class="btn btn-accent rounded-circle shadow position-fixed bottom-0 end-0 m-4 d-none" aria-label="Back to top">
        <i class="bi bi-arrow-up-short fs-3"></i>
    </button>

    <!-- Footer -->
    <footer class="bg-dark text-white pt-5 pb-3 mt-5">
        <div class="container">
            <div class="row g-4 mb-4">
                <div class="col-lg-4 col-md-6">
                    <h5 class="font-heading text-accent mb-3"><i class="bi bi-journal-bookmark-fill me-2"></i>FlavorCraft</h5>
                    <p class="text-secondary small">Your ultimate interactive digital recipe book. Discover delicious recipes, share your culinary creations, and elevate your cooking experience every day.</p>
                    <div class="d-flex gap-3 social-links fs-5 text-secondary">
                        <a href="#" class="text-secondary text-hover-accent"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-secondary text-hover-accent"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="text-secondary text-hover-accent"><i class="bi bi-youtube"></i></a>
                        <a href="#" class="text-secondary text-hover-accent"><i class="bi bi-github"></i></a>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-6">
                    <h6 class="text-white fw-bold mb-3">Quick Links</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><a href="<?php echo get_base_url(); ?>index.php" class="text-secondary text-decoration-none text-hover-accent">Home</a></li>
                        <li class="mb-2"><a href="<?php echo get_base_url(); ?>index.php#recipes-section" class="text-secondary text-decoration-none text-hover-accent">Explore Recipes</a></li>
                        <li class="mb-2"><a href="<?php echo get_base_url(); ?>contact.php" class="text-secondary text-decoration-none text-hover-accent">Contact Support</a></li>
                        <li class="mb-2"><a href="<?php echo get_base_url(); ?>auth/login.php" class="text-secondary text-decoration-none text-hover-accent">User Login</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <h6 class="text-white fw-bold mb-3">Recipe Categories</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><a href="<?php echo get_base_url(); ?>index.php?category=Italian#recipes-section" class="text-secondary text-decoration-none text-hover-accent">Italian Classics</a></li>
                        <li class="mb-2"><a href="<?php echo get_base_url(); ?>index.php?category=Breakfast#recipes-section" class="text-secondary text-decoration-none text-hover-accent">Breakfast & Brunch</a></li>
                        <li class="mb-2"><a href="<?php echo get_base_url(); ?>index.php?category=Asian#recipes-section" class="text-secondary text-decoration-none text-hover-accent">Asian Delights</a></li>
                        <li class="mb-2"><a href="<?php echo get_base_url(); ?>index.php?category=Dessert#recipes-section" class="text-secondary text-decoration-none text-hover-accent">Desserts & Treats</a></li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6">
                    <h6 class="text-white fw-bold mb-3">Course Information</h6>
                    <p class="text-secondary small mb-1"><strong>Course:</strong> ICT 2209 - Web Technologies</p>
                    <p class="text-secondary small mb-1"><strong>Institution:</strong> Rajarata University of Sri Lanka</p>
                    <p class="text-secondary small"><strong>Faculty:</strong> Faculty of Technology, Dept of ICT</p>
                </div>
            </div>
            
            <hr class="border-secondary opacity-25">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center text-secondary small py-2">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> FlavorCraft Digital Recipe Book. All Rights Reserved.</p>
                <p class="mb-0">Designed for ICT 2209 Web Technologies Mini Project.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JavaScript Modules -->
    <script src="<?php echo $js_prefix; ?>js/main.js"></script>
    <script src="<?php echo $js_prefix; ?>js/slider.js"></script>
    <script src="<?php echo $js_prefix; ?>js/search.js"></script>
    <script src="<?php echo $js_prefix; ?>js/validation.js"></script>
</body>
</html>
