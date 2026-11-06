<?php
/**
 * Render the header-nav template with stubbed WordPress functions, so the
 * emitted markup can be inspected for stray text nodes.
 *
 * Verifies:
 *   - the menu toggle button contains NO text node "Menu"
 *   - its `aria-label` is populated ("Menu, 4 items")
 *   - `data-dg-label` is "Menu"
 *   - the whole .dg-nav-actions block has no bare "Menu" text node
 */

define( 'ABSPATH', __DIR__ );
define( 'DG_VERSION', '1.16.7' );
define( 'DG_URI', 'https://example.test/wp-content/themes/dragon-glow' );

function is_user_logged_in() { return true; }
function get_permalink( $p = null ) { return 'https://example.test/wishlist'; }
function get_page_by_path( $path ) { return (object) array( 'ID' => 1 ); }
function home_url( $path = '/' ) { return 'https://example.test' . $path; }
function bloginfo( $what ) { echo 'Dragon Glow'; }
function esc_url( $url ) { return $url; }
function esc_html_e( $t, $d = null ) { echo $t; }
function esc_attr_e( $t, $d = null ) { echo $t; }
function esc_attr__( $t, $d = null ) { return $t; }
function esc_html__( $t, $d = null ) { return $t; }
function __( $t, $d = null ) { return $t; }
function has_nav_menu( $loc ) { return false; }
function is_page_template( $t ) { return false; }
function is_page( $p ) { return false; }
function is_cart() { return false; }
function is_account_page() { return false; }
function wp_login_url() { return 'https://example.test/login/'; }
function wp_logout_url( $r = '' ) { return 'https://example.test/logout/'; }
function wp_create_nonce( $a ) { return 'nonce'; }
function admin_url( $p = '' ) { return 'https://example.test/wp-admin/' . $p; }
function add_filter() {}
function add_action() {}
function add_theme_support() {}
function register_nav_menus() {}
function load_theme_textdomain() {}

// Theme helpers under test.
function dg_is_woocommerce_active() { return true; }
function dg_get_cart_url() { return 'https://example.test/cart/'; }
function dg_account_endpoint_url( $e = '' ) { return 'https://example.test/my-account/'; }
function dg_get_cart_item_count() { return 1; }
function dg_get_wishlist_count() { return 3; }
function dg_render_cart_count_badge( $count ) {
	$classes = 'dg-cart-count absolute -top-1 -right-1 bg-primary text-on-primary text-[10px] font-bold w-5 h-5 rounded-full flex items-center justify-center';
	if ( 0 === $count ) { $classes .= ' hidden'; }
	return '<span class="' . esc_attr( $classes ) . '">' . esc_html( $count ) . '</span>';
}
function dg_render_wishlist_count_badge( $count = null ) {
	$count = null === $count ? dg_get_wishlist_count() : $count;
	$classes = 'dg-wishlist-count absolute -top-1 -right-1 bg-primary text-on-primary text-[10px] font-bold min-w-[20px] h-5 px-1 rounded-full flex items-center justify-center';
	if ( 0 === $count ) { $classes .= ' hidden'; }
	return '<span class="' . esc_attr( $classes ) . '">' . esc_html( $count ) . '</span>';
}
function dg_render_nav_count_badge( $cart, $wishlist ) {
	$total = max( 0, $cart ) + max( 0, $wishlist );
	$classes = 'dg-nav-badge absolute -top-1 -right-1 bg-primary text-on-primary text-[10px] font-bold min-w-[20px] h-5 px-1 rounded-full flex items-center justify-center';
	if ( 0 === $total ) { $classes .= ' hidden'; }
	return '<span class="' . esc_attr( $classes ) . '">' . esc_html( $total ) . '</span>';
}
function esc_attr( $t ) { return htmlspecialchars( $t, ENT_QUOTES ); }
function esc_html( $t ) { return htmlspecialchars( $t, ENT_QUOTES ); }
function add_query_arg( $k, $v = null, $url = null ) {
	if ( is_array( $k ) ) { return $url; }
	return $url . ( false === strpos( $url, '?' ) ? '?' : '&' ) . $k . '=' . $v;
}
function get_bloginfo( $k = '' ) { return 'Dragon Glow'; }
function wp_get_document_title() { return 'Dragon Glow'; }
function wc_get_page_id( $p ) { return 2; }
function wp_nav_menu( $args ) {
	// Empty menu — the injected items are irrelevant for this check.
	echo '<ul class="' . ( isset( $args['menu_class'] ) ? $args['menu_class'] : '' ) . '"></ul>';
}

$template = __DIR__ . '/../../wp-content/themes/dragon-glow/template-parts/global/header-nav.php';

ob_start();
include $template;
$html = ob_get_clean();

$fail = 0;

if ( preg_match( '#<button[^>]*id="dg-mobile-menu-toggle".*?</button>#s', $html, $m ) ) {
	$button = $m[0];
} else {
	echo "FAIL: menu toggle button not found\n";
	exit( 1 );
}

echo "----- TOGGLE BUTTON HTML -----\n";
echo $button . "\n\n";

if ( preg_match( '#>\s*Menu\s*<#', $button ) ) {
	echo "FAIL: stray text node 'Menu' inside the toggle button\n";
	$fail++;
} else {
	echo "PASS: no stray 'Menu' text node inside the toggle button\n";
}

if ( preg_match( '/aria-label="([^"]*)"/', $button, $am ) ) {
	$aria = $am[1];
	if ( '' === trim( $aria ) ) {
		echo "FAIL: aria-label is EMPTY\n";
		$fail++;
	} else {
		echo "PASS: aria-label = \"$aria\"\n";
	}
} else {
	echo "FAIL: aria-label attribute missing\n";
	$fail++;
}

if ( preg_match( '/data-dg-label="([^"]*)"/', $button, $dm ) ) {
	echo "PASS: data-dg-label = \"{$dm[1]}\"\n";
} else {
	echo "FAIL: data-dg-label missing\n";
	$fail++;
}

if ( preg_match( '#<div class="dg-nav-actions.*?</div>#s', $html, $am2 ) ) {
	if ( preg_match( '#>\s*Menu\s*<#', $am2[0] ) ) {
		echo "FAIL: stray 'Menu' text node inside .dg-nav-actions\n";
		$fail++;
	} else {
		echo "PASS: no stray 'Menu' text node inside .dg-nav-actions\n";
	}
}

echo $fail > 0 ? "\nRESULT: FAIL ($fail)\n" : "\nRESULT: PASS\n";
exit( $fail > 0 ? 1 : 0 );
