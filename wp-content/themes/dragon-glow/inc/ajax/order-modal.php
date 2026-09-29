<?php
/**
 * Dragon Glow — AJAX: Order Modal
 *
 * AJAX handler for fetching order details to display in modal.
 * Returns structured data for Split Timeline Modal layout.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * AJAX handler: Get order details for modal display.
 *
 * Expects POST:
 *  - order_id (int): WooCommerce order ID.
 *  - nonce (string): Security nonce.
 *
 * Returns JSON:
 *  - order_number (string)
 *  - date (string)
 *  - status (string)
 *  - timeline (array): Order status history.
 *  - items (array): Product items with name, quantity, price.
 *  - shipping (array): Shipping address details.
 *  - payment (array): Payment summary with subtotal, shipping, tax, total, method.
 *
 * @return void
 */
function dg_ajax_get_order_details(): void {
	// Verify nonce.
	$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
	if ( ! $nonce || ! wp_verify_nonce( $nonce, 'dg_order_modal' ) ) {
		wp_send_json_error( array( 'message' => __( 'Security check failed.', 'dragon-glow' ) ) );
		return;
	}

	// Verify user is logged in.
	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => __( 'You must be logged in to view orders.', 'dragon-glow' ) ) );
		return;
	}

	// Get order ID.
	$order_id = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;
	if ( $order_id <= 0 ) {
		wp_send_json_error( array( 'message' => __( 'Invalid order ID.', 'dragon-glow' ) ) );
		return;
	}

	// Check if WooCommerce is active.
	if ( ! function_exists( 'wc_get_order' ) ) {
		wp_send_json_error( array( 'message' => __( 'WooCommerce is not active.', 'dragon-glow' ) ) );
		return;
	}

	// Get order.
	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		wp_send_json_error( array( 'message' => __( 'Order not found.', 'dragon-glow' ) ) );
		return;
	}

	// Verify order belongs to current user.
	$current_user_id = get_current_user_id();
	$order_user_id   = $order->get_user_id();
	if ( (int) $order_user_id !== $current_user_id ) {
		wp_send_json_error( array( 'message' => __( 'You do not have permission to view this order.', 'dragon-glow' ) ) );
		return;
	}

	// Build response data.
	$data = array(
		'order_number' => $order->get_order_number(),
		'date'         => wc_format_datetime( $order->get_date_created(), get_option( 'date_format' ) ),
		'status'       => $order->get_status(),
		'status_label' => wc_get_order_status_name( $order->get_status() ),
		'timeline'     => dg_build_order_timeline( $order ),
		'items'        => dg_build_order_items( $order ),
		'shipping'     => dg_build_shipping_address( $order ),
		'payment'      => dg_build_payment_summary( $order ),
	);

	/**
	 * Filter order modal data before sending to client.
	 *
	 * @param array    $data  Order data array.
	 * @param WC_Order $order WooCommerce order object.
	 */
	$data = apply_filters( 'dg_order_modal_data', $data, $order );

	wp_send_json_success( $data );
}
add_action( 'wp_ajax_dg_get_order_details', 'dg_ajax_get_order_details' );

/**
 * Build order timeline from order notes and status changes.
 *
 * Returns array of timeline items with label, date, and status (completed/pending).
 * Timeline items are derived from order status history and key events.
 *
 * @param WC_Order $order WooCommerce order object.
 * @return array<int, array{label:string,date:string,status:string}>
 */
function dg_build_order_timeline( WC_Order $order ): array {
	$timeline = array();

	// 1. Order Placed — always first.
	$date_created = $order->get_date_created();
	if ( $date_created ) {
		$timeline[] = array(
			'label'  => __( 'Order Placed', 'dragon-glow' ),
			'date'   => wc_format_datetime( $date_created, get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ),
			'status' => 'completed',
		);
	}

	// 2. Payment Confirmed — if order was paid.
	$date_paid = $order->get_date_paid();
	if ( $date_paid ) {
		$timeline[] = array(
			'label'  => __( 'Payment Confirmed', 'dragon-glow' ),
			'date'   => wc_format_datetime( $date_paid, get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ),
			'status' => 'completed',
		);
	}

	// 3. Processing — current status or past.
	$current_status = $order->get_status();
	$is_processing  = in_array( $current_status, array( 'processing', 'completed', 'shipped' ), true );

	$timeline[] = array(
		'label'  => __( 'Processing', 'dragon-glow' ),
		'date'   => $is_processing ? __( 'In Progress', 'dragon-glow' ) : __( 'Pending', 'dragon-glow' ),
		'status' => $is_processing ? 'completed' : 'pending',
	);

	// 4. Shipped — check order notes for shipping tracking or completed status.
	$is_shipped = in_array( $current_status, array( 'completed' ), true );
	$ship_date  = $is_shipped ? dg_get_order_shipped_date( $order ) : null;

	$timeline[] = array(
		'label'  => __( 'Shipped', 'dragon-glow' ),
		'date'   => $ship_date ? wc_format_datetime( $ship_date, get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) : __( 'Pending', 'dragon-glow' ),
		'status' => $is_shipped ? 'completed' : 'pending',
	);

	// 5. Delivered — order completed.
	$is_completed     = 'completed' === $current_status;
	$date_completed   = $order->get_date_completed();
	$delivered_date   = $is_completed && $date_completed ? wc_format_datetime( $date_completed, get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) : __( 'Pending', 'dragon-glow' );

	$timeline[] = array(
		'label'  => __( 'Delivered', 'dragon-glow' ),
		'date'   => $delivered_date,
		'status' => $is_completed ? 'completed' : 'pending',
	);

	/**
	 * Filter order timeline items.
	 *
	 * @param array    $timeline Timeline items.
	 * @param WC_Order $order    WooCommerce order object.
	 */
	return apply_filters( 'dg_order_timeline', $timeline, $order );
}

