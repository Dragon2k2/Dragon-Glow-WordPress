<?php
/**
 * Dragon Glow — functions.php
 * Entry point: chỉ require các file inc/
 * Không đặt logic trực tiếp ở đây.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

// Define theme constants
define( 'DG_VERSION',   '1.1.70' );
define( 'DG_DIR',       get_template_directory() );
define( 'DG_URI',       get_template_directory_uri() );

// Load core files
require_once DG_DIR . '/inc/setup.php';
require_once DG_DIR . '/inc/nav-setup.php';
require_once DG_DIR . '/inc/helpers.php';           // Must load first — all other files depend on dg_is_woocommerce_active()
require_once DG_DIR . '/inc/enqueue.php';
require_once DG_DIR . '/inc/woocommerce.php';
require_once DG_DIR . '/inc/widgets.php';
require_once DG_DIR . '/inc/ajax-handlers.php';
require_once DG_DIR . '/inc/approval-handler.php';  // careers approval token routing (Cách A — email-only)
require_once DG_DIR . '/inc/shop-customizer.php';

// Load product abstraction layer
require_once DG_DIR . '/inc/products/class-dg-product.php';
require_once DG_DIR . '/inc/products/class-dg-woocommerce-product-repository.php';
require_once DG_DIR . '/inc/products/class-dg-product-factory.php';

// Load checkout concern (WooCommerce + Thank You endpoint fix)
require_once DG_DIR . '/inc/checkout.php';

// Load shared cart utility functions
require_once DG_DIR . '/inc/cart-functions.php';

