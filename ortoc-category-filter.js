document.addEventListener('DOMContentLoaded', function () {
    console.log('Ortoc Category Filter: Script loaded');
    
    // Check if we're on the right page (optional)
    if (!document.querySelector('.woocommerce-products-section')) {
        console.log('Ortoc Category Filter: Not on products page, skipping');
        return;
    }
    
    // Check if localization parameters are available
    if (typeof ortoc_filter_params === 'undefined') {
        console.error('Localization parameters (ortoc_filter_params) not found. AJAX filtering will not work.');
        return; // Stop execution if params are missing
    }

    console.log('Ortoc Category Filter: Parameters found', ortoc_filter_params);

    const ajaxUrl = ortoc_filter_params.ajax_url;
    const nonce = ortoc_filter_params.nonce;
    const filterButtons = document.querySelectorAll('.filter-button');

    console.log('Ortoc Category Filter: Found', filterButtons.length, 'filter buttons');

    if (filterButtons.length === 0) {
        console.error('Ortoc Category Filter: No filter buttons found');
        return;
    }

    // Attempt to find the product container
    let productsContainer = document.querySelector('.woocommerce-products-section ul.products');
    if (!productsContainer) {
        // Fallback selector if the primary one isn't found
        productsContainer = document.querySelector('section.woocommerce-products-section div.container');
        console.log('Ortoc Category Filter: Using fallback container selector');
    }
    
    if (!productsContainer) {
        console.error('Products container not found. AJAX product filtering will not be able to display results.');
        console.log('Ortoc Category Filter: Available containers:', document.querySelectorAll('.woocommerce-products-section'));
        return;
    }

    console.log('Ortoc Category Filter: Products container found', productsContainer);

    filterButtons.forEach(button => {
        button.addEventListener('click', function (event) {
            event.preventDefault();
            console.log('Ortoc Category Filter: Button clicked', this.dataset.category);

            const categorySlug = this.dataset.category;

            // Update active button state
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');

            // Add loading state
            if (productsContainer) {
                productsContainer.classList.add('loading');
            }

            // Prepare data for AJAX request
            const formData = new FormData();
            formData.append('action', 'filter_products_by_category');
            formData.append('category_slug', categorySlug);
            formData.append('_ajax_nonce', nonce);

            console.log('Ortoc Category Filter: Sending AJAX request for category:', categorySlug);

            // Perform AJAX request using Fetch API
            fetch(ajaxUrl, {
                method: 'POST',
                body: formData,
            })
            .then(response => {
                console.log('Ortoc Category Filter: Response received', response.status);
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.statusText);
                }
                return response.json(); // Parse JSON response from WordPress
            })
            .then(data => {
                console.log('Ortoc Category Filter: Data received', data);
                if (data.success && data.data && typeof data.data.html !== 'undefined') {
                    if (productsContainer) {
                        productsContainer.innerHTML = data.data.html;
                        console.log('Ortoc Category Filter: Products updated successfully');
                    } else {
                        console.error('Products container not found when trying to display results.');
                    }
                } else {
                    console.error('Error processing AJAX response or no HTML received:', data);
                    if (productsContainer) {
                        productsContainer.innerHTML = '<p class="woocommerce-error">Erreur lors du chargement des produits. Veuillez réessayer.</p>';
                    }
                }
            })
            .catch(error => {
                console.error('AJAX Request Failed:', error);
                if (productsContainer) {
                    productsContainer.innerHTML = '<p class="woocommerce-error">Échec du chargement des produits. Vérifiez votre connexion et réessayez.</p>';
                }
            })
            .finally(() => {
                // Remove loading state
                if (productsContainer) {
                    productsContainer.classList.remove('loading');
                }
            });
        });
    });

    // Optional: Add a simple CSS rule for the loading class via JavaScript
    const style = document.createElement('style');
    style.textContent = `
        .woocommerce-products-section ul.products.loading,
        section.woocommerce-products-section div.container.loading {
            opacity: 0.5;
            transition: opacity 0.3s ease-in-out;
        }
    `;
    document.head.appendChild(style);
    
    console.log('Ortoc Category Filter: Setup complete');
});
