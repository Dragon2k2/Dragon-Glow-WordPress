<?php
/**
 * Dragon Glow — AJAX: Return Requests
 *
 * Validates email + reason, stores the request in a WordPress option
 * (persisted for review), and notifies both the admin and the customer.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * AJAX: Submit a return request.
 *
 * Validates email + reason, stores the request in a WordPress option
 * (transient for 30 days), and sends a notification email to the admin.
 *
 * @return void
 */
function dg_ajax_submit_return_request(): void {
	check_ajax_referer( 'dg_nonce', 'nonce' );

	$email        = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
	$order_number = sanitize_text_field( wp_unslash( $_POST['order_number'] ?? '' ) );
	$reason       = sanitize_text_field( wp_unslash( $_POST['reason'] ?? '' ) );
	$notes        = sanitize_textarea_field( wp_unslash( $_POST['notes'] ?? '' ) );

	// Validate required fields.
	if ( empty( $email ) || ! is_email( $email ) ) {
		wp_send_json_error( array(
			'message' => __( 'Please enter a valid email address.', 'dragon-glow' ),
		) );
	}

	$valid_reasons = array(
		'defective',
		'wrong_item',
		'not_as_described',
		'changed_mind',
		'other',
	);
	if ( empty( $reason ) || ! in_array( $reason, $valid_reasons, true ) ) {
		wp_send_json_error( array(
			'message' => __( 'Please select a valid reason for return.', 'dragon-glow' ),
		) );
	}

	// Build reason label map (i18n).
	$reason_labels = array(
		'defective'         => __( 'Defective or damaged product', 'dragon-glow' ),
		'wrong_item'        => __( 'Received the wrong item', 'dragon-glow' ),
		'not_as_described'  => __( 'Not as described', 'dragon-glow' ),
		'changed_mind'      => __( 'Changed my mind', 'dragon-glow' ),
		'other'             => __( 'Other', 'dragon-glow' ),
	);
	$reason_label = $reason_labels[ $reason ] ?? $reason;

	// Generate a return reference token.
	$token = wp_generate_password( 12, false );

	// Persist the request.
	$request = array(
		'email'        => $email,
		'order_number' => $order_number,
		'reason'       => $reason_label,
		'reason_key'   => $reason,
		'notes'        => $notes,
		'submitted'    => current_time( 'mysql' ),
		'ip'           => dg_get_client_ip(),
		'token'        => $token,
		'status'       => 'pending',
	);

	// Load existing requests.
	$all_requests = get_option( 'dg_return_requests', array() );
	if ( ! is_array( $all_requests ) ) {
		$all_requests = array();
	}
	$all_requests[ $token ] = $request;
	update_option( 'dg_return_requests', $all_requests, false );

	// Send admin notification email.
	$admin_email = apply_filters( 'dg_return_admin_email', get_option( 'admin_email' ) );
	$from_email  = apply_filters( 'dg_return_from_email', 'anhthuymaiphuong698@gmail.com' );
	$from_name   = apply_filters( 'dg_return_from_name', 'Dragon Glow' );

	$subject = sprintf(
		'[Dragon Glow] Return Request — %s',
		$order_number ? $order_number : $token
	);

	ob_start();
	?>
<p style="margin:0 0 12px;font-family:inherit;">
	<?php
	printf(
		esc_html__( 'A new return request has been submitted.', 'dragon-glow' )
	);
	?>
</p>

<table cellpadding="6" cellspacing="0" style="border-collapse:collapse;font-family:inherit;">
	<tr>
		<td style="color:#888;"><?php esc_html_e( 'Reference', 'dragon-glow' ); ?></td>
		<td><strong><?php echo esc_html( $token ); ?></strong></td>
	</tr>
	<tr>
		<td style="color:#888;"><?php esc_html_e( 'Order', 'dragon-glow' ); ?></td>
		<td><?php echo $order_number ? esc_html( $order_number ) : '—'; ?></td>
	</tr>
	<tr>
		<td style="color:#888;"><?php esc_html_e( 'Email', 'dragon-glow' ); ?></td>
		<td><a href="mailto:<?php echo esc_attr( $email ); ?>" style="color:#1c1b1b;"><?php echo esc_html( $email ); ?></a></td>
	</tr>
	<tr>
		<td style="color:#888;vertical-align:top;"><?php esc_html_e( 'Reason', 'dragon-glow' ); ?></td>
		<td><?php echo esc_html( $reason_label ); ?></td>
	</tr>
	<?php if ( $notes ) : ?>
	<tr>
		<td style="color:#888;vertical-align:top;"><?php esc_html_e( 'Notes', 'dragon-glow' ); ?></td>
		<td><?php echo nl2br( esc_html( $notes ) ); ?></td>
	</tr>
	<?php endif; ?>
	<tr>
		<td style="color:#888;"><?php esc_html_e( 'Submitted', 'dragon-glow' ); ?></td>
		<td><?php echo esc_html( $request['submitted'] ); ?></td>
	</tr>
	<tr>
		<td style="color:#888;"><?php esc_html_e( 'IP', 'dragon-glow' ); ?></td>
		<td><?php echo esc_html( $request['ip'] ); ?></td>
	</tr>
</table>

	<?php
	$body = (string) ob_get_clean();

	$headers = array(
		'Content-Type: text/html; charset=UTF-8',
		sprintf( 'Reply-To: %s <%s>', $email, $email ),
		sprintf( 'From: %s <%s>', $from_name, $from_email ),
	);

	$sent = wp_mail( $admin_email, $subject, $body, $headers );

	// Send auto-reply to customer.
	$customer_subject = sprintf(
		'[Dragon Glow] Return request received — %s',
		$token
	);
	ob_start();
	?>
<p style="font-family:inherit;">
	<?php printf( esc_html__( 'Hi,', 'dragon-glow' ) ); ?>
</p>
<p style="font-family:inherit;">
	<?php
	printf(
		esc_html__(
			'Thank you for submitting a return request. We have received your request and will review it within two business days. Your reference number is: %s',
			'dragon-glow'
		),
		'<strong>' . esc_html( $token ) . '</strong>'
	);
	?>
</p>
<p style="font-family:inherit;">
	<?php
	printf(
		esc_html__(
			'If you have any questions, please reply to this email or contact us at %s.',
			'dragon-glow'
		),
		'<a href="mailto:' . esc_attr( $admin_email ) . '" style="color:#1c1b1b;">' . esc_html( $admin_email ) . '</a>'
	);
	?>
</p>
<p style="font-family:inherit;">
	<?php
	printf(
		esc_html__( 'Warmly, %s', 'dragon-glow' ),
		esc_html( wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) )
	);
	?>
