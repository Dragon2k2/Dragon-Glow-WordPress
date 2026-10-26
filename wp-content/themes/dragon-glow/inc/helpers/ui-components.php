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
	<div class="dg-stars-card">
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
 * @param float  $rating      Rating value (0-5).
 * @param int    $count       Review count.
 * @param string $extra_class Optional extra class on the wrapper (e.g. 'dg-stars-card' to center on shop cards).
 * @return void
 */
function dg_star_rating( float $rating = 5.0, int $count = 0, string $extra_class = '' ): void {
	$rating    = max( 0, min( 5, $rating ) );
	$fractional = $rating - floor( $rating );
	$use_half  = ( $fractional >= 0.5 ) ? 1 : 0;
	$floor     = (int) floor( $rating );
	?>
	<div class="flex items-center gap-0.5 dg-stars<?php echo $extra_class ? ' ' . esc_attr( $extra_class ) : ''; ?>">
		<?php for ( $s = 1; $s <= 5; $s++ ) : ?>
			<?php if ( $s <= $floor ) : ?>
				<span class="material-symbols-outlined" style="--dg-star-fill:1;">star</span>
			<?php elseif ( $s === $floor + 1 && $use_half ) : ?>
				<span class="dg-star-half" style="--dg-star-size:16px;">
					<span class="material-symbols-outlined dg-star-half__fill" style="--dg-star-fill:1;">star</span>
					<span class="material-symbols-outlined dg-star-half__base" style="--dg-star-fill:0;">star</span>
				</span>
			<?php else : ?>
				<span class="material-symbols-outlined" style="--dg-star-fill:0;">star</span>
			<?php endif; ?>
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
 * Each URL is stored in the `dg_{network}_url` theme mod (see
 * inc/footer-customizer.php). Until the store owner sets a brand profile,
 * the URL defaults to the platform home page (opens the app on mobile when
 * installed). Saving an empty value hides the network in the footer.
 * `path` is the 24x24 viewBox SVG path of the brand mark (Simple Icons, CC0).
 *
 * @return array
 */
function dg_get_social_links(): array {
    $networks = array(
        'facebook'  => array(
            'default' => 'https://www.facebook.com/',
            'path'    => 'M9.101 23.691v-7.98H6.627v-3.667h2.474v-1.58c0-4.085 1.848-5.978 5.858-5.978.401 0 .955.042 1.468.103a8.68 8.68 0 0 1 1.141.195v3.325a8.623 8.623 0 0 0-.653-.036 26.805 26.805 0 0 0-.733-.009c-.707 0-1.259.096-1.675.309a1.686 1.686 0 0 0-.679.622c-.258.42-.374.995-.374 1.752v1.297h3.919l-.386 2.103-.287 1.564h-3.246v8.245C19.396 23.238 24 18.179 24 12.044c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.628 3.874 10.35 9.101 11.647Z',
            'label'   => 'Facebook',
        ),
        'instagram' => array(
            'default' => 'https://www.instagram.com/',
            'path'    => 'M7.0301.084c-1.2768.0602-2.1487.264-2.911.5634-.7888.3075-1.4575.72-2.1228 1.3877-.6652.6677-1.075 1.3368-1.3802 2.127-.2954.7638-.4956 1.6365-.552 2.914-.0564 1.2775-.0689 1.6882-.0626 4.947.0062 3.2586.0206 3.6671.0825 4.9473.061 1.2765.264 2.1482.5635 2.9107.308.7889.72 1.4573 1.388 2.1228.6679.6655 1.3365 1.0743 2.1285 1.38.7632.295 1.6361.4961 2.9134.552 1.2773.056 1.6884.069 4.9462.0627 3.2578-.0062 3.668-.0207 4.9478-.0814 1.28-.0607 2.147-.2652 2.9098-.5633.7889-.3086 1.4578-.72 2.1228-1.3881.665-.6682 1.0745-1.3378 1.3795-2.1284.2957-.7632.4966-1.636.552-2.9124.056-1.2809.0692-1.6898.063-4.948-.0063-3.2583-.021-3.6668-.0817-4.9465-.0607-1.2797-.264-2.1487-.5633-2.9117-.3084-.7889-.72-1.4568-1.3876-2.1228C21.2982 1.33 20.628.9208 19.8378.6165 19.074.321 18.2017.1197 16.9244.0645 15.6471.0093 15.236-.005 11.977.0014 8.718.0076 8.31.0215 7.0301.0839m.1402 21.6932c-1.17-.0509-1.8053-.2453-2.2287-.408-.5606-.216-.96-.4771-1.3819-.895-.422-.4178-.6811-.8186-.9-1.378-.1644-.4234-.3624-1.058-.4171-2.228-.0595-1.2645-.072-1.6442-.079-4.848-.007-3.2037.0053-3.583.0607-4.848.05-1.169.2456-1.805.408-2.2282.216-.5613.4762-.96.895-1.3816.4188-.4217.8184-.6814 1.3783-.9003.423-.1651 1.0575-.3614 2.227-.4171 1.2655-.06 1.6447-.072 4.848-.079 3.2033-.007 3.5835.005 4.8495.0608 1.169.0508 1.8053.2445 2.228.408.5608.216.96.4754 1.3816.895.4217.4194.6816.8176.9005 1.3787.1653.4217.3617 1.056.4169 2.2263.0602 1.2655.0739 1.645.0796 4.848.0058 3.203-.0055 3.5834-.061 4.848-.051 1.17-.245 1.8055-.408 2.2294-.216.5604-.4763.96-.8954 1.3814-.419.4215-.8181.6811-1.3783.9-.4224.1649-1.0577.3617-2.2262.4174-1.2656.0595-1.6448.072-4.8493.079-3.2045.007-3.5825-.006-4.848-.0608M16.953 5.5864A1.44 1.44 0 1 0 18.39 4.144a1.44 1.44 0 0 0-1.437 1.4424M5.8385 12.012c.0067 3.4032 2.7706 6.1557 6.173 6.1493 3.4026-.0065 6.157-2.7701 6.1506-6.1733-.0065-3.4032-2.771-6.1565-6.174-6.1498-3.403.0067-6.156 2.771-6.1496 6.1738M8 12.0077a4 4 0 1 1 4.008 3.9921A3.9996 3.9996 0 0 1 8 12.0077',
            'label'   => 'Instagram',
        ),
        'gmail'     => array(
            'default' => 'https://mail.google.com/',
            'path'    => 'M24 5.457v13.909c0 .904-.732 1.636-1.636 1.636h-3.819V11.73L12 16.64l-6.545-4.91v9.273H1.636A1.636 1.636 0 0 1 0 19.366V5.457c0-2.023 2.309-3.178 3.927-1.964L5.455 4.64 12 9.548l6.545-4.91 1.528-1.145C21.69 2.28 24 3.434 24 5.457z',
            'label'   => 'Gmail',
        ),
        'youtube'   => array(
            'default' => 'https://www.youtube.com/',
            'path'    => 'M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z',
            'label'   => 'YouTube',
        ),
    );

    $links = array();
    foreach ( $networks as $network => $data ) {
        $links[ $network ] = array(
            'url'     => (string) dg_get_mod( $network . '_url', $data['default'] ),
            'default' => $data['default'],
            'path'    => $data['path'],
            'label'   => $data['label'],
        );
    }

    return $links;
}

/**
 * Render star-rating HTML using Material Symbols with --dg-star-fill variable.
 * Uses same fill pattern as dg_star_rating() for visual consistency.
 *
 * @param float  $rating       Rating value (e.g. 4.5).
 * @param string $icon_size    CSS font-size for stars (default 16px).
 * @return string
 */
function dg_mock_stars( float $rating, string $icon_size = '16px' ): string {
	$rating     = max( 0, min( 5, $rating ) );
	$fractional = $rating - floor( $rating );
	$use_half   = ( $fractional >= 0.5 ) ? 1 : 0;
	$floor      = (int) floor( $rating );

	$html = '';
	for ( $s = 1; $s <= 5; $s++ ) {
		if ( $s <= $floor ) {
			// Full star — filled
			$html .= '<span class="material-symbols-outlined" style="--dg-star-fill:1;" aria-hidden="true">star</span>';
		} elseif ( $s === $floor + 1 && $use_half ) {
			// Half star — overlay pattern
			$html .= '<span class="dg-star-half" style="--dg-star-size:' . esc_attr( $icon_size ) . ';">';
			$html .= '<span class="material-symbols-outlined dg-star-half__fill" style="--dg-star-fill:1;">star</span>';
			$html .= '<span class="material-symbols-outlined dg-star-half__base" style="--dg-star-fill:0;">star</span>';
			$html .= '</span>';
		} else {
			// Empty star — outline
			$html .= '<span class="material-symbols-outlined" style="--dg-star-fill:0;" aria-hidden="true">star</span>';
		}
	}

	return '<span class="flex items-center gap-0.5" aria-label="' . sprintf( esc_attr__( 'Rating: %s out of 5 stars', 'dragon-glow' ), number_format_i18n( $rating, 1 ) ) . '">' . $html . '</span>';
}

/**
 * Add icon classes to mobile menu items.
 *
 * Maps menu item URLs to icon classes for the mobile navigation drawer.
 * Uses filter on nav_menu_css_class to inject classes like 'dg-mobile-menu-item--home'.
 *
 * CSS then uses ::before with Material Symbols icon to display icon on mobile.
 *
 * @param array    $classes  Array of CSS classes assigned to the menu item.
 * @param WP_Post  $item     The current menu item.
 * @param stdClass $args     An object of wp_nav_menu() arguments.
 * @param int      $depth    Depth of menu item (0-based).
 * @return array
 */
function dg_mobile_menu_item_icon_classes( array $classes, $item, $args, int $depth ): array {
	// Only apply to primary menu in mobile drawer (depth 0 only).
	// The drawer <ul> carries 'dg-mobile-menu__list' in menu_class —
	// the desktop <ul> shares the same `primary` location, so without
	// this guard the icon classes leak onto desktop items.
	if ( 'primary' !== $args->theme_location || 0 !== $depth ) {
		return $classes;
	}
	if ( ! isset( $args->menu_class ) || false === strpos( $args->menu_class, 'dg-mobile-menu__list' ) ) {
		return $classes;
	}

	// Map URL patterns to icon class.
	$item_url = $item->url;
	$home_url = home_url( '/' );

	// Determine icon class based on URL pattern.
	if ( $item_url === $home_url ) {
		$classes[] = 'dg-mobile-menu-item--home';
	} elseif ( false !== strpos( $item_url, '/shop/' ) ) {
		$classes[] = 'dg-mobile-menu-item--shop';
	} elseif ( false !== strpos( $item_url, '/our-story/' ) ) {
		$classes[] = 'dg-mobile-menu-item--our-story';
	} elseif ( false !== strpos( $item_url, '/contact/' ) ) {
		$classes[] = 'dg-mobile-menu-item--contact';
	}

	// DEBUG: Log for troubleshooting (remove in production).
	// error_log( sprintf( 'Menu item: %s | URL: %s | Classes: %s', $item->title, $item_url, implode( ', ', $classes ) ) );

	return $classes;
}
add_filter( 'nav_menu_css_class', 'dg_mobile_menu_item_icon_classes', 10, 4 );

/**
 * Inject mobile menu items (Account, Wishlist, Cart) into the primary menu
 * 
 * This function uses wp_nav_menu_items filter to append items directly
 * into the <ul> element before it closes. This ensures items are semantic
 * <li> elements within the proper <ul> container.
 * 
 * IMPORTANT: Only inject for mobile menu (menu_class contains 'dg-mobile-menu__list').
 * Desktop menu already has icon-based navigation in header.
 * 
 * @hook wp_nav_menu_items — Priority 10, runs AFTER menu items are rendered
 * 
 * @param string $items    HTML output of menu items (<li>...</li>)
 * @param object $args     Menu arguments from wp_nav_menu()
 * 
 * @return string Modified menu items HTML with injected <li> elements
 */
function dg_inject_mobile_menu_items( string $items, object $args ): string {
	// Only inject for primary menu on mobile drawer
	if ( 'primary' !== $args->theme_location ) {
		return $items;
	}

	// Check if this is the mobile menu by looking at menu_class
	// Desktop menu class: 'flex gap-6 items-center'
	// Mobile menu class:  'dg-mobile-menu__list flex flex-col ...'
	if ( ! isset( $args->menu_class ) || false === strpos( $args->menu_class, 'dg-mobile-menu__list' ) ) {
		return $items; // Not mobile menu, skip injection
	}

	// Build injected items HTML
	$divider = sprintf(
		'<li class="menu-item dg-mobile-menu-item--divider"><span class="block border-t border-outline-variant/20 my-2"></span></li>'
	);

	// Active-state detection for injected items. WP core only marks menu
	// items registered in the menu editor as current — these injected
	// <li> elements never match, so detect the current page here and
	// mirror core's classes + aria-current.
	$is_account  = dg_is_woocommerce_active() && function_exists( 'is_account_page' ) && is_account_page();
	$is_wishlist = is_page_template( 'page-templates/template-wishlist.php' ) || is_page( 'wishlist' );
	$is_cart     = dg_is_woocommerce_active() && function_exists( 'is_cart' ) && is_cart();

	$account = sprintf(
		'<li class="menu-item dg-mobile-menu-item--account%s"><a href="%s"%s><span class="font-body text-body-md text-on-surface">%s</span></a></li>',
		$is_account ? ' current-menu-item' : '',
		esc_url( dg_is_woocommerce_active() ? dg_account_endpoint_url( '' ) : wp_login_url() ),
		$is_account ? ' aria-current="page"' : '',
		esc_html__( 'Account', 'dragon-glow' )
	);

	$wishlist = sprintf(
		'<li class="menu-item dg-mobile-menu-item--wishlist%s"><a href="%s"%s><span class="font-body text-body-md text-on-surface">%s</span></a></li>',
		$is_wishlist ? ' current-menu-item' : '',
		esc_url( get_permalink( get_page_by_path( 'wishlist' ) ) ?: home_url( '/wishlist/' ) ),
		$is_wishlist ? ' aria-current="page"' : '',
		esc_html__( 'Wishlist', 'dragon-glow' )
	);

	$cart = sprintf(
		'<li class="menu-item dg-mobile-menu-item--cart%s"><a href="%s"%s><span class="font-body text-body-md text-on-surface">%s</span></a></li>',
		$is_cart ? ' current-menu-item' : '',
		esc_url( dg_get_cart_url() ),
		$is_cart ? ' aria-current="page"' : '',
		esc_html__( 'Cart', 'dragon-glow' )
	);

	// Append injected items to existing menu items
	$items .= $divider . $account . $wishlist . $cart;

	return $items;
}
add_filter( 'wp_nav_menu_items', 'dg_inject_mobile_menu_items', 10, 2 );

/**
 * Render global sign-out confirmation dialog.
 *
 * Displays the same modal on all pages when a user is logged in.
 * Opened by account.js when a `[data-dg-signout]` link is clicked
 * (desktop account nav or mobile menu drawer).
 *
 * The modal is placed outside main content region so it persists
 * when AJAX panel swaps occur on the account page.
 *
 * User info (avatar, name, email) is rendered server-side for the
 * current user, so no client-side fetch is needed.
 *
 * @return void
 */
function dg_render_global_signout_modal(): void {
	// Only render for logged-in users.
	if ( ! is_user_logged_in() ) {
		return;
	}

	$customer     = wp_get_current_user();
	$display_name = trim( (string) $customer->display_name );
	if ( '' === $display_name ) {
		$display_name = (string) $customer->user_login;
	}
	?>
	<div class="dg-signout" id="dg-signout" hidden
		 data-busy-label="<?php echo esc_attr__( 'Signing out…', 'dragon-glow' ); ?>">
		<div class="dg-signout__overlay" data-dg-signout-dismiss></div>
		<div class="dg-signout__dialog"
			 role="alertdialog"
			 aria-modal="true"
			 aria-labelledby="dg-signout-title"
			 aria-describedby="dg-signout-body"
			 tabindex="-1">
			<div class="dg-signout__icon dg-signout__item" aria-hidden="true">
				<span class="material-symbols-outlined">logout</span>
			</div>
			<p class="dg-signout__eyebrow dg-signout__item"><?php esc_html_e( 'Sign out', 'dragon-glow' ); ?></p>
			<h2 class="dg-signout__title dg-signout__item" id="dg-signout-title">
				<?php esc_html_e( 'Leaving so soon?', 'dragon-glow' ); ?>
			</h2>
			<p class="dg-signout__body dg-signout__item" id="dg-signout-body">
				<?php esc_html_e( 'You will need to sign in again to view your orders, addresses and account details.', 'dragon-glow' ); ?>
			</p>

			<div class="dg-signout__user dg-signout__item">
				<span class="dg-signout__avatar" aria-hidden="true">
					<?php echo get_avatar( $customer->ID, 40, '', '', array( 'class' => 'dg-signout__avatar-img' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_avatar returns escaped HTML. ?>
				</span>
				<span class="dg-signout__user-copy">
					<span class="dg-signout__user-label"><?php esc_html_e( 'Signed in as', 'dragon-glow' ); ?></span>
					<span class="dg-signout__user-name"><?php echo esc_html( $display_name ); ?></span>
					<span class="dg-signout__user-email"><?php echo esc_html( $customer->user_email ); ?></span>
				</span>
			</div>

			<div class="dg-signout__actions dg-signout__item">
				<button type="button" class="dg-signout__btn dg-signout__btn--ghost" data-dg-signout-cancel>
					<?php esc_html_e( 'Stay signed in', 'dragon-glow' ); ?>
				</button>
				<button type="button" class="dg-signout__btn dg-signout__btn--danger" data-dg-signout-confirm>
					<span class="material-symbols-outlined dg-signout__btn-icon" aria-hidden="true">logout</span>
					<span class="dg-signout__btn-label"><?php esc_html_e( 'Sign out', 'dragon-glow' ); ?></span>
				</button>
			</div>
		</div>
	</div>
	<?php
}