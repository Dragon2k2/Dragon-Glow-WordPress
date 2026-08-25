<?php
/**
 * Dragon Glow — WooCommerce: My Account
 *
 * Custom My Account page renderer. Replaces the default WooCommerce
 * `my-account.php` / `dashboard.php` rendering with a Luminous Ethereal
 * layout: brand-hero greeting card + vertical sidebar nav (Dashboard, Orders,
 * Addresses, Wishlist, Account details, Sign out) on desktop / dropdown on
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
		array(
			'endpoint' => 'dg-wishlist',
			'label'    => __( 'Wishlist', 'dragon-glow' ),
			'icon'     => 'favorite_border',
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

	// Theme wishlist panel (not a WC core endpoint).
	if ( 'wishlist' === $candidate || 'dg-wishlist' === $candidate ) {
		return 'dg-wishlist';
	}

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
						<?php elseif ( 'dg-wishlist' === $item['endpoint'] ) : ?>
							<span class="dg-account-nav__count"><?php echo esc_html( (string) dg_get_wishlist_count() ); ?></span>
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
 * Render dashboard content (stats + recent orders + wishlist preview + CTA).
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
	$wishlist    = dg_get_wishlist();
	$wish_count  = count( $wishlist );
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

			<a href="<?php echo esc_url( dg_account_endpoint_url( 'dg-wishlist' ) ); ?>" class="dg-account-stat" data-sr>
				<div class="dg-account-stat__icon" data-tone="rose">
					<span class="material-symbols-outlined">favorite</span>
				</div>
				<div class="dg-account-stat__body">
					<p class="dg-account-stat__value dg-count-to" data-count-to="<?php echo esc_attr( (string) $wish_count ); ?>">0</p>
					<p class="dg-account-stat__label"><?php esc_html_e( 'Wishlist items', 'dragon-glow' ); ?></p>
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

		<!-- Wishlist preview -->
		<?php if ( ! empty( $wishlist ) ) : ?>
			<section class="dg-account-panel" data-sr>
				<header class="dg-account-panel__header">
					<h2 class="dg-account-panel__title"><?php esc_html_e( 'From your wishlist', 'dragon-glow' ); ?></h2>
					<a href="<?php echo esc_url( dg_account_endpoint_url( 'dg-wishlist' ) ); ?>" class="dg-account-panel__link">
						<?php esc_html_e( 'View wishlist', 'dragon-glow' ); ?>
						<span class="material-symbols-outlined" aria-hidden="true">arrow_forward</span>
					</a>
				</header>

				<ul class="dg-account-wishlist" role="list">
					<?php
					$wish_preview = array_slice( $wishlist, 0, 4 );
					foreach ( $wish_preview as $product_id ) :
						$product = wc_get_product( (int) $product_id );
						if ( ! $product ) {
							continue;
						}
						?>
						<li class="dg-account-wishlist__item">
							<a href="<?php echo esc_url( get_permalink( (int) $product_id ) ); ?>" class="dg-account-wishlist__link">
								<div class="dg-account-wishlist__thumb">
									<?php
									if ( has_post_thumbnail( (int) $product_id ) ) {
										echo get_the_post_thumbnail( (int) $product_id, 'medium', array( 'class' => 'dg-account-wishlist__img', 'loading' => 'lazy' ) );
									} else {
										echo '<span class="material-symbols-outlined dg-account-wishlist__placeholder">spa</span>';
									}
									?>
								</div>
								<div class="dg-account-wishlist__body">
									<p class="dg-account-wishlist__name"><?php echo esc_html( $product->get_name() ); ?></p>
									<p class="dg-account-wishlist__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></p>
								</div>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
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
				<a href="<?php echo esc_url( add_query_arg( 'address', 'billing', dg_account_endpoint_url( 'edit-address' ) ) ); ?>"
				   class="dg-account-address__action">
					<span class="material-symbols-outlined" aria-hidden="true">swap_horiz</span>
					<?php esc_html_e( 'Use billing instead', 'dragon-glow' ); ?>
				</a>
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
 * Wraps WC's `myaccount/form-edit-address.php` in our panel shell so the WC
 * save action + `woocommerce_edit_address` hook keep working (we do not
 * reimplement the form — WC handles validation, country select, state field
 * refresh, etc.). The shell just gives it a clear header, breadcrumb back
 * link, and Save/Cancel actions that match the rest of the account area.
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
			// WC handles the save action, country/state selects, validation. We
			// just provide the panel chrome around it.
			wc_get_template( 'myaccount/form-edit-address.php', array( 'type' => $type ) );
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
 * Wishlist panel — visual shell that shows the user's saved products.
 *
 * Renders the same product grid layout used on the dashboard preview so the
 * counts in the sidebar nav (`.dg-account-nav__count`) always match the
 * rendered content. Intentionally does NOT echo `post_content` from the
 * wishlist Page — that previous behaviour showed admin-edited text instead
 * of the user's actual wishlist and produced mismatched counts.
 *
 * @return void
 */
