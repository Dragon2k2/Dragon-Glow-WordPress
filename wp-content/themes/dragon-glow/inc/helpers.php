<?php
/**
 * Dragon Glow — Helper Functions
 * Utility functions dùng chung.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Check if WooCommerce is active.
 *
 * Single source of truth for WooCommerce availability checks throughout the theme.
 * Use this instead of repeated class_exists('WooCommerce') checks.
 *
 * @return bool
 */
function dg_is_woocommerce_active(): bool {
	return class_exists( 'WooCommerce' );
}

/**
 * Get the URL of the internal mock checkout page.
 *
 * @return string
 */
function dg_get_mock_checkout_url(): string {
	// Check for a published page using the mock checkout template.
	$pages = get_posts(
		array(
			'post_type'      => 'page',
			'posts_per_page' => 1,
			'meta_key'       => '_wp_page_template',
			'meta_value'     => 'page-templates/template-mock-checkout.php',
			'post_status'    => 'publish',
		)
	);

	if ( ! empty( $pages ) ) {
		return get_permalink( $pages[0] );
	}

	$fallback = get_page_by_path( 'mock-checkout' );
	if ( $fallback ) {
		return get_permalink( $fallback );
	}

	return home_url( '/shop/' );
}

/**
 * Check if the current page is the mock checkout page.

/**
 * Render star rating HTML.
 *
 * @param float $rating Rating value (0-5).
 * @param int   $count  Review count.
 * @return void
 */
