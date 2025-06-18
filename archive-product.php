<?php
/**
 * The Template for displaying product archives, including the main shop page which is a post type archive
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/archive-product.php.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.4.0
 */

defined( 'ABSPATH' ) || exit;

get_header(); ?>

<style>
.archive-product-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 40px 20px;
}

.archive-header {
    text-align: center;
    margin-bottom: 40px;
    padding: 40px 0;
    background: linear-gradient(135deg, #0c4178 0%, #1E78BA 100%);
    color: white;
    border-radius: 12px;
}

.archive-title {
    font-family: 'Fredoka', sans-serif;
    font-size: 3rem;
    font-weight: 700;
    margin-bottom: 15px;
}

.archive-description {
    font-size: 1.2rem;
    opacity: 0.9;
    max-width: 600px;
    margin: 0 auto;
}

.woocommerce-products-header {
    margin-bottom: 30px;
}

.woocommerce-products-header__title {
    font-family: 'Fredoka', sans-serif;
    font-size: 2.5rem;
    color: #0c4178;
    margin-bottom: 10px;
}

.woocommerce-products-header__description {
    color: #666;
    font-size: 1.1rem;
}

.woocommerce-products-section {
    padding: 30px 0;
}

.woocommerce-products-section ul.products {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 25px;
    padding: 0;
    list-style: none;
}

.woocommerce-products-section ul.products li.product {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.woocommerce-products-section ul.products li.product:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}

.breadcrumb-container {
    background-color: #f5f5f5;
    padding: 15px 0;
    margin-bottom: 30px;
}

.breadcrumb {
    display: flex;
    flex-wrap: wrap;
    list-style: none;
    padding: 0;
    margin: 0;
    font-size: 14px;
    color: #3E3E3E;
}

.breadcrumb-item {
    display: flex;
    align-items: center;
}

.breadcrumb-item + .breadcrumb-item::before {
    content: ">";
    display: inline-block;
    padding: 0 0.5em;
    color: #6c757d;
}

.breadcrumb-item a {
    text-decoration: none;
    color: #0c4178;
}

.breadcrumb-item a:hover {
    text-decoration: underline;
}

.breadcrumb-item.active {
    color: #6c757d;
    font-weight: bold;
}

@media (max-width: 768px) {
    .archive-title {
        font-size: 2rem;
    }
    
    .woocommerce-products-section ul.products {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
    }
}
</style>

<div class="breadcrumb-container">
    <div class="archive-product-container">
        <nav class="breadcrumb">
            <span class="breadcrumb-item">
                <a href="<?php echo home_url(); ?>">Accueil</a>
            </span>
            <span class="breadcrumb-item active">
                <?php woocommerce_page_title(); ?>
            </span>
        </nav>
    </div>
</div>

<div class="archive-product-container">
    <?php if ( woocommerce_product_loop() ) : ?>
        
        <div class="archive-header">
            <h1 class="archive-title"><?php woocommerce_page_title(); ?></h1>
            <?php
            /**
             * Hook: woocommerce_archive_description.
             *
             * @hooked woocommerce_taxonomy_archive_description - 10
             * @hooked woocommerce_product_archive_description - 10
             */
            do_action( 'woocommerce_archive_description' );
            ?>
        </div>

        <div class="woocommerce-products-section">
            <?php
            if ( wc_get_loop_prop( 'is_shortcode' ) ) {
                $columns = absint( wc_get_loop_prop( 'columns' ) );
            } else {
                $columns = wc_get_default_products_per_row();
            }

            woocommerce_product_loop_start();

            if ( wc_get_loop_prop( 'total' ) ) {
                while ( have_posts() ) {
                    the_post();

                    /**
                     * Hook: woocommerce_shop_loop.
                     */
                    do_action( 'woocommerce_shop_loop' );

                    wc_get_template_part( 'content', 'product' );
                }
            }

            woocommerce_product_loop_end();

            /**
             * Hook: woocommerce_after_shop_loop.
             *
             * @hooked woocommerce_pagination - 10
             */
            do_action( 'woocommerce_after_shop_loop' );
            ?>
        </div>
    <?php else : ?>
        <?php
        /**
         * Hook: woocommerce_no_products_found.
         *
         * @hooked wc_no_products_found - 10
         */
        do_action( 'woocommerce_no_products_found' );
        ?>
    <?php endif; ?>

    <?php
    /**
     * Hook: woocommerce_after_main_content.
     *
     * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
     */
    do_action( 'woocommerce_after_main_content' );
    ?>
</div>

<?php
get_footer();
?> 