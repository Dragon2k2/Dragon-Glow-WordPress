<?php
/**
 * Dragon Glow — WooCommerce: My Account
 *
 * Custom My Account page renderer. Replaces the default WooCommerce
 * `my-account.php` / `dashboard.php` rendering with a Luminous Ethereal
 * layout: brand-hero greeting card + vertical sidebar nav (Dashboard, Orders,
 * Addresses, Account details, Sign out) on desktop / dropdown on
 * mobile + content surface.
 *
 * Routing: `dg_use_wc_account_template()` swaps in `page-templates/template-wc-account.php`
 * for any request on the WC My Account endpoint, so the page-template
 * assignment on the WC account page is irrelevant (mirrors the checkout
 * pattern — see `dg_use_wc_checkout_template()`).
 *
 * Guards:
 *  - When WC is inactive, shows a friendly fallback (login form + register CTA).
 *  - When not logged in, renders the auth form.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Build paginated URL for account endpoints (legacy/fallback).
 *
 * This function exists for backward compatibility with older versions of
 * woocommerce/myaccount/orders.php that may still be cached on the server.
 * New code should use dg_account_endpoint_url() + add_query_arg() directly.
 *
 * @deprecated Use dg_account_endpoint_url() with add_query_arg() instead.
 * @param string $endpoint Endpoint slug.
 * @param int    $page     Page number (1-indexed).
 * @return string
 */
function dg_account_paginated_url( string $endpoint, int $page ): string {
	if ( $page <= 1 ) {
		return dg_account_endpoint_url( $endpoint );
	}
	return add_query_arg( 'paged', $page, dg_account_endpoint_url( $endpoint ) );
}

/**
 * Get WC account endpoint URL safely.
 *
 * Wrapper around `wc_get_account_endpoint_url()` — only available when WC is
 * active. Falls back to a hand-built URL from the My Account page permalink.
 *
 * @param string $endpoint Endpoint slug (e.g. 'orders', 'edit-account').
 * @return string
 */
function dg_account_endpoint_url( string $endpoint ): string {
	// Build base URL from the configured My Account page id, not from
	// WC's URL builder. WC's wc_get_account_endpoint_url() returns the
	// page permalink which in some environments (especially when
	// permalinks haven't fully flushed or with W3 Total Cache serving
	// stale data) falls back to "?page_id=N" — leaking the query string
	// into nav links and the auth gate's action URLs.
	$page_id = (int) get_option( 'woocommerce_myaccount_page_id' );
	$base    = '';

	if ( $page_id > 0 ) {
		$base = (string) get_permalink( $page_id );
		// `get_permalink()` can return false/'' OR a "?page_id=N" string
		// when WP permalinks aren't pretty (default permalinks) or when
		// the post cache is stale. Only accept clean permalinks —
		// otherwise fall through to the hard-coded "/my-account/".
		if ( false === $base || '' === $base || false !== strpos( $base, 'page_id=' ) || false !== strpos( $base, '?p=' ) ) {
			$base = '';
		}
	}

	// Hard-coded fallback — works even when WP hasn't registered the page
	// in the rewrite rules yet, because it's the path WC's own endpoint
	// system expects.
	if ( '' === $base ) {
		$base = home_url( '/my-account' );
	}
	$base = rtrim( $base, '/' );

	// Empty endpoint = My Account root.
	if ( '' === $endpoint ) {
		return $base . '/';
	}

	return $base . '/' . ltrim( $endpoint, '/' );
}

/**
 * Register custom WooCommerce endpoint for registration page.
 *
 * @return void
 */
function dg_register_custom_account_endpoints(): void {
	add_rewrite_endpoint( 'register', EP_ROOT | EP_PAGES );
	
	// Set a transient to remind admin to flush permalinks after code deployment
	if ( ! get_transient( 'dg_register_endpoint_flushed' ) ) {
		set_transient( 'dg_register_endpoint_flushed', 'pending', DAY_IN_SECONDS );
	}
}
add_action( 'init', 'dg_register_custom_account_endpoints' );

/**
 * Add register endpoint to WooCommerce query vars.
 *
 * @param array<string> $vars Query vars.
 * @return array<string>
 */
function dg_add_register_query_var( array $vars ): array {
	$vars['register'] = 'register';
	return $vars;
}
add_filter( 'woocommerce_get_query_vars', 'dg_add_register_query_var' );

/**
 * Admin notice to remind flushing permalinks after register endpoint addition.
 *
 * @return void
 */
function dg_register_endpoint_admin_notice(): void {
	$status = get_transient( 'dg_register_endpoint_flushed' );
	
	if ( 'pending' !== $status ) {
		return;
	}
	
	$screen = get_current_screen();
	if ( ! $screen || 'options-permalink' === $screen->id ) {
		return; // Don't show on permalinks page itself
	}
	
	?>
	<div class="notice notice-warning is-dismissible">
		<p>
			<strong><?php esc_html_e( 'Dragon Glow: New Account Endpoint Added', 'dragon-glow' ); ?></strong>
		</p>
		<p>
			<?php
			printf(
				/* translators: %s: URL to Permalinks settings page */
				esc_html__( 'The registration page endpoint has been added. Please %s to activate it.', 'dragon-glow' ),
				'<a href="' . esc_url( admin_url( 'options-permalink.php' ) ) . '">' . esc_html__( 'flush permalinks', 'dragon-glow' ) . '</a>'
			);
			?>
		</p>
		<p>
			<em><?php esc_html_e( 'Go to Settings → Permalinks and click "Save Changes" (no need to change anything).', 'dragon-glow' ); ?></em>
		</p>
	</div>
	<?php
}
add_action( 'admin_notices', 'dg_register_endpoint_admin_notice' );

/**
 * Mark permalinks as flushed when admin visits Permalinks settings page.
 *
 * @return void
 */
function dg_mark_permalinks_flushed(): void {
	$screen = get_current_screen();
	
	if ( ! $screen || 'options-permalink' !== $screen->id ) {
		return;
	}
	
	// If admin visited permalinks page, assume they flushed it
	if ( 'pending' === get_transient( 'dg_register_endpoint_flushed' ) ) {
		set_transient( 'dg_register_endpoint_flushed', 'done', MONTH_IN_SECONDS );
	}
}
add_action( 'current_screen', 'dg_mark_permalinks_flushed' );

/**
 * Handle registration form submission.
 *
 * Processes the custom registration form from /my-account/register/ endpoint.
 * Validates input, creates WooCommerce customer account, and saves additional
 * customer meta (skin aspiration, newsletter opt-in).
 *
 * @return void
 */
function dg_process_registration(): void {
	// Only process on register endpoint POST with nonce.
	if ( ! isset( $_POST['register'], $_POST['woocommerce-register-nonce'] ) ) {
		return;
	}

	global $wp_query;
	if ( ! is_account_page() || ! isset( $wp_query->query_vars['register'] ) ) {
		return;
	}

	// Verify nonce.
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['woocommerce-register-nonce'] ) ), 'woocommerce-register' ) ) {
		wc_add_notice( __( 'Security verification failed. Please try again.', 'dragon-glow' ), 'error' );
		return;
	}

	// Validate required fields.
	$email      = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$password   = isset( $_POST['password'] ) ? wp_unslash( $_POST['password'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$first_name = isset( $_POST['billing_first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_first_name'] ) ) : '';
	$last_name  = isset( $_POST['billing_last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_last_name'] ) ) : '';

	if ( empty( $email ) ) {
		wc_add_notice( __( 'Please provide a valid email address.', 'dragon-glow' ), 'error' );
		return;
	}

	if ( empty( $password ) ) {
		wc_add_notice( __( 'Please enter a password.', 'dragon-glow' ), 'error' );
		return;
	}

	if ( strlen( $password ) < 8 ) {
		wc_add_notice( __( 'Password must be at least 8 characters long.', 'dragon-glow' ), 'error' );
		return;
	}

	if ( empty( $first_name ) || empty( $last_name ) ) {
		wc_add_notice( __( 'Please provide both first and last name.', 'dragon-glow' ), 'error' );
		return;
	}

	// Check terms acceptance (required).
	if ( ! isset( $_POST['terms'] ) ) {
		wc_add_notice( __( 'You must accept the Terms of Service to register.', 'dragon-glow' ), 'error' );
		return;
	}

	// Check if email already exists.
	if ( email_exists( $email ) ) {
		wc_add_notice( __( 'An account with this email address already exists.', 'dragon-glow' ), 'error' );
		return;
	}

	// Create customer account.
	$customer_id = wc_create_new_customer( $email, '', $password );

	if ( is_wp_error( $customer_id ) ) {
		wc_add_notice( $customer_id->get_error_message(), 'error' );
		return;
	}

	// Update customer meta with name.
	update_user_meta( $customer_id, 'first_name', $first_name );
	update_user_meta( $customer_id, 'last_name', $last_name );
	update_user_meta( $customer_id, 'billing_first_name', $first_name );
	update_user_meta( $customer_id, 'billing_last_name', $last_name );

	// Save optional fields.
	if ( isset( $_POST['skin_aspiration'] ) ) {
		$aspiration = sanitize_text_field( wp_unslash( $_POST['skin_aspiration'] ) );
		update_user_meta( $customer_id, 'dg_skin_aspiration', $aspiration );
	}

	if ( isset( $_POST['newsletter'] ) ) {
		update_user_meta( $customer_id, 'dg_newsletter_opt_in', '1' );
	}

	// Log the user in.
	wc_set_customer_auth_cookie( $customer_id );

	// Trigger WooCommerce registration action.
	do_action( 'woocommerce_created_customer', $customer_id, array(), '' );

	// Redirect to My Account dashboard.
	wp_safe_redirect( dg_account_endpoint_url( '' ) );
	exit;
}
add_action( 'template_redirect', 'dg_process_registration', 5 );

/**
 * Get the list of account nav items.
 *
 * Endpoint, label, icon name (Material Symbols). Order matters — shown in
 * this exact order in the sidebar and mobile dropdown.
 *
 * @return array<int, array{endpoint:string,label:string,icon:string}>
 */
function dg_account_nav_items(): array {
	return array(
		array(
			'endpoint' => '',
			'label'    => __( 'Dashboard', 'dragon-glow' ),
			'icon'     => 'space_dashboard',
		),
		array(
			'endpoint' => 'orders',
			'label'    => __( 'Orders', 'dragon-glow' ),
			'icon'     => 'receipt_long',
		),
		array(
			'endpoint' => 'edit-address',
			'label'    => __( 'Addresses', 'dragon-glow' ),
			'icon'     => 'home',
		),
		array(
			'endpoint' => 'edit-account',
			'label'    => __( 'Account details', 'dragon-glow' ),
			'icon'     => 'manage_accounts',
		),
	);
}

/**
 * Build rewrite-slug → endpoint-key map from WooCommerce query vars.
 *
 * @return array<string, string>
 */
function dg_account_endpoint_slug_map(): array {
	$map = array();
	if ( ! function_exists( 'WC' ) || ! WC() || ! isset( WC()->query ) || ! is_object( WC()->query ) ) {
		return $map;
	}
	if ( ! method_exists( WC()->query, 'get_query_vars' ) ) {
		return $map;
	}

	$vars = WC()->query->get_query_vars();
	if ( ! is_array( $vars ) ) {
		return $map;
	}

	foreach ( $vars as $key => $slug ) {
		if ( is_string( $key ) && is_string( $slug ) && '' !== $slug ) {
			$map[ $slug ] = $key;
		}
	}

	return $map;
}

/**
 * Path segments after the My Account page URI.
 *
 * Prefers `$wp->request`, then falls back to `REQUEST_URI` so refresh on
 * `/my-account/orders/` still resolves when query vars / `$wp->request` are
 * empty (common on shared hosting with incomplete endpoint rewrites).
 *
 * @return array<int, string>
 */
function dg_account_path_segments_after_base(): array {
	$page_id = (int) get_option( 'woocommerce_myaccount_page_id' );
	if ( $page_id <= 0 ) {
		return array();
	}

	$account_uri = trim( (string) get_page_uri( $page_id ), '/' );
	if ( '' === $account_uri ) {
		$account_uri = 'my-account';
	}

	$candidates = array();

	global $wp;
	if ( isset( $wp->request ) && is_string( $wp->request ) && '' !== trim( $wp->request, '/' ) ) {
		$candidates[] = trim( $wp->request, '/' );
	}

	if ( isset( $_SERVER['REQUEST_URI'] ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- path only via wp_parse_url.
		$uri_path = (string) wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH );
		$uri_path = trim( $uri_path, '/' );

		// Strip subdirectory home path when WP is not at domain root.
		$home_path = trim( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ), '/' );
		if ( '' !== $home_path ) {
			if ( $uri_path === $home_path ) {
				$uri_path = '';
			} elseif ( 0 === strpos( $uri_path, $home_path . '/' ) ) {
				$uri_path = substr( $uri_path, strlen( $home_path ) + 1 );
			}
		}

		if ( '' !== $uri_path ) {
			$candidates[] = $uri_path;
		}
	}

	$prefix = $account_uri . '/';
	foreach ( $candidates as $path ) {
		if ( $path === $account_uri || 0 !== strpos( $path, $prefix ) ) {
			continue;
		}
		$after = substr( $path, strlen( $prefix ) );
		$parts = array_values( array_filter( explode( '/', $after ), 'strlen' ) );
		if ( ! empty( $parts ) ) {
			return $parts;
		}
	}

	return array();
}

