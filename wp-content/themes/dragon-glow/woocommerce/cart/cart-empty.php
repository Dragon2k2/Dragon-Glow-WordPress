<?php
/**
 * Dragon Glow — Empty Cart
 * Override: woocommerce/cart/cart-empty.php
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * @hooked wc_no_products_found - 10
 */
do_action( 'woocommerce_no_products_found' );
?>
<div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-24 text-center">
    <?php get_template_part( 'template-parts/global/breadcrumb' ); ?>

    <!-- Empty Cart Illustration -->
    <div class="mb-12">
        <div class="w-48 h-48 mx-auto bg-surface-container rounded-full flex items-center justify-center mb-8">
            <span class="material-symbols-outlined text-primary" style="font-size: 96px;">shopping_bag</span>
        </div>

        <h1 class="font-headline text-headline-lg text-primary mb-4">
            <?php esc_html_e( 'Your bag is empty', 'dragon-glow' ); ?>
        </h1>

        <p class="text-on-surface-variant text-body-lg max-w-md mx-auto mb-8">
            <?php esc_html_e( 'Looks like you haven\'t added any products to your ritual cart yet. Let\'s change that!', 'dragon-glow' ); ?>
        </p>
    </div>

    <!-- CTA Buttons -->
    <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <a href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>"
           class="btn-primary">
            <?php esc_html_e( 'Continue Shopping', 'dragon-glow' ); ?>
        </a>

        <?php if ( is_user_logged_in() ) : ?>
        <a href="<?php echo esc_url( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) ); ?>"
           class="btn-ghost">
            <?php esc_html_e( 'View Your Account', 'dragon-glow' ); ?>
        </a>
        <?php else : ?>
        <a href="<?php echo esc_url( wp_login_url() ); ?>"
           class="btn-ghost">
            <?php esc_html_e( 'Login / Register', 'dragon-glow' ); ?>
        </a>
        <?php endif; ?>
    </div>

    <!-- Featured Products Suggestion -->
    <section class="mt-24">
        <h2 class="font-headline text-headline-md text-primary mb-8">
            <?php esc_html_e( 'Popular Right Now', 'dragon-glow' ); ?>
        </h2>

        <?php
        // Get featured products
        $args = array(
            'post_type'      => 'product',
            'posts_per_page' => 4,
            'meta_query'    => array(
                array(
                    'key'   => '_featured',
                    'value' => 'yes',
                ),
            ),
        );

        $products = new WP_Query( $args );

        if ( $products->have_posts() ) :
        ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php
            while ( $products->have_posts() ) :
                $products->the_post();
                global $product;
            ?>
            <div class="product-card-glass rounded-2xl overflow-hidden group">
                <div class="relative aspect-square overflow-hidden">
                    <a href="<?php the_permalink(); ?>">
                        <?php the_post_thumbnail( 'medium', array( 'class' => 'w-full h-full object-cover transition-transform group-hover:scale-105' ) ); ?>
                    </a>

                    <?php if ( $product->is_on_sale() ) : ?>
                        <span class="absolute top-4 left-4 badge-new">
                            <?php esc_html_e( 'Sale', 'dragon-glow' ); ?>
                        </span>
                    <?php endif; ?>
                </div>

                <div class="p-4">
                    <h3 class="font-headline text-on-surface mb-2">
                        <a href="<?php the_permalink(); ?>" class="hover:text-primary">
                            <?php the_title(); ?>
                        </a>
                    </h3>
                    <p class="font-bold text-primary">
                        <?php echo wp_kses_post( $product->get_price_html() ); ?>
                    </p>
                </div>
            </div>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
        <?php endif; ?>
    </section>
</div>
