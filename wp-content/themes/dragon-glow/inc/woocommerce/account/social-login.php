<?php
/**
 * Dragon Glow — Social Login (Loader)
 *
 * Thin loader for the "Alternative Access" (Google / Apple Sign-In) concern
 * on the signed-out My Account auth gate. Per the folder-loader convention
 * (CLAUDE.md §5a), each file handles one responsibility:
 *
 *   - config.php       wp-config.php credential constants + enabled-state
 *                       checks + URL builders.
 *   - jwt-helpers.php  Dependency-free JWT/JWK sign+verify primitives
 *                       (openssl only — no Composer JWT library).
 *   - user-mapping.php Find-or-create WP user from a verified provider
 *                       identity + login/redirect/fail helpers.
 *   - google.php       Google OAuth 2.0 authorization-code flow.
 *   - apple.php        Sign in with Apple OAuth flow (ES256 client secret,
 *                       RS256 ID-token verification against Apple's JWKs).
 *   - routing.php       `template_redirect` routes: start flow + shared
 *                       callback dispatch.
 *
 * Buttons stay disabled in login.php until the matching provider's
 * wp-config.php constants are fully defined — see
 * dg_social_login_google_enabled() / dg_social_login_apple_enabled().
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

require_once DG_DIR . '/inc/woocommerce/account/social-login/config.php';
require_once DG_DIR . '/inc/woocommerce/account/social-login/jwt-helpers.php';
require_once DG_DIR . '/inc/woocommerce/account/social-login/user-mapping.php';
require_once DG_DIR . '/inc/woocommerce/account/social-login/google.php';
require_once DG_DIR . '/inc/woocommerce/account/social-login/apple.php';
require_once DG_DIR . '/inc/woocommerce/account/social-login/routing.php';