/**
 * Determine current My Account endpoint key for sidebar + panel switch.
 *
 * Resolution order:
 * 1. WooCommerce native query-var endpoint (`get_current_endpoint`).
 * 2. First path segment after the My Account page URI (REQUEST_URI fallback).
 *
 * Returns the internal endpoint key (e.g. `orders`), not a customized rewrite
 * slug, so `dg_render_wc_account()` switch cases stay stable.
 *
 * @return string Empty string = dashboard.
 */
function dg_current_account_endpoint(): string {
	if ( ! function_exists( 'WC' ) || ! WC() ) {
		return '';
	}
	if ( ! isset( WC()->query ) || ! is_object( WC()->query ) ) {
		return '';
	}

	// 1) Native WC — works when rewrite rules registered the endpoint query var.
	if ( method_exists( WC()->query, 'get_current_endpoint' ) ) {
		$native = (string) WC()->query->get_current_endpoint();
		if ( '' !== $native ) {
			return $native;
		}
	}

	// 2) Path after /my-account/ — survives refresh when query vars are missing.
	$segments = dg_account_path_segments_after_base();
	if ( empty( $segments ) ) {
		return '';
	}

	$candidate = $segments[0];

	$slug_map = dg_account_endpoint_slug_map();
	if ( isset( $slug_map[ $candidate ] ) ) {
		return $slug_map[ $candidate ];
	}

	return '';
}

/**
 * Get current pagination page for account orders endpoint.
 *
 * @return int Page number (1-indexed).
 */
function dg_get_account_orders_page(): int {
	$current_page = 1;
	$request_uri  = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '';

	// Check get_query_var('page') first
	$qv_page = (int) get_query_var( 'page' );
	if ( $qv_page > 1 ) {
		$current_page = $qv_page;
	}

	// Check REQUEST_URI for /page/N/ pattern
	if ( 1 === $current_page && '' !== $request_uri ) {
		if ( preg_match( '#/orders/page/(\d+)/?$#', $request_uri, $matches ) ) {
			$current_page = max( 1, (int) $matches[1] );
		}
	}

	// Fallback: check $_GET['paged']
	if ( 1 === $current_page && isset( $_GET['paged'] ) ) {
		$current_page = max( 1, (int) $_GET['paged'] );
	}

	return $current_page;
}

/**
 * Set document title for My Account pages with pagination support.
 *
 * @param string $title Original title.
 * @return string Modified title.
 */
function dg_account_document_title( string $title ): string {
	// Only modify titles on the My Account page.
	if ( ! is_page( get_option( 'woocommerce_myaccount_page_id' ) ) ) {
		return $title;
	}

	$endpoint = dg_current_account_endpoint();

	if ( 'orders' === $endpoint ) {
		$paged = dg_get_account_orders_page();
		if ( $paged > 1 ) {
			/* translators: %d: Page number */
			$title = sprintf( esc_html__( 'Orders (page %d)', 'dragon-glow' ), $paged );
		} else {
			$title = esc_html__( 'Orders', 'dragon-glow' );
		}
	}

	return $title;
}
add_filter( 'pre_get_document_title', 'dg_account_document_title' );

/**
 * Render the account hero (greeting + member tier).
 *
 * @return void
 */
