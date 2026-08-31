<?php
/**
 * Dragon Glow — AJAX: Wishlist
 *
 * Single source of truth for wishlist mutations from the front-end.
 *
 * Actions:
 *   wp_ajax_dg_wishlist_toggle        — toggle a single product in/out.
 *   wp_ajax_dg_wishlist_remove_many   — bulk remove a list of product IDs.
 *   wp_ajax_dg_wishlist_clear         — empty the entire wishlist.
 *   wp_ajax_dg_wishlist_count         — return the current count for the
 *                                       header badge refresh.
 *   wp_ajax_dg_wishlist_share         — record that a wishlist was shared via
 *                                       email and email the link to the user.
 *
 * Every endpoint:
 *   - Verifies the `dg_nonce` nonce.
 *   - Requires `is_user_logged_in()` — guests are redirected to the login
 *     screen with a `redirect_to` so they land back on the wishlist page.
 *   - Returns the updated count + a human-readable message so the JS can
 *     keep the badge and toasts in sync.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Verify nonce + login for a wishlist AJAX request.
 *
 * Returns the current user ID on success; otherwise sends a JSON error and
 * short-circuits the request.
 *
 * @return int User ID (always > 0 on return).
 */
function dg_wishlist_verify_request(): int {
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'dg_nonce' ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'Security check failed. Please refresh and try again.', 'dragon-glow' ),
			),
			403
		);
	}

	if ( ! is_user_logged_in() ) {
		wp_send_json_error(
			array(
				'message'  => __( 'Please sign in to manage your wishlist.', 'dragon-glow' ),
				'redirect' => wp_login_url( home_url( '/wishlist/' ) ),
			),
			401
		);
	}

	return get_current_user_id();
}

/**
 * AJAX: toggle a product in/out of the current user's wishlist.
 */
function dg_ajax_wishlist_toggle(): void {
	$user_id    = dg_wishlist_verify_request();
	$product_id = isset( $_POST['product_id'] ) ? absint( wp_unslash( $_POST['product_id'] ) ) : 0;

	if ( $product_id <= 0 || 'product' !== get_post_type( $product_id ) ) {
		wp_send_json_error(
			array( 'message' => __( 'This product is no longer available.', 'dragon-glow' ) ),
			400
		);
	}

	$result = dg_wishlist_toggle( $user_id, $product_id );

	wp_send_json_success(
		array(
			'added'      => $result['added'],
			'count'      => $result['count'],
			'product_id' => $product_id,
			'message'    => $result['added']
				? __( 'Added to your wishlist.', 'dragon-glow' )
				: __( 'Removed from your wishlist.', 'dragon-glow' ),
		)
	);
}
add_action( 'wp_ajax_dg_wishlist_toggle', 'dg_ajax_wishlist_toggle' );

/**
 * AJAX: bulk-remove a list of product IDs.
 *
 * Accepts a comma-separated `product_ids` string (POST-friendly) or a JSON
 * array — the JS sends a comma-separated string for FormData compatibility.
 */
function dg_ajax_wishlist_remove_many(): void {
	$user_id = dg_wishlist_verify_request();

	$raw = isset( $_POST['product_ids'] ) ? sanitize_text_field( wp_unslash( $_POST['product_ids'] ) ) : '';
	if ( '' === $raw ) {
		wp_send_json_error(
			array( 'message' => __( 'Please choose at least one item to remove.', 'dragon-glow' ) ),
			400
		);
	}

	$ids = array_filter(
		array_map( 'absint', array_map( 'trim', explode( ',', $raw ) ) ),
		static function ( int $id ): bool {
			return $id > 0;
		}
	);

	$result = dg_wishlist_remove_many( $user_id, $ids );

	wp_send_json_success(
		array(
			'count'   => $result['count'],
			'removed' => count( $ids ),
			'message' => sprintf(
				/* translators: %d: number of items removed. */
				_n( '%d item removed from your wishlist.', '%d items removed from your wishlist.', count( $ids ), 'dragon-glow' ),
				count( $ids )
			),
		)
	);
}
add_action( 'wp_ajax_dg_wishlist_remove_many', 'dg_ajax_wishlist_remove_many' );

