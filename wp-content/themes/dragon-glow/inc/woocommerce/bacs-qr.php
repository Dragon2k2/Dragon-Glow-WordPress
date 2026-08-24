<?php
/**
 * Dragon Glow — WooCommerce: BACS QR Code Payment
 *
 * Inline QR code panel for the Direct Bank Transfer (BACS) gateway.
 * Generates an EMVCo-style QR code (US default) so customers can scan
 * with their banking app on mobile and have the amount + recipient
 * pre-filled.
 *
 * The shop is US-based, so the active merchant account is the US bank.
 * When the customer selects a non-US country, the QR is hidden and the
 * manual wire-transfer instructions take over (the shop does not have a
 * local merchant account in that country).
 *
 *   - Customizer settings (admin → Appearance → Customize → Payment → BACS QR)
 *     expose bank account fields so the owner can update them without a
 *     developer round-trip.
 *   - JS reads bank info + cart total via `dgBacsQr` (localized) and
 *     generates the QR client-side via a CDN library (no server round-trip).
 *   - Country change is observed on `#billing_country` / `#shipping_country`
 *     and the panel is swapped in-place (no `updated_checkout` AJAX needed).
 *
 * Two render contexts share this panel:
 *   1. Checkout form (`dg_bacs_qr_panel()` with no args) — pre-order, the
 *      QR encodes the live cart total + a placeholder order ID.
 *   2. Order-received page (`dg_bacs_qr_panel( $order )`) — post-order, the
 *      QR encodes the real order total + the placed order's ID/number.
 *      `WC()->cart` is empty by then, so all amounts/country are pulled
 *      directly from the order.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Customizer settings for BACS QR.
 *
 * Fields exposed to admin (Appearance → Customize → Payment → BACS QR).
 * Defaults are safe placeholders that render a valid QR even if the
 * owner hasn't configured the panel yet.
 *
 * @param WP_Customize_Manager $wp_customize Customizer.
 * @return void
 */
function dg_bacs_qr_customize_register( WP_Customize_Manager $wp_customize ): void {

	$wp_customize->add_section(
		'dg_bacs_qr',
		array(
			'title'       => __( 'Checkout — BACS QR Code', 'dragon-glow' ),
			'priority'    => 35,
			'description' => __( 'Configure the merchant bank account shown in the Direct Bank Transfer QR panel at checkout. Only US customers will see the QR — other countries will see manual wire-transfer instructions.', 'dragon-glow' ),
		)
	);

	$settings = array(
		'dg_bacs_qr_enabled'           => array(
			'default' => true,
			'label'   => __( 'Enable QR code panel', 'dragon-glow' ),
			'type'    => 'checkbox',
		),
		'dg_bacs_qr_bank_name'         => array(
			'default' => __( 'JPMorgan Chase Bank, N.A.', 'dragon-glow' ),
			'label'   => __( 'Bank Name', 'dragon-glow' ),
			'type'    => 'text',
		),
		'dg_bacs_qr_account_name'      => array(
			'default' => __( 'Dragon Glow LLC', 'dragon-glow' ),
			'label'   => __( 'Account Holder Name', 'dragon-glow' ),
			'type'    => 'text',
		),
		'dg_bacs_qr_routing_number'    => array(
			'default' => '021000021',
			'label'   => __( 'Routing Number (ABA) — US', 'dragon-glow' ),
			'type'    => 'text',
		),
		'dg_bacs_qr_account_number'    => array(
			'default' => '000123456789',
			'label'   => __( 'Account Number', 'dragon-glow' ),
			'type'    => 'text',
		),
		'dg_bacs_qr_account_type'      => array(
			'default' => 'CHECKING',
			'label'   => __( 'Account Type (CHECKING / SAVINGS)', 'dragon-glow' ),
			'type'    => 'text',
		),
		'dg_bacs_qr_merchant_id'       => array(
			'default' => 'DRAGONGLOW',
			'label'   => __( 'Merchant Identifier (optional)', 'dragon-glow' ),
			'type'    => 'text',
		),
		'dg_bacs_qr_instruction_lines' => array(
			'default' => implode(
				"\n",
				array(
					__( 'Please use your Order ID as the payment reference.', 'dragon-glow' ),
					__( 'Your order will be processed once payment is confirmed (1–2 business days).', 'dragon-glow' ),
					__( 'Email support@dragonglow.com if you need assistance.', 'dragon-glow' ),
				)
			),
			'label'   => __( 'Customer Instructions (one line per item)', 'dragon-glow' ),
			'type'    => 'textarea',
		),
	);

	// Reuse the batched registration helper from shop-customizer.php.
	// Keep all helper registration logic in one place — same plumbing as
	// the shop sections (see inc/shop-customizer.php).
	if ( function_exists( 'dg_register_customizer_settings' ) ) {
		dg_register_customizer_settings( $wp_customize, 'dg_bacs_qr', $settings );
	}
}
add_action( 'customize_register', 'dg_bacs_qr_customize_register' );

