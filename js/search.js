/**
 * js/search.js - Live Recipe Search & Dynamic Category Filter Engine
 */

document.addEventListener('DOMContentLoaded', () => {
    initRecipeSearch();
});

function initRecipeSearch() {
    const searchInput = document.getElementById('searchInput');
    const categoryButtons = document.querySelectorAll('.category-filter-btn');
    const recipeGrid = document.getElementById('recipeGridContainer');
    const recipeCountBadge = document.getElementById('recipeCountBadge');
    
    if (!recipeGrid) return;

    let currentCategory = 'All';
    let searchQuery = '';
    let debounceTimer = null;

    // Attach listener to Search Bar (Debounced live search)
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            searchQuery = e.target.value.trim();
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                fetchFilteredRecipes(searchQuery, currentCategory);
            }, 200);
        });
    }

    // Attach listeners to Category Filter Buttons
    categoryButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            categoryButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            currentCategory = this.dataset.category || 'All';
            fetchFilteredRecipes(searchQuery, currentCategory);
        });
    });

    /**
     * Fetch filtered recipes from API endpoint
     */
    function fetchFilteredRecipes(query, category) {
        let url = `api/get_recipes.php?q=${encodeURIComponent(query)}&category=${encodeURIComponent(category)}`;
        if (category === 'Favorites') {
            const favs = getFavorites();
            url += `&fav_ids=${favs.join(',')}`;
        }
        
        recipeGrid.innerHTML = `
            <div class="col-12 text-center py-5">
                <div class="spinner-border text-accent" role="status">
                    <span class="visually-hidden">Loading recipes...</span>
                </div>
                <p class="text-muted mt-2">Searching culinary creations...</p>
            </div>
        `;

        fetch(url)
            .then(response => {
                if (!response.ok) throw new Error('Network response failed');
                return response.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    renderRecipes(data.recipes, category);
                    if (recipeCountBadge) {
                        recipeCountBadge.textContent = `${data.count} Recipe${data.count === 1 ? '' : 's'} Found`;
                    }
                } else {
                    renderError('Failed to load recipes.');
                }
            })
            .catch(error => {
                console.error('Search error:', error);
                // Fallback to DOM filter if server/API unavailable
                fallbackDomFilter(query, category);
            });
    }

    /**
     * Render Recipe Cards into Grid Container
     */
    function renderRecipes(recipes, category = 'All') {
        recipeGrid.innerHTML = '';

        if (!recipes || recipes.length === 0) {
            const isFav = category === 'Favorites';
            recipeGrid.innerHTML = `
                <div class="col-12 text-center py-5 animate-fade-in">
                    <i class="bi ${isFav ? 'bi-heartbreak text-danger' : 'bi-search-heart text-muted'} fs-1 d-block mb-3"></i>
                    <h4 class="fw-bold">${isFav ? 'No Favorites Saved Yet' : 'No Recipes Found'}</h4>
                    <p class="text-muted">${isFav ? 'Click the heart icon on any recipe card to save it to your personal favorites!' : 'We couldn\'t find any recipes matching your search criteria. Try a different keyword or category!'}</p>
                </div>
            `;
            return;
        }

        recipes.forEach((recipe, index) => {
            const cardCol = document.createElement('div');
            cardCol.className = 'col-lg-4 col-md-6 mb-4 animate-fade-in';
            cardCol.style.animationDelay = `${index * 0.05}s`;

            const imgUrl = recipe.image_url && recipe.image_url.trim() !== '' 
                ? recipe.image_url 
                : 'https://images.unsplash.com/photo-1495521821757-a1efb6729352?auto=format&fit=crop&w=800&q=80';

            const difficultyClass = (recipe.difficulty || 'Medium').toLowerCase();
            let badgeHtml = '<span class="badge bg-warning text-dark">Medium</span>';
            if (difficultyClass === 'easy') badgeHtml = '<span class="badge bg-success">Easy</span>';
            if (difficultyClass === 'hard') badgeHtml = '<span class="badge bg-danger">Hard</span>';

            const favs = getFavorites();
            const isFav = favs.includes(parseInt(recipe.id));
            const heartIcon = isFav ? '<i class="bi bi-heart-fill text-danger"></i>' : '<i class="bi bi-heart"></i>';

            cardCol.innerHTML = `
                <div class="card recipe-card h-100 shadow-sm" data-recipe-id="${recipe.id}">
                    <div class="recipe-card-img-wrapper">
                        <span class="category-pill">${escapeHtml(recipe.category)}</span>
                        <button type="button" class="fav-badge-btn position-absolute top-0 end-0 m-3 btn btn-light btn-sm rounded-circle shadow-sm" onclick="event.stopPropagation(); toggleFavorite(${recipe.id}, this);" title="Bookmark Recipe">
                            ${heartIcon}
                        </button>
                        <img src="${escapeHtml(imgUrl)}" alt="${escapeHtml(recipe.title)}" loading="lazy">
                    </div>
                    <div class="card-body d-flex flex-column p-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="recipe-meta"><i class="bi bi-clock me-1 text-accent"></i>${recipe.prep_time + recipe.cook_time} mins</span>
                            ${badgeHtml}
                        </div>
                        <h5 class="card-title font-heading fw-bold text-dark mb-2">${escapeHtml(recipe.title)}</h5>
                        <p class="card-text text-muted small flex-grow-1">
                            ${escapeHtml(recipe.ingredients.substring(0, 95))}...
                        </p>
                        <div class="pt-3 border-top d-flex justify-content-between align-items-center mt-auto">
                            <small class="text-secondary"><i class="bi bi-person me-1"></i>${escapeHtml(recipe.username || 'Chef')}</small>
                            <button class="btn btn-sm btn-outline-accent rounded-pill view-recipe-btn">
                                View Recipe <i class="bi bi-arrow-right ms-1"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;

            // Attach click event to entire card or button
            const cardEl = cardCol.querySelector('.recipe-card');
            cardEl.addEventListener('click', () => {
                openRecipeModal(recipe);
            });

            recipeGrid.appendChild(cardCol);
        });
    }

    /**
     * Fallback DOM Filter in case API endpoint is unreachable
     */
    function fallbackDomFilter(query, category) {
        const cards = recipeGrid.querySelectorAll('.col-lg-4');
        let count = 0;

        cards.forEach(card => {
            const title = card.querySelector('.card-title')?.textContent.toLowerCase() || '';
            const cardCategory = card.querySelector('.category-pill')?.textContent || '';
            const text = card.textContent.toLowerCase();

            const matchesQuery = !query || text.includes(query.toLowerCase());
            const matchesCategory = category === 'All' || cardCategory.toLowerCase() === category.toLowerCase();

            if (matchesQuery && matchesCategory) {
                card.style.display = 'block';
                count++;
            } else {
                card.style.display = 'none';
            }
        });

        if (recipeCountBadge) {
            recipeCountBadge.textContent = `${count} Recipe${count === 1 ? '' : 's'} Found`;
        }
    }

    function renderError(message) {
        recipeGrid.innerHTML = `
            <div class="col-12 text-center py-4 text-danger">
                <i class="bi bi-exclamation-triangle fs-2"></i>
                <p class="mt-2">${message}</p>
            </div>
        `;
    }
}
