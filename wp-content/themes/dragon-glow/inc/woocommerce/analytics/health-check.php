<?php
/**
 * Dragon Glow — WooCommerce Analytics: Queue Health Check
 *
 * Detects a stalled Action Scheduler queue (typically WP-Cron not firing on
 * shared hosting) and reports it in Tools → Site Health, so stale Analytics
 * numbers are noticed by the operator instead of by a customer or accountant.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Count pending Action Scheduler actions that are long overdue.
 *
 * The result is cached briefly so the check stays cheap when called from
 * several places.
 *
 * @return int Number of overdue pending actions (0 when unknown).
 */
function dg_analytics_overdue_action_count(): int {
	if ( ! function_exists( 'as_get_scheduled_actions' ) ) {
		return 0;
	}

	$cached = get_transient( 'dg_analytics_overdue_count' );
	if ( false !== $cached ) {
		return (int) $cached;
	}

	/**
	 * Filter how many seconds a pending action may be overdue before the
	 * queue is considered stalled.
	 *
	 * @param int $seconds Default one hour.
	 */
	$threshold = (int) apply_filters( 'dg_analytics_stalled_threshold', HOUR_IN_SECONDS );

	$ids = as_get_scheduled_actions(
		array(
			'status'        => ActionScheduler_Store::STATUS_PENDING,
			'date'          => gmdate( 'U' ) - $threshold,
			'date_compare'  => '<=',
			'per_page'      => 100,
		),
		'ids'
	);

	$count = is_array( $ids ) ? count( $ids ) : 0;
	set_transient( 'dg_analytics_overdue_count', $count, 5 * MINUTE_IN_SECONDS );

	return $count;
}

/**
 * Whether the background queue looks stalled.
 *
 * @return bool
 */
function dg_analytics_queue_is_stalled(): bool {
	return dg_analytics_overdue_action_count() > 0;
}

/**
 * Register the Site Health test.
 *
 * @param array $tests Existing tests.
 * @return array
 */
function dg_analytics_register_health_test( array $tests ): array {
	if ( ! function_exists( 'WC' ) ) {
		return $tests;
	}

	$tests['direct']['dg_analytics_queue'] = array(
		'label' => __( 'Background jobs for WooCommerce Analytics', 'dragon-glow' ),
		'test'  => 'dg_analytics_health_test',
	);

	return $tests;
}
add_filter( 'site_status_tests', 'dg_analytics_register_health_test' );

/**
 * Site Health test callback.
 *
 * @return array
 */
function dg_analytics_health_test(): array {
	$result = array(
		'label'       => __( 'Background jobs are running on time', 'dragon-glow' ),
		'status'      => 'good',
		'badge'       => array(
			'label' => __( 'Performance', 'dragon-glow' ),
			'color' => 'blue',
		),
		'description' => '<p>' . esc_html__( 'WooCommerce Analytics (revenue, refunds, customers) is being updated normally.', 'dragon-glow' ) . '</p>',
		'actions'     => '',
		'test'        => 'dg_analytics_queue',
	);

	$overdue = dg_analytics_overdue_action_count();
	if ( 0 === $overdue ) {
		return $result;
	}

	$result['status']      = 'recommended';
	$result['label']       = __( 'Background jobs are overdue', 'dragon-glow' );
	$result['description'] = '<p>' . esc_html(
		sprintf(
			/* translators: %d: number of overdue background jobs. */
			_n(
				'%d scheduled job is more than an hour overdue. Analytics revenue and refunds may be out of date. WP-Cron is probably not firing on this host.',
				'%d scheduled jobs are more than an hour overdue. Analytics revenue and refunds may be out of date. WP-Cron is probably not firing on this host.',
				$overdue,
				'dragon-glow'
			),
			$overdue
		)
	) . '</p>';
	$result['actions']     = sprintf(
		'<p><a href="%1$s">%2$s</a></p>',
		esc_url( admin_url( 'admin.php?page=wc-status&tab=tools' ) ),
		esc_html__( 'Run "Sync orders to Analytics" in WooCommerce → Status → Tools', 'dragon-glow' )
	);

	return $result;
}