/**
 * Attempt to extract shipped date from order notes.
 *
 * Looks for notes containing keywords like "shipped", "tracking", "dispatched".
 * Returns DateTime object if found, null otherwise.
 *
 * @param WC_Order $order WooCommerce order object.
 * @return DateTime|null Shipped date or null.
 */
function dg_get_order_shipped_date( WC_Order $order ): ?DateTime {
	$notes = wc_get_order_notes(
		array(
			'order_id' => $order->get_id(),
			'type'     => 'customer',
		)
	);

	if ( empty( $notes ) || ! is_array( $notes ) ) {
		return null;
	}

	// Search for shipping-related keywords in notes.
	$keywords = array( 'shipped', 'tracking', 'dispatched', 'shipped out', 'sent' );

	foreach ( $notes as $note ) {
		if ( ! is_object( $note ) || empty( $note->content ) ) {
			continue;
		}

		$content = strtolower( (string) $note->content );

		foreach ( $keywords as $keyword ) {
			if ( false !== strpos( $content, $keyword ) ) {
				// Return note date as shipped date.
				if ( isset( $note->date_created ) && is_object( $note->date_created ) ) {
					return $note->date_created;
				}
			}
		}
	}

	return null;
}

/**
 * Build order items array for modal display.
 *
 * @param WC_Order $order WooCommerce order object.
 * @return array<int, array{name:string,quantity:int,price:string}>
 */
function dg_build_order_items( WC_Order $order ): array {
	$items      = array();
	$line_items = $order->get_items();

	if ( empty( $line_items ) ) {
		return $items;
	}

	foreach ( $line_items as $item_id => $item ) {
		if ( ! is_object( $item ) || ! method_exists( $item, 'get_name' ) ) {
			continue;
		}

		$product_name = $item->get_name();
		$quantity     = $item->get_quantity();
		$total        = $item->get_total();

		// Format price — strip HTML tags AND decode entities for plain text.
		$price_html = wc_price( $total );
		$price_text = html_entity_decode( wp_strip_all_tags( $price_html ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );

		$items[] = array(
			'name'     => $product_name,
			'quantity' => $quantity,
			'price'    => $price_text,
		);
	}

	return $items;
}

/**
 * Build shipping address array.
 *
 * @param WC_Order $order WooCommerce order object.
 * @return array{name:string,address_1:string,address_2:string,city:string,state:string,postcode:string,country:string,phone:string}
 */
function dg_build_shipping_address( WC_Order $order ): array {
	$shipping = array(
		'name'      => trim( $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name() ),
		'address_1' => $order->get_shipping_address_1(),
		'address_2' => $order->get_shipping_address_2(),
		'city'      => $order->get_shipping_city(),
		'state'     => $order->get_shipping_state(),
		'postcode'  => $order->get_shipping_postcode(),
		'country'   => $order->get_shipping_country(),
		'phone'     => $order->get_billing_phone(), // Shipping phone not available, use billing.
	);

	// If no shipping address, fall back to billing.
	if ( empty( $shipping['address_1'] ) ) {
		$shipping = array(
			'name'      => trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ),
			'address_1' => $order->get_billing_address_1(),
			'address_2' => $order->get_billing_address_2(),
			'city'      => $order->get_billing_city(),
			'state'     => $order->get_billing_state(),
			'postcode'  => $order->get_billing_postcode(),
			'country'   => $order->get_billing_country(),
			'phone'     => $order->get_billing_phone(),
		);
	}

	return $shipping;
}

/**
 * Build payment summary array.
 *
 * @param WC_Order $order WooCommerce order object.
 * @return array{subtotal:string,shipping:string,tax:string,total:string,method:string}
 */
function dg_build_payment_summary( WC_Order $order ): array {
	$subtotal = $order->get_subtotal();
	$shipping = $order->get_shipping_total();
	$tax      = $order->get_total_tax();
	$total    = $order->get_total();

	// Get payment method title.
	$payment_method = $order->get_payment_method_title();
	if ( empty( $payment_method ) ) {
		$payment_method = __( 'N/A', 'dragon-glow' );
	}

	// Helper function to format price as plain text (no HTML entities).
	$format_price = function( $amount ) {
		$price_html = wc_price( $amount );
		return html_entity_decode( wp_strip_all_tags( $price_html ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	};

	return array(
		'subtotal' => $format_price( $subtotal ),
		'shipping' => $format_price( $shipping ),
		'tax'      => $format_price( $tax ),
		'total'    => $format_price( $total ),
		'method'   => $payment_method,
	);
}
