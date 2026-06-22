<?php
/**
 * Dragon Glow — Best Sellers Carousel
 * WooCommerce query: featured products, horizontal scroll + prev/next arrows
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

// Only run if WooCommerce is active
if ( ! dg_is_woocommerce_active() ) {
    return;
}

$args = array(
    'post_type'      => 'product',
    'posts_per_page' => 8,
    'meta_query'     => array(
        array(
            'key'   => '_featured',
            'value' => 'yes',
        ),
    ),
    'orderby'        => 'date',
    'order'          => 'DESC',
);

$products = new WP_Query( $args );

if ( ! $products->have_posts() ) {
    // Fallback: get latest products
    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => 8,
        'orderby'        => 'date',
        'order'          => 'DESC',
    );
    $products = new WP_Query( $args );
}

if ( ! $products->have_posts() ) {
    return;
}

// Product data from original HTML (for fallback display)
$fallback_products = array(
    array(
        'name'        => 'Nectarine Glow Serum',
        'desc'        => 'Illuminating Vitamin C + Peptides',
        'price'       => '$64',
        'rating'      => 5,
        'reviews'     => 124,
        'badge'       => 'Best Seller',
        'badge_class' => 'bg-tertiary-container text-on-tertiary-container',
        'image'       => get_theme_file_uri( 'assets/images/nectarine-glow-serum.jpg' ),
    ),
    array(
        'name'        => 'Cloud Whipped Cream',
        'desc'        => 'Ceramide Barrier Repair Ritual',
        'price'       => '$58',
        'rating'      => 4.5,
        'reviews'     => 98,
        'badge'       => '',
        'badge_class' => '',
        'image'       => get_theme_file_uri( 'assets/images/cloud-whipped-cream.jpg' ),
    ),
    array(
        'name'        => 'Midnight Dew Mask',
        'desc'        => 'Lavender & Squalane Infusion',
        'price'       => '$48',
        'rating'      => 5,
        'reviews'     => 215,
        'badge'       => 'New Ritual',
        'badge_class' => 'bg-secondary-container text-on-secondary-container',
        'image'       => get_theme_file_uri( 'assets/images/new-ritual-product.jpg' ),
    ),
    array(
        'name'        => 'Petal Silk Cleanser',
        'desc'        => 'Rose & Camellia Oil Melter',
        'price'       => '$38',
        'rating'      => 4,
        'reviews'     => 86,
        'badge'       => '',
        'badge_class' => '',
        'image'       => get_theme_file_uri( 'assets/images/product-04.jpg' ),
    ),
);
?>
<section class="py-section-gap bg-surface-container-low relative overflow-hidden">
    <!-- Floating organic shape background -->
    <div class="absolute top-0 right-0 w-1/2 h-full bg-primary-container/10 blur-[120px] rounded-full translate-x-1/2"></div>

    <div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop relative z-10">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-16 reveal">
            <div>
                <h2 class="font-headline text-headline-lg text-primary mb-4">
                    <?php esc_html_e( 'Glow Favorites', 'dragon-glow' ); ?>
                </h2>
                <p class="text-on-surface-variant">
                    <?php esc_html_e( "Our community's most-loved essentials for a radiant daily ritual.", 'dragon-glow' ); ?>
                </p>
            </div>
            <div class="flex gap-4">
                <button class="p-4 rounded-full border border-outline-variant hover:bg-white transition-all text-primary" id="dg-prev-btn" aria-label="<?php esc_attr_e( 'Previous', 'dragon-glow' ); ?>">
                    <span class="material-symbols-outlined">chevron_left</span>
                </button>
                <button class="p-4 rounded-full border border-outline-variant hover:bg-white transition-all text-primary" id="dg-next-btn" aria-label="<?php esc_attr_e( 'Next', 'dragon-glow' ); ?>">
                    <span class="material-symbols-outlined">chevron_right</span>
                </button>
            </div>
        </div>

        <div class="flex gap-8 overflow-x-auto pb-4 scroll-smooth custom-scrollbar snap-x snap-mandatory" id="dg-carousel">
            <?php
            $delay = 0;
            $has_products = $products->have_posts();

            if ( $has_products ) :
                while ( $products->have_posts() ) :
                    $products->the_post();
                    global $product;

                    $img      = get_the_post_thumbnail_url( null, 'dg-product-card' ) ?: wc_placeholder_img_src();
                    $price    = $product->get_price_html();
                    $stars    = $product->get_average_rating();
                    $count    = $product->get_review_count();
                    $is_sale  = $product->is_on_sale();
                    $is_new   = $product->is_featured();
            ?>
            <article <?php wc_product_class( 'min-w-[320px] glass-card p-4 rounded-3xl group flex-shrink-0 snap-start reveal', $product ); ?>
                     style="transition-delay: <?php echo esc_attr( $delay ); ?>ms">

                <div class="relative overflow-hidden rounded-2xl aspect-square mb-6">
                    <a href="<?php the_permalink(); ?>">
                        <img src="<?php echo esc_url( $img ); ?>"
                             alt="<?php echo esc_attr( get_the_title() ); ?>"
                             class="w-full h-full object-cover transition-transform group-hover:scale-105"
                             loading="lazy" />
                    </a>

                    <?php if ( $is_new ) : ?>
                        <span class="absolute top-4 left-4 bg-secondary-container text-on-secondary-container text-[10px] font-bold px-3 py-1 rounded-full tracking-widest uppercase">
                            <?php esc_html_e( 'New Ritual', 'dragon-glow' ); ?>
                        </span>
                    <?php elseif ( $is_sale ) : ?>
                        <span class="absolute top-4 left-4 bg-tertiary-container text-on-tertiary-container text-[10px] font-bold px-3 py-1 rounded-full tracking-widest uppercase">
                            <?php esc_html_e( 'Sale', 'dragon-glow' ); ?>
                        </span>
                    <?php endif; ?>

                    <button class="dg-quick-add absolute bottom-4 right-4 bg-white/90 backdrop-blur text-primary p-3 rounded-full opacity-0 group-hover:opacity-100 transition-all translate-y-2 group-hover:translate-y-0 active:scale-90 shadow-lg"
                            data-product-id="<?php echo esc_attr( $product->get_id() ); ?>"
                            data-original-label="<?php esc_attr_e( 'Add to cart', 'dragon-glow' ); ?>"
                            aria-label="<?php esc_attr_e( 'Add to cart', 'dragon-glow' ); ?>">
                        <span class="material-symbols-outlined">shopping_bag</span>
                        <span class="sr-only dg-quick-add__label"><?php esc_html_e( 'Add to cart', 'dragon-glow' ); ?></span>
                    </button>
                </div>

                <div class="px-2">
                    <div class="flex justify-between items-start mb-2">
                        <h4 class="font-bold text-lg text-primary">
                            <a href="<?php the_permalink(); ?>" class="hover:underline">
                                <?php the_title(); ?>
                            </a>
                        </h4>
                        <span class="font-bold text-tertiary"><?php echo wp_kses_post( $price ); ?></span>
                    </div>

                    <p class="text-sm text-on-surface-variant mb-4">
                        <?php echo esc_html( wp_trim_words( get_the_excerpt(), 8 ) ); ?>
                    </p>

                    <?php dg_star_rating( (float) $stars, $count ); ?>
                </div>
            </article>
            <?php
                    $delay += 100;
                endwhile;
                wp_reset_postdata();
            else :
                // Use fallback products if no WooCommerce products
                foreach ( $fallback_products as $index => $fp ) :
            ?>
            <article class="min-w-[320px] glass-card p-4 rounded-3xl group flex-shrink-0 snap-start reveal" style="transition-delay: <?php echo esc_attr( $index * 100 ); ?>ms;">
                <div class="relative overflow-hidden rounded-2xl aspect-square mb-6">
                    <img src="<?php echo esc_url( $fp['image'] ); ?>"
                         alt="<?php echo esc_attr( $fp['name'] ); ?>"
                         class="w-full h-full object-cover transition-transform group-hover:scale-105"
                         loading="lazy" />

                    <?php if ( ! empty( $fp['badge'] ) ) : ?>
                    <span class="absolute top-4 left-4 <?php echo esc_attr( $fp['badge_class'] ); ?> text-[10px] font-bold px-3 py-1 rounded-full tracking-widest uppercase">
                        <?php echo esc_html( $fp['badge'] ); ?>
                    </span>
                    <?php endif; ?>

                    <button class="dg-quick-add absolute bottom-4 right-4 bg-white/90 backdrop-blur text-primary p-3 rounded-full opacity-0 group-hover:opacity-100 transition-all translate-y-2 group-hover:translate-y-0 active:scale-90 shadow-lg"
                            data-original-label="<?php esc_attr_e( 'Add to cart', 'dragon-glow' ); ?>"
                            aria-label="<?php esc_attr_e( 'Add to cart', 'dragon-glow' ); ?>">
                        <span class="material-symbols-outlined">shopping_bag</span>
                        <span class="sr-only dg-quick-add__label"><?php esc_attr_e( 'Add to cart', 'dragon-glow' ); ?></span>
                    </button>
                </div>

                <div class="px-2">
                    <div class="flex justify-between items-start mb-2">
                        <h4 class="font-bold text-lg text-primary"><?php echo esc_html( $fp['name'] ); ?></h4>
                        <span class="font-bold text-tertiary"><?php echo esc_html( $fp['price'] ); ?></span>
                    </div>

                    <p class="text-sm text-on-surface-variant mb-4"><?php echo esc_html( $fp['desc'] ); ?></p>

                    <div class="flex items-center gap-1 text-tertiary text-sm">
                        <?php for ( $i = 1; $i <= 5; $i++ ) : ?>
                            <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' <?php echo $i <= $fp['rating'] ? '1' : '0'; ?>;">star</span>
                        <?php endfor; ?>
                        <span class="ml-2 text-on-surface-variant">(<?php echo esc_html( $fp['reviews'] ); ?>)</span>
                    </div>
                </div>
            </article>
            <?php
                endforeach;
            endif;
            ?>
        </div>

        <!-- View All Link -->
        <?php $shop_url = dg_is_woocommerce_active() ? get_permalink( wc_get_page_id( 'shop' ) ) : '#'; ?>
        <div class="text-center mt-12 reveal">
            <a href="<?php echo esc_url( $shop_url ); ?>" class="btn-ghost">
                <?php esc_html_e( 'View All Products', 'dragon-glow' ); ?>
                <span class="material-symbols-outlined align-middle ml-2">arrow_forward</span>
            </a>
        </div>
    </div>
</section>
