<?php
/**
 * Dragon Glow — Helpers: UI Components
 * Presentational helpers that render markup: star ratings, breadcrumb,
 * placeholder SVG, footer year, and social links config.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Render star rating HTML for WooCommerce products (card layout).
 *
 * Uses Material Symbols icons with inline styles.
 * Stars are centered with gap and bottom margin for placement above product name.
 *
 * @param float $rating Rating value (0-5).
 * @param int   $count  Review count (optional).
 * @return void
 */
function dg_wc_stars( float $rating = 5.0, int $count = 0 ): void {
	$rating  = max( 0, min( 5, $rating ) );
	$fractional = $rating - floor( $rating );
	$use_half  = ( $fractional >= 0.5 ) ? 1 : 0;
	$floor     = (int) floor( $rating );
	?>
	<div class="dg-stars-card" style="display:flex;align-items:center;justify-content:center;gap:3px;margin-bottom:6px;">
		<?php for ( $s = 1; $s <= 5; $s++ ) : ?>
			<?php if ( $s <= $floor ) : ?>
				<span class="material-symbols-outlined dg-star" style="--dg-star-fill:1;">star</span>
			<?php elseif ( $s === $floor + 1 && $use_half ) : ?>
				<span class="dg-star-half" style="--dg-star-size:20px;font-size:20px;">
					<span class="material-symbols-outlined dg-star-half__fill" style="--dg-star-fill:1;">star</span>
					<span class="material-symbols-outlined dg-star-half__base" style="--dg-star-fill:0;">star</span>
				</span>
			<?php else : ?>
				<span class="material-symbols-outlined dg-star" style="--dg-star-fill:0;">star</span>
			<?php endif; ?>
		<?php endfor; ?>
		<?php if ( $count ) : ?>
			<span class="dg-star-count">(<?php echo esc_html( $count ); ?>)</span>
		<?php endif; ?>
	</div>
	<?php
}

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
            <a href="<?php echo esc_url( dg_get_cart_url() ); ?>"
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
 * Render star-rating HTML using Material Symbols.
 *
 * @param float  $rating       Rating value (e.g. 4.5).
 * @param string $icon_size    CSS font-size for stars (default 16px).
 * @return string
 */
function dg_mock_stars( float $rating, string $icon_size = '16px' ): string {
	$full  = (int) floor( $rating );
	$half  = $rating - $full >= 0.5 ? 1 : 0;
	$empty = 5 - $full - $half;

	$html = '';
	for ( $i = 0; $i < $full; $i++ ) {
		$html .= '<span class="material-symbols-outlined" style="font-size:' . esc_attr( $icon_size ) . ';color:#d4a017;" aria-hidden="true">star</span>';
	}
	for ( $i = 0; $i < $half; $i++ ) {
		$html .= '<span class="material-symbols-outlined" style="font-size:' . esc_attr( $icon_size ) . ';color:#d4a017;" aria-hidden="true">star_half</span>';
	}
	for ( $i = 0; $i < $empty; $i++ ) {
		$html .= '<span class="material-symbols-outlined" style="font-size:' . esc_attr( $icon_size ) . ';color:#d4a017;opacity:0.3;" aria-hidden="true">star</span>';
	}

	return '<span class="flex items-center gap-0.5" aria-label="' . sprintf( esc_attr__( 'Rating: %s out of 5 stars', 'dragon-glow' ), number_format_i18n( $rating, 1 ) ) . '">' . $html . '</span>';
}