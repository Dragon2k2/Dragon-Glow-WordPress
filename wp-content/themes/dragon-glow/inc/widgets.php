<?php
/**
 * Dragon Glow — Widget Areas
 *
 * Đăng ký tất cả sidebar + footer widget areas + custom widgets.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

require_once DG_DIR . '/inc/widgets/class-dg-about-widget.php';

/**
 * Register all widget areas (blog sidebar + 4 footer columns).
 *
 * @return void
 */
function dg_register_widgets(): void {
    // Blog Sidebar
    register_sidebar( array(
        'name'          => __( 'Blog Sidebar', 'dragon-glow' ),
        'id'            => 'sidebar-blog',
        'description'   => __( 'Widgets for blog posts sidebar.', 'dragon-glow' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s glass-card rounded-2xl p-6 mb-6">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="font-headline text-lg text-primary mb-4 border-b border-outline-variant pb-2">',
        'after_title'   => '</h3>',
    ) );

    // Footer Column 1 (Brand)
    register_sidebar( array(
        'name'          => __( 'Footer Column 1 - Brand', 'dragon-glow' ),
        'id'            => 'footer-1',
        'description'   => __( 'Brand info and social links in footer.', 'dragon-glow' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="font-label-sm text-label-sm text-primary uppercase tracking-widest mb-4">',
        'after_title'   => '</h4>',
    ) );

    // Footer Column 2 (Shop)
    register_sidebar( array(
        'name'          => __( 'Footer Column 2 - Shop', 'dragon-glow' ),
        'id'            => 'footer-2',
        'description'   => __( 'Shop links in footer.', 'dragon-glow' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="font-label-sm text-label-sm text-primary uppercase tracking-widest mb-4">',
        'after_title'   => '</h4>',
    ) );

    // Footer Column 3 (Company)
    register_sidebar( array(
        'name'          => __( 'Footer Column 3 - Company', 'dragon-glow' ),
        'id'            => 'footer-3',
        'description'   => __( 'Company links in footer.', 'dragon-glow' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="font-label-sm text-label-sm text-primary uppercase tracking-widest mb-4">',
        'after_title'   => '</h4>',
    ) );

    // Footer Column 4 (Help)
    register_sidebar( array(
        'name'          => __( 'Footer Column 4 - Help', 'dragon-glow' ),
        'id'            => 'footer-4',
        'description'   => __( 'Help and newsletter in footer.', 'dragon-glow' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="font-label-sm text-label-sm text-primary uppercase tracking-widest mb-4">',
        'after_title'   => '</h4>',
    ) );
}
add_action( 'widgets_init', 'dg_register_widgets' );

/**
 * Register custom widgets.
 *
 * @return void
 */
function dg_register_custom_widgets(): void {
    register_widget( 'DG_About_Widget' );
}
add_action( 'widgets_init', 'dg_register_custom_widgets' );
