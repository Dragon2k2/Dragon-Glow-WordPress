<?php
/**
 * Dragon Glow — Product Card (Loop)
 * Override: woocommerce/content-product.php
 * Glass card + hover image swap + Quick Add button + star rating
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( empty( $product ) || ! $product->is_visible() ) {
    return;
}

// Get images
$img1 = get_the_post_thumbnail_url( null, 'dg-product-card' ) ?: wc_placeholder_img_src();

// Second image: first gallery image
$gallery_ids = $product->get_gallery_image_ids();
$img2 = $gallery_ids ? wp_get_attachment_image_url( $gallery_ids[0], 'dg-product-card' ) : $img1;

// Badge logic
$badge = '';
if ( $product->is_featured() ) {
    $badge = 'new';
} elseif ( $product->get_attribute( 'badge' ) ) {
    $badge = $product->get_attribute( 'badge' );
}
?>
<div <?php wc_product_class( 'product-card-glass rounded-2xl overflow-hidden group relative flex flex-col h-full', $product ); ?>>

    <!-- Image Area -->
    <div class="relative aspect-[4/5] overflow-hidden">

        <!-- Primary image (hides on hover) -->
        <a href="<?php the_permalink(); ?>">
            <img src="<?php echo esc_url( $img1 ); ?>"
                 alt="<?php echo esc_attr( get_the_title() ); ?>"
                 class="w-full h-full object-cover transition-all duration-700 group-hover:scale-110 group-hover:opacity-0 absolute inset-0"
                 loading="lazy" />
        </a>

        <!-- Secondary image (shows on hover) -->
        <?php if ( $img2 !== $img1 ) : ?>
        <a href="<?php the_permalink(); ?>">
            <img src="<?php echo esc_url( $img2 ); ?>"
                 alt="<?php echo esc_attr( get_the_title() ) . ' ' . esc_attr__( 'texture', 'dragon-glow' ); ?>"
                 class="w-full h-full object-cover transition-all duration-700 opacity-0 group-hover:opacity-100 group-hover:scale-105 absolute inset-0"
                 loading="lazy" />
        </a>
        <?php endif; ?>

        <!-- Badge -->
        <?php if ( $badge ) : ?>
        <div class="absolute top-4 left-4 z-10">
            <span class="badge-<?php echo esc_attr( $badge ); ?>">
                <?php echo esc_html( ucfirst( $badge ) ); ?>
            </span>
        </div>
        <?php endif; ?>

        <!-- Sale Badge -->
        <?php if ( $product->is_on_sale() ) : ?>
        <div class="absolute top-4 <?php echo $badge ? 'left-24' : 'left-4'; ?> z-10">
            <span class="badge-new">
                <?php
                $percentage = round( ( ( $product->get_regular_price() - $product->get_sale_price() ) / $product->get_regular_price() ) * 100 );
                printf( esc_html__( 'Sale %d%%', 'dragon-glow' ), $percentage );
                ?>
            </span>
        </div>
        <?php endif; ?>

        <!-- Wishlist button -->
        <?php if ( is_user_logged_in() ) : ?>
        <button class="absolute top-4 right-4 z-10 w-9 h-9 rounded-full bg-white/70 backdrop-blur flex items-center justify-center text-primary hover:bg-white transition-all dg-wishlist-btn"
                data-product-id="<?php echo esc_attr( $product->get_id() ); ?>"
                aria-label="<?php esc_attr_e( 'Add to Wishlist', 'dragon-glow' ); ?>">
            <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' <?php echo dg_in_wishlist( $product->get_id() ) ? '1' : '0'; ?>">favorite</span>
        </button>
        <?php endif; ?>

        <!-- Quick Add button (hover reveal) -->
        <?php
        $product_type = $product->get_type();
        $button_text  = ( 'simple' === $product_type ) ? __( 'Quick Add', 'dragon-glow' ) : __( 'View Options', 'dragon-glow' );
        ?>
        <button class="absolute bottom-4 left-4 right-4 bg-primary text-on-primary py-3 rounded-xl font-label-sm text-label-sm opacity-0 translate-y-4 group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-300 hover:brightness-110 wc-add-to-cart-btn z-10"
                data-product-id="<?php echo esc_attr( $product->get_id() ); ?>"
                data-product-slug="<?php echo esc_attr( $product->get_slug() ); ?>"
                data-product-type="<?php echo esc_attr( $product_type ); ?>">
            <?php echo esc_html( $button_text ); ?>
        </button>
    </div>

    <!-- Info Area -->
    <div class="p-6 flex flex-col flex-1 bg-white/40">
        <div class="flex justify-between items-start mb-2 gap-4">
            <h3 class="font-headline text-[18px] text-on-surface leading-tight">
                <a href="<?php the_permalink(); ?>" class="hover:text-primary transition-colors">
                    <?php the_title(); ?>
                </a>
            </h3>
            <span class="font-bold text-primary text-sm whitespace-nowrap"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
        </div>

        <p class="text-on-surface-variant text-sm mb-4 line-clamp-2 flex-grow">
            <?php echo esc_html( wp_trim_words( get_the_excerpt(), 12 ) ); ?>
        </p>

        <div class="mt-auto">
            <?php dg_star_rating( (float) $product->get_average_rating(), $product->get_review_count() ); ?>
        </div>
    </div>

</div>
