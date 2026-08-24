<?php
/**
 * Dragon Glow — AJAX Handlers (loader).
 *
 * Thin loader for the AJAX layer. Every handler lives in a per-concern file
 * under `inc/ajax/` so each feature is easy to locate and maintain:
 *
 *   - wishlist.php       Wishlist toggle (logged-in users).
 *   - newsletter.php     Newsletter subscription.
 *   - contact.php        Contact form submission + admin email.
 *   - cart.php           Cart AJAX: add, buy-now, remove, update, count, identifiers.
 *   - reviews.php        Product review submission (WooCommerce).
 *   - returns.php        Return request submission + admin/customer emails.
 *   - brevo.php          Brevo transactional email API (shared by careers).
 *   - careers.php        Careers application flow (email-only, no CPT).
 *   - dev-endpoints.php  Brevo one-time setup / diagnostic endpoints (manage_options).
 *
 * Careers approval/rejection routing is handled in inc/approval-handler.php by token.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

// Load per-concern AJAX handler modules.
require_once DG_DIR . '/inc/ajax/wishlist.php';
require_once DG_DIR . '/inc/ajax/newsletter.php';
require_once DG_DIR . '/inc/ajax/contact.php';
require_once DG_DIR . '/inc/ajax/cart.php';
require_once DG_DIR . '/inc/ajax/reviews.php';
require_once DG_DIR . '/inc/ajax/returns.php';
require_once DG_DIR . '/inc/ajax/brevo.php';       // Must load before careers.php (careers uses dg_send_brevo_email()).
require_once DG_DIR . '/inc/ajax/careers.php';
require_once DG_DIR . '/inc/ajax/account.php';
require_once DG_DIR . '/inc/ajax/dev-endpoints.php';