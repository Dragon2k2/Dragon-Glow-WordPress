<?php
/**
 * Dragon Glow — Payment Gateway Gating
 *
 * Permanently hides two gateways from the front-end checkout:
 *
 *   1. Cheque  — unsuitable for D2C cosmetics (industry adoption <0.1%,
 *                manual weekly reconciliation, doubles fulfillment cost vs.
 *                BACS QR + digital payments for the same "pay later" intent).
 *
 *   2. COD     — hidden from US customers (the only shipping zone Dragon Glow
 *                currently serves). Rationale (enterprise D2C consensus 2026):
 *                  - High RTO rate (3–5× pre-paid) drives up logistics costs.
 *                  - COD fraud is the #1 vector for D2C chargebacks.
 *                  - US regulatory risk: FTC + state-level rules make any
 *                    "surcharge for payment method" murky, so a no-fee
 *                    option that loses money per order is worse than
 *                    removing it.
 *                  - Sephora, Glossier, Drunk Elephant all disable COD in US.
 *                  - Stripe, Braintree, Square remain available — cards cover
 *                    the entire US market need for "instant checkout".
 *
 * Both gateways remain visible in WP Admin → WooCommerce → Settings →
 * Payments so the operator can audit their internal settings if needed;
 * they're only hidden from customers at checkout.
 *
 * Implementation notes:
 *   - Uses `woocommerce_available_payment_gateways` (runtime list) so the
 *     gateway still appears in WC admin.
 *   - `is_admin()` guard prevents breaking the admin settings screen.
 *   - To re-enable either gateway: wrap its `unset()` in a feature flag or
 *     comment out the `add_filter( ... )` line below.
 *
 * Future COD rollout (if Dragon Glow ships to COD-heavy markets like IN/AE/VN):
 *   - Replace this with `inc/woocommerce/cod-gate.php` (new file) + move
 *     this filter into a per-country helper `dg_cod_is_eligible()`.
 *   - Do NOT simply re-add `'cod' => $gateways['cod']` here — the country
 *     gating + cart-total gating + customer-history gating belongs in its
 *     own concern, not mixed with admin-hide toggles.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Gateways hidden from the front-end checkout.
 *
 * Add a gateway id here to remove it from the customer's payment list at
 * runtime. The admin settings screen is unaffected (`is_admin()` guard).
 *
 * Keep this list in sync with the docblock above — both lists are the
 * single source of truth for "what is intentionally hidden".
 *
 * @return array
 */
function dg_hidden_front_end_gateway_ids(): array {
	return array(
		'cheque', // Industry adoption <0.1%; manual reconciliation; doubles fulfillment cost.
		'cod',    // US D2C consensus 2026: disable. Re-enable per-country, not globally.
	);
}

/**
 * Remove hidden gateways from the front-end checkout.
 *
 * @param array $available_gateways Map of gateway_id => WC_Payment_Gateway.
 * @return array
 */
function dg_hide_front_end_gateways( $available_gateways ) {
	if ( is_admin() ) {
		return $available_gateways;
	}

	foreach ( dg_hidden_front_end_gateway_ids() as $gateway_id ) {
		if ( isset( $available_gateways[ $gateway_id ] ) ) {
			unset( $available_gateways[ $gateway_id ] );
		}
	}

	return $available_gateways;
}
add_filter( 'woocommerce_available_payment_gateways', 'dg_hide_front_end_gateways', 20 );