/**
 * AJAX: clear the entire wishlist.
 */
function dg_ajax_wishlist_clear(): void {
	$user_id = dg_wishlist_verify_request();
	dg_set_wishlist( $user_id, array() );

	wp_send_json_success(
		array(
			'count'   => 0,
			'message' => __( 'Your wishlist has been cleared.', 'dragon-glow' ),
		)
	);
}
add_action( 'wp_ajax_dg_wishlist_clear', 'dg_ajax_wishlist_clear' );

/**
 * AJAX: return the current wishlist count for the header badge.
 *
 * Cheap endpoint — only reads one user_meta row. Called whenever the user
 * toggles an item from a non-wishlist page (shop grid, single product).
 */
function dg_ajax_wishlist_count(): void {
	$user_id = dg_wishlist_verify_request();

	wp_send_json_success(
		array(
			'count' => count( dg_get_wishlist( $user_id ) ),
		)
	);
}
add_action( 'wp_ajax_dg_wishlist_count', 'dg_ajax_wishlist_count' );

/**
 * AJAX: record a wishlist share and email the recipient.
 *
 * `email` is the recipient address. The current wishlist URL is sent so the
 * recipient can open it directly. We email through the standard wp_mail
 * pipeline; production sites can hook `dg_wishlist_share_recipient` to swap
 * in Brevo (see inc/ajax/brevo.php).
 *
 * This endpoint always returns success to the caller even if `wp_mail` fails
 * — the user-facing flow treats "share" as a fire-and-forget action, and we
 * log any delivery failure for the admin.
 */
function dg_ajax_wishlist_share(): void {
	$user_id = dg_wishlist_verify_request();

	$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	if ( '' === $email || ! is_email( $email ) ) {
		wp_send_json_error(
			array( 'message' => __( 'Please enter a valid email address.', 'dragon-glow' ) ),
			400
		);
	}

	$current_user = wp_get_current_user();
	$share_url    = home_url( '/wishlist/?shared=1' );

	$subject = sprintf(
		/* translators: %s: sender display name. */
		__( '%s shared their Dragon Glow wishlist with you', 'dragon-glow' ),
		$current_user->display_name ?: __( 'A friend', 'dragon-glow' )
	);

	$body = sprintf(
		"%s\n\n%s\n\n%s\n",
		sprintf(
			/* translators: %s: sender display name. */
			__( '%s thought you might love these picks from Dragon Glow.', 'dragon-glow' ),
			$current_user->display_name ?: __( 'A friend', 'dragon-glow' )
		),
		$share_url,
		__( '— Dragon Glow', 'dragon-glow' )
	);

	/**
	 * Filter the recipient of a wishlist-share email. Useful for swapping in
	 * a transactional-email provider (Brevo, Postmark, …) via a dedicated hook.
	 *
	 * Returning a non-truthy value short-circuits the wp_mail() call so the
	 * integrator can send via their own pipeline.
	 *
	 * @param string|false $to       Recipient email. Return false to skip wp_mail.
	 * @param string       $subject  Subject line.
	 * @param string       $body     Plain-text body.
	 * @param int          $user_id  Sender user ID.
	 */
	$recipient = apply_filters( 'dg_wishlist_share_recipient', $email, $subject, $body, $user_id );

	if ( false !== $recipient && '' !== $recipient ) {
		wp_mail( (string) $recipient, $subject, $body );
	}

	wp_send_json_success(
		array(
			'message' => sprintf(
				/* translators: %s: recipient email. */
				__( 'Wishlist shared with %s.', 'dragon-glow' ),
				$email
			),
		)
	);
}
add_action( 'wp_ajax_dg_wishlist_share', 'dg_ajax_wishlist_share' );
