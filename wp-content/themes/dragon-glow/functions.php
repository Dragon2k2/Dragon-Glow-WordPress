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
define( 'DG_VERSION',   '1.0.1' );
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
require_once DG_DIR . '/inc/mock-product-sync.php';
require_once DG_DIR . '/inc/shop-customizer.php';

// Load product abstraction layer
require_once DG_DIR . '/inc/products/class-dg-product.php';
require_once DG_DIR . '/inc/products/class-dg-mock-product-repository.php';
require_once DG_DIR . '/inc/products/class-dg-woocommerce-product-repository.php';
require_once DG_DIR . '/inc/products/class-dg-product-factory.php';

// Load checkout routing layer
require_once DG_DIR . '/inc/checkout/class-dg-woocommerce-checkout-handler.php';
require_once DG_DIR . '/inc/checkout/class-dg-mock-checkout-handler.php';
require_once DG_DIR . '/inc/checkout/class-dg-checkout-router.php';
