<?php
/**
 * Dragon Glow — Cart Functions (loader)
 *
 * Thin loader for cart-related helpers. Each concern lives in its own file
 * under `inc/cart/`:
 *
 *   - page-setup.php         Ensure WC Cart page exists + intercept requests.
 *   - operations.php         Add/remove from cart helpers.
 *   - url.php                Build cart/checkout URLs with safe fallbacks.
 *   - identifiers.php        Cart count, IDs, badge render, variation finder.
 *   - template-redirect.php  Serve the custom WC cart template directly.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

require_once DG_DIR . '/inc/cart/page-setup.php';
require_once DG_DIR . '/inc/cart/url.php';
require_once DG_DIR . '/inc/cart/operations.php';
require_once DG_DIR . '/inc/cart/identifiers.php';
require_once DG_DIR . '/inc/cart/template-redirect.php';
