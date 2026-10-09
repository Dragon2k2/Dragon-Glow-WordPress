<?php
/**
 * Dragon Glow — WooCommerce Analytics: Shared Helpers
 *
 * Resolves WooCommerce's own Analytics importer and provides a logger.
 * The theme never writes to the Analytics lookup tables directly; every
 * sync goes through the importer so WooCommerce keeps full ownership of
 * the data model.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Resolve the WooCommerce Analytics orders scheduler class.
 *
 * The class moved namespaces between WooCommerce releases, so both
 * locations are checked.
 *
 * @return string Fully-qualified class name, or empty string if unavailable.
 */
function dg_analytics_get_orders_scheduler_class(): string {
	$candidates = array(
		'\Automattic\WooCommerce\Internal\Admin\Schedulers\OrdersScheduler',
		'\Automattic\WooCommerce\Admin\Schedulers\OrdersScheduler',
	);

	foreach ( $candidates as $class ) {
		if ( class_exists( $class ) && method_exists( $class, 'import' ) ) {
			return $class;
		}
	}

	return '';
}

/**
 * Write a line to the WooCommerce log (WooCommerce → Status → Logs).
 *
 * @param string $level   PSR-3 level, e.g. 'error' or 'info'.
 * @param string $message Log message.
 * @return void
 */
function dg_analytics_log( string $level, string $message ): void {
	if ( ! function_exists( 'wc_get_logger' ) ) {
		return;
	}

	wc_get_logger()->log( $level, $message, array( 'source' => 'dg-analytics' ) );
}
