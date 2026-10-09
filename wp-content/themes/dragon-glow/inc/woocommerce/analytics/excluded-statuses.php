<?php
/**
 * Dragon Glow — WooCommerce Analytics: Excluded Order Statuses
 *
 * Keeps unpaid "On hold" orders (Direct Bank Transfer / BACS, see
 * bacs-qr.php) out of Analytics revenue. WooCommerce excludes only
 * pending, failed and cancelled by default, so a bank-transfer order that
 * has not been paid yet would otherwise be counted as revenue.
 *
 * Uses the `woocommerce_analytics_excluded_order_statuses` filter, which is
 * applied when reports are queried, so no re-import is needed. The filter
 * exists only in recent WooCommerce releases; on older ones add "On hold"
 * manually in WooCommerce → Settings → Analytics → Excluded statuses.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Add theme-level statuses to the Analytics exclusion list.
 *
 * @param mixed $statuses Statuses already excluded (slugs without "wc-").
 * @return mixed
 */
function dg_analytics_excluded_statuses( $statuses ) {
	if ( ! is_array( $statuses ) ) {
		return $statuses;
	}

	/**
	 * Filter the extra order statuses excluded from Analytics.
	 *
	 * Return an empty array to count "On hold" orders as revenue again.
	 *
	 * @param string[] $extra Status slugs without the "wc-" prefix.
	 */
	$extra = (array) apply_filters( 'dg_analytics_extra_excluded_statuses', array( 'on-hold' ) );

	return array_values( array_unique( array_merge( $statuses, $extra ) ) );
}
add_filter( 'woocommerce_analytics_excluded_order_statuses', 'dg_analytics_excluded_statuses' );
