<?php
/**
 * Dragon Glow — Checkout loader.
 *
 * Folder loader for the `inc/checkout/` concern (per §5a of CLAUDE.md).
 * Pulls in all WooCommerce checkout-related classes. Loader does NOT contain
 * any logic — only require_once the children.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

require_once DG_DIR . '/inc/checkout/class-dg-thankyou.php';
require_once DG_DIR . '/inc/checkout/class-dg-woocommerce-checkout-handler.php';