/**
 * Render the BACS QR panel.
 *
 * Two render contexts share this template:
 *
 *   1. Checkout form        → call with no args. The panel reads the live
 *                            cart total + customer billing country from
 *                            `WC()->cart` / `WC()->customer`. Order ID is
 *                            left blank (the QR encodes a placeholder until
 *                            the customer places the order).
 *   2. Order-received page  → call with `WC_Order`. The cart is emptied by
 *                            the time we get here, so amounts + country +
 *                            order ID are pulled from the order itself.
 *                            This is the only way to embed the real order
 *                            number into the QR's Additional Data Field.
 *
 * Output is gated on:
 *   - Customizer enabled (default: on).
 *   - WooCommerce active — without WC we have no cart/order to read from.
 *
 * The QR is generated client-side by `checkout-bacs-qr.js` which reads
 * `dgBacsQr` (localized by `dg_enqueue_scripts_assets()`) and the inline
 * JSON payload below.
 *
 * @param WC_Order|false $order Placed order on the thank-you page; false/null
 *                             on the checkout page (falls back to WC()->cart).
 * @return void
 */
function dg_bacs_qr_panel( $order = false ): void {
	// Bail if Customizer disabled the panel.
	if ( ! get_theme_mod( 'dg_bacs_qr_enabled', true ) ) {
		return;
	}

	// Only render when WC is active — we need either a cart (checkout) or
	// the passed order (thank-you page) to read the amount + currency.
	if ( ! function_exists( 'WC' ) || ! WC() ) {
		return;
	}

	$has_order = $order instanceof WC_Order;
	$has_cart  = ( WC()->cart && ! WC()->cart->is_empty() );

	if ( ! $has_order && ! $has_cart ) {
		// Nothing to render — no order passed AND cart is empty.
		// (Can happen if someone hits the thank-you URL with an invalid order
		// id; we bail early to avoid the WC()->cart null-deference in the
		// original code path.)
		return;
	}

	// ── Source values: order takes priority, fall back to cart ──────────
	if ( $has_order ) {
		$cart_total = (string) $order->get_total();
		$currency   = $order->get_currency();
		$country    = $order->get_billing_country() ?: ( $order->get_shipping_country() ?: 'US' );
		$order_id   = (string) $order->get_order_number();
	} else {
		$cart_total = (string) WC()->cart->get_total( 'numeric' );
		$currency   = get_woocommerce_currency();
		$country    = WC()->customer ? WC()->customer->get_billing_country() : 'US';
		$order_id   = '';
	}

	$bank = array(
		'enabled'        => (bool) get_theme_mod( 'dg_bacs_qr_enabled', true ),
		'bank_name'      => (string) get_theme_mod( 'dg_bacs_qr_bank_name', __( 'JPMorgan Chase Bank, N.A.', 'dragon-glow' ) ),
		'account_name'   => (string) get_theme_mod( 'dg_bacs_qr_account_name', __( 'Dragon Glow LLC', 'dragon-glow' ) ),
		'routing_number' => (string) get_theme_mod( 'dg_bacs_qr_routing_number', '021000021' ),
		'account_number' => (string) get_theme_mod( 'dg_bacs_qr_account_number', '000123456789' ),
		'account_type'   => (string) get_theme_mod( 'dg_bacs_qr_account_type', 'CHECKING' ),
		'merchant_id'    => (string) get_theme_mod( 'dg_bacs_qr_merchant_id', 'DRAGONGLOW' ),
		'instructions'   => array_values(
			array_filter(
				array_map(
					'trim',
					explode(
						"\n",
						(string) get_theme_mod(
							'dg_bacs_qr_instruction_lines',
							''
						)
					)
				)
			)
		),
	);

	// Fallback: WordPress Customizer strips textarea defaults the first time
	// the user saves the panel — once saved empty, get_theme_mod() returns ''
	// indefinitely, and the instructions list never renders. Re-apply the
	// documented default so the UI is always populated with the three
	// reference lines. Override via Customizer still wins (this only fills
	// the array when the saved value is empty).
	if ( empty( $bank['instructions'] ) ) {
		$bank['instructions'] = array(
			__( 'Please use your Order ID as the payment reference.', 'dragon-glow' ),
			__( 'Your order will be processed once payment is confirmed (1–2 business days).', 'dragon-glow' ),
			__( 'Email support@dragonglow.com if you need assistance.', 'dragon-glow' ),
		);
	}
	?>
	<div
		class="dg-bacs-qr"
		data-bacs-qr-panel
		data-country="<?php echo esc_attr( $country ); ?>"
		role="region"
		aria-label="<?php esc_attr_e( 'Pay by bank transfer QR code', 'dragon-glow' ); ?>"
	>
		<div class="dg-bacs-qr__layout">
			<div class="dg-bacs-qr__canvas">
				<div
					class="dg-bacs-qr__code"
					data-bacs-qr-canvas
					aria-hidden="true"
				></div>
				<div class="dg-bacs-qr__badge" aria-hidden="true">
					<span class="material-symbols-outlined">qr_code_2</span>
				</div>
			</div>

			<div class="dg-bacs-qr__body">
				<p class="dg-bacs-qr__eyebrow">
					<span class="material-symbols-outlined" aria-hidden="true">smartphone</span>
					<?php esc_html_e( 'Scan to pay in your banking app', 'dragon-glow' ); ?>
				</p>
				<h4 class="dg-bacs-qr__title">
					<?php esc_html_e( 'Pay instantly with your phone', 'dragon-glow' ); ?>
				</h4>
				<p class="dg-bacs-qr__amount">
					<span class="dg-bacs-qr__amount-label"><?php esc_html_e( 'Amount due', 'dragon-glow' ); ?></span>
					<span class="dg-bacs-qr__amount-value" data-bacs-qr-amount>
						<?php
						// Order total is already a numeric string; cart total may
						// include tax/shipping and needs wc_price() formatting.
						if ( $has_order ) {
							echo esc_html( wp_strip_all_tags( wc_price( $cart_total, array( 'currency' => $currency ) ) ) );
						} else {
							echo esc_html( wp_strip_all_tags( wc_price( $cart_total ) ) );
						}
						?>
					</span>
				</p>

				<details class="dg-bacs-qr__details">
					<summary>
						<span class="material-symbols-outlined" aria-hidden="true">account_balance</span>
						<?php esc_html_e( 'Use a desktop instead? View manual bank details', 'dragon-glow' ); ?>
						<span class="material-symbols-outlined dg-bacs-qr__chevron" aria-hidden="true">expand_more</span>
					</summary>
					<dl class="dg-bacs-qr__bank">
						<div class="dg-bacs-qr__row">
							<dt><?php esc_html_e( 'Bank', 'dragon-glow' ); ?></dt>
							<dd data-bacs-qr-bank-name><?php echo esc_html( $bank['bank_name'] ); ?></dd>
						</div>
						<div class="dg-bacs-qr__row">
							<dt><?php esc_html_e( 'Account name', 'dragon-glow' ); ?></dt>
							<dd data-bacs-qr-account-name><?php echo esc_html( $bank['account_name'] ); ?></dd>
						</div>
						<div class="dg-bacs-qr__row">
							<dt><?php esc_html_e( 'Account type', 'dragon-glow' ); ?></dt>
							<dd data-bacs-qr-account-type><?php echo esc_html( $bank['account_type'] ); ?></dd>
						</div>
						<div class="dg-bacs-qr__row">
							<dt><?php esc_html_e( 'Routing number', 'dragon-glow' ); ?></dt>
							<dd data-bacs-qr-routing-number><?php echo esc_html( $bank['routing_number'] ); ?></dd>
						</div>
						<div class="dg-bacs-qr__row">
							<dt><?php esc_html_e( 'Account number', 'dragon-glow' ); ?></dt>
							<dd data-bacs-qr-account-number><?php echo esc_html( $bank['account_number'] ); ?></dd>
						</div>
					</dl>
				</details>

				<?php if ( ! empty( $bank['instructions'] ) ) : ?>
					<ul class="dg-bacs-qr__instructions">
						<?php foreach ( $bank['instructions'] as $line ) : ?>
							<li><?php echo esc_html( $line ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>

				<div class="dg-bacs-qr__countries" aria-live="polite">
					<span class="material-symbols-outlined dg-bacs-qr__countries-icon" aria-hidden="true">public</span>
					<p>
						<span class="dg-bacs-qr__countries-label">
							<?php esc_html_e( 'Detected region:', 'dragon-glow' ); ?>
						</span>
						<strong class="dg-bacs-qr__country-name--server" data-bacs-qr-country-name>
							<?php
							// Country names are WC's full locale list; `WC()->countries` exposes them.
							// Server renders the full name as the primary source of truth.
							$countries = WC()->countries ? WC()->countries->get_countries() : array();
							echo esc_html( $countries[ $country ] ?? __( 'United States', 'dragon-glow' ) );
							?>
						</strong>
						<span class="dg-bacs-qr__countries-note" data-bacs-qr-country-note>
							<?php esc_html_e( 'QR code is available for US customers.', 'dragon-glow' ); ?>
						</span>
					</p>
				</div>
			</div>
		</div>

		<!--
			Hidden payload, read by JS to compose the EMVCo TLV string.
			Mirrors the rendered fields above so the JS never re-derives
			values from DOM (single source of truth = Customizer settings).
			On the order-received page this carries the real order ID so
			the QR encodes it in the Additional Data Field (Tag 62 / sub 05).
		-->
		<script type="application/json" data-bacs-qr-payload class="dg-bacs-qr__payload"><?php
			echo wp_json_encode(
				array(
					'bank'         => $bank,
					'cart_total'   => $cart_total,
					'currency'     => $currency,
					'country'      => $country,
					'order_id'     => $order_id,
					'shop_name'    => get_bloginfo( 'name' ),
					'home_country' => 'US',
				)
			);
		?></script>
	</div>
	<?php
}
add_action( 'dg_bacs_qr_panel', 'dg_bacs_qr_panel' );