function dg_render_account_wishlist_panel(): void {
	$wishlist = dg_get_wishlist();
	?>
	<section class="dg-account-panel" data-sr>
		<header class="dg-account-panel__header">
			<h2 class="dg-account-panel__title"><?php esc_html_e( 'Wishlist', 'dragon-glow' ); ?></h2>
			<a href="<?php echo esc_url( dg_account_endpoint_url( '' ) ); ?>" class="dg-account-panel__link">
				<span class="material-symbols-outlined" aria-hidden="true">arrow_back</span>
				<?php esc_html_e( 'Back to dashboard', 'dragon-glow' ); ?>
			</a>
		</header>

		<?php if ( empty( $wishlist ) ) : ?>
			<div class="dg-account-empty">
				<span class="material-symbols-outlined dg-account-empty__icon">favorite_border</span>
				<p class="dg-account-empty__title"><?php esc_html_e( 'Your wishlist is empty', 'dragon-glow' ); ?></p>
				<p class="dg-account-empty__text">
					<?php esc_html_e( 'Save your favourite products here so they\'re easy to find next time.', 'dragon-glow' ); ?>
				</p>
				<a href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop' ) ); ?>"
				   class="dg-btn dg-btn--primary">
					<?php esc_html_e( 'Explore products', 'dragon-glow' ); ?>
				</a>
			</div>
		<?php else : ?>
			<ul class="dg-account-wishlist" role="list">
				<?php foreach ( array_slice( $wishlist, 0, 8 ) as $product_id ) :
					$product = wc_get_product( (int) $product_id );
					if ( ! $product ) {
						continue;
					}
					?>
					<li class="dg-account-wishlist__item">
						<a href="<?php echo esc_url( get_permalink( (int) $product_id ) ); ?>" class="dg-account-wishlist__link">
							<div class="dg-account-wishlist__thumb">
								<?php
								if ( has_post_thumbnail( (int) $product_id ) ) {
									echo get_the_post_thumbnail( (int) $product_id, 'medium', array( 'class' => 'dg-account-wishlist__img', 'loading' => 'lazy' ) );
								} else {
									echo '<span class="material-symbols-outlined dg-account-wishlist__placeholder">spa</span>';
								}
								?>
							</div>
							<div class="dg-account-wishlist__body">
								<p class="dg-account-wishlist__name"><?php echo esc_html( $product->get_name() ); ?></p>
								<p class="dg-account-wishlist__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></p>
							</div>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
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
			<?php esc_html_e( 'You can keep your wishlist and saved addresses by staying signed in.', 'dragon-glow' ); ?>
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

	// Auth gate.
	if ( ! is_user_logged_in() ) {
		dg_render_account_signed_out();
		return;
	}

	$customer           = wp_get_current_user();
	$current_endpoint   = dg_current_account_endpoint();
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
						case 'dg-wishlist':
							dg_render_account_wishlist_panel();
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
	</main>
	<?php
}

/**
 * Layout for signed-out users.
 *
 * Renders a hand-built sign-in form + an inline register form (matches
 * the Luminous Ethereal design + matches the form fields that WC expects).
 * The form fields, names, nonces and submission target are all WC
 * defaults so the existing `woocommerce_login_form_*` /
 * `woocommerce_register_form_*` actions continue to work.
 *
 * @return void
 */