function dg_render_account_hero( WP_User $customer ): void {
	$first_name = trim( (string) $customer->user_firstname );
	$greet_name  = '' !== $first_name ? $first_name : $customer->display_name;
	$member_since = date_i18n( 'F Y', strtotime( (string) $customer->user_registered ) );
	$tier         = dg_account_member_tier( (int) $customer->ID );
	?>
	<section class="dg-account-hero" data-sr>
		<div class="dg-account-hero__inner">
			<div class="dg-account-hero__avatar" aria-hidden="true">
				<?php echo get_avatar( $customer->ID, 96, '', '', array( 'class' => 'dg-account-hero__img' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_avatar returns escaped HTML. ?>
				<span class="dg-account-hero__tier" data-tier="<?php echo esc_attr( $tier['slug'] ); ?>">
					<span class="material-symbols-outlined">workspace_premium</span>
					<?php echo esc_html( $tier['label'] ); ?>
				</span>
			</div>

			<div class="dg-account-hero__copy">
				<p class="dg-account-hero__eyebrow">
					<?php esc_html_e( 'Welcome back', 'dragon-glow' ); ?>
				</p>
				<h1 class="dg-account-hero__name"><?php echo esc_html( $greet_name ); ?></h1>
				<p class="dg-account-hero__sub">
					<?php esc_html_e( 'Your luminous skincare ritual continues here.', 'dragon-glow' ); ?>
				</p>

				<dl class="dg-account-hero__meta">
					<div>
						<dt><?php esc_html_e( 'Member since', 'dragon-glow' ); ?></dt>
						<dd><?php echo esc_html( $member_since ); ?></dd>
					</div>
					<div>
						<dt><?php esc_html_e( 'Email', 'dragon-glow' ); ?></dt>
						<dd><?php echo esc_html( $customer->user_email ); ?></dd>
					</div>
				</dl>
			</div>
		</div>
	</section>
	<?php
}

/**
 * Resolve member tier based on lifetime spend.
 *
 * Three tiers: Glow / Aria / Lumière — purely cosmetic, surfaced in the hero
 * badge. Computed from `wc_get_customer_total_spent()` if available.
 *
 * @param int $customer_id Customer ID.
 * @return array{slug:string,label:string}
 */
function dg_account_member_tier( int $customer_id ): array {
	$total = 0.0;
	if ( $customer_id > 0 && function_exists( 'wc_get_customer_total_spent' ) ) {
		try {
			$total = (float) wc_get_customer_total_spent( $customer_id );
		} catch ( \Throwable $e ) {
			$total = 0.0;
		}
	}

	if ( $total >= 1000.0 ) {
		return array(
			'slug'  => 'lumiere',
			'label' => __( 'Lumière', 'dragon-glow' ),
		);
	}
	if ( $total >= 300.0 ) {
		return array(
			'slug'  => 'aria',
			'label' => __( 'Aria', 'dragon-glow' ),
		);
	}
	return array(
		'slug'  => 'glow',
		'label' => __( 'Glow', 'dragon-glow' ),
	);
}

/**
 * Render sidebar navigation (also reused on mobile as a dropdown).
 *
 * @param string $current_endpoint Currently active endpoint slug.
 * @return void
 */
function dg_render_account_sidebar( string $current_endpoint ): void {
	$items = dg_account_nav_items();
	?>
	<nav class="dg-account-nav" aria-label="<?php esc_attr_e( 'Account navigation', 'dragon-glow' ); ?>">
		<button
			type="button"
			class="dg-account-nav__toggle"
			id="dg-account-nav-toggle"
			aria-expanded="false"
			aria-controls="dg-account-nav-list">
			<span class="material-symbols-outlined dg-account-nav__toggle-icon">menu</span>
			<span class="dg-account-nav__toggle-label">
				<?php
				$current_label = __( 'Dashboard', 'dragon-glow' );
				foreach ( $items as $item ) {
					if ( $item['endpoint'] === $current_endpoint ) {
						$current_label = $item['label'];
						break;
					}
				}
				esc_html_e( 'Navigation: ', 'dragon-glow' );
				echo esc_html( $current_label );
				?>
			</span>
			<span class="material-symbols-outlined dg-account-nav__chevron">expand_more</span>
		</button>

		<ul class="dg-account-nav__list" id="dg-account-nav-list" role="list">
			<?php foreach ( $items as $item ) :
				$is_active = ( $item['endpoint'] === $current_endpoint ) || ( '' === $item['endpoint'] && '' === $current_endpoint );
				$url       = dg_account_endpoint_url( $item['endpoint'] );
				?>
				<li>
					<a
						href="<?php echo esc_url( $url ); ?>"
						class="dg-account-nav__link<?php echo $is_active ? ' is-active' : ''; ?>"
						<?php echo $is_active ? 'aria-current="page"' : ''; ?>>
						<span class="material-symbols-outlined dg-account-nav__icon"><?php echo esc_html( $item['icon'] ); ?></span>
						<span class="dg-account-nav__label"><?php echo esc_html( $item['label'] ); ?></span>
						<?php if ( 'orders' === $item['endpoint'] ) : ?>
							<?php
							$_oc = 0;
							if ( function_exists( 'wc_get_customer_order_count' ) ) {
								try {
									$_oc = (int) wc_get_customer_order_count( get_current_user_id() );
								} catch ( \Throwable $e ) {
									$_oc = 0;
								}
							}
							?>
							<span class="dg-account-nav__count"><?php echo esc_html( (string) $_oc ); ?></span>
						<?php endif; ?>
					</a>
				</li>
			<?php endforeach; ?>

			<li class="dg-account-nav__divider" role="separator" aria-hidden="true"></li>

			<li>
				<a href="<?php echo esc_url( dg_account_endpoint_url( 'customer-logout' ) ); ?>"
				   class="dg-account-nav__link dg-account-nav__link--logout">
					<span class="material-symbols-outlined dg-account-nav__icon">logout</span>
					<span class="dg-account-nav__label"><?php esc_html_e( 'Sign out', 'dragon-glow' ); ?></span>
				</a>
			</li>
		</ul>
	</nav>
	<?php
}

/**
 * Render dashboard content (stats + recent orders + CTA).
 *
 * Default / fallback view when no endpoint is matched.
 *
 * @return void
 */
function dg_render_account_dashboard(): void {
	$user_id    = get_current_user_id();
	$order_count = 0;
	if ( $user_id > 0 && function_exists( 'wc_get_customer_order_count' ) ) {
		try {
			$order_count = (int) wc_get_customer_order_count( $user_id );
		} catch ( \Throwable $e ) {
			$order_count = 0;
		}
	}
	$total_spent = 0.0;
	if ( $user_id > 0 && function_exists( 'wc_get_customer_total_spent' ) ) {
		try {
			$total_spent = (float) wc_get_customer_total_spent( $user_id );
		} catch ( \Throwable $e ) {
			$total_spent = 0.0;
		}
	}
	?>
	<section class="dg-account-dashboard" aria-labelledby="dg-dashboard-heading">

		<!-- Stats -->
		<div class="dg-account-stats" data-sr-group>
			<a href="<?php echo esc_url( dg_account_endpoint_url( 'orders' ) ); ?>" class="dg-account-stat" data-sr>
				<div class="dg-account-stat__icon" data-tone="primary">
					<span class="material-symbols-outlined">receipt_long</span>
				</div>
				<div class="dg-account-stat__body">
					<p class="dg-account-stat__value dg-count-to" data-count-to="<?php echo esc_attr( (string) $order_count ); ?>">0</p>
					<p class="dg-account-stat__label"><?php esc_html_e( 'Total orders', 'dragon-glow' ); ?></p>
				</div>
			</a>

			<div class="dg-account-stat" data-sr>
				<div class="dg-account-stat__icon" data-tone="gold">
					<span class="material-symbols-outlined">payments</span>
				</div>
				<div class="dg-account-stat__body">
					<p class="dg-account-stat__value">
						<?php echo wp_kses_post( dg_format_price( $total_spent ) ); ?>
					</p>
					<p class="dg-account-stat__label"><?php esc_html_e( 'Lifetime total', 'dragon-glow' ); ?></p>
				</div>
			</div>
		</div>

		<!-- Recent orders -->
		<?php
		$orders = array();
		if ( $user_id > 0 && function_exists( 'wc_get_orders' ) ) {
			try {
				$orders = wc_get_orders(
					array(
						'customer_id' => $user_id,
						'limit'       => 4,
						'orderby'     => 'date',
						'order'       => 'DESC',
						'return'      => 'objects',
					)
				);
			} catch ( \Throwable $e ) {
				$orders = array();
			}
			if ( ! is_array( $orders ) ) {
				$orders = array();
			}
		}
		if ( function_exists( 'wc_get_orders' ) ) :
			?>
			<section class="dg-account-panel" data-sr>
				<header class="dg-account-panel__header">
					<h2 class="dg-account-panel__title" id="dg-dashboard-heading">
						<?php esc_html_e( 'Recent orders', 'dragon-glow' ); ?>
					</h2>
					<a href="<?php echo esc_url( dg_account_endpoint_url( 'orders' ) ); ?>" class="dg-account-panel__link">
						<?php esc_html_e( 'View all', 'dragon-glow' ); ?>
						<span class="material-symbols-outlined" aria-hidden="true">arrow_forward</span>
					</a>
				</header>

				<?php if ( ! empty( $orders ) ) : ?>
					<ul class="dg-account-orders" role="list">
						<?php foreach ( $orders as $order ) :
							$status    = (string) $order->get_status();
							$status_label = wc_get_order_status_name( $status );
							$status_slug = sanitize_title( $status );
							?>
							<li class="dg-account-order">
								<div class="dg-account-order__id">
									<span class="material-symbols-outlined">receipt_long</span>
									<div>
										<p class="dg-account-order__num">
											<?php
											printf(
												/* translators: %s: order number. */
												esc_html__( 'Order #%s', 'dragon-glow' ),
												esc_html( $order->get_order_number() )
											);
											?>
										</p>
										<p class="dg-account-order__date">
											<?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?>
										</p>
									</div>
								</div>
								<p class="dg-account-order__total"><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></p>
								<span class="dg-account-order__status dg-account-status dg-account-status--<?php echo esc_attr( $status_slug ); ?>">
									<?php echo esc_html( $status_label ); ?>
								</span>
								<a href="<?php echo esc_url( $order->get_view_order_url() ); ?>"
								   class="dg-account-order__action"
								   aria-label="<?php echo esc_attr( sprintf( __( 'View order #%s', 'dragon-glow' ), $order->get_order_number() ) ); ?>">
									<?php esc_html_e( 'View', 'dragon-glow' ); ?>
									<span class="material-symbols-outlined" aria-hidden="true">chevron_right</span>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php else : ?>
					<div class="dg-account-empty">
						<span class="material-symbols-outlined dg-account-empty__icon">shopping_bag</span>
						<p class="dg-account-empty__title"><?php esc_html_e( 'No orders yet', 'dragon-glow' ); ?></p>
						<p class="dg-account-empty__text">
							<?php esc_html_e( 'Your future ritual kits will appear here once you place an order.', 'dragon-glow' ); ?>
						</p>
						<a href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop' ) ); ?>"
						   class="dg-btn dg-btn--primary">
							<?php esc_html_e( 'Discover the collection', 'dragon-glow' ); ?>
						</a>
					</div>
				<?php endif; ?>
			</section>
		<?php endif; ?>

		<!-- Account quick info -->
		<section class="dg-account-panel" data-sr>
			<header class="dg-account-panel__header">
				<h2 class="dg-account-panel__title"><?php esc_html_e( 'Account snapshot', 'dragon-glow' ); ?></h2>
				<a href="<?php echo esc_url( dg_account_endpoint_url( 'edit-account' ) ); ?>" class="dg-account-panel__link">
					<?php esc_html_e( 'Edit details', 'dragon-glow' ); ?>
					<span class="material-symbols-outlined" aria-hidden="true">arrow_forward</span>
				</a>
			</header>
			<?php
			$customer = wp_get_current_user();
			$first    = (string) get_user_meta( $customer->ID, 'billing_first_name', true );
			$last     = (string) get_user_meta( $customer->ID, 'billing_last_name', true );
			$phone    = (string) get_user_meta( $customer->ID, 'billing_phone', true );
			$addr_1   = (string) get_user_meta( $customer->ID, 'billing_address_1', true );
			$city     = (string) get_user_meta( $customer->ID, 'billing_city', true );
			$billing  = array(
				'name'  => trim( $first . ' ' . $last ),
				'phone' => $phone,
				'addr'  => trim( $addr_1 . ( '' !== $city ? ', ' . $city : '' ) ),
			);
			?>
			<dl class="dg-account-snapshot">
				<div>
					<dt><?php esc_html_e( 'Name', 'dragon-glow' ); ?></dt>
					<dd><?php echo '' !== $billing['name'] ? esc_html( $billing['name'] ) : '—'; ?></dd>
				</div>
				<div>
					<dt><?php esc_html_e( 'Email', 'dragon-glow' ); ?></dt>
					<dd><?php echo esc_html( $customer->user_email ); ?></dd>
				</div>
				<div>
					<dt><?php esc_html_e( 'Phone', 'dragon-glow' ); ?></dt>
					<dd><?php echo '' !== $billing['phone'] ? esc_html( $billing['phone'] ) : '—'; ?></dd>
				</div>
				<div>
					<dt><?php esc_html_e( 'Default address', 'dragon-glow' ); ?></dt>
					<dd><?php echo '' !== $billing['addr'] ? esc_html( $billing['addr'] ) : '—'; ?></dd>
				</div>
			</dl>
		</section>
	</section>
	<?php
}

/**
 * Render the Orders list panel (the /my-account/orders endpoint).
 *
 * Queries customer orders and passes them to WC's `myaccount/orders.php` template.
 * When called via AJAX, we must explicitly query orders because WC's internal
 * query context is lost.
 *
 * @return void
 */
function dg_render_account_orders_panel(): void {
	$customer_id  = get_current_user_id();

	// Read page number from the current request.
	// URL formats supported:
	// 1. /my-account/orders/page/2/  (pretty permalinks - WC default)
	// 2. /my-account/orders/?paged=2 (query string)
	$current_page = 1;
	$request_uri  = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '';

	// First try get_query_var('page') - standard WP pagination var
	$qv_page = (int) get_query_var( 'page' );
	if ( $qv_page > 1 ) {
		$current_page = $qv_page;
	}

	// Fallback: check REQUEST_URI for /page/N/ pattern
	if ( 1 === $current_page && '' !== $request_uri ) {
		if ( preg_match( '#/orders/page/(\d+)/?$#', $request_uri, $matches ) ) {
			$current_page = max( 1, (int) $matches[1] );
		}
	}

	// Fallback: check $_GET['paged']
	if ( 1 === $current_page && isset( $_GET['paged'] ) ) {
		$current_page = max( 1, (int) $_GET['paged'] );
	}

	$page_size = 10;

	// Query customer orders.
	$customer_orders = array();
	$has_orders      = false;

	if ( $customer_id > 0 && function_exists( 'wc_get_orders' ) ) {
		try {
			$customer_orders = wc_get_orders(
				array(
					'customer_id' => $customer_id,
					'limit'       => $page_size,
					'page'        => $current_page,
					'paginate'    => true,
					'orderby'     => 'date',
					'order'       => 'DESC',
				)
			);
			$has_orders = ( is_object( $customer_orders ) && isset( $customer_orders->orders ) && ! empty( $customer_orders->orders ) );
		} catch ( \Throwable $e ) {
			$customer_orders = (object) array( 'orders' => array(), 'total' => 0, 'max_num_pages' => 0 );
			$has_orders      = false;
		}
	}

	?>
	<section class="dg-account-panel" data-sr>
		<header class="dg-account-panel__header">
			<h2 class="dg-account-panel__title"><?php esc_html_e( 'My orders', 'dragon-glow' ); ?></h2>
			<a href="<?php echo esc_url( dg_account_endpoint_url( '' ) ); ?>" class="dg-account-panel__link">
				<span class="material-symbols-outlined" aria-hidden="true">arrow_back</span>
				<?php esc_html_e( 'Back to dashboard', 'dragon-glow' ); ?>
			</a>
		</header>
		<?php
		if ( function_exists( 'wc_get_template' ) ) {
			wc_get_template(
				'myaccount/orders.php',
				array(
					'current_page'    => $current_page,
					'customer_orders' => $customer_orders,
					'has_orders'      => $has_orders,
				)
			);
		}
		?>
	</section>
	<?php
}

/**
 * Build the address data array for an address type (billing/shipping).
 *
 * Pulls fields directly from user_meta so we own the markup instead of being
 * bound to WC's `myaccount/my-address.php` template (which renders a flat
 * `<p>` with no actions, no icons, no card layout — incompatible with the
 * Luminous Ethereal design system).
 *
 * Returns `null` if every field is empty (lets the renderer show a friendly
 * empty state with a single CTA to fill in the form).
 *
 * @param int    $customer_id User ID.
 * @param string $type        Address type — 'billing' | 'shipping'.
 * @return array<string, string>|null
 */
function dg_get_account_address_data( int $customer_id, string $type ): ?array {
	if ( $customer_id <= 0 || ! in_array( $type, array( 'billing', 'shipping' ), true ) ) {
		return null;
	}

	$prefix = $type . '_';

	$fields = array(
		'first_name' => (string) get_user_meta( $customer_id, $prefix . 'first_name', true ),
		'last_name'  => (string) get_user_meta( $customer_id, $prefix . 'last_name', true ),
		'company'    => (string) get_user_meta( $customer_id, $prefix . 'company', true ),
		'address_1'  => (string) get_user_meta( $customer_id, $prefix . 'address_1', true ),
		'address_2'  => (string) get_user_meta( $customer_id, $prefix . 'address_2', true ),
		'city'       => (string) get_user_meta( $customer_id, $prefix . 'city', true ),
		'state'      => (string) get_user_meta( $customer_id, $prefix . 'state', true ),
		'postcode'   => (string) get_user_meta( $customer_id, $prefix . 'postcode', true ),
		'country'    => (string) get_user_meta( $customer_id, $prefix . 'country', true ),
		'phone'      => ( 'billing' === $type ? (string) get_user_meta( $customer_id, $prefix . 'phone', true ) : '' ),
		'email'      => ( 'billing' === $type ? (string) get_user_meta( $customer_id, $prefix . 'email', true ) : '' ),
	);

	// Empty if address_1 (the canonical "address line" field) is blank.
	if ( '' === trim( $fields['address_1'] ) ) {
		return null;
	}

	return $fields;
}

/**
 * Format an address field for display.
 *
 * Returns a string with the value properly escaped. Used inside the address
 * card body so we don't repeat `esc_html()` everywhere.
 *
 * @param string $value Field value (raw).
 * @return string
 */
function dg_format_address_field( string $value ): string {
	$value = trim( $value );
	return '' === $value ? '' : esc_html( $value );
}

/**
 * Detect whether the current request is editing a single address.
 *
 * WC's `edit-address` endpoint supports an `?address=` query var (or
 * `/{type}/` path segment) so the customer can edit one address at a time.
 * Returns the address type being edited, or empty string if we're on the
 * list view.
 *
 * @return string 'billing' | 'shipping' | ''
 */
function dg_current_address_edit_type(): string {
	// Query var — used by pretty permalinks.
	$qv = isset( $_GET['address'] ) ? sanitize_key( wp_unslash( $_GET['address'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	if ( in_array( $qv, array( 'billing', 'shipping' ), true ) ) {
		return $qv;
	}

	// Path segment — /my-account/edit-address/billing/ — fallback for shared
	// hosting where query vars aren't registered.
	$segments = dg_account_path_segments_after_base();
	if ( ! empty( $segments ) && 'edit-address' === $segments[0] && isset( $segments[1] ) ) {
		$candidate = sanitize_key( $segments[1] );
		if ( in_array( $candidate, array( 'billing', 'shipping' ), true ) ) {
			return $candidate;
		}
	}

	return '';
}

/**
 * Render the Addresses list view (Billing + Shipping cards).
 *
 * Built from user_meta instead of `wc_get_template( 'myaccount/my-address.php' )`
 * so the markup matches the Luminous Ethereal design system. WC's stock
 * template renders a flat `<p>` with no icon, no card frame, no action bar.
 *
 * @return void
 */
function dg_render_account_addresses_list(): void {
	$customer_id = get_current_user_id();
	$billing     = dg_get_account_address_data( $customer_id, 'billing' );
	$shipping    = dg_get_account_address_data( $customer_id, 'shipping' );

	// Determine which address is the "default" for shipping — WC convention is
	// "ship to billing address" when no shipping address is set.
	$default_shipping_is_billing = ( null === $shipping );

	// Empty-state guard: both addresses blank.
	if ( null === $billing && null === $shipping ) :
		?>
		<div class="dg-account-addresses dg-account-addresses--empty">
			<span class="material-symbols-outlined dg-account-empty__icon">home</span>
			<p class="dg-account-empty__title"><?php esc_html_e( 'No saved addresses yet', 'dragon-glow' ); ?></p>
			<p class="dg-account-empty__text">
				<?php esc_html_e( 'Add your billing address to speed up checkout. You can add a separate shipping address any time.', 'dragon-glow' ); ?>
			</p>
			<a href="<?php echo esc_url( add_query_arg( 'address', 'billing', dg_account_endpoint_url( 'edit-address' ) ) ); ?>"
			   class="dg-btn dg-btn--primary">
				<span class="material-symbols-outlined" aria-hidden="true">add</span>
				<?php esc_html_e( 'Add billing address', 'dragon-glow' ); ?>
			</a>
		</div>
		<?php
		return;
	endif;
	?>
	<div class="dg-account-addresses" data-sr-group>
		<?php if ( null !== $billing ) : ?>
			<?php dg_render_account_address_card( 'billing', $billing, false ); ?>
		<?php endif; ?>

		<?php if ( null !== $shipping ) : ?>
			<?php dg_render_account_address_card( 'shipping', $shipping, $default_shipping_is_billing ); ?>
		<?php elseif ( null !== $billing ) : ?>
			<?php dg_render_account_address_empty_card( 'shipping' ); ?>
		<?php endif; ?>
	</div>

	<p class="dg-account-addresses__hint">
		<span class="material-symbols-outlined" aria-hidden="true">info</span>
		<?php esc_html_e( 'These addresses will be pre-filled at checkout. You can edit or add a separate shipping address any time.', 'dragon-glow' ); ?>
	</p>
	<?php
}

/**
 * Render one address card on the Addresses list view.
 *
 * @param string               $type                   'billing' | 'shipping'.
 * @param array<string, string> $data                  Address fields from dg_get_account_address_data().
 * @param bool                 $is_default_for_other  True when this card is the default for the other address type
 *                                                   (e.g. shipping falls back to billing).
 * @return void
 */
function dg_render_account_address_card( string $type, array $data, bool $is_default_for_other ): void {
	$edit_url  = add_query_arg( 'address', $type, dg_account_endpoint_url( 'edit-address' ) );
	$title     = ( 'billing' === $type ) ? __( 'Billing address', 'dragon-glow' ) : __( 'Shipping address', 'dragon-glow' );
	$icon      = ( 'billing' === $type ) ? 'receipt' : 'local_shipping';
	$is_default_label = ( 'billing' === $type )
		? __( 'Default for all orders', 'dragon-glow' )
		: __( 'Default shipping address', 'dragon-glow' );

	$full_name = trim( $data['first_name'] . ' ' . $data['last_name'] );

	// Build address lines: line1 (+optional line2), then city/state/postcode.
	$line_1 = dg_format_address_field( $data['address_1'] );
	$line_2 = dg_format_address_field( $data['address_2'] );
	$city   = dg_format_address_field( $data['city'] );
	$state  = dg_format_address_field( $data['state'] );
	$zip    = dg_format_address_field( $data['postcode'] );
	$country = dg_format_address_field( $data['country'] );

	$city_line = trim( implode( ' ', array_filter( array( $city, $state, $zip ) ) ) );
	?>
	<article class="dg-account-address" data-sr data-address-type="<?php echo esc_attr( $type ); ?>">
		<header class="dg-account-address__head">
			<div class="dg-account-address__title-block">
				<div class="dg-account-address__icon" aria-hidden="true">
					<span class="material-symbols-outlined"><?php echo esc_html( $icon ); ?></span>
				</div>
				<div>
					<h3 class="dg-account-address__title"><?php echo esc_html( $title ); ?></h3>
					<?php if ( $is_default_for_other ) : ?>
						<span class="dg-account-address__badge dg-account-address__badge--default">
							<span class="material-symbols-outlined" aria-hidden="true">check_circle</span>
							<?php echo esc_html( $is_default_label ); ?>
						</span>
					<?php endif; ?>
				</div>
			</div>
		</header>

		<div class="dg-account-address__body">
			<?php if ( '' !== $full_name ) : ?>
				<p class="dg-account-address__name"><?php echo esc_html( $full_name ); ?></p>
			<?php endif; ?>

			<address class="dg-account-address__lines">
				<?php if ( '' !== $line_1 ) : ?>
					<span><?php echo $line_1; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped via dg_format_address_field(). ?></span>
				<?php endif; ?>
				<?php if ( '' !== $line_2 ) : ?>
					<span><?php echo $line_2; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped via dg_format_address_field(). ?></span>
				<?php endif; ?>
				<?php if ( '' !== $city_line ) : ?>
					<span><?php echo esc_html( $city_line ); ?></span>
				<?php endif; ?>
				<?php if ( '' !== $country ) : ?>
					<span><?php echo esc_html( $country ); ?></span>
				<?php endif; ?>
			</address>

			<?php if ( 'billing' === $type ) : ?>
				<dl class="dg-account-address__contacts">
					<?php if ( '' !== $data['phone'] ) : ?>
						<div>
							<dt>
								<span class="material-symbols-outlined" aria-hidden="true">call</span>
								<?php esc_html_e( 'Phone', 'dragon-glow' ); ?>
							</dt>
							<dd>
								<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $data['phone'] ) ); ?>">
									<?php echo esc_html( $data['phone'] ); ?>
								</a>
							</dd>
						</div>
					<?php endif; ?>
					<?php if ( '' !== $data['email'] ) : ?>
						<div>
							<dt>
								<span class="material-symbols-outlined" aria-hidden="true">mail</span>
								<?php esc_html_e( 'Email', 'dragon-glow' ); ?>
							</dt>
							<dd>
								<a href="mailto:<?php echo esc_attr( $data['email'] ); ?>">
									<?php echo esc_html( $data['email'] ); ?>
								</a>
							</dd>
						</div>
					<?php endif; ?>
				</dl>
			<?php endif; ?>
		</div>

		<footer class="dg-account-address__actions">
			<a href="<?php echo esc_url( $edit_url ); ?>" class="dg-account-address__action dg-account-address__action--primary">
				<span class="material-symbols-outlined" aria-hidden="true">edit</span>
				<?php esc_html_e( 'Edit address', 'dragon-glow' ); ?>
			</a>
			<?php if ( 'shipping' === $type ) : ?>
				<form method="post"
				      action="<?php echo esc_url( dg_account_endpoint_url( 'edit-address' ) ); ?>"
				      class="dg-account-use-billing"
				      data-dg-use-billing
				      data-confirm-title="<?php echo esc_attr__( 'Use billing instead?', 'dragon-glow' ); ?>"
				      data-confirm="<?php echo esc_attr__( 'Remove your shipping address and deliver orders to your billing address instead?', 'dragon-glow' ); ?>"
				      data-confirm-ok="<?php echo esc_attr__( 'Use billing', 'dragon-glow' ); ?>"
				      data-confirm-cancel="<?php echo esc_attr__( 'Cancel', 'dragon-glow' ); ?>">
					<?php wp_nonce_field( 'dg_use_billing_instead', 'dg_use_billing_nonce' ); ?>
					<input type="hidden" name="dg_use_billing_instead" value="1" />
					<button type="submit" class="dg-account-address__action dg-account-use-billing__btn">
						<span class="material-symbols-outlined dg-account-use-billing__icon" aria-hidden="true">swap_horiz</span>
						<span class="dg-account-use-billing__label"><?php esc_html_e( 'Use billing instead', 'dragon-glow' ); ?></span>
					</button>
				</form>
			<?php endif; ?>
		</footer>
	</article>
	<?php
}

/**
 * Render an "add address" placeholder card when one of the two addresses is unset.
 *
 * @param string $type 'billing' | 'shipping'.
 * @return void
 */
function dg_render_account_address_empty_card( string $type ): void {
	$add_url = add_query_arg( 'address', $type, dg_account_endpoint_url( 'edit-address' ) );
	$title   = ( 'shipping' === $type ) ? __( 'Shipping address', 'dragon-glow' ) : __( 'Billing address', 'dragon-glow' );
	$text    = ( 'shipping' === $type )
		? __( 'You haven\'t added a separate shipping address. We currently ship your orders to your billing address.', 'dragon-glow' )
		: __( 'You haven\'t added a billing address yet.', 'dragon-glow' );
	?>
	<article class="dg-account-address dg-account-address--empty" data-sr data-address-type="<?php echo esc_attr( $type ); ?>">
		<div class="dg-account-address__icon" aria-hidden="true">
			<span class="material-symbols-outlined">add_location_alt</span>
		</div>
		<h3 class="dg-account-address__title"><?php echo esc_html( $title ); ?></h3>
		<p class="dg-account-address__text"><?php echo esc_html( $text ); ?></p>
		<a href="<?php echo esc_url( $add_url ); ?>" class="dg-btn dg-btn--ghost">
			<span class="material-symbols-outlined" aria-hidden="true">add</span>
			<?php esc_html_e( 'Add address', 'dragon-glow' ); ?>
		</a>
	</article>
	<?php
}

/**
 * Render the edit-address form (single address).
 *
 * Wraps WC's `myaccount/form-edit-address.php` in our panel shell. The shell
 * gives a clear header and breadcrumb back link. Save posts through WC's
 * `WC_Form_Handler::save_address` (nonce + validation + redirect).
 *
 * @param string $type 'billing' | 'shipping'.
 * @return void
 */
function dg_render_account_addresses_edit( string $type ): void {
	$title     = ( 'billing' === $type ) ? __( 'Billing address', 'dragon-glow' ) : __( 'Shipping address', 'dragon-glow' );
	$subtitle  = ( 'billing' === $type )
		? __( 'Used for invoices and as the default for your orders.', 'dragon-glow' )
		: __( 'Where your luminous ritual kits will be delivered.', 'dragon-glow' );
	$icon      = ( 'billing' === $type ) ? 'receipt' : 'local_shipping';
	$back_url  = dg_account_endpoint_url( 'edit-address' );
	?>
	<article class="dg-account-address-edit" data-sr>
		<header class="dg-account-address-edit__head">
			<a href="<?php echo esc_url( $back_url ); ?>" class="dg-account-address-edit__back">
				<span class="material-symbols-outlined" aria-hidden="true">arrow_back</span>
				<?php esc_html_e( 'All addresses', 'dragon-glow' ); ?>
			</a>
			<div class="dg-account-address-edit__heading">
				<div class="dg-account-address-edit__icon" aria-hidden="true">
					<span class="material-symbols-outlined"><?php echo esc_html( $icon ); ?></span>
				</div>
				<div>
					<h2 class="dg-account-address-edit__title"><?php echo esc_html( $title ); ?></h2>
					<p class="dg-account-address-edit__sub"><?php echo esc_html( $subtitle ); ?></p>
				</div>
			</div>
		</header>

		<?php
		if ( function_exists( 'wc_get_template' ) ) {
			// Re-use WC's own shortcode handler — it already builds the
			// $address array (country-aware fields, current user values,
			// filtered through woocommerce_address_to_edit) and calls
			// wc_get_template('myaccount/form-edit-address.php', ...) with
			// the variable names the template expects. Building $address
			// by hand here would duplicate that logic and drift from
			// checkout field customisations set by plugins.
			\WC_Shortcode_My_Account::edit_address( $type );
		}
		?>
	</article>
	<?php
}

/**
 * Render the Addresses panel shell + route to list or edit view.
 *
 * Auto-detects whether the current request is editing one address or
 * browsing the list. Mirrors WC's `myaccount/my-address.php` + `myaccount/edit-address.php`
 * route, but with our own Luminous Ethereal markup on the list side.
 *
 * @return void
 */
function dg_render_account_addresses_panel(): void {
	$edit_type = dg_current_address_edit_type();
	?>
	<section class="dg-account-panel dg-account-panel--addresses" data-sr>
		<header class="dg-account-panel__header">
			<h2 class="dg-account-panel__title"><?php esc_html_e( 'Addresses', 'dragon-glow' ); ?></h2>
			<?php if ( '' === $edit_type ) : ?>
				<a href="<?php echo esc_url( dg_account_endpoint_url( '' ) ); ?>" class="dg-account-panel__link">
					<span class="material-symbols-outlined" aria-hidden="true">arrow_back</span>
					<?php esc_html_e( 'Back to dashboard', 'dragon-glow' ); ?>
				</a>
			<?php endif; ?>
		</header>

		<?php
		// Success notice after WC save redirects to the list; errors stay on the
		// edit form (also printed inside form-edit-address.php). Print here so
		// the list view surfaces "Address changed successfully."
		if ( '' === $edit_type && function_exists( 'wc_print_notices' ) ) {
			echo '<div class="dg-account-notices">';
			wc_print_notices();
			echo '</div>';
		}

		if ( '' !== $edit_type ) {
			dg_render_account_addresses_edit( $edit_type );
		} else {
			dg_render_account_addresses_list();
		}
		?>
	</section>
	<?php
}

/**
 * Wrap WC edit-account template in our panel shell.
 *
 * @return void
 */
function dg_render_account_edit_panel(): void {
	?>
	<section class="dg-account-panel" data-sr>
		<header class="dg-account-panel__header">
			<h2 class="dg-account-panel__title"><?php esc_html_e( 'Account details', 'dragon-glow' ); ?></h2>
			<a href="<?php echo esc_url( dg_account_endpoint_url( '' ) ); ?>" class="dg-account-panel__link">
				<span class="material-symbols-outlined" aria-hidden="true">arrow_back</span>
				<?php esc_html_e( 'Back to dashboard', 'dragon-glow' ); ?>
			</a>
		</header>
		<?php
		if ( function_exists( 'wc_get_template' ) ) {
			wc_get_template( 'myaccount/form-edit-account.php' );
		}
		?>
	</section>
	<?php
}

/**
 * Sign-out confirmation page.
 *
 * WC's default `customer-logout` endpoint just logs the user out; we wrap
 * it so a "you've signed out" confirmation appears on a friendly surface.
 *
 * @return void
 */
function dg_render_account_logout_panel(): void {
	if ( ! is_user_logged_in() ) {
		return;
	}
	$url = add_query_arg( 'dg_logout', '1', dg_account_endpoint_url( 'customer-logout' ) );
	?>
	<section class="dg-account-panel dg-account-panel--center" data-sr>
		<span class="material-symbols-outlined dg-account-empty__icon">logout</span>
		<h2 class="dg-account-panel__title"><?php esc_html_e( 'Sign out of Dragon Glow', 'dragon-glow' ); ?></h2>
		<p class="dg-account-empty__text">
			<?php esc_html_e( 'You can keep your saved addresses by staying signed in.', 'dragon-glow' ); ?>
		</p>
		<div class="dg-account-logout__actions">
			<a href="<?php echo esc_url( dg_account_endpoint_url( '' ) ); ?>" class="dg-btn dg-btn--ghost">
				<span class="material-symbols-outlined" aria-hidden="true">arrow_back</span>
				<?php esc_html_e( 'Cancel', 'dragon-glow' ); ?>
			</a>
			<a href="<?php echo esc_url( $url ); ?>" class="dg-btn dg-btn--primary dg-btn--danger">
				<span class="material-symbols-outlined" aria-hidden="true">logout</span>
				<?php esc_html_e( 'Sign out', 'dragon-glow' ); ?>
			</a>
		</div>
	</section>
	<?php
}

/**
 * Master renderer for the authenticated account area.
 *
 * Echoes the full page (hero + sidebar + content). Picks the right panel based
 * on the current endpoint, defaulting to the dashboard when none matches.
 *
 * @return void
 */
function dg_render_wc_account(): void {
	// JS guard — prevent FOUC for [data-sr] elements (re-using main.js convention).
	echo '<script>document.documentElement.classList.add(\'dg-js\');</script>';

	// Detect endpoint early — register page is public, others need auth.
	$current_endpoint = dg_current_account_endpoint();

	// Register endpoint is public — render it even when signed out.
	if ( 'register' === $current_endpoint ) {
		if ( is_user_logged_in() ) {
			// Already logged in → redirect to dashboard.
			wp_safe_redirect( dg_account_endpoint_url( '' ) );
			exit;
		}
		dg_render_account_register_page();
		return;
	}

	// Auth gate for all other endpoints.
	if ( ! is_user_logged_in() ) {
		dg_render_account_signed_out();
		return;
	}

	$customer = wp_get_current_user();
	?>
	<main class="dg-account" id="main-content">
		<div class="dg-account__wrap">

			<?php dg_render_account_hero( $customer ); ?>

			<div class="dg-account__layout">

				<aside class="dg-account__sidebar" aria-label="<?php esc_attr_e( 'Account navigation', 'dragon-glow' ); ?>">
					<?php dg_render_account_sidebar( $current_endpoint ); ?>
				</aside>

				<div class="dg-account__content">
					<?php
					switch ( $current_endpoint ) {
						case 'orders':
							dg_render_account_orders_panel();
							break;
						case 'edit-address':
							dg_render_account_addresses_panel();
							break;
						case 'edit-account':
							dg_render_account_edit_panel();
							break;
						case 'customer-logout':
							dg_render_account_logout_panel();
							break;
						default:
							dg_render_account_dashboard();
							break;
					}
					?>
				</div>

			</div>
		</div>

		<?php
		// Confirm dialog lives outside .dg-account__content so AJAX panel
		// swaps do not destroy it. Used by "Use billing instead".
		dg_render_account_confirm_modal();
		?>
	</main>
	<?php
}

/**
 * Account confirm dialog shell (Luminous Ethereal).
 *
 * Markup only — copy/actions filled by account.js when a form with
 * `data-dg-use-billing` is submitted. Kept outside AJAX content region.
 *
 * @return void
 */
function dg_render_account_confirm_modal(): void {
	?>
	<div class="dg-account-confirm" id="dg-account-confirm" hidden>
		<div class="dg-account-confirm__overlay" data-dg-confirm-dismiss tabindex="-1"></div>
		<div class="dg-account-confirm__dialog"
			 role="alertdialog"
			 aria-modal="true"
			 aria-labelledby="dg-account-confirm-title"
			 aria-describedby="dg-account-confirm-body"
			 tabindex="-1">
			<div class="dg-account-confirm__icon" aria-hidden="true">
				<span class="material-symbols-outlined">swap_horiz</span>
			</div>
			<p class="dg-account-confirm__eyebrow"><?php esc_html_e( 'Confirm', 'dragon-glow' ); ?></p>
			<h2 class="dg-account-confirm__title" id="dg-account-confirm-title"></h2>
			<p class="dg-account-confirm__body" id="dg-account-confirm-body"></p>
			<div class="dg-account-confirm__actions">
				<button type="button" class="dg-account-confirm__btn dg-account-confirm__btn--ghost" data-dg-confirm-cancel>
					<?php esc_html_e( 'Cancel', 'dragon-glow' ); ?>
				</button>
				<button type="button" class="dg-account-confirm__btn dg-account-confirm__btn--primary" data-dg-confirm-ok>
					<?php esc_html_e( 'Confirm', 'dragon-glow' ); ?>
				</button>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Layout for signed-out users — "Heritage Atelier" auth gate.
 *
 * Split showcase layout matching the Dragon Glow auth portal design
 * reference (Cormorant Garamond heritage gold styling, scoped to
 * `.dg-account-auth*` via `assets/css/account-auth.css`). Left column is a
 * decorative arch showcase; right column holds sign-in + (optional)
 * register forms. Form fields, names, nonces, and submit action are all
 * WC defaults so `woocommerce_login_form_*` / `woocommerce_register_form_*`
 * actions and WC_Form_Handler continue to work unchanged.
 *
 * @return void
 */
function dg_render_account_signed_out(): void {
	$register_enabled = ( 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) );
	$lost_pwd_url     = (string) wp_lostpassword_url();
	$showcase_image   = get_theme_file_uri( 'assets/images/account-auth/sign-in.jpg' );
	?>
	<!-- Viewport outer double-gold pinstripe frame -->
	<div class="dg-account-auth__viewport-frame dg-account-auth__viewport-frame--outer" aria-hidden="true"></div>
	<div class="dg-account-auth__viewport-frame dg-account-auth__viewport-frame--inner" aria-hidden="true"></div>

	<!-- Classical corner flourishes (top-left, top-right, bottom-left, bottom-right) -->
	<span class="dg-account-auth__corner-flourish dg-account-auth__corner-flourish--tl" aria-hidden="true">&#10022;</span>
	<span class="dg-account-auth__corner-flourish dg-account-auth__corner-flourish--tr" aria-hidden="true">&#10022;</span>
	<span class="dg-account-auth__corner-flourish dg-account-auth__corner-flourish--bl" aria-hidden="true">&#10022;</span>
	<span class="dg-account-auth__corner-flourish dg-account-auth__corner-flourish--br" aria-hidden="true">&#10022;</span>

	<main class="dg-account dg-account--signed-out" id="main-content">
		<div class="dg-account-auth">

			<!-- Brand header (Atelier Imperial / Dragon Glow / Est. 2026) -->
			<header class="dg-account-auth__brand-header" data-sr>
				<div class="dg-account-auth__brand-eyebrow">
					<div class="dg-account-auth__brand-line"></div>
					<div class="dg-account-auth__brand-eyebrow-text">
						<span class="material-symbols-outlined" aria-hidden="true">flare</span>
						<span><?php esc_html_e( 'Atelier Imperial', 'dragon-glow' ); ?></span>
						<span class="material-symbols-outlined" aria-hidden="true">flare</span>
					</div>
					<div class="dg-account-auth__brand-line"></div>
				</div>
				<h1 class="dg-account-auth__brand-title">
					<?php esc_html_e( 'Dragon Glow', 'dragon-glow' ); ?>
				</h1>
				<div class="dg-account-auth__brand-subtitle">
					<span class="dg-account-auth__brand-rule" aria-hidden="true"></span>
					<p><?php esc_html_e( 'Est. 2026  •  San Francisco  •  New York', 'dragon-glow' ); ?></p>
					<span class="dg-account-auth__brand-rule" aria-hidden="true"></span>
				</div>
			</header>

			<div class="dg-account-auth__frame">

				<!-- Outer inset gold filigree border (double inline border inside the frame) -->
				<span class="dg-account-auth__filigree dg-account-auth__filigree--outer" aria-hidden="true"></span>
				<span class="dg-account-auth__filigree dg-account-auth__filigree--inner" aria-hidden="true"></span>

				<!-- LEFT: Heritage showcase -->
				<div class="dg-account-auth__showcase" data-sr>
					<div class="dg-account-auth__showcase-top">
						<div class="dg-account-auth__tome-row">
							<span class="dg-account-auth__tome"><?php esc_html_e( 'Tome IV', 'dragon-glow' ); ?></span>
							<span class="dg-account-auth__dot" aria-hidden="true">&bull;</span>
							<span class="dg-account-auth__tome-sub"><?php esc_html_e( 'The Golden Alchemy Formulation', 'dragon-glow' ); ?></span>
						</div>
						<span class="dg-account-auth__sanctuary-badge"><?php esc_html_e( 'Ritual Sanctuary', 'dragon-glow' ); ?></span>
					</div>

					<div class="dg-account-auth__arch-wrap">
						<div class="dg-account-auth__crown" aria-hidden="true">
							<span class="dg-account-auth__crown-line"></span>
							<span class="material-symbols-outlined">wb_twilight</span>
							<span class="dg-account-auth__crown-line"></span>
						</div>

						<div class="dg-account-auth__arch">
							<div class="dg-account-auth__arch-inner">
								<img src="<?php echo esc_url( $showcase_image ); ?>"
									alt="<?php esc_attr_e( 'Dragon Glow luxury elixir bottle', 'dragon-glow' ); ?>"
									class="dg-account-auth__arch-img" loading="eager" />
								<div class="dg-account-auth__arch-caption">
									<p><?php esc_html_e( 'Dragon Elixir N° 1', 'dragon-glow' ); ?></p>
									<span><?php esc_html_e( '24K Botanical Golden Serum', 'dragon-glow' ); ?></span>
								</div>
							</div>
							<span class="dg-account-auth__medallion" aria-hidden="true">
								<span class="material-symbols-outlined">verified</span>
							</span>
						</div>
					</div>

					<div class="dg-account-auth__ritual">
						<p class="dg-account-auth__quote">
							&ldquo;<?php esc_html_e( 'Awaken the sovereign radiance dormant within each morning dawn.', 'dragon-glow' ); ?>&rdquo;
						</p>
						<div class="dg-account-auth__steps">
							<div class="dg-account-auth__step">
								<span class="dg-account-auth__step-label"><?php esc_html_e( 'I. PURIFY', 'dragon-glow' ); ?></span>
								<span class="dg-account-auth__step-sub"><?php esc_html_e( 'Nectar Emulsion', 'dragon-glow' ); ?></span>
							</div>
							<div class="dg-account-auth__step">
								<span class="dg-account-auth__step-label"><?php esc_html_e( 'II. INFUSE', 'dragon-glow' ); ?></span>
								<span class="dg-account-auth__step-sub"><?php esc_html_e( 'Golden Drops', 'dragon-glow' ); ?></span>
							</div>
							<div class="dg-account-auth__step">
								<span class="dg-account-auth__step-label"><?php esc_html_e( 'III. SEAL', 'dragon-glow' ); ?></span>
								<span class="dg-account-auth__step-sub"><?php esc_html_e( 'Silk Veil Balm', 'dragon-glow' ); ?></span>
							</div>
						</div>
					</div>
				</div>

				<!-- RIGHT: Sign in / Register -->
				<div class="dg-account-auth__panel-col" data-sr>

					<header class="dg-account-auth__head">
						<div class="dg-account-auth__eyebrow-row">
							<span class="dg-account-auth__eyebrow-line" aria-hidden="true"></span>
							<span class="dg-account-auth__eyebrow"><?php esc_html_e( 'Registry of Members', 'dragon-glow' ); ?></span>
							<span class="dg-account-auth__eyebrow-line" aria-hidden="true"></span>
						</div>
						<h1 class="dg-account-auth__title"><?php esc_html_e( 'Enter the Sanctuary', 'dragon-glow' ); ?></h1>
						<span class="dg-account-auth__title-rule" aria-hidden="true"></span>
						<p class="dg-account-auth__sub">
							<?php esc_html_e( 'Present your apothecary credentials to receive curated formulations, private releases, and ancestral botanical privileges.', 'dragon-glow' ); ?>
						</p>
					</header>

					<?php wc_print_notices(); ?>

					<form method="post" class="dg-account-form" action="<?php echo esc_url( dg_account_endpoint_url( '' ) ); ?>" novalidate>

						<?php do_action( 'woocommerce_login_form_start' ); ?>

						<div class="dg-account-field">
							<div class="dg-account-field__row-label">
								<label for="dg-login-username"><?php esc_html_e( 'Client ID or Email', 'dragon-glow' ); ?></label>
								<span class="dg-account-field__hint"><?php esc_html_e( 'Dragon Codex', 'dragon-glow' ); ?></span>
							</div>
							<div class="dg-account-field__wrap">
								<span class="material-symbols-outlined dg-account-field__icon" aria-hidden="true">fingerprint</span>
								<input
									type="text"
									class="dg-account-field__input dg-account-field__input--icon"
									name="username"
									id="dg-login-username"
									autocomplete="username"
									placeholder="<?php esc_attr_e( 'patron@maison-dragonglow.com', 'dragon-glow' ); ?>"
									value="<?php echo isset( $_POST['username'] ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
										?>" />
							</div>
						</div>

						<div class="dg-account-field">
							<div class="dg-account-field__row-label">
								<label for="dg-login-password"><?php esc_html_e( 'Security Passphrase', 'dragon-glow' ); ?></label>
								<a href="<?php echo esc_url( $lost_pwd_url ); ?>" class="dg-account-form__link">
									<?php esc_html_e( 'Lost credentials?', 'dragon-glow' ); ?>
								</a>
							</div>
							<div class="dg-account-field__wrap">
								<span class="material-symbols-outlined dg-account-field__icon" aria-hidden="true">key</span>
								<input
									type="password"
									class="dg-account-field__input dg-account-field__input--icon"
									name="password"
									id="dg-login-password"
									autocomplete="current-password"
									placeholder="<?php esc_attr_e( 'Enter your confidential cipher', 'dragon-glow' ); ?>" />
								<button
									type="button"
									class="dg-account-field__toggle"
									aria-label="<?php esc_attr_e( 'Show password', 'dragon-glow' ); ?>"
									data-label-hide="<?php esc_attr_e( 'Show password', 'dragon-glow' ); ?>"
									data-label-show="<?php esc_attr_e( 'Hide password', 'dragon-glow' ); ?>"
									data-dg-toggle-password="#dg-login-password">
									<span class="material-symbols-outlined">visibility</span>
								</button>
							</div>
						</div>

						<div class="dg-account-field__row">
							<label class="dg-account-checkbox">
								<input type="checkbox" name="rememberme" value="forever" />
								<span><?php esc_html_e( 'Preserve session token on this terminal', 'dragon-glow' ); ?></span>
							</label>
						</div>

						<?php do_action( 'woocommerce_login_form' ); ?>

						<input type="hidden" name="woocommerce-login-nonce" value="<?php echo esc_attr( wp_create_nonce( 'woocommerce-login' ) ); ?>" />

						<button type="submit" name="login" value="<?php esc_attr_e( 'Sign in', 'dragon-glow' ); ?>" class="dg-account-auth__submit">
							<span class="dg-account-auth__submit-frame" aria-hidden="true"></span>
							<span class="material-symbols-outlined">workspace_premium</span>
							<span><?php esc_html_e( 'Enter the Sanctuary', 'dragon-glow' ); ?></span>
							<span class="material-symbols-outlined">arrow_right_alt</span>
						</button>

					<?php do_action( 'woocommerce_login_form_end' ); ?>
				</form>

			<!-- Alternative Access (social sign-in buttons) -->
			<div class="dg-account-auth__alt-access">
				<div class="dg-account-auth__alt-access-header">
					<span class="dg-account-auth__alt-access-line" aria-hidden="true"></span>
					<div class="dg-account-auth__alt-access-text">
						<span class="dg-account-auth__divider-star">&#10022;</span>
						<span><?php esc_html_e( 'Alternative Access', 'dragon-glow' ); ?></span>
						<span class="dg-account-auth__divider-star">&#10022;</span>
					</div>
					<span class="dg-account-auth__alt-access-line" aria-hidden="true"></span>
				</div>

					<div class="dg-account-auth__social-buttons">
						<button type="button" class="dg-account-auth__social-btn dg-account-auth__social-btn--google" disabled>
							<svg class="dg-account-auth__social-icon" viewBox="0 0 24 24" aria-hidden="true">
								<path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
								<path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
								<path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
								<path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
							</svg>
							<span><?php esc_html_e( 'Google', 'dragon-glow' ); ?></span>
						</button>

						<button type="button" class="dg-account-auth__social-btn dg-account-auth__social-btn--apple" disabled>
							<svg class="dg-account-auth__social-icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
								<path d="M17.05 20.28c-.98.95-2.05.88-3.08.4-1.09-.5-2.08-.48-3.24 0-1.44.62-2.2.44-3.06-.4C2.79 15.25 3.51 7.59 9.05 7.31c1.35.07 2.29.74 3.08.8 1.18-.24 2.31-.93 3.57-.84 1.51.12 2.65.72 3.4 1.8-3.12 1.87-2.38 5.98.48 7.13-.57 1.5-1.31 2.99-2.54 4.09l.01-.01zM12.03 7.25c-.15-2.23 1.66-4.07 3.74-4.25.29 2.58-2.34 4.5-3.74 4.25z"/>
							</svg>
							<span><?php esc_html_e( 'Apple ID', 'dragon-glow' ); ?></span>
						</button>
					</div>

					<p class="dg-account-auth__register-prompt">
						<?php esc_html_e( 'Not yet inscribed in our ledger?', 'dragon-glow' ); ?>
						<a href="<?php echo esc_url( dg_account_endpoint_url( 'register' ) ); ?>" class="dg-account-auth__register-link">
							<?php esc_html_e( 'Request Atelier Initiation', 'dragon-glow' ); ?>
						</a>
					</p>
				</div>

				<?php if ( $register_enabled ) : ?>
						<div class="dg-account-auth__divider">
							<span class="dg-account-auth__divider-star">&#10022;</span>
							<span><?php esc_html_e( 'Not Yet Inscribed?', 'dragon-glow' ); ?></span>
							<span class="dg-account-auth__divider-star">&#10022;</span>
						</div>

						<section class="dg-account-auth__register" id="dg-account-register">
							<p class="dg-account-auth__text">
								<?php esc_html_e( 'Track orders, save your favourites, and unlock exclusive offers from Dragon Glow.', 'dragon-glow' ); ?>
							</p>

							<form method="post" class="dg-account-form" action="<?php echo esc_url( dg_account_endpoint_url( '' ) ); ?>" novalidate>

								<?php do_action( 'woocommerce_register_form_start' ); ?>

								<?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>
									<div class="dg-account-field">
										<label for="dg-reg-username"><?php esc_html_e( 'Username', 'dragon-glow' ); ?></label>
										<input
											type="text"
											class="dg-account-field__input"
											name="username"
											id="dg-reg-username"
											autocomplete="username"
											value="<?php echo isset( $_POST['username'] ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
												?>" />
									</div>
								<?php endif; ?>

								<div class="dg-account-field">
									<label for="dg-reg-email"><?php esc_html_e( 'Client ID or Primary Email', 'dragon-glow' ); ?></label>
									<input
										type="email"
										class="dg-account-field__input"
										name="email"
										id="dg-reg-email"
										autocomplete="email"
										placeholder="<?php esc_attr_e( 'patron@maison-dragonglow.com', 'dragon-glow' ); ?>"
										value="<?php echo isset( $_POST['email'] ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
											?>" />
								</div>

								<div class="dg-account-field">
									<label for="dg-reg-password"><?php esc_html_e( 'Choose Security Cipher (Password)', 'dragon-glow' ); ?></label>
									<div class="dg-account-field__wrap">
										<input
											type="password"
											class="dg-account-field__input"
											name="password"
											id="dg-reg-password"
											autocomplete="new-password"
											placeholder="<?php esc_attr_e( 'Minimum 8 golden characters', 'dragon-glow' ); ?>" />
										<button
											type="button"
											class="dg-account-field__toggle"
											aria-label="<?php esc_attr_e( 'Show password', 'dragon-glow' ); ?>"
											data-label-hide="<?php esc_attr_e( 'Show password', 'dragon-glow' ); ?>"
											data-label-show="<?php esc_attr_e( 'Hide password', 'dragon-glow' ); ?>"
											data-dg-toggle-password="#dg-reg-password">
											<span class="material-symbols-outlined">visibility</span>
										</button>
									</div>
								</div>

								<?php do_action( 'woocommerce_register_form' ); ?>

								<input type="hidden" name="woocommerce-register-nonce" value="<?php echo esc_attr( wp_create_nonce( 'woocommerce-register' ) ); ?>" />

								<button type="submit" name="register" value="<?php esc_attr_e( 'Create account', 'dragon-glow' ); ?>" class="dg-account-auth__submit">
									<span class="dg-account-auth__submit-frame" aria-hidden="true"></span>
									<span class="material-symbols-outlined">verified_user</span>
									<span><?php esc_html_e( 'Inscribe in Atelier Ledger', 'dragon-glow' ); ?></span>
								</button>

								<?php do_action( 'woocommerce_register_form_end' ); ?>
							</form>
						</section>
					<?php endif; ?>

			<div class="dg-account-auth__footnote">
				<span class="dg-account-auth__footnote-item">
					<span class="material-symbols-outlined" aria-hidden="true">eco</span>
					<span><?php esc_html_e( 'Pure Botanical Distillation', 'dragon-glow' ); ?></span>
				</span>
				<span class="dg-account-auth__dot" aria-hidden="true">&bull;</span>
				<span class="dg-account-auth__footnote-item">
					<span class="material-symbols-outlined" aria-hidden="true">verified</span>
					<span><?php esc_html_e( 'Certified Cruelty-Free', 'dragon-glow' ); ?></span>
				</span>
			</div>

				</div>

			</div>
		</div>
	</main>

	<!-- Classical Heritage Footer -->
	<footer class="dg-account-auth__heritage-footer">
		<div class="dg-account-auth__heritage-locations">
			<span><?php esc_html_e( 'Heritage Atelier San Francisco', 'dragon-glow' ); ?></span>
			<span class="dg-account-auth__heritage-dot" aria-hidden="true">&bull;</span>
			<span><?php esc_html_e( 'Fifth Avenue New York', 'dragon-glow' ); ?></span>
			<span class="dg-account-auth__heritage-dot" aria-hidden="true">&bull;</span>
			<span><?php esc_html_e( 'Parisian Archive', 'dragon-glow' ); ?></span>
		</div>
		<div class="dg-account-auth__heritage-copyright">
			<?php
			/* translators: %d: Current year */
			printf( esc_html__( '© %d Dragon Glow Cosmetics Inc. All rights reserved.', 'dragon-glow' ), (int) gmdate( 'Y' ) );
			?>
		</div>
	</footer>
	<?php
}

/**
 * Force the WC account endpoint to render through our custom template.
 *
 * Mirrors `dg_use_wc_checkout_template()`. Without this filter the WC default
 * `my-account.php` template would run, which doesn't match the Luminous
 * Ethereal design system.
 *
 * @param string $template Resolved template path.
 * @return string
 */
function dg_use_wc_account_template( string $template ): string {
	if ( ! did_action( 'wp' ) ) {
		return $template;
	}
	if ( function_exists( 'is_account_page' ) && is_account_page() && ! is_order_received_page() ) {
		$custom = locate_template( 'page-templates/template-wc-account.php' );
		if ( $custom ) {
			return $custom;
		}
	}
	return $template;
}
add_filter( 'template_include', 'dg_use_wc_account_template' );

/**
 * Sync theme `?address=` / path type into WC's `edit-address` query var.
 *
 * Shared hosting often cannot rely on `/edit-address/billing/` rewrites, so
 * the theme uses `?address=billing|shipping`. `WC_Form_Handler::save_address`
 * still reads `$wp->query_vars['edit-address']` — without this sync, shipping
 * saves would fall back to billing (empty query var).
 *
 * Runs at priority 1 on `template_redirect`, before WC's save handler (10).
 *
 * @return void
 */
function dg_sync_edit_address_query_var(): void {
	global $wp;

	if ( ! function_exists( 'is_account_page' ) || ! is_account_page() ) {
		return;
	}

	$type = dg_current_address_edit_type();
	if ( '' === $type ) {
		return;
	}

	if ( ! isset( $wp->query_vars ) || ! is_array( $wp->query_vars ) ) {
		return;
	}

	$current = isset( $wp->query_vars['edit-address'] ) ? sanitize_key( (string) $wp->query_vars['edit-address'] ) : '';
	if ( in_array( $current, array( 'billing', 'shipping' ), true ) ) {
		return;
	}

	$wp->query_vars['edit-address'] = $type;
}
add_action( 'template_redirect', 'dg_sync_edit_address_query_var', 1 );

/**
 * Clear the logged-in customer's shipping address fields.
 *
 * WC convention: empty shipping meta ⇒ ship to billing at checkout.
 * Does not modify billing fields.
 *
 * @param int $user_id Customer user ID.
 * @return bool True when cleared and saved.
 */
function dg_clear_customer_shipping_address( int $user_id ): bool {
	if ( $user_id <= 0 || ! class_exists( 'WC_Customer' ) ) {
		return false;
	}

	$customer = new WC_Customer( $user_id );
	$keys     = array(
		'shipping_first_name',
		'shipping_last_name',
		'shipping_company',
		'shipping_address_1',
		'shipping_address_2',
		'shipping_city',
		'shipping_state',
		'shipping_postcode',
		'shipping_country',
		'shipping_phone',
	);

	foreach ( $keys as $key ) {
		$setter = 'set_' . $key;
		if ( is_callable( array( $customer, $setter ) ) ) {
			$customer->{$setter}( '' );
		} else {
			$customer->update_meta_data( $key, '' );
		}
	}

	$customer->save();

	/**
	 * Fires after shipping address fields are cleared (ship-to-billing).
	 *
	 * @param int         $user_id  Customer ID.
	 * @param WC_Customer $customer Customer object after save.
	 */
	do_action( 'dg_customer_cleared_shipping_address', $user_id, $customer );

	return true;
}

/**
 * Handle "Use billing instead" — remove shipping so orders ship to billing.
 *
 * Expects POST: dg_use_billing_instead=1 + dg_use_billing_nonce.
 * Mirrors WC My Account form handlers (nonce → mutate → notice → redirect).
 *
 * @return void
 */
function dg_handle_use_billing_instead(): void {
	if ( empty( $_POST['dg_use_billing_instead'] ) ) {
		return;
	}

	if ( ! function_exists( 'is_account_page' ) || ! is_account_page() ) {
		return;
	}

	if ( ! is_user_logged_in() ) {
		return;
	}

	$nonce = isset( $_POST['dg_use_billing_nonce'] )
		? sanitize_text_field( wp_unslash( $_POST['dg_use_billing_nonce'] ) )
		: '';

	if ( ! $nonce || ! wp_verify_nonce( $nonce, 'dg_use_billing_instead' ) ) {
		wc_add_notice( __( 'Security check failed. Please try again.', 'dragon-glow' ), 'error' );
		wp_safe_redirect( dg_account_endpoint_url( 'edit-address' ) );
		exit;
	}

	$user_id = get_current_user_id();
	$billing = dg_get_account_address_data( $user_id, 'billing' );

	if ( null === $billing ) {
		wc_add_notice(
			__( 'Add a billing address before removing your shipping address.', 'dragon-glow' ),
			'error'
		);
		wp_safe_redirect( dg_account_endpoint_url( 'edit-address' ) );
		exit;
	}

	$shipping = dg_get_account_address_data( $user_id, 'shipping' );
	if ( null === $shipping ) {
		wc_add_notice(
			__( 'Orders already ship to your billing address.', 'dragon-glow' ),
			'notice'
		);
		wp_safe_redirect( dg_account_endpoint_url( 'edit-address' ) );
		exit;
	}

	if ( ! dg_clear_customer_shipping_address( $user_id ) ) {
		wc_add_notice(
			__( 'Could not update your shipping address. Please try again.', 'dragon-glow' ),
			'error'
		);
		wp_safe_redirect( dg_account_endpoint_url( 'edit-address' ) );
		exit;
	}

	wc_add_notice(
		__( 'Shipping address removed. Orders will ship to your billing address.', 'dragon-glow' ),
		'success'
	);
	wp_safe_redirect( dg_account_endpoint_url( 'edit-address' ) );
	exit;
}
add_action( 'template_redirect', 'dg_handle_use_billing_instead', 5 );

/**
 * Add body class to hide site chrome (header-nav and footer-main) when viewing
 * the auth portal (My Account page while signed out or /register endpoint).
 *
 * The auth portal (Heritage Atelier) is a full-bleed, distraction-free screen —
 * enterprise pattern for dedicated auth flows. We still call `get_header()` and
 * `get_footer()` so `wp_head()`/`wp_footer()` fire (enqueue CSS/JS), but hide
 * the site nav and footer columns via CSS scoped to the body class.
 *
 * @param array $classes Existing body classes.
 * @return array Modified body classes.
 */
function dg_auth_portal_body_class( array $classes ): array {
	// Sign-in page (not logged in)
	if ( is_account_page() && ! is_user_logged_in() ) {
		$classes[] = 'dg-hide-site-chrome';
	}
	
	// Register endpoint (accessed via /my-account/register/)
	global $wp_query;
	if ( is_account_page() && isset( $wp_query->query_vars['register'] ) ) {
		$classes[] = 'dg-hide-site-chrome';
	}
	
	return $classes;
}
add_filter( 'body_class', 'dg_auth_portal_body_class' );

/**
 * Render the full registration page (Heritage Atelier design, 2-column layout).
 *
 * Port of stitch_dragon_glow_auth_portal/đăng-ký reference — 100% visual match.
 * Left column: product showcase (arched frame + privilege cards).
 * Right column: registration form (first name, last name, email, password with
 * strength indicator, aspiration dropdown, checkboxes, social buttons).
 *
 * @return void
 */
function dg_render_account_register_page(): void {
	$showcase_image = get_theme_file_uri( 'assets/images/account-auth/register.jpg' );
	?>
	<!-- Viewport outer double-gold pinstripe frame -->
	<div class="dg-account-auth__viewport-frame dg-account-auth__viewport-frame--outer" aria-hidden="true"></div>
	<div class="dg-account-auth__viewport-frame dg-account-auth__viewport-frame--inner" aria-hidden="true"></div>

	<!-- Classical corner flourishes -->
	<span class="dg-account-auth__corner-flourish dg-account-auth__corner-flourish--tl" aria-hidden="true">&#10022;</span>
	<span class="dg-account-auth__corner-flourish dg-account-auth__corner-flourish--tr" aria-hidden="true">&#10022;</span>
	<span class="dg-account-auth__corner-flourish dg-account-auth__corner-flourish--bl" aria-hidden="true">&#10022;</span>
	<span class="dg-account-auth__corner-flourish dg-account-auth__corner-flourish--br" aria-hidden="true">&#10022;</span>

	<main class="dg-account dg-account--register" id="main-content">
		<div class="dg-account-auth dg-account-auth--register">
			
			<!-- Symmetrical Classical Crest Header -->
			<header class="dg-account-auth__crest-header">
				<div class="dg-account-auth__crest-ornaments">
					<span class="dg-account-auth__crest-line" aria-hidden="true"></span>
					<div class="dg-account-auth__crest-badge">
						<span class="material-symbols-outlined" aria-hidden="true">flare</span>
						<span class="dg-account-auth__crest-text"><?php esc_html_e( 'Atelier Imperial', 'dragon-glow' ); ?></span>
						<span class="material-symbols-outlined" aria-hidden="true">flare</span>
					</div>
					<span class="dg-account-auth__crest-line" aria-hidden="true"></span>
				</div>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="dg-account-auth__brand-link">
					<h1 class="dg-account-auth__brand-title"><?php esc_html_e( 'Dragon Glow', 'dragon-glow' ); ?></h1>
					<div class="dg-account-auth__brand-meta">
						<span class="dg-account-auth__brand-rule" aria-hidden="true"></span>
						<p class="dg-account-auth__brand-tagline">
							<?php esc_html_e( 'Est. 2026  •  San Francisco  •  New York', 'dragon-glow' ); ?>
						</p>
						<span class="dg-account-auth__brand-rule" aria-hidden="true"></span>
					</div>
				</a>
			</header>

			<div class="dg-account-auth__panel dg-account-auth__panel--register">
				<div class="dg-account-auth__panel-inner">
					
					<!-- Ambient golden glow wash -->
					<div class="dg-account-auth__glow dg-account-auth__glow--tl" aria-hidden="true"></div>
					<div class="dg-account-auth__glow dg-account-auth__glow--br" aria-hidden="true"></div>

					<!-- Toast notification (shown after successful registration via JS) -->
					<div class="dg-account-auth__toast" id="dg-register-toast" hidden>
						<span class="material-symbols-outlined">verified</span>
						<span class="dg-account-auth__toast-msg" id="dg-register-toast-msg">
							<?php esc_html_e( 'Registry updated successfully.', 'dragon-glow' ); ?>
						</span>
					</div>

					<div class="dg-account-auth__panel-grid">
						
						<!-- LEFT COLUMN: Heritage Ritual Showcase -->
						<div class="dg-account-auth__showcase-col">
							
							<!-- Column separator (desktop) -->
							<div class="dg-account-auth__col-separator" aria-hidden="true"></div>

							<!-- Column header -->
							<div class="dg-account-auth__showcase-header">
								<div class="dg-account-auth__showcase-meta">
									<span class="dg-account-auth__eyebrow"><?php esc_html_e( 'Tome IV • Initiation', 'dragon-glow' ); ?></span>
									<span class="dg-account-auth__sanctuary-badge">
										<span class="dg-account-auth__pulse-dot" aria-hidden="true"></span>
										<?php esc_html_e( 'Sanctuary Registry', 'dragon-glow' ); ?>
									</span>
								</div>
								<h2 class="dg-account-auth__showcase-title">
									<?php esc_html_e( 'The Golden Alchemy Ritual', 'dragon-glow' ); ?>
								</h2>
								<span class="dg-account-auth__title-rule" aria-hidden="true"></span>
							</div>

							<!-- Arched Roman silhouette showcase -->
							<div class="dg-account-auth__arched-frame">
								<!-- Crown motif -->
								<div class="dg-account-auth__crown-motif">
									<span aria-hidden="true">&#10023;</span>
									<span class="material-symbols-outlined">crown</span>
									<span aria-hidden="true">&#10023;</span>
								</div>

								<!-- Roman arch picture frame -->
								<div class="dg-account-auth__arch-border">
									<div class="dg-account-auth__arch-inner">
										<img 
											src="<?php echo esc_url( $showcase_image ); ?>" 
											alt="<?php esc_attr_e( 'Dragon Glow luxury skincare golden serum', 'dragon-glow' ); ?>"
											class="dg-account-auth__arch-img" />
										<div class="dg-account-auth__arch-overlay" aria-hidden="true"></div>
										<div class="dg-account-auth__arch-label">
											<span class="dg-account-auth__arch-eyebrow">
												<?php esc_html_e( 'Vintage Distillation', 'dragon-glow' ); ?>
											</span>
											<span class="dg-account-auth__arch-name">
												<?php esc_html_e( 'Dragon Elixir Nº 1', 'dragon-glow' ); ?>
											</span>
										</div>
									</div>
								</div>

								<!-- Product caption -->
								<div class="dg-account-auth__arch-caption">
									<p class="dg-account-auth__arch-spec">
										<?php esc_html_e( 'Dragon Elixir Nº 1 — 24K Botanical Golden Serum', 'dragon-glow' ); ?>
									</p>
									<p class="dg-account-auth__arch-quote">
										<?php esc_html_e( '"Inscribe your name into our ledger of eternal radiance."', 'dragon-glow' ); ?>
									</p>
								</div>
							</div>

							<!-- Initiation privileges: 3 gilded cards -->
							<div class="dg-account-auth__privileges">
								<div class="dg-account-auth__privilege-card">
									<div class="dg-account-auth__privilege-num">I</div>
									<div class="dg-account-auth__privilege-content">
										<div class="dg-account-auth__privilege-header">
											<h4 class="dg-account-auth__privilege-title"><?php esc_html_e( 'Maiden Gift', 'dragon-glow' ); ?></h4>
											<span class="dg-account-auth__privilege-badge">15% Off</span>
										</div>
										<p class="dg-account-auth__privilege-text">
											<?php esc_html_e( 'Code', 'dragon-glow' ); ?> 
											<span class="dg-account-auth__privilege-code">RADIANT15</span> 
											<?php esc_html_e( 'applied automatically upon initiation.', 'dragon-glow' ); ?>
										</p>
									</div>
								</div>

								<div class="dg-account-auth__privilege-card">
									<div class="dg-account-auth__privilege-num">II</div>
									<div class="dg-account-auth__privilege-content">
										<h4 class="dg-account-auth__privilege-title"><?php esc_html_e( 'Archive Access', 'dragon-glow' ); ?></h4>
										<p class="dg-account-auth__privilege-text">
											<?php esc_html_e( 'Early seasonal allocations & private reserve formulation drafts.', 'dragon-glow' ); ?>
										</p>
									</div>
								</div>

								<div class="dg-account-auth__privilege-card">
									<div class="dg-account-auth__privilege-num">III</div>
									<div class="dg-account-auth__privilege-content">
										<h4 class="dg-account-auth__privilege-title"><?php esc_html_e( 'Bespoke Ritual', 'dragon-glow' ); ?></h4>
										<p class="dg-account-auth__privilege-text">
											<?php esc_html_e( 'Complimentary dermal diagnostic and customized botanical guide.', 'dragon-glow' ); ?>
										</p>
									</div>
								</div>
							</div>

						</div>

						<!-- RIGHT COLUMN: Initiation & Registration Form -->
						<div class="dg-account-auth__form-col">
							
							<!-- Section heading -->
							<div class="dg-account-auth__form-header">
								<div class="dg-account-auth__form-eyebrow">
									<span aria-hidden="true">&#10023;</span>
									<span><?php esc_html_e( 'Registry of New Patrons', 'dragon-glow' ); ?></span>
									<span aria-hidden="true">&#10023;</span>
								</div>
								<h2 class="dg-account-auth__form-title">
									<?php esc_html_e( 'Initiate Your Atelier Sanctuary', 'dragon-glow' ); ?>
								</h2>
								<p class="dg-account-auth__form-subtitle">
									<?php esc_html_e( 'Enroll into our apothecary ledger to unlock bespoke botanical elixirs, seasonal private allocations, and royal member privileges.', 'dragon-glow' ); ?>
								</p>
							</div>

							<?php wc_print_notices(); ?>

							<!-- Registration form -->
							<form method="post" class="dg-register-form" action="<?php echo esc_url( dg_account_endpoint_url( 'register' ) ); ?>" novalidate>
								
								<?php do_action( 'woocommerce_register_form_start' ); ?>

								<!-- Row: First & Last Name -->
								<div class="dg-register-form__row">
									<div class="dg-register-form__field">
										<label for="dg-reg-first-name" class="dg-register-form__label">
											<?php esc_html_e( 'Patron First Name', 'dragon-glow' ); ?> 
											<span class="dg-register-form__required">*</span>
										</label>
										<div class="dg-register-form__input-wrap">
											<input
												type="text"
												class="dg-register-form__input"
												name="billing_first_name"
												id="dg-reg-first-name"
												placeholder="<?php esc_attr_e( 'Seraphina', 'dragon-glow' ); ?>"
												required
												value="<?php echo isset( $_POST['billing_first_name'] ) ? esc_attr( wp_unslash( $_POST['billing_first_name'] ) ) : ''; // phpcs:ignore ?>" />
											<span class="material-symbols-outlined dg-register-form__icon">person</span>
										</div>
									</div>

									<div class="dg-register-form__field">
										<label for="dg-reg-last-name" class="dg-register-form__label">
											<?php esc_html_e( 'Patron Last Name', 'dragon-glow' ); ?> 
											<span class="dg-register-form__required">*</span>
										</label>
										<div class="dg-register-form__input-wrap">
											<input
												type="text"
												class="dg-register-form__input"
												name="billing_last_name"
												id="dg-reg-last-name"
												placeholder="<?php esc_attr_e( 'Vanderbilt', 'dragon-glow' ); ?>"
												required
												value="<?php echo isset( $_POST['billing_last_name'] ) ? esc_attr( wp_unslash( $_POST['billing_last_name'] ) ) : ''; // phpcs:ignore ?>" />
											<span class="material-symbols-outlined dg-register-form__icon">shield_person</span>
										</div>
									</div>
								</div>

								<!-- Email address -->
								<div class="dg-register-form__field">
									<label for="dg-reg-email" class="dg-register-form__label">
										<?php esc_html_e( 'Client ID or Primary Email', 'dragon-glow' ); ?> 
										<span class="dg-register-form__required">*</span>
									</label>
									<div class="dg-register-form__input-wrap">
										<input
											type="email"
											class="dg-register-form__input"
											name="email"
											id="dg-reg-email"
											autocomplete="email"
											placeholder="<?php esc_attr_e( 'patron@maison-dragonglow.com', 'dragon-glow' ); ?>"
											required
											value="<?php echo isset( $_POST['email'] ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; // phpcs:ignore ?>" />
										<span class="material-symbols-outlined dg-register-form__icon">alternate_email</span>
									</div>
								</div>

								<!-- Password cipher & strength indicator -->
								<div class="dg-register-form__field">
									<label for="dg-reg-password" class="dg-register-form__label">
										<?php esc_html_e( 'Choose Security Cipher (Password)', 'dragon-glow' ); ?> 
										<span class="dg-register-form__required">*</span>
									</label>
									<div class="dg-register-form__input-wrap">
										<input
											type="password"
											class="dg-register-form__input"
											name="password"
											id="dg-reg-password"
											autocomplete="new-password"
											placeholder="<?php esc_attr_e( 'Minimum 8 golden characters', 'dragon-glow' ); ?>"
											required
											data-dg-password-strength />
										<button
											type="button"
											class="dg-register-form__toggle"
											aria-label="<?php esc_attr_e( 'Show password', 'dragon-glow' ); ?>"
											data-dg-toggle-password="#dg-reg-password">
											<span class="material-symbols-outlined">visibility</span>
										</button>
									</div>
									
									<!-- Password strength indicator -->
									<div class="dg-register-form__strength">
										<div class="dg-register-form__strength-bars">
											<div class="dg-register-form__strength-bar" data-strength-bar="1"></div>
											<div class="dg-register-form__strength-bar" data-strength-bar="2"></div>
											<div class="dg-register-form__strength-bar" data-strength-bar="3"></div>
											<div class="dg-register-form__strength-bar" data-strength-bar="4"></div>
										</div>
										<span class="dg-register-form__strength-text" data-strength-text>
											<?php esc_html_e( 'Cipher Strength: Unsealed', 'dragon-glow' ); ?>
										</span>
									</div>
								</div>

								<!-- Primary skin aspiration -->
								<div class="dg-register-form__field">
									<label for="dg-reg-aspiration" class="dg-register-form__label">
										<?php esc_html_e( 'Primary Skin Aspiration', 'dragon-glow' ); ?>
									</label>
									<div class="dg-register-form__input-wrap dg-register-form__input-wrap--select">
										<select
											class="dg-register-form__input dg-register-form__input--select"
											name="skin_aspiration"
											id="dg-reg-aspiration">
											<option value="radiance"><?php esc_html_e( 'Solar Luminous Radiance & Vitality', 'dragon-glow' ); ?></option>
											<option value="cellular"><?php esc_html_e( 'Cellular Age Defying & Longevity', 'dragon-glow' ); ?></option>
											<option value="barrier"><?php esc_html_e( 'Barrier Deep Restoration & Calming', 'dragon-glow' ); ?></option>
											<option value="hydration"><?php esc_html_e( 'Sacred Hydration & Botanical Dew', 'dragon-glow' ); ?></option>
										</select>
										<span class="material-symbols-outlined dg-register-form__icon">expand_more</span>
									</div>
								</div>

								<!-- Checkboxes -->
								<div class="dg-register-form__checkboxes">
									<label class="dg-register-form__checkbox">
										<input type="checkbox" name="newsletter" value="1" checked />
										<span class="dg-register-form__checkbox-text">
											<?php esc_html_e( 'Inscribe me to receive confidential seasonal dispatches, golden hour previews, and private apothecary invitations.', 'dragon-glow' ); ?>
										</span>
									</label>

									<label class="dg-register-form__checkbox">
										<input type="checkbox" name="terms" value="1" required />
										<span class="dg-register-form__checkbox-text">
											<?php
											/* translators: %s: Terms link */
											printf(
												__( 'I accept the <a href="%s" target="_blank" rel="noopener">Sacred Atelier Covenant</a> (Terms of Sanctuary & Privacy Codex).', 'dragon-glow' ),
												esc_url( get_permalink( wc_get_page_id( 'terms' ) ) )
											);
											?>
										</span>
									</label>
								</div>

								<?php do_action( 'woocommerce_register_form' ); ?>

								<input type="hidden" name="woocommerce-register-nonce" value="<?php echo esc_attr( wp_create_nonce( 'woocommerce-register' ) ); ?>" />

								<!-- Primary submission CTA -->
								<button type="submit" name="register" class="dg-register-form__submit">
									<span class="material-symbols-outlined">verified_user</span>
									<span><?php esc_html_e( '✦ Inscribe in Atelier Ledger & Unlock 15% ✦', 'dragon-glow' ); ?></span>
								</button>

								<?php do_action( 'woocommerce_register_form_end' ); ?>

							</form>

							<!-- Alternative accession divider -->
							<div class="dg-register-form__divider">
								<span class="dg-register-form__divider-line"></span>
								<div class="dg-register-form__divider-text">
									<span>&#10022;</span>
									<span><?php esc_html_e( 'Alternative Accession', 'dragon-glow' ); ?></span>
									<span>&#10022;</span>
								</div>
								<span class="dg-register-form__divider-line"></span>
							</div>

							<!-- Social access buttons -->
							<div class="dg-register-form__social">
								<button type="button" class="dg-register-form__social-btn" disabled>
									<svg class="dg-register-form__social-icon" viewBox="0 0 24 24">
										<path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
										<path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
										<path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z" fill="#FBBC05"/>
										<path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z" fill="#EA4335"/>
									</svg>
									<span><?php esc_html_e( 'Google Sanctuary', 'dragon-glow' ); ?></span>
								</button>

								<button type="button" class="dg-register-form__social-btn" disabled>
									<svg class="dg-register-form__social-icon" viewBox="0 0 24 24" fill="currentColor">
										<path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M15.97 6.37c.62-.77 1.04-1.85.92-2.92-.91.04-2 .6-2.65 1.37-.56.65-1.06 1.74-.93 2.8 1.02.08 2.04-.51 2.66-1.25z"/>
									</svg>
									<span><?php esc_html_e( 'Apple Passkey', 'dragon-glow' ); ?></span>
								</button>
							</div>

							<!-- Existing member sign-in prompt -->
							<p class="dg-register-form__signin-prompt">
								<?php esc_html_e( 'Already inscribed in our ledger?', 'dragon-glow' ); ?>
								<a href="<?php echo esc_url( dg_account_endpoint_url( '' ) ); ?>" class="dg-register-form__signin-link">
									<?php esc_html_e( 'Enter the Sanctuary (Sign In)', 'dragon-glow' ); ?>
								</a>
							</p>

						</div>

					</div>

					<!-- Apothecary authentication micro-badge -->
					<div class="dg-account-auth__footnote">
						<span class="dg-account-auth__footnote-item">
							<span class="material-symbols-outlined">eco</span>
							<span><?php esc_html_e( 'Pure Botanical Distillation', 'dragon-glow' ); ?></span>
						</span>
						<span class="dg-account-auth__footnote-dot">&bull;</span>
						<span class="dg-account-auth__footnote-item">
							<span class="material-symbols-outlined">lock</span>
							<span><?php esc_html_e( '256-Bit Encrypted Ledger', 'dragon-glow' ); ?></span>
						</span>
						<span class="dg-account-auth__footnote-dot">&bull;</span>
						<span class="dg-account-auth__footnote-item">
							<span class="material-symbols-outlined">cruelty_free</span>
							<span><?php esc_html_e( 'Certified Cruelty-Free', 'dragon-glow' ); ?></span>
						</span>
					</div>

				</div>
			</div>
		</div>
	</main>

	<!-- Classical heritage footer -->
	<footer class="dg-account-auth__heritage-footer">
		<div class="dg-account-auth__heritage-locations">
			<span><?php esc_html_e( 'Heritage Atelier San Francisco', 'dragon-glow' ); ?></span>
			<span class="dg-account-auth__heritage-dot">&bull;</span>
			<span><?php esc_html_e( 'Fifth Avenue New York', 'dragon-glow' ); ?></span>
			<span class="dg-account-auth__heritage-dot">&bull;</span>
			<span><?php esc_html_e( 'Parisian Archive', 'dragon-glow' ); ?></span>
		</div>
		<div class="dg-account-auth__heritage-copyright">
			<?php
			/* translators: %d: Current year */
			printf(
				esc_html__( '© %d Dragon Glow Cosmetics Inc. All rights reserved.', 'dragon-glow' ),
				(int) gmdate( 'Y' )
			);
			?>
		</div>
	</footer>
	<?php
}
