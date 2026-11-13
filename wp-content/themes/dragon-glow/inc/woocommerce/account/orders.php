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
 * Build the set of page numbers that stay visible in the pagination row.
 *
 * WooCommerce core's `paginate_links()` hides the middle of a long page list
 * behind an ellipsis. We need the same information, but for a different
 * reason: the Orders panel renders one link per page inside a single row, so
 * a customer with a long order history would overflow the column and wrap
 * the row into ragged lines (the Previous button on line 1, the Next button
 * on the last line).
 *
 * Rather than hiding pages in the markup (which would change the output for
 * every viewport), the template renders every page and tags the ones outside
 * each window. The stylesheet hides the tagged pages and reveals the
 * ellipsis markers in their place.
 *
 * Window shape (the classic "1 … 5 6 7 … 12" pattern):
 *   - Page 1 and the last page are always visible.
 *   - The current page and its `$radius` neighbours are always visible.
 *   - Near the first or the last page the window extends inwards instead, so
 *     the customer always has a run of pages to move through.
 *
 * @param int $current Current page number (1-indexed).
 * @param int $total   Total number of pages (1-indexed).
 * @param int $radius  How many neighbours on each side of the current page
 *                     stay visible. 1 → "1 … 5 6 7 … 12"; 2 gives the wider
 *                     "1 … 4 5 6 7 8 … 12" window used on desktop.
 * @return int[] Sorted list of page numbers that stay visible.
 */
function dg_account_orders_page_window( int $current, int $total, int $radius = 1 ): array {
	$total   = max( 1, $total );
	$current = min( max( 1, $current ), $total );
	$radius  = max( 0, $radius );

	// Short lists fit entirely — nothing to collapse.
	if ( $total <= 5 ) {
		return range( 1, $total );
	}

	$pages = array();

	if ( $current <= $radius + 1 ) {
		// Near the start: list the opening pages, then jump to the last one.
		// Mirrors WooCommerce core's `paginate_links()`: the run ends at
		// current + mid_size.
		$run_end = min( $total, $current + $radius );

		for ( $i = 1; $i <= $run_end; $i++ ) {
			$pages[] = $i;
		}

		$pages[] = $total;
	} elseif ( $current >= $total - $radius ) {
		// Near the end: jump to the last pages.
		$pages[] = 1;

		$run_start = max( 2, $current - $radius );

		for ( $i = $run_start; $i <= $total; $i++ ) {
			$pages[] = $i;
		}
	} else {
		// In the middle: first page, the current run, last page.
		$pages[] = 1;

		for ( $i = $current - $radius; $i <= $current + $radius; $i++ ) {
			$pages[] = $i;
		}

		$pages[] = $total;
	}

	$pages = array_values( array_unique( $pages ) );
	sort( $pages );

	return $pages;
}

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
