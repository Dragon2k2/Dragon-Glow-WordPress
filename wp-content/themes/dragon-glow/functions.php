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
define( 'DG_VERSION',   '1.0.0' );
define( 'DG_DIR',       get_template_directory() );
define( 'DG_URI',       get_template_directory_uri() );

// Load core files
require_once DG_DIR . '/inc/setup.php';
require_once DG_DIR . '/inc/nav-setup.php';
require_once DG_DIR . '/inc/enqueue.php';
require_once DG_DIR . '/inc/woocommerce.php';
require_once DG_DIR . '/inc/widgets.php';
require_once DG_DIR . '/inc/ajax-handlers.php';
require_once DG_DIR . '/inc/helpers.php';
require_once DG_DIR . '/inc/shop-customizer.php';
