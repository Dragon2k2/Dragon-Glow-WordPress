<?php
/**
 * Dragon Glow — Global Navigation
 * Glassmorphism sticky nav: logo | menu | search + icons
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

// Disable all server caching for this template
header( 'Cache-Control: no-cache, no-store, must-revalidate, max-age=0' );
header( 'Pragma: no-cache' );
header( 'Expires: 0' );

$cart_count   = dg_get_cart_item_count();
$wishlist_url = get_permalink( get_page_by_path( 'wishlist' ) ) ?: home_url( '/wishlist/' );
?>
<nav class="glass-nav header-nav sticky top-0 z-[100] w-full" role="navigation" aria-label="Primary navigation">
	<div class="dg-nav-container flex justify-between items-center px-margin-mobile md:px-margin-desktop py-4 max-w-container-max-width mx-auto">
		<!-- Logo + Brand Name (left) -->
		<div class="flex items-center lg:gap-3">
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

		<!-- Right side: Icons or Toggle Button -->
		<?php if ( is_user_logged_in() ) : ?>
		<div class="flex items-center gap-3">
			<a href="<?php echo esc_url( dg_is_woocommerce_active() ? dg_account_endpoint_url( '' ) : wp_login_url() ); ?>"
			   class="hidden lg:block p-2 cursor-pointer hover:bg-primary-container/20 rounded-full transition-all text-primary"
			   aria-label="<?php esc_attr_e( 'Account', 'dragon-glow' ); ?>">
				<span class="material-symbols-outlined">person</span>
			</a>

			<a href="<?php echo esc_url( $wishlist_url ); ?>"
			   class="hidden lg:block p-2 cursor-pointer hover:bg-primary-container/20 rounded-full transition-all text-primary relative"
			   aria-label="<?php esc_attr_e( 'Wishlist', 'dragon-glow' ); ?>">
				<span class="material-symbols-outlined">favorite</span>
				<?php echo dg_render_wishlist_count_badge(); ?>
			</a>

			<a href="<?php echo esc_url( dg_get_cart_url() ); ?>"
			   class="hidden lg:block relative p-2 cursor-pointer hover:bg-primary-container/20 rounded-full transition-all text-primary"
			   aria-label="<?php esc_attr_e( 'Cart', 'dragon-glow' ); ?>">
				<span class="material-symbols-outlined">shopping_bag</span>
				<?php echo dg_render_cart_count_badge( $cart_count ); ?>
			</a>

			<button class="lg:hidden p-2 text-primary" id="dg-mobile-menu-toggle" aria-label="<?php esc_attr_e( 'Menu', 'dragon-glow' ); ?>" aria-expanded="false" aria-controls="dg-mobile-menu">
				<span class="material-symbols-outlined">menu</span>
			</button>
		</div>
		<?php else : ?>
		<!-- Auth Buttons: Log In / Sign Up (visible on all breakpoints when logged out) -->
		<div class="dg-nav-auth-group">
			<a href="<?php echo esc_url( home_url( '/my-account/' ) ); ?>"
			   class="dg-nav-auth-btn dg-nav-auth-btn--login">
				<?php esc_html_e( 'Log in', 'dragon-glow' ); ?>
			</a>
			<a href="<?php echo esc_url( home_url( '/register/' ) ); ?>"
			   class="dg-nav-auth-btn dg-nav-auth-btn--signup">
				<?php esc_html_e( 'Sign up', 'dragon-glow' ); ?>
			</a>
		</div>
		<?php endif; ?>
	</div>
</nav>

<!-- Mobile Menu Backdrop -->
<div id="dg-mobile-menu-backdrop"></div>

<!-- Mobile Menu Drawer -->
<div class="lg:hidden" id="dg-mobile-menu">
	<!-- Menu Header -->
	<div class="dg-mobile-menu__header">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" 
		   class="dg-mobile-menu__logo"
		   aria-label="<?php bloginfo( 'name' ); ?> - Home">
			<img src="<?php echo esc_url( add_query_arg( 'ver', DG_VERSION, DG_URI . '/assets/images/logo/logo-mark.webp' ) ); ?>"
			     class="dg-mobile-menu__logo-mark"
			     alt=""
			     decoding="async">
		</a>
		<span class="dg-mobile-menu__brand-name"><?php bloginfo( 'name' ); ?></span>
		<button class="dg-mobile-menu__close" id="dg-mobile-menu-close" aria-label="<?php esc_attr_e( 'Close menu', 'dragon-glow' ); ?>">
			<span class="material-symbols-outlined">close</span>
		</button>
	</div>

	<!-- Primary Menu Items -->
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

	<!-- Sign Out Button — Logged-In Users Only -->
	<?php if ( is_user_logged_in() ) : ?>
	<div class="dg-mobile-menu__auth-group">
		<a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>"
		   class="dg-mobile-menu__auth-btn dg-mobile-menu__auth-btn--logout"
		   data-dg-signout
		   aria-haspopup="dialog">
			<span class="material-symbols-outlined">logout</span>
			<span class="font-body text-body-md"><?php esc_html_e( 'Sign out', 'dragon-glow' ); ?></span>
		</a>
	</div>
	<?php else : ?>
	<!-- Auth Buttons: Log In / Sign Up — Logged-Out Users Only -->
	<div class="dg-mobile-menu__auth-group">
		<a href="<?php echo esc_url( home_url( '/my-account/' ) ); ?>"
		   class="dg-mobile-menu__auth-btn dg-mobile-menu__auth-btn--login">
			<span class="material-symbols-outlined">login</span>
			<span class="font-body text-body-md"><?php esc_html_e( 'Log in', 'dragon-glow' ); ?></span>
		</a>
		<a href="<?php echo esc_url( home_url( '/my-account/register/' ) ); ?>"
		   class="dg-mobile-menu__auth-btn dg-mobile-menu__auth-btn--signup">
			<span class="material-symbols-outlined">person_add</span>
			<span class="font-body text-body-md"><?php esc_html_e( 'Sign up', 'dragon-glow' ); ?></span>
		</a>
	</div>
	<?php endif; ?>
</div>
