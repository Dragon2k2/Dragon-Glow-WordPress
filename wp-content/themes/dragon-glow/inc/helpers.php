<?php
/**
 * Dragon Glow — Helper Functions (loader).
 *
 * Thin loader for the shared helper layer. Helpers are grouped by concern under
 * `inc/helpers/` so each area is easy to locate:
 *
 *   - woocommerce.php    WooCommerce checks, categories, price.
 *   - ui-components.php  Presentational render helpers (stars, breadcrumb, SVG).
 *   - utilities.php      Generic string + customizer helpers.
 *
 * Loaded first in functions.php so every other module can rely on these
 * (notably dg_is_woocommerce_active()).
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

// Load helper modules (woocommerce first — provides dg_is_woocommerce_active()).
require_once DG_DIR . '/inc/helpers/woocommerce.php';
require_once DG_DIR . '/inc/helpers/wishlist.php';
require_once DG_DIR . '/inc/helpers/ui-components.php';
require_once DG_DIR . '/inc/helpers/utilities.php';