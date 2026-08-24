<?php
/**
 * Dragon Glow — WooCommerce Integration
 *
 * Thin loader for the WooCommerce integration layer. All hooks and filters
 * live in the per-concern files under `inc/woocommerce/` so each page/area is
 * easy to locate and maintain:
 *
 *   - general.php          Site-wide wrapper, sidebar, style dequeue, button text.
 *   - shop.php             Product loop / archive customizations + dropdown filter.
 *   - single-product.php   Single product page (thumbnails, related products).
 *   - cart.php             Cart fragments + selected-size line item.
 *   - checkout.php         Checkout fields, classic template, custom renderer.
 *   - checkout-locale.php  Country states, locale field rules, cache clearing.
 *   - bacs-qr.php          BACS Direct Bank Transfer — inline EMVCo QR panel
 *                          shared between the checkout form and the order-
 *                          received ("Thank You") page.
 *   - gateways.php         Permanent front-end disabling of Check Payments
 *                          gateway (Cheque).
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

// Load per-concern WooCommerce integration modules.
// Note: We load these files unconditionally so that function definitions are
// always available (e.g., for AJAX handlers in inc/ajax/account.php that call
// dg_render_account_orders_panel()). Each function guards itself by checking
// WC availability via function_exists('wc_...') or dg_is_woocommerce_active().
require_once DG_DIR . '/inc/woocommerce/general.php';
require_once DG_DIR . '/inc/woocommerce/account.php';
require_once DG_DIR . '/inc/woocommerce/shop.php';
require_once DG_DIR . '/inc/woocommerce/single-product.php';
require_once DG_DIR . '/inc/woocommerce/cart.php';
require_once DG_DIR . '/inc/woocommerce/checkout.php';
require_once DG_DIR . '/inc/woocommerce/checkout-locale.php';
require_once DG_DIR . '/inc/woocommerce/bacs-qr.php';
require_once DG_DIR . '/inc/woocommerce/gateways.php';
