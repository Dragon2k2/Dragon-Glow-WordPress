<?php
/**
 * Dragon Glow — WooCommerce: Checkout
 *
 * Checkout-page customizations: field ordering/labels, forcing the classic
 * (shortcode) checkout template, the custom two-column checkout renderer, and
 * routing the checkout endpoint to the theme's page template.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Customize checkout fields to match luxury cosmetics industry standards.
 * Based on Sephora, Glossier, Drunk Elephant, Fenty Beauty checkout flows.
 *
 * Standard order:
 * 1. Email (for order confirmation)
 * 2. First name + Last name
 * 3. Country (determines state/province requirements)
 * 4. Street address
 * 5. Apartment/Suite (optional)
 * 6. City
 * 7. State/Province (when applicable)
 * 8. Postal code
 * 9. Phone (required for delivery)
 * 10. Order notes (optional)
 *
 * @param array $fields Checkout fields.
 * @return array
 */
function dg_customize_checkout_fields( array $fields ): array {
	// ═══════════════════════════════════════════════════════════════════════════
	// BILLING FIELDS — Contact & Address
	// ═══════════════════════════════════════════════════════════════════════════

	if ( isset( $fields['billing'] ) ) {

		// Email — priority 5 (first field)
		if ( isset( $fields['billing']['billing_email'] ) ) {
			$fields['billing']['billing_email']['priority']    = 5;
			$fields['billing']['billing_email']['required']    = true;
			$fields['billing']['billing_email']['label']       = __( 'Email', 'dragon-glow' );
			$fields['billing']['billing_email']['placeholder'] = __( 'you@example.com', 'dragon-glow' );
		}

		// First name — priority 10
		if ( isset( $fields['billing']['billing_first_name'] ) ) {
			$fields['billing']['billing_first_name']['priority']    = 10;
			$fields['billing']['billing_first_name']['required']    = true;
			$fields['billing']['billing_first_name']['placeholder'] = __( 'First name', 'dragon-glow' );
		}

		// Last name — priority 20
		if ( isset( $fields['billing']['billing_last_name'] ) ) {
			$fields['billing']['billing_last_name']['priority']    = 20;
			$fields['billing']['billing_last_name']['required']    = true;
			$fields['billing']['billing_last_name']['placeholder'] = __( 'Last name', 'dragon-glow' );
		}

		// Company — priority 25 (optional, for B2B)
		if ( isset( $fields['billing']['billing_company'] ) ) {
			$fields['billing']['billing_company']['priority']    = 25;
			$fields['billing']['billing_company']['required']    = false;
			$fields['billing']['billing_company']['placeholder'] = __( 'Company name (optional)', 'dragon-glow' );
		}

		// Country — priority 30
		if ( isset( $fields['billing']['billing_country'] ) ) {
			$fields['billing']['billing_country']['priority'] = 30;
			$fields['billing']['billing_country']['required'] = true;
		}

		// Address line 1 — priority 40
		if ( isset( $fields['billing']['billing_address_1'] ) ) {
			$fields['billing']['billing_address_1']['priority']    = 40;
			$fields['billing']['billing_address_1']['required']    = true;
			$fields['billing']['billing_address_1']['label']       = __( 'Address line 1', 'dragon-glow' );
			$fields['billing']['billing_address_1']['placeholder'] = __( 'House number and street name', 'dragon-glow' );
		}

		// Address line 2 — priority 50 (optional)
		if ( isset( $fields['billing']['billing_address_2'] ) ) {
			$fields['billing']['billing_address_2']['priority']    = 50;
			$fields['billing']['billing_address_2']['required']    = false;
			$fields['billing']['billing_address_2']['label']       = __( 'Address line 2', 'dragon-glow' );
			$fields['billing']['billing_address_2']['placeholder'] = __( 'Apartment, suite, unit, etc.', 'dragon-glow' );
		}

		// City — priority 60
		if ( isset( $fields['billing']['billing_city'] ) ) {
			$fields['billing']['billing_city']['priority']    = 60;
			$fields['billing']['billing_city']['required']    = true;
			$fields['billing']['billing_city']['placeholder'] = __( 'City', 'dragon-glow' );
		}

		// State — priority 70 (required for US/CA/AU)
		if ( isset( $fields['billing']['billing_state'] ) ) {
			$fields['billing']['billing_state']['priority'] = 70;
			$fields['billing']['billing_state']['required'] = true; // WC auto-hides for countries without states
		}

		// Postcode — priority 80
		if ( isset( $fields['billing']['billing_postcode'] ) ) {
			$fields['billing']['billing_postcode']['priority']    = 80;
			$fields['billing']['billing_postcode']['required']    = true;
			$fields['billing']['billing_postcode']['placeholder'] = __( 'Postal code', 'dragon-glow' );
		}

		// Phone — priority 90 (required for delivery)
		if ( isset( $fields['billing']['billing_phone'] ) ) {
			$fields['billing']['billing_phone']['priority']    = 90;
			$fields['billing']['billing_phone']['required']    = true;
			$fields['billing']['billing_phone']['label']       = __( 'Phone', 'dragon-glow' );
			$fields['billing']['billing_phone']['placeholder'] = __( 'Phone number', 'dragon-glow' );
		}
	}

	// ═══════════════════════════════════════════════════════════════════════════
	// ORDER NOTES — Move to billing section (priority 100)
	// ═══════════════════════════════════════════════════════════════════════════

	if ( isset( $fields['order']['order_comments'] ) ) {
		$fields['billing']['order_comments'] = $fields['order']['order_comments'];
		$fields['billing']['order_comments']['label']       = __( 'Order notes', 'dragon-glow' );
		$fields['billing']['order_comments']['placeholder'] = __( 'Gift message or special delivery instructions (optional)', 'dragon-glow' );
		$fields['billing']['order_comments']['required']    = false;
		$fields['billing']['order_comments']['priority']    = 100;
		unset( $fields['order']['order_comments'] );
	}

	// ═══════════════════════════════════════════════════════════════════════════
	// SHIPPING FIELDS — Remove to simplify (billing = shipping by default)
	// ═══════════════════════════════════════════════════════════════════════════

	// Keep shipping fields but hide "Ship to different address" by default
	// Users can still enable it if needed via WC settings

	return $fields;
}
add_filter( 'woocommerce_checkout_fields', 'dg_customize_checkout_fields', 20 );

