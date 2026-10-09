<?php
/**
 * Dragon Glow — WooCommerce Analytics: Manual Sync Tool
 *
 * Adds a "Sync orders to Analytics" tool under WooCommerce → Status → Tools.
 *
 * Why: Analytics reads from lookup tables (wc_order_stats, ...) that are
 * normally filled by Action Scheduler jobs. On hosts where WP-Cron is
 * unreliable (e.g. free shared hosting) those jobs stay pending for hours and
 * Analytics → Revenue shows $0.00 even though orders exist. This tool imports
 * orders and refunds synchronously, bypassing the queue.
 *
 * Behaviour:
 *   - Works in batches inside a time budget, so a shared host's
 *     `max_execution_time` never kills the run half-way. Progress is stored in
 *     an option; click "Sync now" again to continue until it reports done.
 *   - A failing record is logged and skipped; it never aborts the run.
 *   - Only WooCommerce's own importer is called; the theme never writes to the
 *     lookup tables. WooCommerce limits tools to `manage_woocommerce`.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'DG_ANALYTICS_SYNC_CURSOR' ) ) {
	define( 'DG_ANALYTICS_SYNC_CURSOR', 'dg_analytics_sync_cursor' );
}

/**
 * Register the sync tool in WooCommerce → Status → Tools.
 *
 * @param array $tools Existing debug tools.
 * @return array
 */
function dg_analytics_register_sync_tool( array $tools ): array {
	$tools['dg_sync_orders_to_analytics'] = array(
		'name'     => __( 'Sync orders to Analytics', 'dragon-glow' ),
		'button'   => __( 'Sync now', 'dragon-glow' ),
		'desc'     => __( 'Imports orders and refunds into WooCommerce Analytics immediately, without waiting for background jobs. Use this when Analytics → Revenue is empty or out of date. Large stores may need several clicks; the result message tells you when it is finished. Safe to run more than once.', 'dragon-glow' ),
		'callback' => 'dg_analytics_sync_orders_tool',
	);

	return $tools;
}
add_filter( 'woocommerce_debug_tools', 'dg_analytics_register_sync_tool' );

/**
 * Seconds this request may spend importing before it pauses.
 *
 * Capped at half of PHP's max_execution_time when one is set.
 *
 * @return int
 */
function dg_analytics_sync_time_budget(): int {
	$budget = 20;
	$limit  = (int) ini_get( 'max_execution_time' );

	if ( $limit > 0 ) {
		$budget = min( $budget, max( 5, (int) floor( $limit / 2 ) ) );
	}

	return max( 5, (int) apply_filters( 'dg_analytics_sync_time_budget', $budget ) );
}

/**
 * Import orders, then refunds, into the Analytics lookup tables.
 *
 * @return string|bool Result message, or false when the importer is missing.
 */
function dg_analytics_sync_orders_tool() {
	$scheduler = dg_analytics_get_orders_scheduler_class();

	if ( '' === $scheduler ) {
		return false;
	}

	// Refunds are separate records; Analytics subtracts them from revenue
	// only once they are imported, so both types are synced.
	$types     = array( 'shop_order', 'shop_order_refund' );
	$page_size = max( 1, (int) apply_filters( 'dg_analytics_sync_page_size', 50 ) );
	$budget    = dg_analytics_sync_time_budget();
	$started   = microtime( true );
	$cursor    = (array) get_option( DG_ANALYTICS_SYNC_CURSOR, array() );
	$type_idx  = min( (int) ( $cursor['type'] ?? 0 ), count( $types ) );
	$page      = max( 1, (int) ( $cursor['page'] ?? 1 ) );
	$imported  = 0;
	$failed    = 0;

	for ( ; $type_idx < count( $types ); ++$type_idx, $page = 1 ) {
		do {
			if ( ( microtime( true ) - $started ) >= $budget ) {
				update_option(
					DG_ANALYTICS_SYNC_CURSOR,
					array(
						'type' => $type_idx,
						'page' => $page,
					),
					false
				);

				return sprintf(
					/* translators: 1: records imported in this run, 2: records that failed. */
					__( 'Paused after %1$d records (%2$d failed). More remain: click "Sync now" again to continue.', 'dragon-glow' ),
					$imported,
					$failed
				);
			}

			$args = array(
				'limit'    => $page_size,
				'paged'    => $page,
				'type'     => $types[ $type_idx ],
				'orderby'  => 'ID',
				'order'    => 'ASC',
				'return'   => 'ids',
				'paginate' => false,
			);

			// Refund records have no order status of their own.
			if ( 'shop_order' === $types[ $type_idx ] ) {
				$args['status'] = array_keys( wc_get_order_statuses() );
			}

			$ids = wc_get_orders( $args );

			foreach ( $ids as $id ) {
				try {
					$scheduler::import( (int) $id );
					++$imported;
				} catch ( \Throwable $e ) {
					++$failed;
					dg_analytics_log( 'error', sprintf( 'Import of record #%d failed: %s', (int) $id, $e->getMessage() ) );
				}
			}

			++$page;
		} while ( count( $ids ) === $page_size );
	}

	delete_option( DG_ANALYTICS_SYNC_CURSOR );
	dg_analytics_log( 'info', sprintf( 'Manual sync finished: %d imported, %d failed.', $imported, $failed ) );

	$message = sprintf(
		/* translators: %d: number of records imported. */
		_n( 'Done. %d record synced to Analytics.', 'Done. %d records synced to Analytics.', $imported, 'dragon-glow' ),
		$imported
	);

	if ( $failed > 0 ) {
		$message .= ' ' . sprintf(
			/* translators: %d: number of records that failed. */
			_n( '%d record failed; see WooCommerce → Status → Logs (dg-analytics).', '%d records failed; see WooCommerce → Status → Logs (dg-analytics).', $failed, 'dragon-glow' ),
			$failed
		);
	}

	return $message;
}
