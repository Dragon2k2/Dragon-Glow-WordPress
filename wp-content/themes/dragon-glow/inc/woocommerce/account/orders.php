<?php
/**
 * Dragon Glow — My Account: Orders Panel
 *
 * Renders the `/my-account/orders/` endpoint panel. Queries customer orders
 * and passes them to WC's `myaccount/orders.php` template. When called via
 * AJAX, we must explicitly query orders because WC's internal query context
 * is lost.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Render the Orders list panel (the /my-account/orders endpoint).
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
