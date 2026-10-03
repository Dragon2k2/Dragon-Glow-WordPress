<?php
/**
 * Dragon Glow — WooCommerce: My Account (Loader)
 *
 * Thin loader for the My Account integration layer. All hooks, filters, and
 * renderers live in the per-concern files under `inc/woocommerce/account/`
 * so each area (routing, register endpoint/handler, dashboard, orders,
 * addresses, edit-account, signed-out gate, full register page) is easy to
 * locate and maintain:
 *
 *   - routing.php            URL helpers, endpoint detection, template_include
 *                            swap, document title filter, body class.
 *   - register-endpoint.php  Registers the `register` WC rewrite endpoint +
 *                            admin flush-permalinks notice.
 *   - register-handler.php   Processes the registration form submission.
 *   - dashboard.php          Hero, sidebar nav, dashboard panel, master
 *                            `dg_render_wc_account()` dispatcher, confirm modal.
 *   - orders.php             `/my-account/orders/` panel.
 *   - addresses.php          `/my-account/edit-address/` list + edit views,
 *                            "use billing instead" handler.
 *   - edit-account.php       `/my-account/edit-account/` panel.
 *   - signed-out.php         "Heritage Atelier" auth gate (signed-out users).
 *   - register-page.php      Full `/my-account/register/` page.
 *
 * Guards:
 *  - When WC is inactive, shows a friendly fallback (login form + register CTA).
 *  - When not logged in, renders the auth form.
 *
 * Note: We load these files unconditionally so that function definitions are
 * always available (e.g., for AJAX handlers in inc/ajax/account.php that call
 * dg_render_account_orders_panel()). Each function guards itself by checking
 * WC availability via function_exists('wc_...') or dg_is_woocommerce_active().
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

require_once DG_DIR . '/inc/woocommerce/account/routing.php';
require_once DG_DIR . '/inc/woocommerce/account/register-endpoint.php';
require_once DG_DIR . '/inc/woocommerce/account/register-handler.php';
require_once DG_DIR . '/inc/woocommerce/account/dashboard.php';
require_once DG_DIR . '/inc/woocommerce/account/orders.php';
require_once DG_DIR . '/inc/woocommerce/account/addresses.php';
require_once DG_DIR . '/inc/woocommerce/account/edit-account.php';
require_once DG_DIR . '/inc/woocommerce/account/signed-out.php';
require_once DG_DIR . '/inc/woocommerce/account/register-page.php';