</p>
	<?php
	$customer_body = (string) ob_get_clean();

	$customer_headers = array(
		'Content-Type: text/html; charset=UTF-8',
		sprintf( 'From: %s <%s>', $from_name, $from_email ),
	);

	$customer_sent = wp_mail( $email, $customer_subject, $customer_body, $customer_headers );

	$msg = __( 'Your return request has been submitted. We will be in touch within two business days.', 'dragon-glow' );
	if ( ! $sent && ! $customer_sent ) {
		$msg = __( 'Your request was received, but email delivery may be delayed. We will follow up shortly.', 'dragon-glow' );
	}

	wp_send_json_success( array(
		'message'  => $msg,
		'token'    => $token,
	) );
}

/**
 * Get the requester's IP address.
 *
 * @return string
 */
function dg_get_client_ip(): string {
	$ip = $_SERVER['REMOTE_ADDR'] ?? '';
	if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		$parts = explode( ',', sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) );
		$ip    = trim( $parts[0] );
	}
	return (string) apply_filters( 'dg_client_ip', $ip );
}

add_action( 'wp_ajax_dg_submit_return_request',        'dg_ajax_submit_return_request' );
add_action( 'wp_ajax_nopriv_dg_submit_return_request', 'dg_ajax_submit_return_request' );
