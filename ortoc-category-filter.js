document.addEventListener('DOMContentLoaded', function () {
    // Check if localization parameters are available
    if (typeof ortoc_filter_params === 'undefined') {
        console.error('Localization parameters (ortoc_filter_params) not found. AJAX filtering will not work.');
        return; // Stop execution if params are missing
    }

    const ajaxUrl = ortoc_filter_params.ajax_url;
    const nonce = ortoc_filter_params.nonce;
    const filterButtons = document.querySelectorAll('.filter-button');

    // Attempt to find the product container
    let productsContainer = document.querySelector('.woocommerce-products-section ul.products');
    if (!productsContainer) {
        // Fallback selector if the primary one isn't found
        productsContainer = document.querySelector('section.woocommerce-products-section div.container');
    }
    // If still not found, log an error and disable filtering, as there's nowhere to put results.
    if (!productsContainer) {
        console.error('Products container not found. AJAX product filtering will not be able to display results.');
        // Optionally, disable buttons or provide user feedback here
        return;
    }

    filterButtons.forEach(button => {
        button.addEventListener('click', function (event) {
            event.preventDefault();

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

            // Perform AJAX request using Fetch API
            fetch(ajaxUrl, {
                method: 'POST',
                body: formData,
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.statusText);
                }
                return response.json(); // Parse JSON response from WordPress
            })
            .then(data => {
                if (data.success && data.data && typeof data.data.html !== 'undefined') {
                    if (productsContainer) {
                        productsContainer.innerHTML = data.data.html;
                    } else {
                        // This case should ideally be prevented by the initial check,
                        // but as a safeguard:
                        console.error('Products container not found when trying to display results.');
                    }
                } else {
                    console.error('Error processing AJAX response or no HTML received:', data);
                    // Optionally display a user-friendly error message in the productsContainer
                    if (productsContainer) {
                        productsContainer.innerHTML = '<p class="woocommerce-error">Error loading products. Please try again.</p>';
                    }
                }
            })
            .catch(error => {
                console.error('AJAX Request Failed:', error);
                if (productsContainer) {
                    // Display a user-friendly error message
                    productsContainer.innerHTML = '<p class="woocommerce-error">Failed to load products. Please check your connection and try again.</p>';
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
    // This is just for demonstration; ideally, this would be in your theme's CSS file.
    const style = document.createElement('style');
    style.textContent = `
        .woocommerce-products-section ul.products.loading,
        section.woocommerce-products-section div.container.loading {
            opacity: 0.5;
            transition: opacity 0.3s ease-in-out;
        }
    `;
    document.head.appendChild(style);
});
