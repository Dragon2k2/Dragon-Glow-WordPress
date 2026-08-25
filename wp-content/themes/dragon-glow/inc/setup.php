<?php
/**
 * Dragon Glow — Theme Setup
 * Đăng ký theme supports, nav menus, image sizes, content width, excerpt.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Theme setup function.
 *
 * @return void
 */
function dg_theme_setup(): void {
    // Translations
    load_theme_textdomain( 'dragon-glow', DG_DIR . '/languages' );

    // WordPress features
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script'
    ) );
    add_theme_support( 'customize-selective-refresh-widgets' );

    // WooCommerce features — guarded so they don't fatal-error when WC is inactive.
    // Uses class_exists directly because helpers.php is not yet loaded at this point.
    if ( class_exists( 'WooCommerce', false ) ) {
        add_theme_support( 'wc-product-gallery-zoom' );
        add_theme_support( 'wc-product-gallery-lightbox' );
        add_theme_support( 'wc-product-gallery-slider' );

        add_theme_support( 'woocommerce', array(
            'thumbnail_image_width' => 600,
            'single_image_width'    => 900,
            'product_grid'          => array(
                'default_rows'    => 3,
                'min_rows'        => 1,
                'default_columns' => 4,
                'min_columns'     => 1,
                'max_columns'     => 6,
            ),
        ) );
    }

    // Navigation Menus
    register_nav_menus( array(
        'primary'     => __( 'Primary Navigation', 'dragon-glow' ),
        'footer-shop' => __( 'Footer — Shop Links', 'dragon-glow' ),
        'footer-info' => __( 'Footer — Company Links', 'dragon-glow' ),
        'footer-help' => __( 'Footer — Help Links', 'dragon-glow' ),
    ) );

    // Custom image sizes
    add_image_size( 'dg-product-card',   600, 750, true );   // aspect-ratio 4:5
    add_image_size( 'dg-product-hero',   900, 900, true );   // single product main
    add_image_size( 'dg-category-card',  800, 1000, true );  // category grid
    add_image_size( 'dg-hero',          1920, 1080, true );  // homepage hero

    // Custom header
    add_theme_support( 'custom-header', array(
        'default-image'      => '',
        'header-text'        => false,
        'flex-width'         => true,
        'flex-height'        => true,
        'default-preset'    => 'default',
    ) );

    // Custom logo
    add_theme_support( 'custom-logo', array(
        'height'      => 100,
        'width'       => 200,
        'flex-width'  => true,
        'flex-height' => true,
    ) );

    // Add support for editor styles
    add_theme_support( 'editor-styles' );

    // Add support for wide alignment
    add_theme_support( 'align-wide' );
}
add_action( 'after_setup_theme', 'dg_theme_setup' );

/**
 * Set the content width.
 *
 * @return void
 */
function dg_content_width(): void {
    $GLOBALS['content_width'] = 1280;
}
add_action( 'after_setup_theme', 'dg_content_width', 0 );

/**
 * Custom excerpt length.
 *
 * @param int $length Excerpt length.
 * @return int
 */
function dg_excerpt_length( int $length ): int {
    return 20;
}
add_filter( 'excerpt_length', 'dg_excerpt_length' );

/**
 * Custom excerpt more.
 *
 * @param string $more More string.
 * @return string
 */
function dg_excerpt_more( string $more ): string {
	return '&hellip;';
}
add_filter( 'excerpt_more', 'dg_excerpt_more' );

/**
 * Flush rewrite rules after theme activation.
 *
 * WooCommerce registers custom endpoints (orders, edit-address, edit-account,
 * downloads, etc.) that need rewrite rules. When switching to Dragon Glow,
 * we must flush rules so WordPress recognizes these endpoints and doesn't
 * return 404 on direct access or refresh.
 *
 * CRITICAL: We must wait for WooCommerce to register its query vars first,
 * otherwise flushing rewrite rules won't include WC endpoints. WooCommerce
 * registers query vars on 'init' priority 0, so we flush on 'init' priority 999.
 *
 * @return void
 */
