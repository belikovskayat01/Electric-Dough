let productsData = [];

function initFilters(data) {
    productsData = data;

    document.addEventListener('click', function(event) {
        if (!event.target.closest('.filter-dropdown')) {
            document.querySelectorAll('.dropdown-content.show').forEach(dropdown => {
                dropdown.classList.remove('show');
            });
        }
    });
}

function toggleDropdown(dropdownId) {
    const dropdown = document.getElementById(dropdownId);
    const allDropdowns = document.querySelectorAll('.dropdown-content');
    
    allDropdowns.forEach(dd => {
        if (dd.id !== dropdownId && dd.classList.contains('show')) {
            dd.classList.remove('show');
        }
    });
    
    dropdown.classList.toggle('show');
}

function setPriceRange(min, max) {
    const minInput = document.getElementById('priceMinFilter');
    const maxInput = document.getElementById('priceMaxFilter');
    
    if (min > 0) minInput.value = min;
    else minInput.value = '';
    
    if (max < 2000) maxInput.value = max;
    else maxInput.value = '';
    
    applyFilters();
}

function applyFilters() {
    const sortByRadio = document.querySelector('input[name="sortBy"]:checked');
    const sortBy = sortByRadio ? sortByRadio.value : 'default';
    
    const categoryCheckboxes = document.querySelectorAll('#categoryDropdown .dropdown-option input[type="checkbox"]');
    const selectedCategories = Array.from(categoryCheckboxes)
        .filter(cb => cb.checked)
        .map(cb => cb.value);
    
    const priceMin = document.getElementById('priceMinFilter').value ? parseFloat(document.getElementById('priceMinFilter').value) : null;
    const priceMax = document.getElementById('priceMaxFilter').value ? parseFloat(document.getElementById('priceMaxFilter').value) : null;
    
    const cookingTimeRadio = document.querySelector('input[name="cookingTime"]:checked');
    const cookingTime = cookingTimeRadio ? cookingTimeRadio.value : 'all';
    
    let filteredProducts = [...productsData];
    
    if (selectedCategories.length > 0) {
        filteredProducts = filteredProducts.filter(product => 
            selectedCategories.includes(product.category)
        );
    }
    
    if (priceMin !== null) {
        filteredProducts = filteredProducts.filter(product => 
            product.price >= priceMin
        );
    }
    if (priceMax !== null) {
        filteredProducts = filteredProducts.filter(product => 
            product.price <= priceMax
        );
    }
    
    if (cookingTime !== 'all') {
        filteredProducts = filteredProducts.filter(product => 
            product.cookingTime === cookingTime
        );
    }
    
    switch(sortBy) {
        case 'price_asc':
            filteredProducts.sort((a, b) => a.price - b.price);
            break;
        case 'price_desc':
            filteredProducts.sort((a, b) => b.price - a.price);
            break;
        case 'name_asc':
            filteredProducts.sort((a, b) => a.name.localeCompare(b.name));
            break;
        case 'name_desc':
            filteredProducts.sort((a, b) => b.name.localeCompare(a.name));
            break;
        default:
            const categoryOrder = {'Десерты': 1, 'Выпечка': 2, 'Напитки': 3};
            filteredProducts.sort((a, b) => (categoryOrder[a.category] || 99) - (categoryOrder[b.category] || 99));
            break;
    }
    
    renderFilteredProducts(filteredProducts);
}

function renderFilteredProducts(products) {
    const container = document.getElementById('filteredProductsContainer');
    if (!container) return;
    
    if (products.length === 0) {
        container.innerHTML = `
            <div class="no-products-found">
                <p>😔 Ничего не найдено по выбранным фильтрам</p>
                <button class="reset-filters-empty-btn" onclick="resetAllFilters()">Сбросить фильтры</button>
            </div>
        `;
        return;
    }
    
    const grouped = {};
    products.forEach(product => {
        if (!grouped[product.category]) {
            grouped[product.category] = [];
        }
        grouped[product.category].push(product);
    });
    
    let html = '';
    for (const [category, categoryProducts] of Object.entries(grouped)) {
        html += `<h2 id="${category.toLowerCase()}" class="category-name">${category}</h2>`;
        
        for (let i = 0; i < categoryProducts.length; i += 3) {
            const chunk = categoryProducts.slice(i, i + 3);
            html += `<div class="products_cards">`;
            chunk.forEach(product => {
                html += `
                    <div class="product_item" data-id="${product.id}">
                        <img src="IMG/products/${product.image}" 
                             class="product-image"
                             alt="${product.name}"
                             onerror="this.src='IMG/placeholder.jpg'">
                        <p class="item_name">${escapeHtml(product.name)}</p>
                        <div class="product-price">${product.price} ₽</div>
                        <a href="product.php?id=${product.id}">
                            <button>подробнее...</button>
                        </a>
                    </div>
                `;
            });
            html += `</div>`;
        }
    }
    
    container.innerHTML = html;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function resetAllFilters() {
    const defaultSort = document.querySelector('input[name="sortBy"][value="default"]');
    if (defaultSort) defaultSort.checked = true;
    
    document.querySelectorAll('#categoryDropdown .dropdown-option input[type="checkbox"]').forEach(cb => {
        cb.checked = false;
    });
    
    const minInput = document.getElementById('priceMinFilter');
    const maxInput = document.getElementById('priceMaxFilter');
    if (minInput) minInput.value = '';
    if (maxInput) maxInput.value = '';
    
    const allTime = document.querySelector('input[name="cookingTime"][value="all"]');
    if (allTime) allTime.checked = true;
    
    applyFilters();
}

function addToViewedProducts(productId) {
    let viewed = localStorage.getItem('viewed_products');
    let viewedArray = viewed ? viewed.split(',') : [];
    
    viewedArray = viewedArray.filter(id => id != productId);
    viewedArray.unshift(productId);
    viewedArray = viewedArray.slice(0, 10);
    
    localStorage.setItem('viewed_products', viewedArray.join(','));
    document.cookie = `viewed_products=${viewedArray.join(',')}; path=/; max-age=${60 * 60 * 24 * 30}`;
    
    if (typeof isAuthenticated !== 'undefined' && isAuthenticated) {
        fetch('save_viewed_products.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ viewed_products: viewedArray.join(',') })
        });
    }
}

window.toggleDropdown = toggleDropdown;
window.setPriceRange = setPriceRange;
window.applyFilters = applyFilters;
window.resetAllFilters = resetAllFilters;
window.addToViewedProducts = addToViewedProducts;
window.initFilters = initFilters;