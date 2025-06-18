document.addEventListener('DOMContentLoaded', function () {
    if (typeof ortoc_filter_params === 'undefined') {
        console.error('Localization parameters (ortoc_filter_params) not found. AJAX filtering will not work.');
        return;
    }

    const ajaxUrl = ortoc_filter_params.ajax_url;
    const nonce = ortoc_filter_params.nonce;
    // Get all filter buttons (parents and children) within the main filter container
    const allFilterButtons = document.querySelectorAll('.category-filters-container .filter-button');

    let productsContainer = document.querySelector('.woocommerce-products-section ul.products');
    if (!productsContainer) {
        productsContainer = document.querySelector('section.woocommerce-products-section div.container');
    }
    if (!productsContainer) {
        console.error('Products container not found. AJAX product filtering will not be able to display results.');
        return;
    }

    allFilterButtons.forEach(button => {
        button.addEventListener('click', function (event) {
            event.preventDefault();
            const clickedButton = this;

            // --- Toggle Functionality for Parent Buttons ---
            if (clickedButton.classList.contains('is-parent')) {
                const parentGroup = clickedButton.closest('.category-group');
                if (parentGroup) {
                    const subcategoryList = parentGroup.querySelector('.subcategory-list');
                    const toggleIcon = clickedButton.querySelector('.toggle-icon');

                    if (subcategoryList) {
                        const isCurrentlyOpen = subcategoryList.style.display !== 'none';
                        subcategoryList.style.display = isCurrentlyOpen ? 'none' : 'block';
                        if (toggleIcon) {
                            toggleIcon.classList.toggle('open', !isCurrentlyOpen);
                            toggleIcon.textContent = isCurrentlyOpen ? '+' : '-';
                        }
                    }
                }
            }

            // --- Active State Management ---
            // Remove 'active' from all filter buttons
            allFilterButtons.forEach(btn => btn.classList.remove('active'));
            // Add 'active' to the currently clicked button
            clickedButton.classList.add('active');

            // --- AJAX Call ---
            const categorySlug = clickedButton.dataset.category;

            if (productsContainer) {
                productsContainer.classList.add('loading');
            }

            const formData = new FormData();
            formData.append('action', 'filter_products_by_category');
            formData.append('category_slug', categorySlug);
            formData.append('_ajax_nonce', nonce);

            fetch(ajaxUrl, {
                method: 'POST',
                body: formData,
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.statusText);
                }
                return response.json();
            })
            .then(data => {
                if (data.success && data.data && typeof data.data.html !== 'undefined') {
                    if (productsContainer) {
                        productsContainer.innerHTML = data.data.html;
                    }
                } else {
                    console.error('Error processing AJAX response or no HTML received:', data);
                    if (productsContainer) {
                        productsContainer.innerHTML = '<p class="woocommerce-error">Error loading products. Please try again.</p>';
                    }
                }
            })
            .catch(error => {
                console.error('AJAX Request Failed:', error);
                if (productsContainer) {
                    productsContainer.innerHTML = '<p class="woocommerce-error">Failed to load products. Please check your connection and try again.</p>';
                }
            })
            .finally(() => {
                if (productsContainer) {
                    productsContainer.classList.remove('loading');
                }
            });
        });
    });

    const style = document.createElement('style');
    style.textContent = `
        .woocommerce-products-section ul.products.loading,
        section.woocommerce-products-section div.container.loading {
            opacity: 0.5;
            transition: opacity 0.3s ease-in-out;
        }
        /* CSS for .toggle-icon.open is expected to be in page-boutique-ortoc.php's <style> block */
    `;
    document.head.appendChild(style);

    // Initial state: If an active button (e.g. set by backend if page loaded with a filter)
    // is a child, expand its parent group.
    const initiallyActiveButton = document.querySelector('.category-filters-container .filter-button.active');
    if (initiallyActiveButton && initiallyActiveButton.classList.contains('is-child')) {
        const parentGroup = initiallyActiveButton.closest('.category-group');
        if (parentGroup) {
            const parentButton = parentGroup.querySelector('.is-parent'); // The actual parent button
            const subcategoryList = parentGroup.querySelector('.subcategory-list');
            // Ensure parentButton and its toggleIcon exist before trying to modify them
            if (parentButton) {
                 const toggleIcon = parentButton.querySelector('.toggle-icon');
                 if (subcategoryList && subcategoryList.style.display === 'none') {
                    subcategoryList.style.display = 'block';
                    if (toggleIcon) {
                        toggleIcon.classList.add('open');
                        toggleIcon.textContent = '-';
                    }
                }
            }
        }
    }
});
