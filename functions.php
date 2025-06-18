<?php

// Fonction qui ajoute les styles au thème et autres pages
function ortoc_theme_style()
{
    wp_enqueue_style('bootstrap-style-child', 'https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css');
    wp_enqueue_style('bootstrap-datepicker3', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker3.min.css');
    wp_enqueue_style('fancybox', 'https://cdn.jsdelivr.net/npm/@fancyapps/fancybox@3.5.6/dist/jquery.fancybox.min.css');
    wp_enqueue_style('style-css', get_stylesheet_directory_uri() . '/style.css');
}
add_action('wp_enqueue_scripts', 'ortoc_theme_style');

function ortoc_enqueue_scripts()
{
    wp_enqueue_script('jquery-js', 'https://code.jquery.com/jquery-3.4.1.min.js');
    wp_enqueue_script('boostrap-js', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js');
    wp_enqueue_script('boostrap-datepicker-js', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js');
    wp_enqueue_script('masonry-js', 'https://cdn.jsdelivr.net/npm/masonry-layout@4.2.2/dist/masonry.pkgd.min.js');
    wp_enqueue_script('fancybox-js', 'https://cdn.jsdelivr.net/npm/@fancyapps/fancybox@3.5.6/dist/jquery.fancybox.min.js');
    wp_enqueue_script('isotope-js', 'https://npmcdn.com/isotope-layout@3/dist/isotope.pkgd.js');
}
add_action('wp_enqueue_scripts', 'ortoc_enqueue_scripts');

function ortoc_widgets_init()
{
    register_sidebar(array(
        'name'          => __('Side Right', 'ortoc'),
        'id'            => 'side_right',
    ));
    register_sidebar(array(
        'name'          => __('Header contact infos', 'ortoc'),
        'id'            => 'header_contact_infos',
    ));
    register_sidebar(array(
        'name'          => __('Langues', 'ortoc'),
        'id'            => 'langues',
    ));
    register_sidebar(array(
        'name'          => __('Destinations recentes', 'ortoc'),
        'id'            => 'recents_destinations',
    ));
    register_sidebar(array(
        'name'          => __('Articles recents', 'ortoc'),
        'id'            => 'recents_posts',
    ));
    register_sidebar(array(
        'name'          => __('Newsletter', 'ortoc'),
        'id'            => 'newsletter',
    ));
    register_sidebar(array(
        'name'          => __('Navigation footer', 'ortoc'),
        'id'            => 'menu_navigation',
    ));
}

add_action('widgets_init', 'ortoc_widgets_init');

add_action('woocommerce_after_shop_loop_item', 'woo_show_excerpt_shop_page', 5);
function woo_show_excerpt_shop_page()
{
    global $product;

    echo $product->post->post_excerpt;
}

function chefferie_enqueue_scripts()
{
    // Swiper CSS
    wp_enqueue_style('swiper-css', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css');
    // Swiper JS
    wp_enqueue_script('swiper-js', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', [], null, true);
}
add_action('wp_enqueue_scripts', 'chefferie_enqueue_scripts');

// Tableau pour les festivals
add_action('acf/input/admin_footer', 'load_json_editor_for_festivals');
function load_json_editor_for_festivals()
{
?>
    <script src="https://cdn.jsdelivr.net/npm/jsoneditor@9.10.0/dist/jsoneditor.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jsoneditor@9.10.0/dist/jsoneditor.min.css">
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const textarea = document.querySelector('[name="acf[festivals_json]"]');
            if (textarea) {
                textarea.style.display = 'none';

                const container = document.createElement('div');
                container.style.height = '400px';
                textarea.parentNode.insertBefore(container, textarea);

                const editor = new JSONEditor(container, {
                    mode: 'form',
                    modes: ['form', 'code'],
                    onChange: () => {
                        try {
                            const json = editor.get();
                            textarea.value = JSON.stringify(json, null, 2);
                        } catch (e) {
                            // ignore errors while typing
                        }
                    }
                });

                try {
                    editor.set(JSON.parse(textarea.value || '[]'));
                } catch (e) {
                    editor.set([]);
                }
            }
        });
    </script>
<?php
}

// =======================================================================
// AJOUT DU SUPPORT WOOCOMMERCE
// =======================================================================
add_action('after_setup_theme', 'mon_theme_support_woocommerce');
function mon_theme_support_woocommerce()
{
    add_theme_support('woocommerce');
}

// =======================================================================
// FONCTIONS AJAX POUR LE FILTRAGE DES PRODUITS
// =======================================================================

/**
 * Enqueue and localize the category filter script.
 */
function ortoc_enqueue_category_filter_script() {
    // Vérifier que WooCommerce est actif
    if ( ! class_exists( 'WooCommerce' ) ) {
        return;
    }
    
    // Ne charger que sur la page boutique
    if ( ! is_page( 'boutique-ortoc' ) ) {
        return;
    }
    
    // Enqueue the JavaScript file
    wp_enqueue_script(
        'ortoc-category-filter',
        get_stylesheet_directory_uri() . '/ortoc-category-filter.js',
        array('jquery'),
        null,
        true
    );

    // Localize the script with necessary data
    wp_localize_script(
        'ortoc-category-filter',
        'ortoc_filter_params',
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
    // Vérifier que WooCommerce est actif
    if ( ! class_exists( 'WooCommerce' ) ) {
        wp_send_json_error('WooCommerce not active');
        return;
    }
    
    // Verify the nonce for security
    check_ajax_referer('ortoc_category_filter_nonce', '_ajax_nonce');

    // Sanitize the received category slug
    $category_slug = isset($_POST['category_slug']) ? sanitize_text_field($_POST['category_slug']) : '';

    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => 8,
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
        // Start the products list
        echo '<ul class="products">';
        
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
        
        // End the products list
        echo '</ul>';
    } else {
        echo '<p class="woocommerce-info">' . esc_html__('Aucun produit trouvé correspondant à votre sélection.', 'ortoc') . '</p>';
    }

    wp_reset_postdata(); // Reset post data

    $html = ob_get_clean(); // Get buffered HTML

    // Send JSON response
    wp_send_json_success(array('html' => $html));

    wp_die(); // This is required to terminate immediately and return a proper response
}
add_action('wp_ajax_filter_products_by_category', 'ortoc_filter_products_by_category_handler');
add_action('wp_ajax_nopriv_filter_products_by_category', 'ortoc_filter_products_by_category_handler');

