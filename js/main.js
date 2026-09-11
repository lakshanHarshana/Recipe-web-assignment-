/**
 * js/main.js - General UI interactivity, smooth scrolling, event listeners, and recipe modal loader
 */

document.addEventListener('DOMContentLoaded', () => {
    initBackToTop();
    initSmoothScroll();
    initTooltips();
});

/**
 * Initialize Back to Top floating button
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
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}

/**
 * Smooth scrolling for navigation links
 */
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            if (targetId === '#' || !targetId.startsWith('#')) return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                e.preventDefault();
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

/**
 * Initialize Bootstrap Tooltips
 */
function initTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

/**
 * Open Recipe Modal with detailed information
 * @param {Object} recipe 
 */
function openRecipeModal(recipe) {
    if (!recipe) return;

    document.getElementById('recipeModalTitle').textContent = recipe.title || 'Recipe Details';
    document.getElementById('modalCategoryBadge').textContent = recipe.category || 'General';
    document.getElementById('modalAuthor').textContent = recipe.username || recipe.author || 'Community Chef';
    document.getElementById('modalImage').src = recipe.image_url || 'https://images.unsplash.com/photo-1495521821757-a1efb6729352?auto=format&fit=crop&w=800&q=80';
    document.getElementById('modalImage').alt = recipe.title;

    document.getElementById('modalPrepTime').textContent = (recipe.prep_time || 0) + ' mins';
    document.getElementById('modalCookTime').textContent = (recipe.cook_time || 0) + ' mins';
    document.getElementById('modalServings').textContent = (recipe.servings || 1) + ' Servings';

    const difficultyEl = document.getElementById('modalDifficulty');
    const diff = (recipe.difficulty || 'Medium').toLowerCase();
    if (diff === 'easy') {
        difficultyEl.innerHTML = '<span class="badge bg-success">Easy</span>';
    } else if (diff === 'hard') {
        difficultyEl.innerHTML = '<span class="badge bg-danger">Hard</span>';
    } else {
        difficultyEl.innerHTML = '<span class="badge bg-warning text-dark">Medium</span>';
    }

    // Populate Ingredients
    const ingredientsList = document.getElementById('modalIngredientsList');
    ingredientsList.innerHTML = '';
    const ingredientsArray = (recipe.ingredients || '').split('\n').filter(i => i.trim().length > 0);
    
    if (ingredientsArray.length === 0) {
        ingredientsList.innerHTML = '<li class="list-group-item text-muted">No ingredients listed.</li>';
    } else {
        ingredientsArray.forEach(item => {
            const li = document.createElement('li');
            li.className = 'list-group-item bg-transparent ps-0 d-flex align-items-center';
            li.innerHTML = `<i class="bi bi-check-circle-fill text-success me-2 fs-6"></i>${escapeHtml(item.trim())}`;
            ingredientsList.appendChild(li);
        });
    }

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

    // Show Modal
    const modalEl = document.getElementById('recipeDetailModal');
    const bsModal = new bootstrap.Modal(modalEl);
    bsModal.show();
}

/**
 * Helper to escape HTML characters
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
