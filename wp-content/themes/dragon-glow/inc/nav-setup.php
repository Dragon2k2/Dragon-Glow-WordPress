<?php
/**
 * Dragon Glow — Nav Setup
 *
 * Programmatically creates default pages and assigns them to the primary menu.
 *
 * Each page is created via dg_ensure_page() (helpers/utilities.php) which
 * handles insert-or-update idempotently. The menu items are added in a single
 * pass and assigned to the `primary` location.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Page map — slug => [title, template]. Used by dg_register_default_menu_pages()
 * to create pages and build the primary menu.
 *
 * @return array<string, array{0: string, 1: string}>
 */
function dg_nav_setup_pages_map(): array {
	$pages = array(
		'home'             => array( 'Home', '' ),
		'shop'             => array( 'Shop', '' ),
		'our-story'        => array( 'Our Story', 'template-our-story.php' ),
		'contact'          => array( 'Contact', 'template-contact.php' ),
		'shipping-returns' => array( 'Shipping & Returns', 'template-shipping-returns.php' ),
		'faq'              => array( 'FAQ', 'template-faq.php' ),
		'help-center'      => array( 'Help Center', 'template-help-center.php' ),
		'wishlist'         => array( 'Wishlist', 'template-wishlist.php' ),
	);

	/**
	 * Lọc danh sách page mặc định trước khi tạo.
	 *
	 * @param array $pages Map slug => [title, template].
	 */
	return (array) apply_filters( 'dg_nav_setup_pages_map', $pages );
}

/**
 * Register default menu pages and assign them to the primary navigation.
 *
 * Creates: Home, Shop, Our Story, Contact, Shipping & Returns, FAQ, Help Center,
 * Wishlist. The Wishlist page id is persisted to the `dg_wishlist_page_id`
 * option so other modules (header nav, account wishlist panel) can resolve it
 * without re-running page-by-slug lookups on every request.
 *
 * @return void
 */
function dg_register_default_menu_pages(): void {
	$menu_name     = 'Primary Navigation';
	$menu_location = 'primary';

	// Ensure the menu exists.
	$menu = wp_get_nav_menu_object( $menu_name );
	if ( ! $menu ) {
		$menu_id = wp_create_nav_menu( $menu_name );
		if ( is_wp_error( $menu_id ) ) {
			return;
		}
		$menu = wp_get_nav_menu_object( $menu_id );
	}

	$menu_id = (int) $menu->term_id;

	// Assign menu to theme location.
	$locations = get_theme_mod( 'nav_menu_locations' );
	$locations[ $menu_location ] = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );

	// Set blog as front page (uses Home as the front).
	$home_page_id = (int) dg_ensure_page( 'home', 'Home', '' );
	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $home_page_id );

	// Ensure each configured page exists.
	$page_ids = array(
		'home'             => $home_page_id,
		'shop'             => 0, // resolved below depending on WC availability
		'our-story'        => 0,
		'contact'          => 0,
		'shipping-returns' => 0,
		'faq'              => 0,
		'help-center'      => 0,
		'wishlist'         => 0,
	);

	// Shop: prefer WC shop page id, otherwise fall back to creating a Page.
	if ( dg_is_woocommerce_active() ) {
		$page_ids['shop'] = (int) wc_get_page_id( 'shop' );
	}
	if ( $page_ids['shop'] <= 0 ) {
		$page_ids['shop'] = (int) dg_ensure_page( 'shop', 'Shop', '' );
	}

	// Other editorial pages — straight pass through dg_ensure_page().
	$map = dg_nav_setup_pages_map();
	foreach ( array( 'our-story', 'contact', 'shipping-returns', 'faq', 'help-center', 'wishlist' ) as $slug ) {
		$cfg           = $map[ $slug ] ?? array( ucwords( str_replace( '-', ' ', $slug ) ), '' );
		$page_ids[ $slug ] = (int) dg_ensure_page( $slug, $cfg[0], $cfg[1] );
	}

	// Persist wishlist page id so it can be referenced by header-nav +
	// account wishlist panel without re-resolving the slug every request.
	if ( $page_ids['wishlist'] > 0 ) {
		update_option( 'dg_wishlist_page_id', $page_ids['wishlist'] );
	}

	// Clear any existing menu items — we'll rebuild from the map.
	$existing_items = wp_get_nav_menu_items( $menu_id );
	if ( ! empty( $existing_items ) ) {
		foreach ( $existing_items as $item ) {
			wp_delete_post( $item->db_id, true );
		}
	}

	// Add menu items in the configured order. Slug 'home' and 'shop' use
	// special URLs (home_url + shop permalink); the rest use get_permalink().
	$menu_items = array();
	$order      = 0;

	$entries = array(
		array(
			'label' => __( 'Home', 'dragon-glow' ),
			'url'   => home_url( '/' ),
		),
		array(
			'label' => __( 'Shop', 'dragon-glow' ),
			'url'   => $page_ids['shop'] > 0 ? get_permalink( $page_ids['shop'] ) : home_url( '/shop/' ),
		),
		array(
			'label' => __( 'Our Story', 'dragon-glow' ),
			'url'   => $page_ids['our-story'] > 0 ? get_permalink( $page_ids['our-story'] ) : home_url( '/our-story/' ),
		),
		array(
			'label' => __( 'Contact', 'dragon-glow' ),
			'url'   => $page_ids['contact'] > 0 ? get_permalink( $page_ids['contact'] ) : home_url( '/contact/' ),
		),
		array(
			'label' => __( 'Help Center', 'dragon-glow' ),
			'url'   => $page_ids['help-center'] > 0 ? get_permalink( $page_ids['help-center'] ) : home_url( '/help-center/' ),
		),
	);

	foreach ( $entries as $entry ) {
		$item_id = wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-title'  => $entry['label'],
				'menu-item-url'    => $entry['url'],
				'menu-item-status' => 'publish',
				'menu-item-type'   => 'custom',
			)
		);
		if ( ! is_wp_error( $item_id ) ) {
			wp_update_post(
				array(
					'ID'         => $item_id,
					'menu_order' => $order++,
				)
			);
		}
	}
}
add_action( 'after_switch_theme', 'dg_register_default_menu_pages' );
