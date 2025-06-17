<?php
defined( 'ABSPATH' ) || exit;

global $product;
if ( empty( $product ) || ! $product->is_visible() ) {
    return;
}
?>

<style>
/*.product-card {*/
/*    border: 1px solid #eee;*/
/*    border-radius: 16px;*/
/*    background-color: #fffdf9;*/
/*    box-shadow: 0 6px 12px rgba(0,0,0,0.08);*/
/*    max-width: 270px;*/
/*    display: flex;*/
/*    flex-direction: column;*/
/*    overflow: hidden;*/
/*    margin: 15px auto;*/
/*    transition: transform 0.3s ease, box-shadow 0.3s ease;*/
/*}*/

.product-card {
    border: 1px solid #eee;
    border-radius: 16px;
    box-shadow: 0 6px 12px rgba(0,0,0,0.08);
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}


.product-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.12);
}

/*.product-thumb {*/
/*    width: 100%;*/
/*    height: 190px;*/
/*    overflow: hidden;*/
/*    background-color: #f7f7f7;*/
/*}*/

/*.product-thumb img {*/
/*    width: 100%;*/
/*    height: 100%;*/
/*    object-fit: cover;*/
/*    transition: transform 0.4s ease;*/
/*}*/

.product-thumb {
  height: 190px;
  overflow: hidden;
  background-color: #f7f7f7;
  display: flex;
  align-items: center;
  justify-content: center;
}

.product-thumb img {
  max-height: 100%;
  width: auto;
  object-fit: cover;
}

.product-thumb:hover img {
    transform: scale(1.03);
}

.product-card h2 {
    font-size: 17px;
    font-family: 'Fredoka', sans-serif;
    font-weight: 600;
    color: #0c4178;
    padding: 0 12px;
    margin: 12px 0 4px;
    text-align: left;
    line-height: 1.3;
    text-overflow: ellipsis;
    white-space: nowrap;
    overflow: hidden;
}

.product-card .price {
    font-size: 16px;
    font-weight: 500;
    color: #b12704;
    margin: 0 12px 10px;
}

.price del {
    color: #999;
    font-size: 14px;
    margin-right: 5px;
    text-decoration: line-through;
}

.price ins {
    color: #b12704;
    font-size: 16px;
    font-weight: bold;
    text-decoration: none;
}


.add-to-cart-container {
    background-color: #ffd700;
    color: #0c4178;
    padding: 10px;
    border-radius: 0 0 16px 16px;
    text-align: center;
    font-weight: bold;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.add-to-cart-container:hover {
    background-color: #f2c600;
}

.add-to-cart-container a {
    text-decoration: none !important;
    color: inherit;
}
</style>

<div <?php wc_product_class( 'product-card', $product ); ?>>
    <div class="product-thumb">
        <a href="<?php the_permalink(); ?>">
            <?php echo $product->get_image(); ?>
        </a>
    </div>

    <h2 class="woocommerce-loop-product__title">
        <a href="<?php the_permalink(); ?>">
            <?php the_title(); ?>
        </a>
    </h2>

    <div class="price"><?php echo $product->get_price_html(); ?></div>

    <div class="add-to-cart-container">
        <?php woocommerce_template_loop_add_to_cart(); ?>
    </div>
</div>
