<?php
/**
 * Template Name: Mock Checkout — Dragon Glow
 *
 * Internal checkout page for mock (display-only) products.
 * Receives product selection via URL query args from DG_Mock_Checkout_Handler:
 *   ?dg_mock_checkout=1&dg_mock_item={slug|size}
 *
 * This template is NOT a WooCommerce template — it works independently
 * of WooCommerce and does not depend on any WC functions.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

// Guard: redirect away if WooCommerce is active — real products should use WC checkout.
if ( dg_is_woocommerce_active() && function_exists( 'wc_get_checkout_url' ) ) {
	$wc_checkout = wc_get_checkout_url();
	$has_wc_cart_items = false;
	try {
		$has_wc_cart_items = ! WC()->cart->is_empty();
	} catch ( Exception $e ) {
		$has_wc_cart_items = false;
	}

	if ( $has_wc_cart_items ) {
		wp_safe_redirect( $wc_checkout );
		exit;
	}
}

get_header();

// Load the mock checkout handler.
$mock_handler = new DG_Mock_Checkout_Handler();
$cart        = $mock_handler->load_cart();
$item_key    = isset( $_GET['dg_mock_item'] ) ? rawurldecode( sanitize_text_field( wp_unslash( $_GET['dg_mock_item'] ) ) ) : '';

// Extract slug and size from the item key.
$current_item = isset( $cart[ $item_key ] ) ? $cart[ $item_key ] : null;
$slug        = '';
$size        = '';

if ( $current_item ) {
	$slug = $current_item['slug'];
	$size = $current_item['size'];
} elseif ( ! empty( $item_key ) ) {
	$parts = explode( '|', $item_key );
	$slug  = $parts[0] ?? '';
	$size  = $parts[1] ?? '';
}

// Fetch product data if we have a slug.
$product = null;
if ( $slug ) {
	$product = DG_Product_Factory::mock()->get_by_slug( $slug );
}

$quantity  = $current_item ? (int) $current_item['quantity'] : 1;
$subtotal  = $product ? $product->get_price() * $quantity : 0;
$formatted = dg_format_price( $subtotal );

// Handle form submission.
$submitted = false;
$errors   = array();

if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['dg_mock_checkout_nonce'] ) ) {
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dg_mock_checkout_nonce'] ) ), 'dg_mock_checkout_action' ) ) {
		$errors[] = __( 'Security check failed. Please try again.', 'dragon-glow' );
	} else {
		// Validate required fields.
		$first_name = sanitize_text_field( $_POST['first_name'] ?? '' );
		$last_name  = sanitize_text_field( $_POST['last_name'] ?? '' );
		$email      = sanitize_email( $_POST['email'] ?? '' );
		$address    = sanitize_text_field( $_POST['address'] ?? '' );
		$city       = sanitize_text_field( $_POST['city'] ?? '' );
		$country    = sanitize_text_field( $_POST['country'] ?? 'US' );
		$zip        = sanitize_text_field( $_POST['zip'] ?? '' );

		if ( empty( $first_name ) ) {
			$errors[] = __( 'First name is required.', 'dragon-glow' );
		}
		if ( empty( $last_name ) ) {
			$errors[] = __( 'Last name is required.', 'dragon-glow' );
		}
		if ( empty( $email ) || ! is_email( $email ) ) {
			$errors[] = __( 'A valid email address is required.', 'dragon-glow' );
		}
		if ( empty( $address ) ) {
			$errors[] = __( 'Shipping address is required.', 'dragon-glow' );
		}
		if ( empty( $city ) ) {
			$errors[] = __( 'City is required.', 'dragon-glow' );
		}
		if ( empty( $zip ) ) {
			$errors[] = __( 'Postal code is required.', 'dragon-glow' );
		}

		if ( empty( $errors ) ) {
			// Persist order (in a real implementation this would save to a custom table).
			// For now, clear the mock cart and show a confirmation.
			$mock_handler->clear_cart();

			$submitted = true;
		}
	}
}
?>
<main class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-12">
	<?php if ( $submitted ) : ?>
		<!-- ── Confirmation ── -->
		<div class="text-center py-16">
			<div class="w-20 h-20 bg-primary-container/30 rounded-full flex items-center justify-center mx-auto mb-6">
				<span class="material-symbols-outlined text-primary text-4xl">check_circle</span>
			</div>
			<h1 class="font-headline text-headline-lg text-primary mb-4">
				<?php esc_html_e( 'Order Confirmed!', 'dragon-glow' ); ?>
			</h1>
			<p class="text-on-surface-variant text-body-lg max-w-md mx-auto mb-8">
				<?php esc_html_e( 'Thank you for your order. A confirmation email will be sent to your inbox shortly. Your ritual awaits!', 'dragon-glow' ); ?>
			</p>
			<a href="<?php echo esc_url( home_url( '/shop/' ) ); ?>"
			   class="btn-luxury bg-primary text-on-primary px-10 py-4 font-label-sm text-label-sm uppercase tracking-widest inline-block">
				<?php esc_html_e( 'Continue Shopping', 'dragon-glow' ); ?>
			</a>
		</div>

	<?php elseif ( ! $product ) : ?>
		<!-- ── Empty / Not found ── -->
		<div class="text-center py-16">
			<div class="w-32 h-32 bg-surface-container rounded-full flex items-center justify-center mx-auto mb-6">
				<span class="material-symbols-outlined text-primary" style="font-size: 64px;">shopping_bag_off</span>
			</div>
			<h1 class="font-headline text-headline-md text-primary mb-4">
				<?php esc_html_e( 'Your bag is empty', 'dragon-glow' ); ?>
			</h1>
			<p class="text-on-surface-variant text-body-lg max-w-md mx-auto mb-8">
				<?php esc_html_e( 'It looks like you haven\'t added anything to your bag yet.', 'dragon-glow' ); ?>
			</p>
			<a href="<?php echo esc_url( home_url( '/shop/' ) ); ?>"
			   class="btn-luxury bg-primary text-on-primary px-10 py-4 font-label-sm text-label-sm uppercase tracking-widest inline-block">
				<?php esc_html_e( 'Browse Collection', 'dragon-glow' ); ?>
			</a>
		</div>

	<?php else : ?>
		<!-- ── Checkout form ── -->
		<h1 class="font-headline text-headline-lg text-primary mb-8">
			<?php esc_html_e( 'Checkout', 'dragon-glow' ); ?>
		</h1>

		<?php if ( ! empty( $errors ) ) : ?>
			<div class="mb-6 p-4 bg-error-container text-on-error-container rounded-xl">
				<ul class="list-disc pl-5 space-y-1">
					<?php foreach ( $errors as $err ) : ?>
						<li><?php echo esc_html( $err ); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>

		<form method="POST" class="grid grid-cols-1 lg:grid-cols-3 gap-12">
			<?php wp_nonce_field( 'dg_mock_checkout_action', 'dg_mock_checkout_nonce' ); ?>

			<!-- LEFT: Form fields -->
			<div class="lg:col-span-2 space-y-8">

				<!-- Contact information -->
				<section class="bg-white rounded-3xl shadow-sm p-8">
					<h2 class="font-headline text-xl text-primary mb-6">
						<?php esc_html_e( 'Contact Information', 'dragon-glow' ); ?>
					</h2>
					<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
						<div>
							<label class="block text-label-sm text-on-surface-variant mb-2 uppercase tracking-wider"
								   for="dg-first-name">
								<?php esc_html_e( 'First Name', 'dragon-glow' ); ?>
							</label>
							<input type="text" id="dg-first-name" name="first_name"
								   class="w-full px-4 py-3 rounded-xl border border-outline-variant focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-colors"
								   value="<?php echo esc_attr( $first_name ?? '' ); ?>"
								   required autocomplete="given-name" />
						</div>
						<div>
							<label class="block text-label-sm text-on-surface-variant mb-2 uppercase tracking-wider"
								   for="dg-last-name">
								<?php esc_html_e( 'Last Name', 'dragon-glow' ); ?>
							</label>
							<input type="text" id="dg-last-name" name="last_name"
								   class="w-full px-4 py-3 rounded-xl border border-outline-variant focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-colors"
								   value="<?php echo esc_attr( $last_name ?? '' ); ?>"
								   required autocomplete="family-name" />
						</div>
						<div class="sm:col-span-2">
							<label class="block text-label-sm text-on-surface-variant mb-2 uppercase tracking-wider"
								   for="dg-email">
								<?php esc_html_e( 'Email Address', 'dragon-glow' ); ?>
							</label>
							<input type="email" id="dg-email" name="email"
								   class="w-full px-4 py-3 rounded-xl border border-outline-variant focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-colors"
								   value="<?php echo esc_attr( $email ?? '' ); ?>"
								   required autocomplete="email" />
						</div>
					</div>
				</section>

				<!-- Shipping address -->
				<section class="bg-white rounded-3xl shadow-sm p-8">
					<h2 class="font-headline text-xl text-primary mb-6">
						<?php esc_html_e( 'Shipping Address', 'dragon-glow' ); ?>
					</h2>
					<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
						<div class="sm:col-span-2">
							<label class="block text-label-sm text-on-surface-variant mb-2 uppercase tracking-wider"
								   for="dg-address">
								<?php esc_html_e( 'Street Address', 'dragon-glow' ); ?>
							</label>
							<input type="text" id="dg-address" name="address"
								   class="w-full px-4 py-3 rounded-xl border border-outline-variant focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-colors"
								   value="<?php echo esc_attr( $address ?? '' ); ?>"
								   required autocomplete="street-address" />
						</div>
						<div>
							<label class="block text-label-sm text-on-surface-variant mb-2 uppercase tracking-wider"
								   for="dg-city">
								<?php esc_html_e( 'City', 'dragon-glow' ); ?>
							</label>
							<input type="text" id="dg-city" name="city"
								   class="w-full px-4 py-3 rounded-xl border border-outline-variant focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-colors"
								   value="<?php echo esc_attr( $city ?? '' ); ?>"
								   required autocomplete="address-level2" />
						</div>
						<div>
							<label class="block text-label-sm text-on-surface-variant mb-2 uppercase tracking-wider"
								   for="dg-zip">
								<?php esc_html_e( 'Postal Code', 'dragon-glow' ); ?>
							</label>
							<input type="text" id="dg-zip" name="zip"
								   class="w-full px-4 py-3 rounded-xl border border-outline-variant focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-colors"
								   value="<?php echo esc_attr( $zip ?? '' ); ?>"
								   required autocomplete="postal-code" />
						</div>
						<div>
							<label class="block text-label-sm text-on-surface-variant mb-2 uppercase tracking-wider"
								   for="dg-country">
								<?php esc_html_e( 'Country', 'dragon-glow' ); ?>
							</label>
							<select id="dg-country" name="country"
									class="w-full px-4 py-3 rounded-xl border border-outline-variant focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-colors bg-white"
									required autocomplete="country">
								<option value="US" <?php selected( $country ?? '', 'US' ); ?>><?php esc_html_e( 'United States', 'dragon-glow' ); ?></option>
								<option value="CA" <?php selected( $country ?? '', 'CA' ); ?>><?php esc_html_e( 'Canada', 'dragon-glow' ); ?></option>
								<option value="GB" <?php selected( $country ?? '', 'GB' ); ?>><?php esc_html_e( 'United Kingdom', 'dragon-glow' ); ?></option>
								<option value="AU" <?php selected( $country ?? '', 'AU' ); ?>><?php esc_html_e( 'Australia', 'dragon-glow' ); ?></option>
								<option value="VN" <?php selected( $country ?? '', 'VN' ); ?>><?php esc_html_e( 'Vietnam', 'dragon-glow' ); ?></option>
							</select>
						</div>
					</div>
				</section>
			</div>

			<!-- RIGHT: Order summary -->
			<div class="lg:col-span-1">
				<div class="bg-white rounded-3xl shadow-sm p-8 sticky top-24">
					<h2 class="font-headline text-xl text-primary mb-6">
						<?php esc_html_e( 'Order Summary', 'dragon-glow' ); ?>
					</h2>

					<!-- Product line -->
					<div class="flex gap-4 mb-6 pb-6 border-b border-outline-variant/20">
						<?php if ( $product->get_image_url() ) : ?>
							<div class="w-16 h-20 rounded-xl overflow-hidden bg-surface-container flex-shrink-0">
								<img src="<?php echo esc_url( $product->get_image_url() ); ?>"
									 alt="<?php echo esc_attr( $product->get_name() ); ?>"
									 class="w-full h-full object-cover" />
							</div>
						<?php endif; ?>
						<div class="flex-1 min-w-0">
							<p class="font-bold text-on-surface truncate"><?php echo esc_html( $product->get_name() ); ?></p>
							<?php if ( $size ) : ?>
								<p class="text-sm text-on-surface-variant"><?php echo esc_html( $size ); ?></p>
							<?php endif; ?>
							<p class="text-sm text-on-surface-variant">
								<?php
								printf(
									esc_html( _nx( '%d item', '%d items', $quantity, 'checkout order summary', 'dragon-glow' ) ),
									(int) $quantity
								);
								?>
							</p>
						</div>
						<div class="text-right flex-shrink-0">
							<p class="font-bold text-primary">
								<?php echo esc_html( $product->get_price_formatted() ); ?>
							</p>
							<?php if ( $quantity > 1 ) : ?>
								<p class="text-xs text-on-surface-variant">
									&times; <?php echo esc_html( $quantity ); ?>
								</p>
							<?php endif; ?>
						</div>
					</div>

					<!-- Totals -->
					<div class="space-y-3 mb-6">
						<div class="flex justify-between text-on-surface-variant">
							<span><?php esc_html_e( 'Subtotal', 'dragon-glow' ); ?></span>
							<span><?php echo esc_html( $formatted ); ?></span>
						</div>
						<div class="flex justify-between text-on-surface-variant">
							<span><?php esc_html_e( 'Shipping', 'dragon-glow' ); ?></span>
							<span class="text-primary font-medium">
								<?php echo esc_html( $subtotal >= 75 ? __( 'FREE', 'dragon-glow' ) : __( 'Calculated at next step', 'dragon-glow' ) ); ?>
							</span>
						</div>
						<div class="flex justify-between text-sm text-on-surface-variant">
							<span><?php esc_html_e( 'Tax', 'dragon-glow' ); ?></span>
							<span><?php esc_html_e( 'Calculated at next step', 'dragon-glow' ); ?></span>
						</div>
						<div class="flex justify-between font-bold text-lg text-primary pt-3 border-t border-outline-variant/20">
							<span><?php esc_html_e( 'Estimated Total', 'dragon-glow' ); ?></span>
							<span><?php echo esc_html( $formatted ); ?></span>
						</div>
					</div>

					<button type="submit"
							class="w-full py-4 rounded-2xl bg-primary text-on-primary font-bold uppercase tracking-widest text-sm flex items-center justify-center gap-2 hover:opacity-90 transition-all">
						<span class="material-symbols-outlined">lock</span>
						<?php esc_html_e( 'Place Order', 'dragon-glow' ); ?>
					</button>

					<!-- Trust signals -->
					<div class="mt-6 pt-4 border-t border-outline-variant/20 space-y-3">
						<div class="flex items-center gap-3 text-sm text-on-surface-variant">
							<span class="material-symbols-outlined text-primary text-lg">local_shipping</span>
							<span><?php esc_html_e( 'Free Shipping on orders $75+', 'dragon-glow' ); ?></span>
						</div>
						<div class="flex items-center gap-3 text-sm text-on-surface-variant">
							<span class="material-symbols-outlined text-primary text-lg">verified</span>
							<span><?php esc_html_e( '30-Day Ritual Trial', 'dragon-glow' ); ?></span>
						</div>
						<div class="flex items-center gap-3 text-sm text-on-surface-variant">
							<span class="material-symbols-outlined text-primary text-lg">lock</span>
							<span><?php esc_html_e( 'Secure checkout', 'dragon-glow' ); ?></span>
						</div>
					</div>
				</div>
			</div>
		</form>
	<?php endif; ?>
</main>

<?php get_footer();
