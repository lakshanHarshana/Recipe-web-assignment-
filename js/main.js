/**
 * js/main.js - Interactive UI Engine
 * Features: Servings Scaler, Kitchen Timer, Star Ratings/Reviews, Favorites, Theme Toggle, Modal Binder
 */

let currentRecipeData = null;
let baseServings = 4;
let currentServings = 4;
let timerInterval = null;
let timerSeconds = 900; // 15 mins default

document.addEventListener('DOMContentLoaded', () => {
    initThemeToggle();
    initBackToTop();
    initSmoothScroll();
    initTooltips();
    initServingsScaler();
    initKitchenTimer();
    initReviewForm();
    initFavoriteButtons();
    loadCustomerFavorites();
});

function initFavoriteButtons() {
    const favs = getFavorites();
    document.querySelectorAll('.fav-badge-btn').forEach(btn => {
        const id = parseInt(btn.dataset.recipeId);
        if (id && favs.includes(id)) {
            btn.innerHTML = '<i class="bi bi-heart-fill text-danger"></i>';
        } else {
            btn.innerHTML = '<i class="bi bi-heart"></i>';
        }
    });
}

function loadCustomerFavorites() {
    const grid = document.getElementById('customerFavoritesGrid');
    if (!grid) return;

    const favs = getFavorites();
    if (!favs || favs.length === 0) {
        grid.innerHTML = `
            <div class="col-12 text-center py-4">
                <i class="bi bi-heartbreak text-muted fs-1 d-block mb-2"></i>
                <p class="text-muted small">You haven't bookmarked any favorite recipes yet.</p>
                <a href="index.php#recipes-section" class="btn btn-accent rounded-pill btn-sm px-4 py-2"><i class="bi bi-compass me-1"></i>Explore Catalog & Bookmark Recipes</a>
            </div>
        `;
        return;
    }

    grid.innerHTML = '<div class="col-12 text-center py-3"><div class="spinner-border spinner-border-sm text-accent"></div> Loading your favorites...</div>';

    fetch(`api/get_recipes.php?category=Favorites&fav_ids=${favs.join(',')}`)
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success' && data.recipes.length > 0) {
                grid.innerHTML = '';
                data.recipes.forEach(recipe => {
                    const col = document.createElement('div');
                    col.className = 'col-md-6 mb-3';
                    const imgUrl = recipe.image_url || 'https://images.unsplash.com/photo-1495521821757-a1efb6729352?auto=format&fit=crop&w=800&q=80';
                    col.innerHTML = `
                        <div class="card border rounded-3 p-2 shadow-sm h-100 hover-shadow transition cursor-pointer" onclick='openRecipeModal(${JSON.stringify(recipe)})'>
                            <div class="d-flex gap-3 align-items-center">
                                <img src="${escapeHtml(imgUrl)}" alt="${escapeHtml(recipe.title)}" class="rounded-3 object-fit-cover" style="width: 65px; height: 65px;">
                                <div class="flex-grow-1 min-w-0">
                                    <span class="badge bg-light text-dark border mb-1">${escapeHtml(recipe.category)}</span>
                                    <h6 class="fw-bold text-dark text-truncate mb-1" style="font-size: 0.95rem;">${escapeHtml(recipe.title)}</h6>
                                    <small class="text-muted"><i class="bi bi-clock me-1 text-accent"></i>${recipe.prep_time + recipe.cook_time} mins</small>
                                </div>
                            </div>
                        </div>
                    `;
                    grid.appendChild(col);
                });
            } else {
                grid.innerHTML = `
                    <div class="col-12 text-center py-4">
                        <i class="bi bi-heartbreak text-muted fs-1 d-block mb-2"></i>
                        <p class="text-muted small">No saved recipes found.</p>
                        <a href="index.php#recipes-section" class="btn btn-accent rounded-pill btn-sm px-4 py-2"><i class="bi bi-compass me-1"></i>Browse Recipes</a>
                    </div>
                `;
            }
        })
        .catch(err => {
            console.error('Error loading customer favorites:', err);
        });
}

/**
 * Dark / Light Mode Theme Toggle
 */
function initThemeToggle() {
    const themeBtn = document.getElementById('themeToggleBtn');
    if (!themeBtn) return;

    const savedTheme = localStorage.getItem('flavorcraft_theme') || 'light';
    applyTheme(savedTheme);

    themeBtn.addEventListener('click', () => {
        const currentTheme = document.body.classList.contains('dark-mode') ? 'dark' : 'light';
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        applyTheme(newTheme);
        localStorage.setItem('flavorcraft_theme', newTheme);
    });
}

function applyTheme(theme) {
    const themeBtn = document.getElementById('themeToggleBtn');
    if (theme === 'dark') {
        document.body.classList.add('dark-mode');
        if (themeBtn) themeBtn.innerHTML = '<i class="bi bi-sun-fill text-warning"></i>';
    } else {
        document.body.classList.remove('dark-mode');
        if (themeBtn) themeBtn.innerHTML = '<i class="bi bi-moon-stars-fill"></i>';
    }
}

