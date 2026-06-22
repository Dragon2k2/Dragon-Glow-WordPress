<?php
/**
 * Dragon Glow — Global Navigation
 * Glassmorphism sticky nav: logo | menu | search + icons
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$cart_count = dg_get_cart_item_count();
?>
<nav class="glass-nav sticky top-0 z-[100] w-full" role="navigation" aria-label="Primary navigation">
<div class="flex justify-between items-center px-margin-mobile md:px-margin-desktop py-4 max-w-container-max-width mx-auto">
        <!-- Logo -->
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>"
           class="font-headline text-headline-md font-bold tracking-tight text-primary hover:opacity-80 transition-opacity"
           aria-label="<?php bloginfo( 'name' ); ?> - Home">
            <?php bloginfo( 'name' ); ?>
        </a>

        <!-- Primary Nav Menu -->
        <div class="hidden md:flex gap-6">
            <?php
            if ( has_nav_menu( 'primary' ) ) {
                wp_nav_menu( array(
                    'theme_location' => 'primary',
                    'container'      => false,
                    'menu_class'     => 'flex gap-6 items-center',
                    'fallback_cb'    => false,
                    'items_wrap'     => '<ul id="%1$s" class="%2$s">%3$s</ul>',
                    'item_spacing'   => 'discard',
                    'link_before'    => '<span class="font-body text-body-md text-on-surface hover:text-primary transition-colors">',
                    'link_after'     => '</span>',
                ) );
            } else {
                // Fallback menu items
                $shop_url = dg_is_woocommerce_active()
                    ? get_permalink( wc_get_page_id( 'shop' ) )
                    : ( get_permalink( get_page_by_path( 'shop' ) ) ?: home_url( '/shop/' ) );
                $our_story_page = get_page_by_path( 'our-story' );
                $our_story_url  = $our_story_page
                    ? get_permalink( $our_story_page )
                    : esc_url( home_url( '/our-story/' ) );
                $contact_page = get_page_by_path( 'contact' );
                $contact_url  = $contact_page
                    ? get_permalink( $contact_page )
                    : esc_url( home_url( '/contact/' ) );
                ?>
                <ul class="flex gap-6 items-center">
                    <li><a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="font-body text-body-md text-on-surface hover:text-primary transition-colors"><?php esc_html_e( 'Home', 'dragon-glow' ); ?></a></li>
                    <li><a href="<?php echo esc_url( $shop_url ); ?>" class="font-body text-body-md text-on-surface hover:text-primary transition-colors"><?php esc_html_e( 'Shop', 'dragon-glow' ); ?></a></li>
                    <li><a href="<?php echo $our_story_url; ?>" class="font-body text-body-md text-on-surface hover:text-primary transition-colors"><?php esc_html_e( 'About', 'dragon-glow' ); ?></a></li>
                    <li><a href="<?php echo $contact_url; ?>" class="font-body text-body-md text-on-surface hover:text-primary transition-colors"><?php esc_html_e( 'Contact', 'dragon-glow' ); ?></a></li>
                </ul>
                <?php
            }
            ?>
        </div>

        <!-- Icons: Search, Wishlist, Cart, Account -->
        <div class="flex items-center gap-3">


            <!-- Account -->
            <a href="<?php echo esc_url( dg_is_woocommerce_active() ? get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) : wp_login_url() ); ?>"
               class="p-2 cursor-pointer hover:bg-primary-container/20 rounded-full transition-all text-primary"
               aria-label="<?php esc_attr_e( 'Account', 'dragon-glow' ); ?>">
                <span class="material-symbols-outlined">person</span>
            </a>

            <!-- Wishlist -->
            <?php
            $wishlist_page_id = get_option( 'dg_wishlist_page_id' );
            $wishlist_url = $wishlist_page_id
                ? get_permalink( $wishlist_page_id )
                : home_url( '/wishlist/' );
            ?>
            <a href="<?php echo esc_url( $wishlist_url ); ?>"
               class="p-2 cursor-pointer hover:bg-primary-container/20 rounded-full transition-all text-primary"
               aria-label="<?php esc_attr_e( 'Wishlist', 'dragon-glow' ); ?>">
                <span class="material-symbols-outlined">favorite</span>
            </a>

            <!-- Cart -->
            <?php
            $cart_url = dg_is_woocommerce_active()
                ? wc_get_cart_url()
                : home_url( '/cart/' );
            ?>
            <a href="<?php echo esc_url( $cart_url ); ?>"
               class="relative p-2 cursor-pointer hover:bg-primary-container/20 rounded-full transition-all text-primary"
               aria-label="<?php esc_attr_e( 'Cart', 'dragon-glow' ); ?>">
                <span class="material-symbols-outlined">shopping_bag</span>
                <?php echo dg_render_cart_count_badge( $cart_count ); ?>
            </a>

            <!-- Mobile menu toggle -->
            <button class="md:hidden p-2 text-primary" id="dg-mobile-menu-toggle" aria-label="<?php esc_attr_e( 'Menu', 'dragon-glow' ); ?>" aria-expanded="false" aria-controls="dg-mobile-menu">
                <span class="material-symbols-outlined">menu</span>
            </button>
        </div>
    </div>

    <!-- Mobile Nav Drawer -->
    <div class="hidden md:hidden" id="dg-mobile-menu">
        <?php
        wp_nav_menu( array(
            'theme_location' => 'primary',
            'container'      => false,
            'menu_class'     => 'flex flex-col px-margin-mobile py-4 gap-4 border-t border-outline-variant/20',
            'fallback_cb'    => false,
            'link_before'    => '<span class="font-body text-body-md text-on-surface">',
            'link_after'     => '</span>',
        ) );
        ?>

        <!-- Mobile Search -->
        <div class="px-margin-mobile pb-4">
            <?php get_search_form(); ?>
        </div>
    </div>
</nav>