/**
 * Force the classic (shortcode-based) checkout by short-circuiting the
 * `woocommerce_has_block_template` check that the WooCommerce Blocks plugin
 * uses to decide between rendering `<wc-checkout>` and the legacy
 * `checkout/form-checkout.php` template.
 *
 * Without this filter, WC 8.3+ renders block checkout which does not fire
 * `woocommerce_checkout_billing` / `woocommerce_checkout_shipping` — the
 * hooks our layout relies on. Return false specifically for the `checkout`
 * template so the rest of the WP block-template lookup stays untouched.
 *
 * The filter signature changed in WC 10.2.0: now takes
 * `( $has_template, $template_name )`. We accept both signatures.
 *
 * @param bool             $has_template Whether a block template exists.
 * @param string|null      $template_name Template name (only on WC 10.2+).
 * @return bool
 */
function dg_force_classic_checkout_template( $has_template, $template_name = null ) {
	if ( ! did_action( 'wp' ) ) {
		return $has_template;
	}
	if ( function_exists( 'is_checkout' ) && is_checkout() && ! is_order_received_page() ) {
		if ( null === $template_name || 'checkout' === $template_name ) {
			return false;
		}
	}
	return $has_template;
}
add_filter( 'woocommerce_has_block_template', 'dg_force_classic_checkout_template', 20, 2 );

/**
 * Render the WooCommerce checkout form.
 *
 * Two-column layout: left = billing/shipping fields, right = order summary
 * card + trust signals. Called by `page-templates/template-wc-checkout.php` so the
 * checkout endpoint bypasses `index.php`'s `<article>` + page-title shell.
 *
 * Wraps the WC hooks in a `<form>` because the classic checkout form lives
 * in `templates/checkout/form-checkout.php` which is intentionally not
 * overridden here (we own the layout, but the WC `wc_get_template()` call
 * is bypassed by rendering the hooks directly).
 *
 * @return void
 */
