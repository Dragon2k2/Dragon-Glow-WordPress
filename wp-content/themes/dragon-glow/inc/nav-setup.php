<?php
/**
 * Dragon Glow — Nav Setup
 * Programmatically creates default pages and assigns them to the primary menu.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register default menu pages and assign them to the primary navigation.
 *
 * Creates: Home (blog), Shop (WooCommerce), Our Story, Contact
 *
 * @return void
 */
function dg_register_default_menu_pages(): void {
    $menu_name     = 'Primary Navigation';
    $menu_location = 'primary';

    // Ensure the menu exists
    $menu = wp_get_nav_menu_object( $menu_name );
    if ( ! $menu ) {
        $menu_id = wp_create_nav_menu( $menu_name );
        if ( is_wp_error( $menu_id ) ) {
            return;
        }
        $menu = wp_get_nav_menu_object( $menu_id );
    }

    $menu_id = (int) $menu->term_id;

    // Assign menu to theme location
    $locations = get_theme_mod( 'nav_menu_locations' );
    $locations[ $menu_location ] = $menu_id;
    set_theme_mod( 'nav_menu_locations', $locations );

    // --- Home (blog posts page) ---
    $home_page = get_page_by_path( 'home' );
    if ( ! $home_page ) {
        $home_page_id = wp_insert_post( array(
            'post_type'    => 'page',
            'post_title'   => 'Home',
            'post_name'    => 'home',
            'post_status' => 'publish',
        ) );
    } else {
        $home_page_id = $home_page->ID;
    }

    // Set blog as front page
    update_option( 'show_on_front', 'page' );
    update_option( 'page_on_front', $home_page_id );

    // --- Shop (WooCommerce) ---
    $shop_page_id = 0;
    if ( dg_is_woocommerce_active() ) {
        $shop_page_id = wc_get_page_id( 'shop' );
    }
    if ( $shop_page_id <= 0 ) {
        $shop_page = get_page_by_path( 'shop' );
        if ( ! $shop_page ) {
            $shop_page_id = wp_insert_post( array(
                'post_type'    => 'page',
                'post_title'   => 'Shop',
                'post_name'    => 'shop',
                'post_status'  => 'publish',
            ) );
        } else {
            $shop_page_id = $shop_page->ID;
        }
    }

    // --- Our Story ---
    $our_story_page = get_page_by_path( 'our-story' );
    if ( ! $our_story_page ) {
        $our_story_page_id = wp_insert_post( array(
            'post_type'    => 'page',
            'post_title'   => 'Our Story',
            'post_name'    => 'our-story',
            'post_status'  => 'publish',
            'page_template' => 'template-our-story.php',
        ) );
    } else {
        $our_story_page_id = $our_story_page->ID;
        // Ensure correct template is set
        update_post_meta( $our_story_page_id, '_wp_page_template', 'template-our-story.php' );
    }

    // --- Contact ---
    $contact_page = get_page_by_path( 'contact' );
    if ( ! $contact_page ) {
        $contact_page_id = wp_insert_post( array(
            'post_type'    => 'page',
            'post_title'   => 'Contact',
            'post_name'    => 'contact',
            'post_status'  => 'publish',
            'page_template' => 'template-contact.php',
        ) );
    } else {
        $contact_page_id = $contact_page->ID;
        update_post_meta( $contact_page_id, '_wp_page_template', 'template-contact.php' );
    }

    // --- Shipping & Returns ---
    $shipping_page = get_page_by_path( 'shipping-returns' );
    if ( ! $shipping_page ) {
        $shipping_page_id = wp_insert_post( array(
            'post_type'    => 'page',
            'post_title'   => 'Shipping & Returns',
            'post_name'    => 'shipping-returns',
            'post_status'  => 'publish',
            'page_template' => 'template-shipping-returns.php',
        ) );
    } else {
        $shipping_page_id = $shipping_page->ID;
        update_post_meta( $shipping_page_id, '_wp_page_template', 'template-shipping-returns.php' );
    }

    // --- FAQ ---
    $faq_page = get_page_by_path( 'faq' );
    if ( ! $faq_page ) {
        $faq_page_id = wp_insert_post( array(
            'post_type'    => 'page',
            'post_title'   => 'FAQ',
            'post_name'    => 'faq',
            'post_status'  => 'publish',
            'page_template' => 'template-faq.php',
        ) );
    } else {
        $faq_page_id = $faq_page->ID;
        update_post_meta( $faq_page_id, '_wp_page_template', 'template-faq.php' );
    }

    // --- Track Your Order ---
    $track_page = get_page_by_path( 'order-tracking' );
    if ( ! $track_page ) {
        $track_page_id = wp_insert_post( array(
            'post_type'    => 'page',
            'post_title'   => 'Track Your Order',
            'post_name'    => 'order-tracking',
            'post_status'  => 'publish',
            'page_template' => 'template-order-tracking.php',
        ) );
    } else {
        $track_page_id = $track_page->ID;
        update_post_meta( $track_page_id, '_wp_page_template', 'template-order-tracking.php' );
    }

    // --- Clear any existing menu items ---
    $existing_items = wp_get_nav_menu_items( $menu_id );
    if ( ! empty( $existing_items ) ) {
        foreach ( $existing_items as $item ) {
            wp_delete_post( $item->db_id, true );
        }
    }

    // --- Add menu items (preserving order) ---
    $menu_items = array();

    // Home
    $home_item_id = wp_update_nav_menu_item(
        $menu_id,
        0,
        array(
            'menu-item-title'  => __( 'Home', 'dragon-glow' ),
            'menu-item-url'    => home_url( '/' ),
            'menu-item-status' => 'publish',
            'menu-item-type'   => 'custom',
        )
    );
    if ( ! is_wp_error( $home_item_id ) ) {
        $menu_items[ $home_item_id ] = 0;
    }

    // Shop
    $shop_url = $shop_page_id > 0
        ? get_permalink( $shop_page_id )
        : home_url( '/shop/' );

    $shop_item_id = wp_update_nav_menu_item(
        $menu_id,
        0,
        array(
            'menu-item-title'  => __( 'Shop', 'dragon-glow' ),
            'menu-item-url'    => $shop_url,
            'menu-item-status' => 'publish',
            'menu-item-type'   => 'custom',
        )
    );
    if ( ! is_wp_error( $shop_item_id ) ) {
        $menu_items[ $shop_item_id ] = 0;
    }

    // Our Story
    $our_story_url = get_permalink( $our_story_page_id );
    if ( ! $our_story_url ) {
        $our_story_url = home_url( '/our-story/' );
    }

    $our_story_item_id = wp_update_nav_menu_item(
        $menu_id,
        0,
        array(
            'menu-item-title'  => __( 'Our Story', 'dragon-glow' ),
            'menu-item-url'    => $our_story_url,
            'menu-item-status' => 'publish',
            'menu-item-type'   => 'custom',
        )
    );
    if ( ! is_wp_error( $our_story_item_id ) ) {
        $menu_items[ $our_story_item_id ] = 0;
    }

    // Contact
    $contact_url = get_permalink( $contact_page_id );
    if ( ! $contact_url ) {
        $contact_url = home_url( '/contact/' );
    }

    $contact_item_id = wp_update_nav_menu_item(
        $menu_id,
        0,
        array(
            'menu-item-title'  => __( 'Contact', 'dragon-glow' ),
            'menu-item-url'    => $contact_url,
            'menu-item-status' => 'publish',
            'menu-item-type'   => 'custom',
        )
    );
    if ( ! is_wp_error( $contact_item_id ) ) {
        $menu_items[ $contact_item_id ] = 0;
    }

    // --- Set menu item order ---
    $order = 0;
    foreach ( $menu_items as $item_id => $parent_id ) {
        wp_update_post( array(
            'ID'         => $item_id,
            'menu_order' => $order++,
        ) );
    }
}
add_action( 'after_switch_theme', 'dg_register_default_menu_pages' );
