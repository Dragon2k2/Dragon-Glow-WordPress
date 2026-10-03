<?php
/**
 * Dragon Glow — My Account: Hero, Sidebar & Dashboard Panel
 *
 * Renders the authenticated account shell: brand-hero greeting card,
 * sidebar/dropdown navigation, member tier badge, and the dashboard panel
 * (stats + recent orders + account snapshot) shown at the My Account root.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

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
 * Render the account hero (greeting + member tier).
 *
 * @param WP_User $customer Current customer.
 * @return void
 */
function dg_render_account_hero( WP_User $customer ): void {
	$first_name   = trim( (string) $customer->user_firstname );
	$greet_name   = '' !== $first_name ? $first_name : $customer->display_name;
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
				<a href="<?php echo esc_url( wp_logout_url( dg_account_endpoint_url( '' ) ) ); ?>"
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
 * Master renderer for the authenticated account area.
 *
 * Echoes the full page (hero + sidebar + content). Picks the right panel based
 * on the current endpoint, defaulting to the dashboard when none matches.
 *
 * Routing for the public `register` endpoint and the signed-out auth gate
 * lives here too, since this is the single entry point called by
 * `page-templates/template-wc-account.php`.
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

