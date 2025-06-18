<?php
/**
 * Enqueue and localize the category filter script.
 */
function ortoc_enqueue_category_filter_script() {
    // Enqueue the JavaScript file
    // IMPORTANT: Adjust the path if 'ortoc-category-filter.js' is not in the theme root.
    // If it's in a 'js' subdirectory, for example: get_template_directory_uri() . '/js/ortoc-category-filter.js'
    wp_enqueue_script(
        'ortoc-category-filter',
        get_template_directory_uri() . '/ortoc-category-filter.js', // Assuming JS is in theme root
        array('jquery'), // Add 'jquery' if your script directly uses it, though Fetch API doesn't need it.
        null, // Version number, null for no version or filemtime for auto-versioning
        true  // Load in footer
    );

    // Localize the script with necessary data
    wp_localize_script(
        'ortoc-category-filter',
        'ortoc_filter_params', // Object name in JavaScript
        array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('ortoc_category_filter_nonce')
        )
    );
}
add_action('wp_enqueue_scripts', 'ortoc_enqueue_category_filter_script');

/**
 * AJAX handler for filtering products by category.
 */
function ortoc_filter_products_by_category_handler() {
    // Verify the nonce for security
    check_ajax_referer('ortoc_category_filter_nonce', '_ajax_nonce');

    // Sanitize the received category slug
    $category_slug = isset($_POST['category_slug']) ? sanitize_text_field($_POST['category_slug']) : '';

    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => 8, // Match original query or make it configurable
        'status'         => 'publish',
    );

    // If a specific category is selected (and not 'all'), add tax_query
    if (!empty($category_slug) && $category_slug !== 'all') {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'product_cat',
                'field'    => 'slug',
                'terms'    => $category_slug,
            ),
        );
    }

    $products_query = new WP_Query($args);

    ob_start(); // Start output buffering

    if ($products_query->have_posts()) {
        // Set WooCommerce loop properties if needed (e.g., columns)
        // global $woocommerce_loop;
        // $woocommerce_loop['columns'] = 4; // Example: if you want to control columns

        woocommerce_product_loop_start();

        while ($products_query->have_posts()) : $products_query->the_post();
            global $product;
            // Ensure product object is valid before calling wc_get_template_part
            if (is_a($product, 'WC_Product')) {
                 wc_get_template_part('content', 'product');
            } else {
                // If $product is not a WC_Product instance, try to get it.
                $product_obj = wc_get_product(get_the_ID());
                if(is_a($product_obj, 'WC_Product')) {
                    $GLOBALS['product'] = $product_obj; // Set it globally for the template part
                    wc_get_template_part('content', 'product');
                }
            }
        endwhile;

        woocommerce_product_loop_end();
    } else {
        echo '<p class="woocommerce-info">' . esc_html__('No products found matching your selection.', 'your-theme-textdomain') . '</p>';
    }

    wp_reset_postdata(); // Reset post data

    $html = ob_get_clean(); // Get buffered HTML

    // Send JSON response
    wp_send_json_success(array('html' => $html));

    wp_die(); // This is required to terminate immediately and return a proper response
}
add_action('wp_ajax_filter_products_by_category', 'ortoc_filter_products_by_category_handler');
add_action('wp_ajax_nopriv_filter_products_by_category', 'ortoc_filter_products_by_category_handler');

?>
