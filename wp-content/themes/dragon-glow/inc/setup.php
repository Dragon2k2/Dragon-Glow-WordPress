<?php
/**
 * Dragon Glow — Theme Setup
 * Đăng ký theme supports, nav menus, image sizes.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Theme setup function.
 *
 * @return void
 */
function dg_theme_setup(): void {
    // Translations
    load_theme_textdomain( 'dragon-glow', DG_DIR . '/languages' );

    // WordPress features
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script'
    ) );
    add_theme_support( 'customize-selective-refresh-widgets' );

    // WooCommerce product gallery features
    add_theme_support( 'wc-product-gallery-zoom' );
    add_theme_support( 'wc-product-gallery-lightbox' );
    add_theme_support( 'wc-product-gallery-slider' );

    // WooCommerce theme support
    add_theme_support( 'woocommerce', array(
        'thumbnail_image_width' => 600,
        'single_image_width'    => 900,
        'product_grid'          => array(
            'default_rows'    => 3,
            'min_rows'        => 1,
            'default_columns' => 4,
            'min_columns'     => 1,
            'max_columns'     => 6,
        ),
    ) );

    // Navigation Menus
    register_nav_menus( array(
        'primary'     => __( 'Primary Navigation', 'dragon-glow' ),
        'footer-shop' => __( 'Footer — Shop Links', 'dragon-glow' ),
        'footer-info' => __( 'Footer — Company Links', 'dragon-glow' ),
        'footer-help' => __( 'Footer — Help Links', 'dragon-glow' ),
    ) );

    // Custom image sizes
    add_image_size( 'dg-product-card',   600, 750, true );   // aspect-ratio 4:5
    add_image_size( 'dg-product-hero',   900, 900, true );   // single product main
    add_image_size( 'dg-category-card',  800, 1000, true );  // category grid
    add_image_size( 'dg-hero',          1920, 1080, true );  // homepage hero

    // Custom header
    add_theme_support( 'custom-header', array(
        'default-image'      => '',
        'header-text'        => false,
        'flex-width'         => true,
        'flex-height'        => true,
        'default-preset'    => 'default',
    ) );

    // Custom logo
    add_theme_support( 'custom-logo', array(
        'height'      => 100,
        'width'       => 200,
        'flex-width'  => true,
        'flex-height' => true,
    ) );

    // Add support for editor styles
    add_theme_support( 'editor-styles' );

    // Add support for wide alignment
    add_theme_support( 'align-wide' );
}
add_action( 'after_setup_theme', 'dg_theme_setup' );

/**
 * Set the content width.
 *
 * @return void
 */
function dg_content_width(): void {
    $GLOBALS['content_width'] = 1280;
}
add_action( 'after_setup_theme', 'dg_content_width', 0 );

/**
 * Register widget areas.
 *
 * @return void
 */
function dg_widgets_init(): void {
    register_sidebar( array(
        'name'          => __( 'Blog Sidebar', 'dragon-glow' ),
        'id'            => 'sidebar-blog',
        'description'   => __( 'Add widgets here for blog posts.', 'dragon-glow' ),
        'before_widget' => '<section id="%1$s" class="widget %2$s glass-card rounded-2xl p-6 mb-6">',
        'after_widget'  => '</section>',
        'before_title'  => '<h3 class="widget-title font-headline text-lg text-primary mb-4">',
        'after_title'   => '</h3>',
    ) );
}
add_action( 'widgets_init', 'dg_widgets_init' );

/**
 * Custom excerpt length.
 *
 * @param int $length Excerpt length.
 * @return int
 */
function dg_excerpt_length( int $length ): int {
    return 20;
}
add_filter( 'excerpt_length', 'dg_excerpt_length' );

/**
 * Custom excerpt more.
 *
 * @param string $more More string.
 * @return string
 */
function dg_excerpt_more( string $more ): string {
	return '&hellip;';
}
add_filter( 'excerpt_more', 'dg_excerpt_more' );

/**
 * Load mock product template via query string.
 * URL: /shop/?dg_product=[slug]
 * No rewrite rules required — works immediately on file save,
 * no conflict with WooCommerce's /shop/ page or /product/[slug]/.
 *
 * @return void
 */
function dg_mock_product_template_redirect(): void {
	if ( empty( $_GET['dg_product'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- public template loader, not state-changing
		return;
	}

	$template = get_template_directory() . '/page-templates/template-mock-product.php';
	if ( file_exists( $template ) ) {
		status_header( 200 );
		include $template;
		exit;
	}
}
add_action( 'template_redirect', 'dg_mock_product_template_redirect' );