function dg_render_wc_checkout(): void {
	?>
	<main class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-12">
		<h1 class="dg-checkout-title font-headline text-headline-lg text-primary mb-8">
			<span class="dg-checkout-title__icon dg-checkout-title__icon--gold" aria-hidden="true">
				<svg width="1em" height="1em" viewBox="0 0 32 32" fill="none" focusable="false">
					<path d="M7 13h18l-1.2 14a2 2 0 01-2 1.8H10.2a2 2 0 01-2-1.8L7 13z"
						fill="currentColor" fill-opacity="0.08"
						stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" />
					<path d="M11 13V10.5a5 5 0 0110 0V13"
						stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
					<path d="M16 23C 13.5 21 12 19.5 12 18C 12 16.5 13 15.5 14.5 15.5C 15.2 15.5 15.7 15.8 16 16.5C 16.3 15.8 16.8 15.5 17.5 15.5C 19 15.5 20 16.5 20 18C 20 19.5 18.5 21 16 23Z"
						fill="currentColor" />
				</svg>
			</span>
			<?php esc_html_e( 'Checkout', 'dragon-glow' ); ?>
		</h1>

		<form name="checkout" method="post" class="checkout woocommerce-checkout"
			action="<?php echo esc_url( wc_get_checkout_url() ); ?>"
			enctype="multipart/form-data">

			<div class="grid grid-cols-1 lg:grid-cols-3 gap-12">

				<div class="lg:col-span-2 space-y-8">
					<?php
					/*
					 * WC 10.x: WC Blocks / Store API hijack the
					 * `woocommerce_checkout_billing` and `woocommerce_checkout_shipping`
					 * actions so that — when fired through `do_action()` — they emit only
					 * <wc-order-attribution-inputs> elements instead of the actual billing
					 * and shipping form fields. That is why the left column previously
					 * rendered empty. Call the public WC_Checkout methods directly so we
					 * bypass the hook layer and still get the full legacy form rendered
					 * via wc_get_template( 'checkout/form-billing.php' / 'form-shipping.php' ).
					 */
					if ( class_exists( 'WC_Checkout' ) ) {
						$dg_wc_checkout = WC_Checkout::instance();
						$dg_wc_checkout->checkout_form_billing();
						$dg_wc_checkout->checkout_form_shipping();
					}
					do_action( 'woocommerce_checkout_after_customer_details' );
					?>

					<?php
					/*
					 * Payment Methods Section
					 * Enhanced glassmorphism design with smooth animations.
					 * Render only the payment-gateways list (radio inputs + description
					 * boxes). We intentionally do NOT call `woocommerce_checkout_payment()`
					 * because that would also emit WC's default place-order button — and
					 * we render our own styled button below.
					 */
					$dg_available_gateways = WC()->payment_gateways()->get_available_payment_gateways();
					?>

					<div class="bg-white rounded-3xl shadow-sm p-8">
						<h3 class="font-headline text-xl text-primary mb-6 flex items-center gap-2">
							<span class="material-symbols-outlined">credit_card</span>
							<?php esc_html_e( 'Payment Method', 'dragon-glow' ); ?>
						</h3>

						<?php if ( ! empty( $dg_available_gateways ) ) : ?>
							<ul class="dg-review-payment-methods wc_payment_methods payment_methods methods">
								<?php foreach ( $dg_available_gateways as $dg_gateway ) :
									wc_get_template( 'checkout/payment-method.php', array( 'gateway' => $dg_gateway ) );
								endforeach; ?>
							</ul>
						<?php else : ?>
							<div class="dg-review-no-payment" role="status">
								<?php esc_html_e( 'Sorry, it seems that there are no available payment methods. Please contact us if you require assistance or wish to make alternate arrangements.', 'dragon-glow' ); ?>
							</div>
						<?php endif; ?>

						<?php
						/*
						 * Nonce + referer hidden inputs. WC's `payment-method.php` does
						 * not emit these; without them the form submit silently fails the
						 * nonce check. Emit unconditionally so both branches (with and
						 * without gateways) work.
						 */
						wp_nonce_field( 'woocommerce-process_checkout', 'woocommerce-process-checkout-nonce' );
						wp_nonce_field( 'woocommerce-process_checkout', '_wp_http_referer', false );
						?>
					</div>
				</div>

				<div class="lg:col-span-1">
					<div class="bg-white rounded-3xl shadow-sm p-8 sticky top-24">
						<h2 class="font-headline text-xl text-primary mb-6">
							<?php esc_html_e( 'Order Summary', 'dragon-glow' ); ?>
						</h2>

						<?php
						/*
						 * Render the review-order template directly (so the sticky
						 * card contains only cart line items + totals — matching the
						 * mock layout). The `review-order.php` override in
						 * `woocommerce/checkout/` skips the default shop_table + payment
						 * block; we render those pieces explicitly below so we control
						 * their placement and styling.
						 */
						wc_get_template( 'checkout/review-order.php', array( 'checkout' => WC()->checkout() ) );
						?>

						<button type="submit"
								class="dg-place-order w-full py-4 rounded-2xl bg-primary text-on-primary font-bold uppercase tracking-widest text-sm flex items-center justify-center gap-2 hover:opacity-90 transition-all mt-6"
								name="woocommerce_checkout_place_order"
								id="place_order"
								value="<?php esc_attr_e( 'Place Order', 'dragon-glow' ); ?>">
							<span class="material-symbols-outlined">lock</span>
							<?php esc_html_e( 'Place Order', 'dragon-glow' ); ?>
						</button>

						<?php if ( ! empty( $dg_available_gateways ) ) : ?>
							<div class="dg-privacy-text mt-4 text-xs text-on-surface-variant">
								<?php esc_html_e( 'Your personal data will be used to process your order, support your experience throughout this website, and for other purposes described in our privacy policy.', 'dragon-glow' ); ?>
							</div>
						<?php endif; ?>

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

			</div>
		</form>
	</main>
	<?php
}

/**
 * Use `template-wc-checkout.php` for the WC checkout endpoint so the page
 * template assigned to the checkout page (e.g. "Default template") does
 * not wrap the form in `index.php`'s `<article>` + `<header><h1>` shell.
 *
 * Guarded with `did_action( 'wp' )` because `is_checkout()` can return
 * incorrect values before the main WP query is resolved (see WC issue
 * #63966). `template_include` runs after the main query is parsed, so the
 * check is normally safe, but the explicit guard avoids edge cases with
 * plugins that may load templates earlier than usual.
 *
 * @param string $template Resolved template path.
 * @return string
 */
function dg_use_wc_checkout_template( string $template ): string {
	if ( ! did_action( 'wp' ) ) {
		return $template;
	}
	if ( function_exists( 'is_checkout' ) && is_checkout() && ! is_order_received_page() ) {
		$custom = locate_template( 'page-templates/template-wc-checkout.php' );
		if ( $custom ) {
			return $custom;
		}
	}
	return $template;
}
add_filter( 'template_include', 'dg_use_wc_checkout_template' );