/**
 * Back to Top Floating Button
 */
function initBackToTop() {
    const backToTopBtn = document.getElementById('backToTopBtn');
    if (!backToTopBtn) return;

    window.addEventListener('scroll', () => {
        if (window.scrollY > 300) {
            backToTopBtn.classList.remove('d-none');
        } else {
            backToTopBtn.classList.add('d-none');
        }
    });

    backToTopBtn.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
}

function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            if (targetId === '#' || !targetId.startsWith('#')) return;
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                e.preventDefault();
                targetElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
}

function initTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

/**
 * Interactive Servings Scaler (+ / - buttons)
 */
function initServingsScaler() {
    const btnUp = document.getElementById('btnScaleUp');
    const btnDown = document.getElementById('btnScaleDown');

    if (btnUp) {
        btnUp.addEventListener('click', () => {
            currentServings++;
            updateServingsAndIngredients();
        });
    }

    if (btnDown) {
        btnDown.addEventListener('click', () => {
            if (currentServings > 1) {
                currentServings--;
                updateServingsAndIngredients();
            }
        });
    }
}

function updateServingsAndIngredients() {
    const servingsEl = document.getElementById('modalServings');
    if (servingsEl) servingsEl.textContent = currentServings;

    if (!currentRecipeData || !currentRecipeData.ingredients) return;

    const ratio = currentServings / baseServings;
    const ingredientsList = document.getElementById('modalIngredientsList');
    ingredientsList.innerHTML = '';

    const lines = currentRecipeData.ingredients.split('\n').filter(l => l.trim().length > 0);
    lines.forEach(line => {
        const scaledLine = scaleIngredientLine(line.trim(), ratio);
        const li = document.createElement('li');
        li.className = 'list-group-item bg-transparent ps-0 d-flex align-items-center';
        li.innerHTML = `<i class="bi bi-check-circle-fill text-success me-2 fs-6"></i>${escapeHtml(scaledLine)}`;
        ingredientsList.appendChild(li);
    });
}

/**
 * Scale quantities in ingredient line (e.g. "400g Spaghetti" -> "800g Spaghetti")
 */
function scaleIngredientLine(line, ratio) {
    return line.replace(/(\d+(?:\.\d+)?)/g, (match) => {
        const val = parseFloat(match);
        if (isNaN(val)) return match;
        const scaled = Math.round((val * ratio) * 10) / 10;
        return scaled;
    });
}

/**
 * Kitchen Timer Widget
 */
function initKitchenTimer() {
    const btnStart = document.getElementById('btnStartTimer');
    const btnReset = document.getElementById('btnResetTimer');

    if (btnStart) {
        btnStart.addEventListener('click', () => {
            if (timerInterval) {
                clearInterval(timerInterval);
                timerInterval = null;
                btnStart.textContent = 'Start';
                btnStart.classList.replace('btn-warning', 'btn-accent');
            } else {
                btnStart.textContent = 'Pause';
                btnStart.classList.replace('btn-accent', 'btn-warning');
                timerInterval = setInterval(() => {
                    if (timerSeconds > 0) {
                        timerSeconds--;
                        updateTimerDisplay();
                    } else {
                        clearInterval(timerInterval);
                        timerInterval = null;
                        btnStart.textContent = 'Done!';
                        alert('⏰ Kitchen Timer Finished!');
                    }
                }, 1000);
            }
        });
    }

    if (btnReset) {
        btnReset.addEventListener('click', () => {
            if (timerInterval) {
                clearInterval(timerInterval);
                timerInterval = null;
            }
            const totalPrepCook = (currentRecipeData ? (currentRecipeData.prep_time || 15) : 15);
            timerSeconds = totalPrepCook * 60;
            if (btnStart) {
                btnStart.textContent = 'Start';
                btnStart.className = 'btn btn-sm btn-accent flex-grow-1 rounded-pill';
            }
            updateTimerDisplay();
        });
    }
}