function dg_star_rating( float $rating = 5.0, int $count = 0 ): void {
    $rating = max( 0, min( 5, $rating ) );
    ?>
    <div class="flex items-center gap-0.5 text-tertiary-container dg-stars">
        <?php for ( $i = 1; $i <= 5; $i++ ) : ?>
            <?php
            $fill = $i <= $rating ? '1' : '0';
            printf(
                '<span class="material-symbols-outlined text-[16px]" style="--dg-star-fill:%s">star</span>',
                esc_attr( $fill )
            );
            ?>
        <?php endfor; ?>
        <?php if ( $count ) : ?>
            <span class="text-[12px] text-on-surface-variant ml-1">(<?php echo esc_html( $count ); ?>)</span>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Check if product is in wishlist.
 *
 * @param int $product_id Product ID.
 * @return bool
 */
function dg_in_wishlist( int $product_id ): bool {
    if ( ! is_user_logged_in() ) {
        return false;
    }

    $wishlist = (array) get_user_meta( get_current_user_id(), 'dg_wishlist', true );
    return in_array( $product_id, $wishlist, true );
}

/**
 * Get wishlist product IDs.
 *
 * @return array
 */
function dg_get_wishlist(): array {
    if ( ! is_user_logged_in() ) {
        return array();
    }

    return (array) get_user_meta( get_current_user_id(), 'dg_wishlist', true );
}

/**
 * Get wishlist count.
 *
 * @return int
 */
function dg_get_wishlist_count(): int {
    return count( dg_get_wishlist() );
}

/**
 * Render breadcrumb.
 *
 * @return void
 */
function dg_breadcrumb(): void {
    ?>
    <nav class="flex items-center gap-2 text-label-sm font-label-sm text-on-surface-variant mb-8" aria-label="Breadcrumb">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>"
           class="hover:text-primary transition-colors"
           aria-label="<?php esc_attr_e( 'Home', 'dragon-glow' ); ?>">
            <?php esc_html_e( 'Home', 'dragon-glow' ); ?>
        </a>
        <?php if ( is_shop() ) : ?>
            <span class="text-primary font-bold"><?php esc_html_e( 'Shop', 'dragon-glow' ); ?></span>
        <?php elseif ( is_product_category() ) : ?>
            <span>/</span>
            <span class="text-primary font-bold"><?php single_cat_title(); ?></span>
        <?php elseif ( is_product() ) : ?>
            <span>/</span>
            <a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>"
               class="hover:text-primary">
                <?php esc_html_e( 'Shop', 'dragon-glow' ); ?>
            </a>
            <span>/</span>
            <span class="text-primary font-bold"><?php the_title(); ?></span>
        <?php elseif ( is_page() ) : ?>
            <span>/</span>
            <span class="text-primary font-bold"><?php the_title(); ?></span>
        <?php elseif ( is_cart() ) : ?>
            <span>/</span>
            <span class="text-primary font-bold"><?php esc_html_e( 'Your Bag', 'dragon-glow' ); ?></span>
        <?php elseif ( is_checkout() ) : ?>
            <span>/</span>
            <a href="<?php echo esc_url( wc_get_cart_url() ); ?>"
               class="hover:text-primary">
                <?php esc_html_e( 'Your Bag', 'dragon-glow' ); ?>
            </a>
            <span>/</span>
            <span class="text-primary font-bold"><?php esc_html_e( 'Checkout', 'dragon-glow' ); ?></span>
        <?php elseif ( is_account_page() ) : ?>
            <span>/</span>
            <span class="text-primary font-bold"><?php esc_html_e( 'My Account', 'dragon-glow' ); ?></span>
        <?php endif; ?>
    </nav>
    <?php
}

/**
 * Get product categories for filtering.
 *
 * @return array
 */
function dg_get_product_categories(): array {
    $categories = get_terms( array(
        'taxonomy'   => 'product_cat',
        'hide_empty' => true,
        'parent'     => 0,
    ) );

    if ( is_wp_error( $categories ) ) {
        return array();
    }

    return $categories;
}

/**
 * Format price with currency symbol.
 *
 * @param float $price Price.
 * @return string
 */
function dg_format_price( float $price ): string {
	if ( dg_is_woocommerce_active() ) {
		return wc_price( $price );
	}

	return '$' . number_format( $price, 2 );
}

/**
 * Truncate text with ellipsis.
 *
 * @param string $text   Text to truncate.
 * @param int    $length Maximum length.
 * @return string
 */
function dg_truncate( string $text, int $length = 100 ): string {
    if ( strlen( $text ) <= $length ) {
        return $text;
    }

    return substr( $text, 0, $length ) . '&hellip;';
}

/**
 * Get theme customizer settings.
 *
 * @param string $key     Setting key.
 * @param mixed  $default Default value.
 * @return mixed
 */
function dg_get_mod( string $key, $default = null ) {
    return get_theme_mod( $dg_key = 'dg_' . $key, $default );
}

/**
 * Check if we're on a WooCommerce page.
 *
 * @return bool
 */
function dg_is_woocommerce_page(): bool {
	if ( ! dg_is_woocommerce_active() ) {
		return false;
	}

	return is_shop() || is_product_category() || is_product() || is_cart() || is_checkout() || is_account_page();
}

/**
 * Get SVG placeholder for images.
 *
 * @param int    $width  Width.
 * @param int    $height Height.
 * @param string $text   Placeholder text.
 * @return string
 */
function dg_get_placeholder_svg( int $width = 400, int $height = 500, string $text = '' ): string {
    $text = $text ?: get_bloginfo( 'name' );

    return sprintf(
        '<svg width="%d" height="%d" viewBox="0 0 %d %d" xmlns="http://www.w3.org/2000/svg" class="bg-surface-container">
            <rect width="100%%" height="100%%" fill="%s"/>
            <text x="50%%" y="50%%" dominant-baseline="middle" text-anchor="middle" fill="%s" font-family="Plus Jakarta Sans, sans-serif" font-size="14">%s</text>
        </svg>',
        $width,
        $height,
        $width,
        $height,
        esc_attr( '#efeeea' ),
        esc_attr( '#827473' ),
        esc_html( $text )
    );
}

/**
 * Output year for footer.
 *
 * @return void
 */
function dg_copyright_year(): void {
    echo esc_html( date( 'Y' ) );
}

/**
 * Get social media links from customizer.
 *
 * @return array
 */
function dg_get_social_links(): array {
    return array(
        'facebook'  => array(
            'url'   => dg_get_mod( 'facebook_url', '#' ),
            'icon'  => 'public',
            'label' => 'Facebook',
        ),
        'instagram' => array(
            'url'   => dg_get_mod( 'instagram_url', '#' ),
            'icon'  => 'photo_camera',
            'label' => 'Instagram',
        ),
        'tiktok'    => array(
            'url'   => dg_get_mod( 'tiktok_url', '#' ),
            'icon'  => 'movie',
            'label' => 'TikTok',
        ),
        'youtube'    => array(
            'url'   => dg_get_mod( 'youtube_url', '#' ),
            'icon'  => 'play_arrow',
            'label' => 'YouTube',
        ),
    );
}

/**
 * Get primary category for a product.
 *
 * @param int $product_id Product ID.
 * @return object|null
 */
function dg_get_product_primary_category( int $product_id ) {
    $categories = get_the_terms( $product_id, 'product_cat' );

    if ( ! $categories || is_wp_error( $categories ) ) {
        return null;
    }

    // Return first category that's not "Uncategorized"
    foreach ( $categories as $category ) {
        if ( 'uncategorized' !== $category->slug ) {
            return $category;
        }
    }

    return $categories[0];
}

/**
 * Check if the current page is the mock checkout page.
 *
 * @return bool
 */
function dg_is_mock_checkout_page(): bool {
	if ( ! empty( $_GET['dg_mock_checkout'] ) && '1' === $_GET['dg_mock_checkout'] ) {
		return true;
	}

	$template = get_post_meta( get_queried_object_id(), '_wp_page_template', true );
	return 'page-templates/template-mock-checkout.php' === $template;
}
