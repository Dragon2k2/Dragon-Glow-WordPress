<?php
/**
 * Dragon Glow — WooCommerce: My Account (Loader)
 *
 * Thin loader for the My Account integration layer. All hooks, filters, and
 * renderers live in the sibling per-concern files in this same folder
 * (`inc/woocommerce/account/`) so each area (routing, register
 * endpoint/handler, dashboard, orders, addresses, account-details,
 * address-details, edit-account, signed-out gate, full register page) is easy
 * to locate and maintain:
 *
 *   - routing.php                URL helpers, endpoint detection, template_include
 *                                swap, document title filter, body class.
 *   - register-endpoint.php      Registers the `register` WC rewrite endpoint +
 *                                admin flush-permalinks notice.
 *   - register-validation.php    Server-side validation of every register-form
 *                                field; collects all errors in one pass.
 *   - register-handler.php       Processes the registration form submission.
 *   - dashboard.php              Hero, sidebar nav, dashboard panel, master
 *                                `dg_render_wc_account()` dispatcher, confirm modal.
 *   - account-details-endpoint.php
 *                                Registers the `account-details` WC rewrite
 *                                endpoint + admin flush-permalinks notice.
 *   - address-details-endpoint.php
 *                                Registers the `address-details` WC rewrite
 *                                endpoint + admin flush-permalinks notice.
 *   - orders.php                 `/my-account/orders/` panel.
 *   - addresses.php              `/my-account/edit-address/` edit views (billing/shipping forms).
 *   - account-details.php        `/my-account/account-details/` read-only view
 *                                panel (Stat Tiles Overview).
 *   - address-details.php        `/my-account/address-details/` read-only addresses
 *                                list panel (billing + shipping cards).
 *   - edit-account.php           `/my-account/edit-account/` edit form panel.
 *   - login.php                  "Heritage Atelier" auth gate (signed-out users).
 *   - register.php               Full `/my-account/register/` page.
 *   - social-login.php           Google / Apple Sign-In (OAuth) — loader for
 *                                inc/woocommerce/account/social-login/*.
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

require_once __DIR__ . '/routing.php';
require_once __DIR__ . '/register-endpoint.php';
require_once __DIR__ . '/register-validation.php';
require_once __DIR__ . '/register-handler.php';
require_once __DIR__ . '/account-details-endpoint.php';
require_once __DIR__ . '/address-details-endpoint.php';
require_once __DIR__ . '/dashboard.php';
require_once __DIR__ . '/orders.php';
require_once __DIR__ . '/addresses.php';
require_once __DIR__ . '/account-details.php';
require_once __DIR__ . '/address-details.php';
require_once __DIR__ . '/edit-account.php';
require_once __DIR__ . '/login.php';
require_once __DIR__ . '/register.php';
require_once __DIR__ . '/social-login.php';