function dg_flush_rewrite_rules_on_activation(): void {
	// Only flush if WooCommerce is active — otherwise there are no endpoints
	// to register and flushing would be pointless (and could cause issues).
	if ( class_exists( 'WooCommerce', false ) ) {
		// Set a flag to trigger flush on next 'init' — we can't flush immediately
		// because WooCommerce hasn't registered its query vars yet at
		// 'after_switch_theme' time.
		set_transient( 'dg_needs_rewrite_flush', true, 300 );
		
		// Set a transient to show admin notice after activation.
		set_transient( 'dg_permalinks_flushed', true, 60 );
	}
}
add_action( 'after_switch_theme', 'dg_flush_rewrite_rules_on_activation' );

/**
 * Purge W3 Total Cache (if active) after flushing rewrite rules.
 *
 * W3TC's Database/Object Cache can keep serving a stale copy of the
 * `rewrite_rules` option after `flush_rewrite_rules()` writes the new one,
 * which is what causes WooCommerce My Account endpoints (orders,
 * edit-address, etc.) to keep 404ing even right after re-saving permalinks.
 * Purging here makes the flush actually take effect immediately.
 *
 * @return void
 */
function dg_purge_w3tc_cache(): void {
	if ( function_exists( 'w3tc_flush_all' ) ) {
		w3tc_flush_all();
	} elseif ( has_action( 'w3tc_flush_all' ) ) {
		do_action( 'w3tc_flush_all' );
	}
}

/**
 * Perform deferred rewrite flush on init (after WC registers query vars).
 *
 * WooCommerce registers its endpoints on 'init' priority 0. We must flush
 * rewrite rules AFTER that, otherwise WordPress won't recognize WC endpoints
 * like /my-account/orders, /my-account/edit-address, etc.
 *
 * @return void
 */
function dg_deferred_rewrite_flush(): void {
	if ( get_transient( 'dg_needs_rewrite_flush' ) ) {
		// Ensure WC query vars are registered before flushing.
		if ( class_exists( 'WooCommerce', false ) && function_exists( 'WC' ) ) {
			$wc = WC();
			// Trigger WC query var registration if not done yet.
			if ( isset( $wc->query ) && is_object( $wc->query ) && method_exists( $wc->query, 'init_query_vars' ) ) {
				$wc->query->init_query_vars();
			}
			if ( isset( $wc->query ) && is_object( $wc->query ) && method_exists( $wc->query, 'add_endpoints' ) ) {
				$wc->query->add_endpoints();
			}
		}
		
		flush_rewrite_rules( false );
		dg_purge_w3tc_cache();
		delete_transient( 'dg_needs_rewrite_flush' );
	}
}
add_action( 'init', 'dg_deferred_rewrite_flush', 999 );

/**
 * Show admin notice to re-save permalinks if needed.
 *
 * WooCommerce endpoints (my-account/orders, my-account/edit-address, etc.)
 * require pretty permalinks and a valid .htaccess file. If .htaccess is
 * missing or permalinks are set to "Plain", endpoints will return 404 on
 * direct access or page refresh.
 *
 * @return void
 */