function dg_render_account_signed_out(): void {
	$register_enabled = ( 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) );
	$lost_pwd_url     = (string) wp_lostpassword_url();
	?>
	<main class="dg-account dg-account--signed-out" id="main-content">
		<div class="dg-account__wrap">

			<header class="dg-account-auth" data-sr>
				<p class="dg-account-auth__eyebrow"><?php esc_html_e( 'Luminous rituals', 'dragon-glow' ); ?></p>
				<h1 class="dg-account-auth__title">
					<?php esc_html_e( 'Welcome back', 'dragon-glow' ); ?>
				</h1>
				<p class="dg-account-auth__sub">
					<?php esc_html_e( 'Sign in to access your orders, wishlist, and saved addresses.', 'dragon-glow' ); ?>
				</p>
			</header>

			<div class="dg-account-auth__grid">

				<section class="dg-account-auth__panel" data-sr>
					<h2 class="dg-account-auth__heading"><?php esc_html_e( 'Sign in', 'dragon-glow' ); ?></h2>

					<?php wc_print_notices(); ?>

					<form method="post" class="dg-account-form" action="<?php echo esc_url( dg_account_endpoint_url( '' ) ); ?>" novalidate>

						<?php do_action( 'woocommerce_login_form_start' ); ?>

						<div class="dg-account-field">
							<label for="dg-login-username"><?php esc_html_e( 'Email or username', 'dragon-glow' ); ?></label>
							<input
								type="text"
								class="dg-account-field__input"
								name="username"
								id="dg-login-username"
								autocomplete="username"
								placeholder="<?php esc_attr_e( 'you@example.com', 'dragon-glow' ); ?>"
								value="<?php echo isset( $_POST['username'] ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
									?>" />
						</div>

						<div class="dg-account-field">
							<label for="dg-login-password"><?php esc_html_e( 'Password', 'dragon-glow' ); ?></label>
							<div class="dg-account-field__wrap">
								<input
									type="password"
									class="dg-account-field__input"
									name="password"
									id="dg-login-password"
									autocomplete="current-password"
									placeholder="<?php esc_attr_e( 'Your password', 'dragon-glow' ); ?>" />
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
								<span><?php esc_html_e( 'Remember me', 'dragon-glow' ); ?></span>
							</label>
							<a href="<?php echo esc_url( $lost_pwd_url ); ?>" class="dg-account-form__link">
								<?php esc_html_e( 'Forgot password?', 'dragon-glow' ); ?>
							</a>
						</div>

						<?php do_action( 'woocommerce_login_form' ); ?>

						<input type="hidden" name="woocommerce-login-nonce" value="<?php echo esc_attr( wp_create_nonce( 'woocommerce-login' ) ); ?>" />

						<button type="submit" name="login" value="<?php esc_attr_e( 'Sign in', 'dragon-glow' ); ?>" class="dg-btn dg-btn--primary dg-btn--full">
							<?php esc_html_e( 'Sign in', 'dragon-glow' ); ?>
						</button>

						<?php do_action( 'woocommerce_login_form_end' ); ?>
					</form>
				</section>

				<?php if ( $register_enabled ) : ?>
					<section class="dg-account-auth__panel" id="dg-account-register" data-sr>
						<h2 class="dg-account-auth__heading"><?php esc_html_e( 'Create an account', 'dragon-glow' ); ?></h2>
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
								<label for="dg-reg-email"><?php esc_html_e( 'Email address', 'dragon-glow' ); ?></label>
								<input
									type="email"
									class="dg-account-field__input"
									name="email"
									id="dg-reg-email"
									autocomplete="email"
									placeholder="<?php esc_attr_e( 'you@example.com', 'dragon-glow' ); ?>"
									value="<?php echo isset( $_POST['email'] ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
										?>" />
							</div>

							<div class="dg-account-field">
								<label for="dg-reg-password"><?php esc_html_e( 'Password', 'dragon-glow' ); ?></label>
								<div class="dg-account-field__wrap">
									<input
										type="password"
										class="dg-account-field__input"
										name="password"
										id="dg-reg-password"
										autocomplete="new-password"
										placeholder="<?php esc_attr_e( 'Create a strong password', 'dragon-glow' ); ?>" />
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

							<button type="submit" name="register" value="<?php esc_attr_e( 'Create account', 'dragon-glow' ); ?>" class="dg-btn dg-btn--primary dg-btn--full">
								<?php esc_html_e( 'Create account', 'dragon-glow' ); ?>
							</button>

							<?php do_action( 'woocommerce_register_form_end' ); ?>
						</form>
					</section>
				<?php endif; ?>

			</div>

		</div>
	</main>
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
