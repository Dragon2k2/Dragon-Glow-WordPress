<?php
/**
 * Orders
 *
 * Shows orders on the account page.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/orders.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.2.0
 *
 * Dragon Glow override: Wrap order status in semantic badge HTML for glassmorphism design.
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_account_orders', $has_orders ); ?>

<?php if ( $has_orders ) : ?>

	<table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">
		<thead>
			<tr>
				<?php foreach ( wc_get_account_orders_columns() as $column_id => $column_name ) : ?>
					<th scope="col" class="woocommerce-orders-table__header woocommerce-orders-table__header-<?php echo esc_attr( $column_id ); ?>"><span class="nobr"><?php echo esc_html( $column_name ); ?></span></th>
				<?php endforeach; ?>
			</tr>
		</thead>

		<tbody>
			<?php
			foreach ( $customer_orders->orders as $customer_order ) {
				$order      = wc_get_order( $customer_order );
				$item_count = $order->get_item_count() - $order->get_item_count_refunded();
				?>
				<tr class="woocommerce-orders-table__row woocommerce-orders-table__row--status-<?php echo esc_attr( $order->get_status() ); ?> order">
					<?php foreach ( wc_get_account_orders_columns() as $column_id => $column_name ) : ?>
						<?php if ( 'order-number' === $column_id ) : ?>
							<th class="woocommerce-orders-table__cell woocommerce-orders-table__cell-<?php echo esc_attr( $column_id ); ?>" data-title="<?php echo esc_attr( $column_name ); ?>" scope="row">
								<?php
								/**
								 * Fires before the order number in the orders table.
								 *
								 * @since 8.8.0
								 * @param WC_Order $order The current order object.
								 */
								do_action( 'woocommerce_account_orders_column_order_number_before', $order );
								?>
								<a href="<?php echo esc_url( $order->get_view_order_url() ); ?>" aria-label="<?php printf( esc_attr__( 'View order number %s', 'woocommerce' ), esc_attr( $order->get_order_number() ) ); ?>">
									<?php echo esc_html( _x( '#', 'hash before order number', 'woocommerce' ) . $order->get_order_number() ); ?>
								</a>

								<?php
								/**
								 * Fires after the order number in the orders table.
								 *
								 * @since 8.8.0
								 * @param WC_Order $order The current order object.
								 */
								do_action( 'woocommerce_account_orders_column_order_number_after', $order );
								?>
							</th>
						<?php elseif ( 'order-date' === $column_id ) : ?>
							<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-<?php echo esc_attr( $column_id ); ?>" data-title="<?php echo esc_attr( $column_name ); ?>">
								<?php
								/**
								 * Fires before the order date in the orders table.
								 *
								 * @since 8.8.0
								 * @param WC_Order $order The current order object.
								 */
								do_action( 'woocommerce_account_orders_column_order_date_before', $order );
								?>
								<time datetime="<?php echo esc_attr( $order->get_date_created()->date( 'c' ) ); ?>"><?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?></time>

								<?php
								/**
								 * Fires after the order date in the orders table.
								 *
								 * @since 8.8.0
								 * @param WC_Order $order The current order object.
								 */
								do_action( 'woocommerce_account_orders_column_order_date_after', $order );
								?>
							</td>
						<?php elseif ( 'order-status' === $column_id ) : ?>
							<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-<?php echo esc_attr( $column_id ); ?>" data-title="<?php echo esc_attr( $column_name ); ?>">
								<?php
								/**
								 * Fires before the order status in the orders table.
								 *
								 * @since 8.8.0
								 * @param WC_Order $order The current order object.
								 */
								do_action( 'woocommerce_account_orders_column_order_status_before', $order );

								// Dragon Glow: Wrap status in semantic badge HTML
								$status_slug = $order->get_status();
								$status_name = wc_get_order_status_name( $status_slug );
								?>
								<span class="order-status status-<?php echo esc_attr( $status_slug ); ?>">
									<?php echo esc_html( $status_name ); ?>
								</span>
								<?php

								/**
								 * Fires after the order status in the orders table.
								 *
								 * @since 8.8.0
								 * @param WC_Order $order The current order object.
								 */
								do_action( 'woocommerce_account_orders_column_order_status_after', $order );
								?>
							</td>
						<?php elseif ( 'order-total' === $column_id ) : ?>
							<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-<?php echo esc_attr( $column_id ); ?>" data-title="<?php echo esc_attr( $column_name ); ?>">
								<?php
								/**
								 * Fires before the order total in the orders table.
								 *
								 * @since 8.8.0
								 * @param WC_Order $order The current order object.
								 */
								do_action( 'woocommerce_account_orders_column_order_total_before', $order );
								/* translators: 1: formatted order total 2: total order items */
								echo wp_kses_post( sprintf( _n( '%1$s for %2$s item', '%1$s for %2$s items', $item_count, 'woocommerce' ), $order->get_formatted_order_total(), $item_count ) );
								/**
								 * Fires after the order total in the orders table.
								 *
								 * @since 8.8.0
								 * @param WC_Order $order The current order object.
								 */
								do_action( 'woocommerce_account_orders_column_order_total_after', $order );
								?>
							</td>
						<?php elseif ( 'order-actions' === $column_id ) : ?>
							<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-<?php echo esc_attr( $column_id ); ?>" data-title="<?php echo esc_attr( $column_name ); ?>">
								<?php
								/**
								 * Fires before the order actions in the orders table.
								 *
								 * @since 8.8.0
								 * @param WC_Order $order The current order object.
								 */
								do_action( 'woocommerce_account_orders_column_order_actions_before', $order );

								$actions = wc_get_account_orders_actions( $order );

								if ( ! empty( $actions ) ) {
									foreach ( $actions as $key => $action ) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
										echo '<a href="' . esc_url( $action['url'] ) . '" class="woocommerce-button button ' . sanitize_html_class( $key ) . '" aria-label="' . esc_attr( $action['name'] ) . '">' . esc_html( $action['name'] ) . '</a>';
									}
								}

								/**
								 * Fires after the order actions in the orders table.
								 *
								 * @since 8.8.0
								 * @param WC_Order $order The current order object.
								 */
								do_action( 'woocommerce_account_orders_column_order_actions_after', $order );
								?>
							</td>
						<?php else : ?>
							<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-<?php echo esc_attr( $column_id ); ?>" data-title="<?php echo esc_attr( $column_name ); ?>">
								<?php
									/**
									 * Fires within a custom column in the orders table.
									 *
									 * The dynamic portion of the hook name, `$column_id`, refers to the column ID.
									 *
									 * @since 8.8.0
									 * @param WC_Order $order The current order object.
									 */
									do_action( 'woocommerce_my_account_my_orders_column_' . $column_id, $order );
								?>
							</td>
						<?php endif; ?>
					<?php endforeach; ?>
				</tr>
				<?php
			}
			?>
		</tbody>
	</table>

	<?php do_action( 'woocommerce_before_account_orders_pagination' ); ?>

	<?php if ( 1 < $customer_orders->max_num_pages ) : ?>
		<div class="woocommerce-pagination woocommerce-Pagination">
			<?php
			// Previous button
			if ( 1 !== $current_page ) :
				$prev_url = 1 === ( $current_page - 1 )
					? dg_account_endpoint_url( 'orders' )
					: add_query_arg( 'paged', $current_page - 1, dg_account_endpoint_url( 'orders' ) );
				?>
				<a class="woocommerce-button woocommerce-button--previous woocommerce-Button woocommerce-Button--previous button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" href="<?php echo esc_url( $prev_url ); ?>"><?php esc_html_e( 'Previous', 'woocommerce' ); ?></a>
				<?php
			endif;

			// Page numbers
			echo '<span class="woocommerce-pagination__numbers">';
			for ( $i = 1; $i <= intval( $customer_orders->max_num_pages ); $i++ ) :
				if ( $i === $current_page ) :
					echo '<span class="page-numbers current" aria-current="page">' . esc_html( $i ) . '</span>';
				else :
					$page_url = 1 === $i
						? dg_account_endpoint_url( 'orders' )
						: add_query_arg( 'paged', $i, dg_account_endpoint_url( 'orders' ) );
					echo '<a class="page-numbers" href="' . esc_url( $page_url ) . '">' . esc_html( $i ) . '</a>';
				endif;
			endfor;
			echo '</span>';

			// Next button
			if ( intval( $customer_orders->max_num_pages ) !== $current_page ) :
				$next_url = add_query_arg( 'paged', $current_page + 1, dg_account_endpoint_url( 'orders' ) );
				?>
				<a class="woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" href="<?php echo esc_url( $next_url ); ?>"><?php esc_html_e( 'Next', 'woocommerce' ); ?></a>
				<?php
			endif;
			?>
		</div>
	<?php endif; ?>

<?php else : ?>

	<?php wc_print_notice( esc_html__( 'No order has been made yet.', 'woocommerce' ) . ' <a class="woocommerce-Button button' . esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ) . '" href="' . esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ) . '">' . esc_html__( 'Browse products', 'woocommerce' ) . '</a>', 'notice' ); // phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment ?>

<?php endif; ?>

<?php do_action( 'woocommerce_after_account_orders', $has_orders ); ?>
