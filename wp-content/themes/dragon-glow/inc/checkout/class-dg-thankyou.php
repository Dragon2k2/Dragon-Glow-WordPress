<?php
/**
 * Dragon Glow — Thank You endpoint routing.
 *
 * When a customer lands on `/checkout/order-received/{order_id}/?key=…`,
 * WooCommerce renders the configured checkout page (page 104) using the
 * `page-template-default` template (the regular `index.php` flow). That
 * template wraps the post content in `<article>` + `<h1>"Order received"</h1>`,
 * which clobbers the custom Dragon Glow layout rendered by our
 * `woocommerce/checkout/thankyou.php` override.
 *
 * This handler swaps the page template in via the `template_include` filter,
 * mirroring exactly the pattern already used by `dg_use_wc_checkout_template`
 * in `inc/woocommerce/checkout.php`. The new template
 * (`page-templates/template-wc-thankyou.php`) calls `get_header()`,
 * renders `checkout/thankyou.php` directly, then `get_footer()` — bypassing
 * the post-content shell entirely so the Dragon Glow block stands alone.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

class DG_Thankyou {

	/**
	 * Boot — wire filters.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_filter( 'template_include', array( __CLASS__, 'use_thankyou_page_template' ) );
	}

	/**
	 * Force `page-templates/template-wc-thankyou.php` for the order-received
	 * endpoint. Guarded with `did_action( 'wp' )` so `is_order_received_page()`
	 * returns a reliable value (see WC issue #63966 for the same guard on the
	 * checkout template).
	 *
	 * @param string $template Resolved template path.
	 * @return string
	 */
	public static function use_thankyou_page_template( string $template ): string {
		if ( ! did_action( 'wp' ) ) {
			return $template;
		}
		if ( ! function_exists( 'is_order_received_page' ) || ! is_order_received_page() ) {
			return $template;
		}

		$custom = locate_template( 'page-templates/template-wc-thankyou.php' );
		if ( $custom ) {
			return $custom;
		}

		return $template;
	}
}

DG_Thankyou::init();
