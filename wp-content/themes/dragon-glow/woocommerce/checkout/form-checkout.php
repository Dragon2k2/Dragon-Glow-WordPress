<?php
/**
 * Dragon Glow — Checkout Form
 * Override: woocommerce/checkout/form-checkout.php
 * 3-step checkout: Shipping → Payment → Review
 *
 * Step 1 (Shipping): WooCommerce billing + shipping fields.
 * Step 2 (Payment): Payment methods + Place Order button (extracted from #order_review).
 * Step 3 (Review): Read-only address/payment summary + final confirm.
 * Sidebar: sticky glass-card Order Summary mirroring real cart totals.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_checkout_form', $checkout );

// If checkout registration is disabled and not logged in, the user cannot checkout.
if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'dragon-glow' ) ) );
	return;
}
?>

<div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-8 md:py-12">
	<?php get_template_part( 'template-parts/global/breadcrumb' ); ?>

	<h1 class="font-headline text-headline-lg text-primary mb-8 text-center md:text-left">
		<?php esc_html_e( 'Checkout', 'dragon-glow' ); ?>
	</h1>

	<!-- ── Progress Stepper ─────────────────────────────────────── -->
	<div class="flex items-center justify-center mb-10" id="dg-checkout-stepper" role="navigation" aria-label="<?php esc_attr_e( 'Checkout steps', 'dragon-glow' ); ?>">
		<?php
		$steps = array(
			1 => array(
				'label' => __( 'Shipping', 'dragon-glow' ),
				'icon'  => 'local_shipping',
			),
			2 => array(
				'label' => __( 'Payment', 'dragon-glow' ),
				'icon'  => 'credit_card',
			),
			3 => array(
				'label' => __( 'Review', 'dragon-glow' ),
				'icon'  => 'fact_check',
			),
		);
		foreach ( $steps as $num => $info ) :
			$is_first = 1 === $num;
			$is_last  = 3 === $num;
		?>
			<div class="flex items-center gap-3<?php echo $is_first ? '' : ' step-group'; ?>"
			     id="dg-step-group-<?php echo (int) $num; ?>">
				<?php if ( ! $is_first ) : ?>
					<div class="dg-step-connector w-10 md:w-16 h-0.5 bg-outline-variant transition-all"></div>
				<?php endif; ?>
				<div class="flex items-center gap-3" id="dg-step-<?php echo (int) $num; ?>">
					<div id="dg-step-<?php echo (int) $num; ?>-circle"
					     class="step-active w-10 h-10 rounded-full flex items-center justify-center font-bold border-2 transition-all">
						<span class="step-number material-symbols-outlined text-base leading-none"><?php echo esc_html( $info['icon'] ); ?></span>
						<span class="step-check hidden material-symbols-outlined text-base leading-none">check</span>
					</div>
					<span id="dg-step-<?php echo (int) $num; ?>-label"
					      class="font-medium text-primary hidden sm:block"><?php echo esc_html( $info['label'] ); ?></span>
				</div>
			</div>
		<?php endforeach; ?>
	</div>

	<!-- ── Two-column layout ──────────────────────────────────── -->
	<div class="grid grid-cols-1 lg:grid-cols-5 gap-8 lg:gap-12 items-start">

		<!-- ── LEFT: Step panes (lg:col-span-3) ──────────────── -->
		<div class="lg:col-span-3 order-2 lg:order-1">

			<form name="checkout" method="post" class="checkout woocommerce-checkout"
			      action="<?php echo esc_url( wc_get_checkout_url() ); ?>"
			      enctype="multipart/form-data"
			      novalidate>

				<?php if ( $checkout->get_checkout_fields() ) : ?>
					<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>
				<?php endif; ?>

				<!-- ── STEP 1: Shipping ──────────────────────────────── -->
				<div class="dg-step-pane" id="dg-pane-1" data-step="1">

					<?php if ( $checkout->get_checkout_fields() ) : ?>
						<div id="customer_details">
							<div class="bg-surface-container-low rounded-3xl p-6 md:p-8 mb-6">
								<h2 class="font-headline text-xl text-primary mb-6 flex items-center gap-2">
									<span class="material-symbols-outlined text-primary">person</span>
									<?php esc_html_e( 'Contact Information', 'dragon-glow' ); ?>
								</h2>
								<div class="space-y-4">
									<?php
									// Render billing email field directly so it appears in Step 1.
									$billing_fields = $checkout->get_checkout_fields( 'billing' );
									if ( isset( $billing_fields['billing_email'] ) ) {
										woocommerce_checkout_form_field( 'billing_email', $billing_fields['billing_email'] );
									} elseif ( isset( $billing_fields['billing_email'] ) ) {
										echo '<p class="text-sm text-on-surface-variant">' . esc_html__( 'Email is collected below.', 'dragon-glow' ) . '</p>';
									}
									?>
								</div>
							</div>

							<!-- Shipping address block -->
							<div class="bg-surface-container-low rounded-3xl p-6 md:p-8 mb-6">
								<h2 class="font-headline text-xl text-primary mb-6 flex items-center gap-2">
									<span class="material-symbols-outlined text-primary">home</span>
									<?php esc_html_e( 'Shipping Address', 'dragon-glow' ); ?>
								</h2>
								<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
									<?php
									// First name + last name always at top.
									woocommerce_checkout_form_field( 'billing_first_name', array(
										'label'    => __( 'First name', 'dragon-glow' ),
										'required' => true,
										'class'    => array( 'form-row-first' ),
									) );
									woocommerce_checkout_form_field( 'billing_last_name', array(
										'label'    => __( 'Last name', 'dragon-glow' ),
										'required' => true,
										'class'    => array( 'form-row-last' ),
									) );
									// Remaining billing fields.
									$skip = array( 'billing_email', 'billing_first_name', 'billing_last_name' );
									foreach ( $billing_fields as $key => $field ) {
										if ( in_array( $key, $skip, true ) ) {
											continue;
										}
										woocommerce_checkout_form_field( $key, $field );
									}
									?>
								</div>

								<?php if ( wc_ship_to_billing_address_only() ) : ?>
									<div class="mt-6">
										<?php do_action( 'woocommerce_checkout_shipping' ); ?>
									</div>
								<?php else : ?>
									<div class="mt-6 pt-6 border-t border-outline-variant/20">
										<p class="form-row">
											<label for="ship-to-different-address-checkbox"
											       class="flex items-center gap-3 cursor-pointer">
												<input id="ship-to-different-address-checkbox"
												       name="ship_to_different_address"
												       type="checkbox"
												       class="w-5 h-5 rounded border-outline-variant text-primary focus:ring-primary"
												       value="1" />
												<span class="text-on-surface font-medium">
													<?php esc_html_e( 'Ship to a different address?', 'dragon-glow' ); ?>
												</span>
											</label>
										</p>
										<div class="woocommerce-shipping-fields__field-wrapper mt-4 space-y-4 hidden"
										     id="ship-to-different-address"
										     aria-hidden="true">
											<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
												<?php
												$shipping_fields = $checkout->get_checkout_fields( 'shipping' );
												foreach ( $shipping_fields as $key => $field ) {
													woocommerce_checkout_form_field( $key, $field );
												}
												?>
											</div>
										</div>
									</div>
								<?php endif; ?>
							</div>

							<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>
						</div>
					<?php endif; ?>

					<!-- Step 1 navigation -->
					<div class="flex justify-between items-center mt-6 gap-4">
						<a href="<?php echo esc_url( wc_get_cart_url() ); ?>"
						   class="flex items-center gap-2 text-on-surface-variant hover:text-primary transition-colors text-sm font-medium">
							<span class="material-symbols-outlined text-lg">arrow_back</span>
							<?php esc_html_e( 'Back to cart', 'dragon-glow' ); ?>
						</a>
						<button type="button"
						        class="dg-step-next btn-primary-glow px-8 py-4 rounded-2xl font-bold uppercase tracking-widest text-sm flex items-center gap-2"
						        data-goto="2">
							<?php esc_html_e( 'Continue to Payment', 'dragon-glow' ); ?>
							<span class="material-symbols-outlined text-lg">arrow_forward</span>
						</button>
					</div>
				</div><!-- end #dg-pane-1 -->

				<!-- ── STEP 2: Payment ──────────────────────────────── -->
				<div class="dg-step-pane hidden" id="dg-pane-2" data-step="2">
					<div class="bg-surface-container-low rounded-3xl p-6 md:p-8">
						<h2 class="font-headline text-xl text-primary mb-6 flex items-center gap-2">
							<span class="material-symbols-outlined text-primary">credit_card</span>
							<?php esc_html_e( 'Payment Method', 'dragon-glow' ); ?>
						</h2>

						<?php
						/**
						 * The #order_review div contains BOTH cart items/totals AND payment
						 * methods — we extract just the payment block here and move it
						 * into this step pane via JS (see checkout.js).  The full
						 * #order_review stays in the sidebar so the real WooCommerce
						 * AJAX machinery (country/shipping/coupon updates) keeps working.
						 *
						 * The JS also re-runs this extraction on every 'updated_checkout'
						 * event so the payment section never goes stale.
						 */
						?>
						<div id="dg-payment-section">
							<p class="text-on-surface-variant text-sm italic">
								<?php esc_html_e( 'Loading payment options…', 'dragon-glow' ); ?>
							</p>
						</div>
					</div>

					<!-- Step 2 navigation -->
					<div class="flex justify-between items-center mt-6 gap-4">
						<button type="button"
						        class="dg-step-back flex items-center gap-2 text-on-surface-variant hover:text-primary transition-colors text-sm font-medium bg-transparent border-none cursor-pointer p-0"
						        data-goto="1">
							<span class="material-symbols-outlined text-lg">arrow_back</span>
							<?php esc_html_e( 'Back to Shipping', 'dragon-glow' ); ?>
						</button>
						<button type="button"
						        class="dg-step-next btn-primary-glow px-8 py-4 rounded-2xl font-bold uppercase tracking-widest text-sm flex items-center gap-2"
						        data-goto="3">
							<?php esc_html_e( 'Review Order', 'dragon-glow' ); ?>
							<span class="material-symbols-outlined text-lg">arrow_forward</span>
						</button>
					</div>
				</div><!-- end #dg-pane-2 -->

				<!-- ── STEP 3: Review ──────────────────────────────── -->
				<div class="dg-step-pane hidden" id="dg-pane-3" data-step="3">
					<div class="space-y-4">

						<!-- Shipping summary -->
						<div class="bg-surface-container-low rounded-3xl p-6 md:p-8">
							<div class="flex items-center justify-between mb-4">
								<h2 class="font-headline text-lg text-primary flex items-center gap-2">
									<span class="material-symbols-outlined text-primary">local_shipping</span>
									<?php esc_html_e( 'Shipping', 'dragon-glow' ); ?>
								</h2>
								<button type="button"
								        class="dg-jump-step text-sm text-primary hover:underline font-medium bg-transparent border-none cursor-pointer p-0"
								        data-goto="1">
									<?php esc_html_e( 'Edit', 'dragon-glow' ); ?>
								</button>
							</div>
							<div id="dg-review-shipping"
							     class="text-on-surface-variant text-sm space-y-1">
								<p><?php esc_html_e( 'Please complete Step 1 to see your shipping address.', 'dragon-glow' ); ?></p>
							</div>
						</div>

						<!-- Payment summary -->
						<div class="bg-surface-container-low rounded-3xl p-6 md:p-8">
							<div class="flex items-center justify-between mb-4">
								<h2 class="font-headline text-lg text-primary flex items-center gap-2">
									<span class="material-symbols-outlined text-primary">credit_card</span>
									<?php esc_html_e( 'Payment', 'dragon-glow' ); ?>
								</h2>
								<button type="button"
								        class="dg-jump-step text-sm text-primary hover:underline font-medium bg-transparent border-none cursor-pointer p-0"
								        data-goto="2">
									<?php esc_html_e( 'Edit', 'dragon-glow' ); ?>
								</button>
							</div>
							<div id="dg-review-payment"
							     class="text-on-surface-variant text-sm space-y-1">
								<p><?php esc_html_e( 'Please complete Step 2 to see your payment method.', 'dragon-glow' ); ?></p>
							</div>
						</div>

						<!-- Trust signals -->
						<div class="bg-primary-container/10 rounded-3xl p-6 flex flex-wrap gap-6 justify-center">
							<div class="flex items-center gap-2 text-sm text-primary font-medium">
								<span class="material-symbols-outlined text-lg">lock</span>
								<?php esc_html_e( 'Secure Checkout', 'dragon-glow' ); ?>
							</div>
							<div class="flex items-center gap-2 text-sm text-primary font-medium">
								<span class="material-symbols-outlined text-lg">eco</span>
								<?php esc_html_e( 'Vegan & Cruelty-Free', 'dragon-glow' ); ?>
							</div>
							<div class="flex items-center gap-2 text-sm text-primary font-medium">
								<span class="material-symbols-outlined text-lg">replay_circle_filled</span>
								<?php esc_html_e( '30-Day Guarantee', 'dragon-glow' ); ?>
							</div>
						</div>

						<!-- Concierge line -->
						<p class="text-center text-sm text-on-surface-variant">
							<?php
							printf(
								esc_html__( 'Need help? %sContact our concierge%s.', 'dragon-glow' ),
								'<a href="' . esc_url( home_url( '/contact/' ) ) . '" class="text-primary hover:underline font-medium">',
								'</a>'
							);
							?>
						</p>

					</div>

					<!-- Step 3 navigation -->
					<div class="flex justify-between items-center mt-6 gap-4">
						<button type="button"
						        class="dg-step-back flex items-center gap-2 text-on-surface-variant hover:text-primary transition-colors text-sm font-medium bg-transparent border-none cursor-pointer p-0"
						        data-goto="2">
							<span class="material-symbols-outlined text-lg">arrow_back</span>
							<?php esc_html_e( 'Back to Payment', 'dragon-glow' ); ?>
						</button>
						<?php
						/**
						 * The actual Place Order button lives in #order_review in the sidebar
						 * (moved there by WooCommerce). The Review step explains what will
						 * happen and shows the "Place Order" button location visually, while
						 * the hidden submit in the form below mirrors WooCommerce's own submit.
						 */
						?>
						<button type="submit"
						        class="dg-place-order btn-primary-glow px-10 py-4 rounded-2xl font-bold uppercase tracking-widest text-sm flex items-center gap-2"
						        id="dg-place-order-btn">
							<span class="material-symbols-outlined text-lg">check_circle</span>
							<?php esc_html_e( 'Place Order', 'dragon-glow' ); ?>
						</button>
					</div>
				</div><!-- end #dg-pane-3 -->

				<?php do_action( 'woocommerce_checkout_before_order_review_heading' ); ?>
				<h3 id="order_review_heading" class="hidden"><?php esc_html_e( 'Your order', 'dragon-glow' ); ?></h3>

				<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>

			<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>

		</form>
	</div><!-- end .lg-col-span-3 -->

		<!-- ── RIGHT: Sticky Order Summary sidebar (lg:col-span-2) ── -->
		<div class="lg:col-span-2 order-1 lg:order-2 lg:sticky lg:top-24">
			<div class="glass-card rounded-3xl p-6 md:p-8">

				<h2 class="font-headline text-xl text-primary mb-6 flex items-center gap-2">
					<span class="material-symbols-outlined text-primary">receipt_long</span>
					<?php esc_html_e( 'Order Summary', 'dragon-glow' ); ?>
				</h2>

				<!-- Cart items -->
				<div class="space-y-4 mb-6" id="dg-order-items">
						<?php
						if ( WC()->cart ) :
							foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) :
								$__product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
								if ( ! $__product || ! $__product->exists() ) {
									continue;
								}
							$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $__product->is_visible() ? $__product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
							$thumbnail         = apply_filters( 'woocommerce_cart_item_thumbnail', $__product->get_image( 'woocommerce_gallery_thumbnail' ), $cart_item, $cart_item_key );
							$product_name      = apply_filters( 'woocommerce_cart_item_name', $__product->get_name(), $cart_item, $cart_item_key );
							$product_price     = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $__product ), $cart_item, $cart_item_key );
							$item_qty          = isset( $cart_item['quantity'] ) ? $cart_item['quantity'] : 1;
							$item_total        = apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $__product, $cart_item['quantity'] ), $cart_item, $cart_item_key );
							$attributes        = '';
							if ( ! empty( $cart_item['variation'] ) ) {
								$attrs = array();
								foreach ( $cart_item['variation'] as $name => $value ) {
									$attrs[] = esc_html( $value );
								}
								$attributes = implode( ' / ', $attrs );
							}
						?>
							<div class="flex items-start gap-4">
								<div class="flex-shrink-0 w-16 h-16 rounded-xl overflow-hidden bg-surface-container">
									<?php if ( $thumbnail ) : ?>
										<?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									<?php else : ?>
										<div class="w-full h-full flex items-center justify-center">
											<span class="material-symbols-outlined text-on-surface-variant text-2xl">image</span>
										</div>
									<?php endif; ?>
								</div>
								<div class="flex-1 min-w-0">
									<p class="text-on-surface font-medium text-sm leading-tight truncate">
										<?php echo esc_html( $product_name ); ?>
									</p>
									<?php if ( $attributes ) : ?>
										<p class="text-on-surface-variant text-xs mt-0.5"><?php echo esc_html( $attributes ); ?></p>
									<?php endif; ?>
									<p class="text-on-surface-variant text-xs mt-1">
										<?php
										printf(
											esc_html__( 'Qty: %d', 'dragon-glow' ),
											(int) $item_qty
										);
										?>
									</p>
								</div>
								<div class="flex-shrink-0 text-right">
									<p class="text-on-surface font-medium text-sm">
										<?php echo $item_total; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									</p>
								</div>
							</div>
						<?php endforeach; ?>
					<?php endif; ?>
				</div><!-- end #dg-order-items -->

				<!-- Totals -->
				<div class="space-y-2 pt-4 border-t border-outline-variant/30">
					<div class="flex justify-between text-sm text-on-surface-variant">
						<span><?php esc_html_e( 'Subtotal', 'dragon-glow' ); ?></span>
						<span id="dg-summary-subtotal">
							<?php
							if ( WC()->cart ) {
								echo wp_kses_post( WC()->cart->get_cart_subtotal() );
							} else {
								echo esc_html( wc_price( 0 ) );
							}
							?>
						</span>
					</div>
					<div class="flex justify-between text-sm text-on-surface-variant">
						<span><?php esc_html_e( 'Shipping', 'dragon-glow' ); ?></span>
						<span id="dg-summary-shipping">
							<?php
							if ( WC()->cart && WC()->cart->needs_shipping() ) {
								echo wp_kses_post( WC()->cart->get_shipping_total() > 0 ? wc_price( WC()->cart->get_shipping_total() ) : __( 'TBD', 'dragon-glow' ) );
							} else {
								echo esc_html__( 'Free', 'dragon-glow' );
							}
							?>
						</span>
					</div>
					<?php if ( WC()->cart && WC()->cart->get_total_tax() > 0 ) : ?>
						<div class="flex justify-between text-sm text-on-surface-variant">
							<span><?php esc_html_e( 'Tax', 'dragon-glow' ); ?></span>
							<span id="dg-summary-tax"><?php echo wp_kses_post( wc_price( WC()->cart->get_total_tax() ) ); ?></span>
						</div>
					<?php endif; ?>
					<div class="flex justify-between text-lg font-bold text-primary pt-3 border-t border-outline-variant/30 mt-2">
						<span><?php esc_html_e( 'Total', 'dragon-glow' ); ?></span>
						<span id="dg-summary-total">
							<?php
							if ( WC()->cart ) {
								echo wp_kses_post( WC()->cart->get_total() );
							} else {
								echo esc_html( wc_price( 0 ) );
							}
							?>
						</span>
					</div>
				</div>

				<?php
				/**
				 * Promo / coupon code — wired to WooCommerce's existing coupon system
				 * via the standard woocommerce_checkout_coupon_form action.
				 */
				?>
				<div class="mt-6 pt-6 border-t border-outline-variant/20">
					<?php do_action( 'woocommerce_checkout_coupon_form' ); ?>
				</div>

				<!-- Trust badges -->
				<div class="mt-6 pt-6 border-t border-outline-variant/20 space-y-3">
					<div class="flex items-center gap-3 text-sm text-on-surface-variant">
						<span class="material-symbols-outlined text-primary text-lg flex-shrink-0">lock</span>
						<span><?php esc_html_e( 'Secure 256-bit SSL encryption', 'dragon-glow' ); ?></span>
					</div>
					<div class="flex items-center gap-3 text-sm text-on-surface-variant">
						<span class="material-symbols-outlined text-primary text-lg flex-shrink-0">local_shipping</span>
						<span><?php esc_html_e( 'Free shipping on orders over $75', 'dragon-glow' ); ?></span>
					</div>
					<div class="flex items-center gap-3 text-sm text-on-surface-variant">
						<span class="material-symbols-outlined text-primary text-lg flex-shrink-0">replay_circle_filled</span>
						<span><?php esc_html_e( '30-day money-back guarantee', 'dragon-glow' ); ?></span>
					</div>
				</div>

				<!-- WooCommerce #order_review lives here so its AJAX update triggers
				     cart/shipping/coupon refreshes in the normal WC way. -->
				<div id="order_review">
					<?php do_action( 'woocommerce_checkout_order_review' ); ?>
				</div>

			</div><!-- end .glass-card -->
		</div><!-- end .lg-col-span-2 -->

	</div><!-- end grid -->
</div>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
