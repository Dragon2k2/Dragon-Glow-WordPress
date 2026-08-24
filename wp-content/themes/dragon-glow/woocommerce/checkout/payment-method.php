<?php
/**
 * Payment method item — Dragon Glow override.
 *
 * Enhanced visual treatment for each payment gateway:
 *   - Glassmorphism card with icon + title
 *   - Smooth expand/collapse for description (Motion API)
 *   - Active state with primary accent
 *
 * @see WC_Payment_Gateway
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$icon_map = array(
	'cod'         => 'payments',
	'bacs'        => 'account_balance',
	'paypal'      => 'credit_card',
	'stripe'      => 'credit_card',
	'square'      => 'credit_card',
	'default'     => 'payment',
);

$gateway_id   = esc_attr( $gateway->id );
$gateway_icon = isset( $icon_map[ $gateway->id ] ) ? $icon_map[ $gateway->id ] : $icon_map['default'];
?>

<li class="dg-payment-method wc_payment_method payment_method_<?php echo $gateway_id; ?>" data-payment-id="<?php echo $gateway_id; ?>">
	<label class="dg-payment-label" for="payment_method_<?php echo $gateway_id; ?>">
		<input
			id="payment_method_<?php echo $gateway_id; ?>"
			type="radio"
			class="input-radio dg-payment-radio"
			name="payment_method"
			value="<?php echo $gateway_id; ?>"
			<?php checked( $gateway->chosen, true ); ?>
			data-order_button_text="<?php echo esc_attr( $gateway->order_button_text ); ?>"
		/>

		<div class="dg-payment-card">
			<div class="dg-payment-header">
				<span class="material-symbols-outlined dg-payment-icon" aria-hidden="true">
					<?php echo esc_html( $gateway_icon ); ?>
				</span>
				<span class="dg-payment-title">
					<?php echo wp_kses_post( $gateway->get_title() ); ?>
				</span>
				<?php if ( $gateway->get_icon() ) : ?>
					<span class="dg-payment-logo">
						<?php echo wp_kses_post( $gateway->get_icon() ); ?>
					</span>
				<?php endif; ?>
				<span class="dg-payment-checkmark material-symbols-outlined" aria-hidden="true">check_circle</span>
			</div>

			<?php if ( $gateway->has_fields() || ! empty( $gateway->description ) ) : ?>
				<div class="dg-payment-description" data-payment-desc>
				<?php
				/*
				 * Output the gateway description. Note: payment_fields() is NOT called here.
				 * Most gateways (BACS, COD, Cheque) only output the description via payment_fields(),
				 * which we handle manually below. Stripe/PayPal render their own JS-powered fields
				 * independently. Calling payment_fields() for broken 3rd-party gateway plugins
				 * (Stripe, PayPal, Square) can throw fatal errors that corrupt the entire
				 * checkout page output, so we skip it entirely.
				 */

				// Output description if present
				if ( ! empty( $gateway->description ) ) {
					echo wp_kses_post( wpautop( wptexturize( $gateway->description ) ) );
				}
				?>

				</div>
			<?php endif; ?>
		</div>
	</label>
</li>
