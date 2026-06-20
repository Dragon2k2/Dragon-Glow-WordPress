<?php
/**
 * Dragon Glow — AJAX Handlers
 * Wishlist toggle, newsletter subscribe, contact form.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handle wishlist toggle.
 *
 * @return void
 */
function dg_handle_wishlist(): void {
    check_ajax_referer( 'dg_nonce', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( array(
            'message'  => __( 'Please login to save items to your wishlist.', 'dragon-glow' ),
            'redirect' => wc_get_page_permalink( 'myaccount' ),
        ) );
    }

    $product_id = absint( $_POST['product_id'] ?? 0 );

    if ( ! $product_id ) {
        wp_send_json_error( array( 'message' => __( 'Invalid product.', 'dragon-glow' ) ) );
    }

    $user_id  = get_current_user_id();
    $wishlist = (array) get_user_meta( $user_id, 'dg_wishlist', true );

    if ( in_array( $product_id, $wishlist, true ) ) {
        $wishlist = array_diff( $wishlist, array( $product_id ) );
        $added    = false;
        $message  = __( 'Removed from wishlist', 'dragon-glow' );
    } else {
        $wishlist[] = $product_id;
        $added      = true;
        $message    = __( 'Added to wishlist', 'dragon-glow' );
    }

    update_user_meta( $user_id, 'dg_wishlist', array_values( $wishlist ) );

    wp_send_json_success( array(
        'added' => $added,
        'count' => count( $wishlist ),
        'message' => $message,
    ) );
}
add_action( 'wp_ajax_dg_toggle_wishlist', 'dg_handle_wishlist' );

/**
 * Handle newsletter subscription.
 *
 * @return void
 */
function dg_handle_newsletter(): void {
    check_ajax_referer( 'dg_nonce', 'nonce' );

    $email = sanitize_email( $_POST['email'] ?? '' );

    if ( ! $email || ! is_email( $email ) ) {
        wp_send_json_error( array( 'message' => __( 'Please enter a valid email address.', 'dragon-glow' ) ) );
    }

    $subscribers = get_option( 'dg_newsletter_subscribers', array() );

    if ( in_array( $email, $subscribers, true ) ) {
        wp_send_json_success( array( 'message' => __( 'You are already subscribed!', 'dragon-glow' ) ) );
    }

    $subscribers[] = $email;
    update_option( 'dg_newsletter_subscribers', $subscribers );

    wp_send_json_success( array(
        'message' => __( 'Thank you for joining the ritual! Check your inbox for a welcome gift.', 'dragon-glow' ),
    ) );
}
add_action( 'wp_ajax_dg_newsletter', 'dg_handle_newsletter' );
add_action( 'wp_ajax_nopriv_dg_newsletter', 'dg_handle_newsletter' );

/**
 * Handle contact form submission.
 *
 * @return void
 */
function dg_handle_contact(): void {
    check_ajax_referer( 'dg_contact_nonce', 'dg_nonce_field' );

    $first_name = sanitize_text_field( $_POST['first_name'] ?? '' );
    $last_name  = sanitize_text_field( $_POST['last_name'] ?? '' );
    $email      = sanitize_email( $_POST['email'] ?? '' );
    $subject    = sanitize_text_field( $_POST['subject'] ?? '' );
    $message    = sanitize_textarea_field( $_POST['message'] ?? '' );

    // Validation
    $errors = array();

    if ( empty( $first_name ) ) {
        $errors[] = __( 'First name is required.', 'dragon-glow' );
    }

    if ( empty( $last_name ) ) {
        $errors[] = __( 'Last name is required.', 'dragon-glow' );
    }

    if ( empty( $email ) || ! is_email( $email ) ) {
        $errors[] = __( 'A valid email is required.', 'dragon-glow' );
    }

    if ( empty( $message ) ) {
        $errors[] = __( 'Please include a message.', 'dragon-glow' );
    }

    if ( ! empty( $errors ) ) {
        wp_send_json_error( array( 'message' => implode( ' ', $errors ) ) );
    }

    $full_name = $first_name . ' ' . $last_name;
    $admin_email = get_option( 'admin_email' );
    $headers     = array(
        'Content-Type: text/html; charset=UTF-8',
        sprintf( 'Reply-To: %s <%s>', $full_name, $email ),
    );

    // Subject labels
    $subject_labels = array(
        'orders'    => __( 'Order Inquiry', 'dragon-glow' ),
        'products'  => __( 'Product Consultation', 'dragon-glow' ),
        'wholesale' => __( 'Wholesale & Stockists', 'dragon-glow' ),
        'press'     => __( 'Press & Media', 'dragon-glow' ),
        'other'     => __( 'Other', 'dragon-glow' ),
    );
    $subject_label = $subject_labels[ $subject ] ?? __( 'General Inquiry', 'dragon-glow' );

    $body = sprintf(
        '<p><strong>%s</strong> %s (%s)</p>
         <p><strong>%s</strong> %s</p>
         <p><strong>%s</strong></p>
         <p>%s</p>',
        esc_html__( 'From:', 'dragon-glow' ),
        esc_html( $full_name ),
        esc_html( $email ),
        esc_html__( 'Subject:', 'dragon-glow' ),
        esc_html( $subject_label ),
        esc_html__( 'Message:', 'dragon-glow' ),
        nl2br( esc_html( $message ) )
    );

    $sent = wp_mail(
        $admin_email,
        sprintf( '[Dragon Glow] Contact: %s - %s', $subject_label, $full_name ),
        $body,
        $headers
    );

    if ( $sent ) {
        wp_send_json_success( array(
            'message' => __( 'Your message has been sent. We\'ll be in touch within 24 hours!', 'dragon-glow' ),
        ) );
    } else {
        wp_send_json_error( array(
            'message' => __( 'There was an error sending your message. Please try again.', 'dragon-glow' ),
        ) );
    }
}
add_action( 'wp_ajax_dg_contact_form', 'dg_handle_contact' );
add_action( 'wp_ajax_nopriv_dg_contact_form', 'dg_handle_contact' );

