<?php
/**
 * Dragon Glow — Global Navigation
 * Glassmorphism sticky nav: logo | menu | search + icons
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$cart_count   = dg_get_cart_item_count();
$wishlist_url = get_permalink( get_page_by_path( 'wishlist' ) ) ?: home_url( '/wishlist/' );
?>
<nav class="glass-nav header-nav sticky top-0 z-[100] w-full" role="navigation" aria-label="Primary navigation">
<div class="dg-nav-container flex justify-between items-center px-margin-mobile md:px-margin-desktop py-4 max-w-container-max-width mx-auto">
        <!-- Logo + Brand Name (left) — flex container for desktop inline layout -->
        <div class="flex items-center lg:gap-3">
            <!-- Logo: mark (left) -->
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>"
               class="dg-nav-logo hover:opacity-80 transition-opacity"
               aria-label="<?php bloginfo( 'name' ); ?> - Home">
                <img src="<?php echo esc_url( add_query_arg( 'ver', DG_VERSION, DG_URI . '/assets/images/logo/logo-mark.webp' ) ); ?>"
                     class="dg-nav-brand__mark"
                     width="192" height="192"
                     alt=""
                     decoding="async"
                     fetchpriority="high">
            </a>

            <!-- Brand Name (center on mobile/tablet, right of logo on desktop) -->
            <span class="dg-nav-brand-name font-headline text-headline-md font-bold tracking-tight text-primary">
                <?php bloginfo( 'name' ); ?>
            </span>
        </div>

        <!-- Primary Nav Menu (hidden on mobile/tablet, shown on desktop) -->
        <div class="hidden lg:flex gap-6">
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
        <?php if ( is_user_logged_in() ) : ?>
        <div class="flex items-center gap-3">

            <!-- Account
                 Use dg_account_endpoint_url('') instead of raw get_permalink()
                 so the URL resolves to "/my-account/" even when the WP permalink
                 cache is stale or "woocommerce_myaccount_page_id" is unset — same
                 helper the dashboard nav uses, keeps the header icon in sync. -->
            <a href="<?php echo esc_url( dg_is_woocommerce_active() ? dg_account_endpoint_url( '' ) : wp_login_url() ); ?>"
               class="hidden lg:block p-2 cursor-pointer hover:bg-primary-container/20 rounded-full transition-all text-primary"
               aria-label="<?php esc_attr_e( 'Account', 'dragon-glow' ); ?>">
                <span class="material-symbols-outlined">person</span>
            </a>

            <!-- Wishlist -->
            <a href="<?php echo esc_url( $wishlist_url ); ?>"
               class="hidden lg:block p-2 cursor-pointer hover:bg-primary-container/20 rounded-full transition-all text-primary relative"
               aria-label="<?php esc_attr_e( 'Wishlist', 'dragon-glow' ); ?>">
                <span class="material-symbols-outlined">favorite</span>
                <?php echo dg_render_wishlist_count_badge(); ?>
            </a>

            <!-- Cart -->
            <a href="<?php echo esc_url( dg_get_cart_url() ); ?>"
               class="hidden lg:block relative p-2 cursor-pointer hover:bg-primary-container/20 rounded-full transition-all text-primary"
               aria-label="<?php esc_attr_e( 'Cart', 'dragon-glow' ); ?>">
                <span class="material-symbols-outlined">shopping_bag</span>
                <?php echo dg_render_cart_count_badge( $cart_count ); ?>
            </a>

            <!-- Mobile/Tablet menu toggle (logged in) -->
            <button class="lg:hidden p-2 text-primary" id="dg-mobile-menu-toggle" aria-label="<?php esc_attr_e( 'Menu', 'dragon-glow' ); ?>" aria-expanded="false" aria-controls="dg-mobile-menu">
                <span class="material-symbols-outlined">menu</span>
            </button>
        </div>
        <?php else : ?>
        <!-- Auth: Log in / Sign up (signed-out state) + Mobile menu toggle -->
        <div class="flex items-center gap-3">
            <div class="dg-auth-group">
                <a href="<?php echo esc_url( dg_is_woocommerce_active() ? dg_auth_gate_url_with_return( '' ) : wp_login_url( home_url( add_query_arg( null, null ) ) ) ); ?>" class="dg-auth-group__login">
                    <?php esc_html_e( 'Log in', 'dragon-glow' ); ?>
                </a>
                <a href="<?php echo esc_url( dg_is_woocommerce_active() ? dg_auth_gate_url_with_return( 'register' ) : wp_registration_url() ); ?>" class="dg-auth-group__signup">
                    <span><?php esc_html_e( 'Sign up', 'dragon-glow' ); ?></span>
                </a>
            </div>

            <!-- Mobile/Tablet menu toggle (logged out) -->
            <button class="lg:hidden p-2 text-primary" id="dg-mobile-menu-toggle" aria-label="<?php esc_attr_e( 'Menu', 'dragon-glow' ); ?>" aria-expanded="false" aria-controls="dg-mobile-menu">
                <span class="material-symbols-outlined">menu</span>
            </button>
        </div>
        <?php endif; ?>
    </div>
</nav>

<!-- Mobile Menu Backdrop (outside nav to avoid overflow clip) -->
<div id="dg-mobile-menu-backdrop"></div>

<!-- Mobile/Tablet Nav Drawer (outside nav to avoid overflow clip) -->
<div class="lg:hidden" id="dg-mobile-menu">
    <!-- Menu Header: Logo (left) + Brand Name (center) + Close button (right) -->
    <div class="dg-mobile-menu__header">
        <!-- Logo (left) -->
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" 
           class="dg-mobile-menu__logo"
           aria-label="<?php bloginfo( 'name' ); ?> - Home">
            <img src="<?php echo esc_url( add_query_arg( 'ver', DG_VERSION, DG_URI . '/assets/images/logo/logo-mark.webp' ) ); ?>"
                 class="dg-mobile-menu__logo-mark"
                 alt=""
                 decoding="async">
        </a>

        <!-- Brand Name (center) -->
        <span class="dg-mobile-menu__brand-name"><?php bloginfo( 'name' ); ?></span>

        <!-- Close button (right) -->
        <button class="dg-mobile-menu__close" id="dg-mobile-menu-close" aria-label="<?php esc_attr_e( 'Close menu', 'dragon-glow' ); ?>">
            <span class="material-symbols-outlined">close</span>
        </button>
    </div>

    <?php
    wp_nav_menu( array(
        'theme_location' => 'primary',
        'container'      => false,
        'menu_class'     => 'dg-mobile-menu__list flex flex-col px-margin-mobile py-4 gap-4 border-t border-outline-variant/20',
        'fallback_cb'    => false,
        'link_before'    => '<span class="font-body text-body-md text-on-surface">',
        'link_after'     => '</span>',
        'item_spacing'   => 'discard',
    ) );
    ?>
</div>
