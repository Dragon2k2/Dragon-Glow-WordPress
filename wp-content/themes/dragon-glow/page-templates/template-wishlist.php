<?php
/**
 * Template Name: Wishlist - Dragon Glow
 * Description: Grid san pham + empty state
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

// Check if WooCommerce is active
if ( ! dg_is_woocommerce_active() ) {
    get_header();
    ?>
    <main id="main-content">
        <div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-12 text-center">
            <h1 class="font-headline text-headline-lg text-primary mb-4"><?php esc_html_e( 'WooCommerce Required', 'dragon-glow' ); ?></h1>
            <p class="text-on-surface-variant mb-8"><?php esc_html_e( 'Please install and activate WooCommerce to use the wishlist feature.', 'dragon-glow' ); ?></p>
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn-primary"><?php esc_html_e( 'Go Home', 'dragon-glow' ); ?></a>
        </div>
    </main>
    <?php
    get_footer();
    return;
}

// Check if user is logged in
if ( ! is_user_logged_in() ) {
    wp_redirect( wp_login_url( get_permalink() ) );
    exit;
}

get_header();

$wishlist = dg_get_wishlist();
$shop_url = get_permalink( wc_get_page_id( 'shop' ) );
?>

<main id="main-content">
    <div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-12">
        <?php get_template_part( 'template-parts/global/breadcrumb' ); ?>

        <h1 class="font-headline text-headline-lg text-primary mb-4">
            <?php esc_html_e( 'My Wishlist', 'dragon-glow' ); ?>
        </h1>
        <p class="text-on-surface-variant text-body-lg mb-12">
            <?php
            printf(
                esc_html( _n( '%d item saved', '%d items saved', count( $wishlist ), 'dragon-glow' ) ),
                count( $wishlist )
            );
            ?>
        </p>

        <?php if ( ! empty( $wishlist ) ) : ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" id="dg-wishlist-grid">
            <?php
            foreach ( $wishlist as $product_id ) :
                $product = wc_get_product( $product_id );

                if ( ! $product ) {
                    continue;
                }

                $img = get_the_post_thumbnail_url( $product_id, 'dg-product-card' ) ?: wc_placeholder_img_src();
            ?>
            <article class="product-card-glass rounded-2xl overflow-hidden group relative" data-product-id="<?php echo esc_attr( $product_id ); ?>">
                <button type="button"
                        class="dg-remove-wishlist absolute top-4 right-4 z-10 w-9 h-9 rounded-full bg-white/70 backdrop-blur flex items-center justify-center text-primary hover:bg-error hover:text-white transition-all"
                        data-product-id="<?php echo esc_attr( $product_id ); ?>"
                        aria-label="<?php esc_attr_e( 'Remove from wishlist', 'dragon-glow' ); ?>">
                    <span class="material-symbols-outlined text-[18px]">close</span>
                </button>

                <a href="<?php echo esc_url( get_permalink( $product_id ) ); ?>" class="block">
                    <div class="aspect-[4/5] overflow-hidden">
                        <img src="<?php echo esc_url( $img ); ?>"
                             alt="<?php echo esc_attr( $product->get_name() ); ?>"
                             class="w-full h-full object-cover transition-transform group-hover:scale-105"
                             loading="lazy" />
                    </div>
                </a>

                <div class="p-6 bg-white/40">
                    <h3 class="font-headline text-[18px] text-on-surface mb-2">
                        <a href="<?php echo esc_url( get_permalink( $product_id ) ); ?>" class="hover:text-primary transition-colors">
                            <?php echo esc_html( $product->get_name() ); ?>
                        </a>
                    </h3>

                    <div class="flex items-center justify-between mb-4">
                        <span class="font-bold text-primary"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
                        <?php dg_star_rating( (float) $product->get_average_rating(), $product->get_review_count() ); ?>
                    </div>

                    <?php if ( $product->is_purchasable() && $product->is_in_stock() ) : ?>
                    <button type="button"
                            class="w-full bg-primary text-white py-3 rounded-xl font-medium hover:brightness-110 transition-all wc-add-to-cart-btn"
                            data-product-id="<?php echo esc_attr( $product_id ); ?>"
                            data-product-slug="<?php echo esc_attr( $product->get_slug() ); ?>"
                            data-product-type="<?php echo esc_attr( $product->get_type() ); ?>">
                        <?php echo 'simple' === $product->get_type() ? esc_html__( 'Add to Bag', 'dragon-glow' ) : esc_html__( 'View Options', 'dragon-glow' ); ?>
                    </button>
                    <?php else : ?>
                    <button type="button" class="w-full bg-surface-container text-on-surface-variant py-3 rounded-xl font-medium cursor-not-allowed" disabled>
                        <?php esc_html_e( 'Out of Stock', 'dragon-glow' ); ?>
                    </button>
                    <?php endif; ?>
                </div>
            </article>
            <?php endforeach; ?>
        </div>

        <?php else : ?>
        <div class="text-center py-24">
            <div class="w-48 h-48 mx-auto bg-surface-container rounded-full flex items-center justify-center mb-8">
                <span class="material-symbols-outlined text-primary" style="font-size: 96px;">favorite</span>
            </div>

            <h2 class="font-headline text-headline-md text-primary mb-4">
                <?php esc_html_e( 'Your wishlist is empty', 'dragon-glow' ); ?>
            </h2>

            <p class="text-on-surface-variant text-body-lg max-w-md mx-auto mb-8">
                <?php esc_html_e( 'Save your favorite products here so you can easily find them later. Start exploring our collection!', 'dragon-glow' ); ?>
            </p>

            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="<?php echo esc_url( $shop_url ); ?>" class="btn-primary">
                    <?php esc_html_e( 'Shop Now', 'dragon-glow' ); ?>
                </a>
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn-ghost">
                    <?php esc_html_e( 'Back to Home', 'dragon-glow' ); ?>
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</main>

<?php get_footer(); ?>