/**
 * AJAX: Quick add to cart.
 *
 * @return void
 */
function dg_ajax_add_to_cart(): void {
	check_ajax_referer( 'dg_nonce', 'nonce' );

	if ( ! dg_is_woocommerce_active() ) {
		wp_send_json_error( array( 'message' => __( 'WooCommerce is not active.', 'dragon-glow' ) ) );
	}

	$product_id = absint( $_POST['product_id'] ?? 0 );
	$quantity   = absint( $_POST['quantity'] ?? 1 );

	if ( ! $product_id ) {
		wp_send_json_error( array( 'message' => __( 'Invalid product.', 'dragon-glow' ) ) );
	}

	$product = wc_get_product( $product_id );

	if ( ! $product ) {
		wp_send_json_error( array( 'message' => __( 'Product not found.', 'dragon-glow' ) ) );
	}

	// Check for variable products - redirect instead
	if ( 'variable' === $product->get_type() ) {
		wp_send_json_error( array(
			'message'  => __( 'Please select options for this product.', 'dragon-glow' ),
			'redirect' => get_permalink( $product_id ),
		) );
	}

	$added = WC()->cart->add_to_cart( $product_id, $quantity );

	if ( $added ) {
		wp_send_json_success( array(
			'message'  => __( 'Added to bag!', 'dragon-glow' ),
			'fragments' => apply_filters( 'woocommerce_add_to_cart_fragments', array() ),
		) );
	} else {
		wp_send_json_error( array( 'message' => __( 'Could not add to cart.', 'dragon-glow' ) ) );
	}
}
add_action( 'wp_ajax_dg_ajax_add_to_cart', 'dg_ajax_add_to_cart' );
add_action( 'wp_ajax_nopriv_dg_ajax_add_to_cart', 'dg_ajax_add_to_cart' );

/**
 * AJAX: Buy Now — unified entry point via DG_Checkout_Router.
 *
 * @return void
 */
function dg_ajax_buy_now(): void {
	check_ajax_referer( 'dg_nonce', 'nonce' );

	$product_id = absint( $_POST['product_id'] ?? 0 );
	$slug      = sanitize_text_field( $_POST['slug'] ?? '' );
	$size      = sanitize_text_field( $_POST['size'] ?? '' );
	$quantity  = absint( $_POST['quantity'] ?? 1 );

	$result = DG_Checkout_Router::handle(
		array(
			'product_id' => $product_id,
			'slug'       => $slug,
			'size'       => $size,
			'quantity'   => $quantity,
		)
	);

	if ( $result['success'] ) {
		wp_send_json_success( $result );
	} else {
		wp_send_json_error( $result );
	}
}
add_action( 'wp_ajax_dg_ajax_buy_now', 'dg_ajax_buy_now' );
add_action( 'wp_ajax_nopriv_dg_ajax_buy_now', 'dg_ajax_buy_now' );

/**
 * AJAX: Remove from cart.
 *
 * @return void
 */
function dg_ajax_remove_from_cart(): void {
	check_ajax_referer( 'dg_nonce', 'nonce' );

	if ( ! dg_is_woocommerce_active() ) {
		wp_send_json_error( array( 'message' => __( 'WooCommerce is not active.', 'dragon-glow' ) ) );
	}

    $cart_item_key = sanitize_text_field( $_POST['cart_item_key'] ?? '' );

    if ( empty( $cart_item_key ) ) {
        wp_send_json_error( array( 'message' => __( 'Invalid cart item.', 'dragon-glow' ) ) );
    }

    $removed = WC()->cart->remove_cart_item( $cart_item_key );

    if ( $removed ) {
        wp_send_json_success( array(
            'message'   => __( 'Item removed from cart.', 'dragon-glow' ),
            'fragments' => apply_filters( 'woocommerce_add_to_cart_fragments', array() ),
        ) );
    } else {
        wp_send_json_error( array( 'message' => __( 'Could not remove item.', 'dragon-glow' ) ) );
    }
}
add_action( 'wp_ajax_dg_ajax_remove_from_cart', 'dg_ajax_remove_from_cart' );

/**
 * AJAX: Update cart quantity.
 *
 * @return void
 */
function dg_ajax_update_cart(): void {
	check_ajax_referer( 'dg_nonce', 'nonce' );

	if ( ! dg_is_woocommerce_active() ) {
		wp_send_json_error( array( 'message' => __( 'WooCommerce is not active.', 'dragon-glow' ) ) );
	}

    $cart_item_key = sanitize_text_field( $_POST['cart_item_key'] ?? '' );
    $quantity      = absint( $_POST['quantity'] ?? 0 );

    if ( empty( $cart_item_key ) ) {
        wp_send_json_error( array( 'message' => __( 'Invalid cart item.', 'dragon-glow' ) ) );
    }

    if ( $quantity < 1 ) {
        WC()->cart->remove_cart_item( $cart_item_key );
    } else {
        WC()->cart->set_quantity( $cart_item_key, $quantity );
    }

    wp_send_json_success( array(
        'fragments' => apply_filters( 'woocommerce_add_to_cart_fragments', array() ),
    ) );
}
add_action( 'wp_ajax_dg_ajax_update_cart', 'dg_ajax_update_cart' );