function dg_permalink_admin_notice(): void {
	// Only show to admins who can manage options.
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	
	// Check if we just flushed rewrite rules.
	$just_flushed = get_transient( 'dg_permalinks_flushed' );
	
	// Check permalink structure — empty means "Plain" (/?p=123).
	$permalink_structure = get_option( 'permalink_structure' );
	$is_plain            = empty( $permalink_structure );
	
	// Check if .htaccess exists (approximate check via get_home_path).
	$home_path    = function_exists( 'get_home_path' ) ? get_home_path() : ABSPATH;
	$htaccess_exists = file_exists( $home_path . '.htaccess' );
	
	// Show notice if: just activated theme, OR permalinks are Plain, OR no .htaccess.
	if ( $just_flushed || $is_plain || ! $htaccess_exists ) {
		$flush_url = add_query_arg( 'dg_action', 'flush_rewrites', admin_url() );
		$flush_url = wp_nonce_url( $flush_url, 'dg_flush_rewrites' );
		?>
		<div class="notice notice-warning is-dismissible">
			<p>
				<strong><?php esc_html_e( 'Dragon Glow Theme:', 'dragon-glow' ); ?></strong>
				<?php
				esc_html_e(
					'To ensure WooCommerce My Account pages work correctly (orders, addresses, etc.), please go to Settings → Permalinks and click "Save Changes" to regenerate rewrite rules.',
					'dragon-glow'
				);
				?>
			</p>
			<?php if ( $is_plain ) : ?>
				<p>
					<?php
					esc_html_e(
						'Your permalinks are currently set to "Plain". We recommend choosing "Post name" or "Custom Structure" for better SEO and WooCommerce compatibility.',
						'dragon-glow'
					);
					?>
				</p>
			<?php endif; ?>
			<p>
				<a href="<?php echo esc_url( admin_url( 'options-permalink.php' ) ); ?>" class="button button-primary">
					<?php esc_html_e( 'Go to Permalinks Settings', 'dragon-glow' ); ?>
				</a>
				<a href="<?php echo esc_url( $flush_url ); ?>" class="button button-secondary">
					<?php esc_html_e( 'Flush Rewrite Rules Now', 'dragon-glow' ); ?>
				</a>
			</p>
		</div>
		<?php
		// Delete transient after showing notice once.
		if ( $just_flushed ) {
			delete_transient( 'dg_permalinks_flushed' );
		}
	}
}
add_action( 'admin_notices', 'dg_permalink_admin_notice' );

/**
 * Handle manual flush rewrite rules action.
 *
 * Provides a quick action link in admin notices to flush rewrite rules
 * without visiting Settings → Permalinks. Useful when WooCommerce endpoints
 * still return 404 after theme activation.
 *
 * @return void
 */
function dg_handle_manual_flush_rewrites(): void {
	// Check if action is set.
	if ( ! isset( $_GET['dg_action'] ) || 'flush_rewrites' !== $_GET['dg_action'] ) {
		return;
	}
	
	// Check user capability.
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to perform this action.', 'dragon-glow' ) );
	}
	
	// Verify nonce.
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'dg_flush_rewrites' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'dragon-glow' ) );
	}
	
	// Ensure WooCommerce query vars are registered.
	if ( class_exists( 'WooCommerce', false ) && function_exists( 'WC' ) ) {
		$wc = WC();
		if ( isset( $wc->query ) && is_object( $wc->query ) ) {
			if ( method_exists( $wc->query, 'init_query_vars' ) ) {
				$wc->query->init_query_vars();
			}
			if ( method_exists( $wc->query, 'add_endpoints' ) ) {
				$wc->query->add_endpoints();
			}
		}
	}
	
	// Flush rewrite rules.
	flush_rewrite_rules( false );
	dg_purge_w3tc_cache();
	
	// Redirect back with success message.
	wp_safe_redirect(
		add_query_arg(
			array(
				'dg_flushed' => '1',
			),
			admin_url()
		)
	);
	exit;
}
add_action( 'admin_init', 'dg_handle_manual_flush_rewrites' );

/**
 * Show success notice after manual flush.
 *
 * @return void
 */
function dg_flush_success_notice(): void {
	if ( ! isset( $_GET['dg_flushed'] ) || '1' !== $_GET['dg_flushed'] ) {
		return;
	}
	
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="notice notice-success is-dismissible">
		<p>
			<strong><?php esc_html_e( 'Dragon Glow Theme:', 'dragon-glow' ); ?></strong>
			<?php esc_html_e( 'Rewrite rules have been flushed and W3 Total Cache purged. Please test your My Account pages now.', 'dragon-glow' ); ?>
		</p>
	</div>
	<?php
}
add_action( 'admin_notices', 'dg_flush_success_notice' );
