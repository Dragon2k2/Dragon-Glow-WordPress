<?php
/**
 * Dragon Glow — WooCommerce Analytics: Fallback Real-time Sync
 *
 * When the Action Scheduler queue is stalled (WP-Cron not firing), new
 * orders and refunds never reach Analytics on their own. This fallback
 * collects the record IDs touched during a request and imports them once
 * the response has been sent (shutdown), so the customer never waits on it.
 *
 * It only runs while the queue is detected as stalled
 * (see health-check.php). On a healthy host WooCommerce's own scheduling
 * does the work and this file does nothing. Importing is idempotent, so a
 * record handled twice is harmless.
 *
 * The proper fix for a stalled queue is a real cron call to wp-cron.php
 * (server cron or an external service); this is only a safety net.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Remember an order or refund ID to import at shutdown.
 *
 * @param int $id Order or refund ID.
 * @return void
 */
function dg_analytics_queue_record( $id ): void {
	static $registered = false;

	$id = absint( $id );
	if ( ! $id ) {
		return;
	}

	$GLOBALS['dg_analytics_pending_ids'][ $id ] = $id;

	if ( ! $registered ) {
		$registered = true;
		add_action( 'shutdown', 'dg_analytics_import_pending_records', 20 );
	}
}
add_action( 'woocommerce_refund_created', 'dg_analytics_queue_record' );
add_action( 'woocommerce_order_status_changed', 'dg_analytics_queue_record' );

/**
 * Import the collected records when the queue is stalled.
 *
 * @return void
 */
function dg_analytics_import_pending_records(): void {
	$ids = $GLOBALS['dg_analytics_pending_ids'] ?? array();
	unset( $GLOBALS['dg_analytics_pending_ids'] );

	if ( empty( $ids ) || ! dg_analytics_queue_is_stalled() ) {
		return;
	}

	$scheduler = dg_analytics_get_orders_scheduler_class();
	if ( '' === $scheduler ) {
		return;
	}

	// Let the visitor's browser finish first where the SAPI supports it.
	if ( function_exists( 'fastcgi_finish_request' ) ) {
		fastcgi_finish_request();
	}

	foreach ( $ids as $id ) {
		try {
			$scheduler::import( (int) $id );
		} catch ( \Throwable $e ) {
			dg_analytics_log( 'error', sprintf( 'Fallback import of record #%d failed: %s', (int) $id, $e->getMessage() ) );
		}
	}
}