function updateTimerDisplay() {
    const display = document.getElementById('timerDisplay');
    if (!display) return;
    const mins = Math.floor(timerSeconds / 60);
    const secs = timerSeconds % 60;
    display.textContent = `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
}

/**
 * Open Recipe Modal and load data + reviews
 */
function openRecipeModal(recipe) {
    if (!recipe) return;

    currentRecipeData = recipe;
    baseServings = parseInt(recipe.servings) || 4;
    currentServings = baseServings;

    document.getElementById('recipeModalTitle').textContent = recipe.title || 'Recipe Details';
    document.getElementById('modalCategoryBadge').textContent = recipe.category || 'General';
    document.getElementById('modalAuthor').textContent = recipe.username || 'Community Chef';
    document.getElementById('modalImage').src = recipe.image_url || 'https://images.unsplash.com/photo-1495521821757-a1efb6729352?auto=format&fit=crop&w=800&q=80';

    document.getElementById('modalPrepTime').textContent = (recipe.prep_time || 0) + ' mins';
    document.getElementById('modalCookTime').textContent = (recipe.cook_time || 0) + ' mins';
    document.getElementById('modalServings').textContent = currentServings;

    const difficultyEl = document.getElementById('modalDifficulty');
    const diff = (recipe.difficulty || 'Medium').toLowerCase();
    if (diff === 'easy') {
        difficultyEl.innerHTML = '<span class="badge bg-success">Easy</span>';
    } else if (diff === 'hard') {
        difficultyEl.innerHTML = '<span class="badge bg-danger">Hard</span>';
    } else {
        difficultyEl.innerHTML = '<span class="badge bg-warning text-dark">Medium</span>';
    }

    // Set Kitchen Timer to Prep Time
    timerSeconds = (parseInt(recipe.prep_time) || 15) * 60;
    updateTimerDisplay();

    // Populate Ingredients
    updateServingsAndIngredients();

    // Populate Instructions
    const instructionsList = document.getElementById('modalInstructionsList');
    instructionsList.innerHTML = '';
    const instructionsArray = (recipe.instructions || '').split('\n').filter(i => i.trim().length > 0);
    
    if (instructionsArray.length === 0) {
        instructionsList.innerHTML = '<p class="text-muted">No instructions available.</p>';
    } else {
        instructionsArray.forEach(step => {
            const li = document.createElement('li');
            li.className = 'mb-2';
            li.textContent = step.replace(/^\d+\.\s*/, '').trim();
            instructionsList.appendChild(li);
        });
    }

    // Set Hidden Recipe ID for Review Form
    const reviewRecipeIdEl = document.getElementById('reviewRecipeId');
    if (reviewRecipeIdEl) reviewRecipeIdEl.value = recipe.id;

    // Load Reviews
    loadRecipeReviews(recipe.id);

    // Show Modal
    const modalEl = document.getElementById('recipeDetailModal');
    const bsModal = new bootstrap.Modal(modalEl);
    bsModal.show();
}

/**
 * Load Reviews via API
 */
function loadRecipeReviews(recipeId) {
    const container = document.getElementById('modalReviewsContainer');
    const reviewCountEl = document.getElementById('modalReviewCount');
    if (!container) return;

    container.innerHTML = '<p class="text-muted small">Loading reviews...</p>';

    fetch(`api/get_reviews.php?recipe_id=${recipeId}`)
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                if (reviewCountEl) reviewCountEl.textContent = data.review_count;
                renderReviewsList(data.reviews);
            } else {
                container.innerHTML = '<p class="text-muted small">No reviews yet.</p>';
            }
        })
        .catch(() => {
            container.innerHTML = '<p class="text-muted small">No reviews yet. Be the first to rate!</p>';
        });
}

function renderReviewsList(reviews) {
    const container = document.getElementById('modalReviewsContainer');
    container.innerHTML = '';

    if (!reviews || reviews.length === 0) {
        container.innerHTML = '<p class="text-muted small mb-0">No reviews yet. Be the first to leave a review!</p>';
        return;
    }

    reviews.forEach(r => {
        const div = document.createElement('div');
        div.className = 'p-2 bg-light rounded-3 border mb-1';
        const stars = '⭐'.repeat(r.rating);
        div.innerHTML = `
            <div class="d-flex justify-content-between align-items-center">
                <strong class="small text-dark">${escapeHtml(r.username)}</strong>
                <span class="small">${stars}</span>
            </div>
            <p class="small text-muted mb-0 mt-1">${escapeHtml(r.comment || '')}</p>
        `;
        container.appendChild(div);
    });
}

/**
 * Submit Review Handler
 */
function initReviewForm() {
    const form = document.getElementById('reviewForm');
    if (!form) return;

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        const recipeId = document.getElementById('reviewRecipeId').value;
        const rating = document.getElementById('reviewRatingSelect').value;
        const comment = document.getElementById('reviewCommentText').value;

        const formData = new FormData();
        formData.append('recipe_id', recipeId);
        formData.append('rating', rating);
        formData.append('comment', comment);

        fetch('api/rate_recipe.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                alert('⭐ ' + data.message);
                document.getElementById('reviewCommentText').value = '';
                loadRecipeReviews(recipeId);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(err => alert('Failed to post review.'));
    });
}

/**
 * Favorites Bookmarking System (LocalStorage)
 */
function getFavorites() {
    return JSON.parse(localStorage.getItem('flavorcraft_favorites') || '[]');
}

function toggleFavorite(recipeId, btnEl) {
    let favs = getFavorites();
    const id = parseInt(recipeId);
    if (favs.includes(id)) {
        favs = favs.filter(f => f !== id);
    } else {
        favs.push(id);
    }
    localStorage.setItem('flavorcraft_favorites', JSON.stringify(favs));
    initFavoriteButtons();
    loadCustomerFavorites();
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
