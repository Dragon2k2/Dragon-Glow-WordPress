<?php
/**
 * Template Name: Mock Cart — Dragon Glow
 *
 * Internal cart page for mock (display-only) products.
 * Displays items stored in the transient mock cart created by dg_add_to_cart_silently().
 *
 * This template is NOT a WooCommerce template — it works independently
 * of WooCommerce and does not depend on any WC functions.
 *
 * HOW TO SET UP: Create a WordPress page with any slug (e.g. "cart" when WC is
 * inactive), assign this template, and publish it.  When WooCommerce is active
 * and its Cart page is published, this page is bypassed in favour of the real
 * WooCommerce cart (see the guard below).
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

// Guard: if WooCommerce is active and the real cart has items, redirect there.
// Shadow products created by mock-product-sync.php feed real WC cart items,
// so the user should go through the standard WC cart when available.
if ( dg_is_woocommerce_active() && function_exists( 'WC' ) ) {
	try {
		if ( ! WC()->cart->is_empty() ) {
			wp_safe_redirect( dg_get_cart_url() );
			exit;
		}
	} catch ( Exception $e ) {
		// WC not fully initialised — continue to mock cart.
	}
}

// Guard: if the cart page is unavailable (page not created), show a clear notice.
if ( isset( $_GET['dg_cart_unavailable'] ) && '1' === $_GET['dg_cart_unavailable'] ) :
	get_header();
	?>
	<main class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-16 text-center">
		<div class="w-20 h-20 bg-primary-container/30 rounded-full flex items-center justify-center mx-auto mb-6">
			<span class="material-symbols-outlined text-primary" style="font-size: 48px;">info</span>
		</div>
		<h1 class="font-headline text-headline-md text-primary mb-4">
			<?php esc_html_e( 'Cart Unavailable', 'dragon-glow' ); ?>
		</h1>
		<p class="text-on-surface-variant text-body-lg max-w-md mx-auto mb-8">
			<?php esc_html_e( 'The cart page has not been set up yet. Please create a WordPress page with the "Mock Cart" template, or enable WooCommerce to use the built-in cart.', 'dragon-glow' ); ?>
		</p>
		<a href="<?php echo esc_url( home_url( '/shop/' ) ); ?>"
		   class="btn-luxury bg-primary text-on-primary px-10 py-4 font-label-sm text-label-sm uppercase tracking-widest inline-block">
			<?php esc_html_e( 'Back to Shop', 'dragon-glow' ); ?>
		</a>
	</main>
	<?php
	get_footer();
	exit;
endif;

get_header();

$cart = dg_get_mock_cart();
?>
<main class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-12">

	<?php if ( empty( $cart ) ) : ?>
		<!-- ── Empty cart ── -->
		<div class="text-center py-16">
			<?php
			get_template_part(
				'template-parts/global/empty-state',
				null,
				array(
					'icon'          => 'shopping_bag',
					'icon_size'    => 96,
					'circle_size'  => 'w-48 h-48',
					'title'        => __( 'Your bag is empty', 'dragon-glow' ),
					'description'  => __( "It looks like you haven't added anything to your bag yet.", 'dragon-glow' ),
					'primary_cta'  => array(
						'label' => __( 'Browse Collection', 'dragon-glow' ),
						'url'   => esc_url( home_url( '/shop/' ) ),
					),
				)
			);
			?>
		</div>

	<?php else : ?>
		<!-- ── Cart with items ── -->
		<h1 class="font-headline text-headline-lg text-primary mb-8">
			<?php esc_html_e( 'Your Bag', 'dragon-glow' ); ?>
		</h1>

		<!-- Notice for multiple items (mock checkout currently supports 1 item) -->
		<?php if ( count( $cart ) > 1 ) : ?>
			<div class="mb-6 p-4 bg-primary-container/20 border border-primary/20 rounded-xl text-sm text-on-surface-variant">
				<span class="material-symbols-outlined text-primary align-middle mr-2">info</span>
				<?php
				esc_html_e(
					'Your bag contains multiple items. The checkout flow currently supports one item at a time — please check out items individually.',
					'dragon-glow'
				);
				?>
			</div>
		<?php endif; ?>

		<div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
			<!-- LEFT: Cart items -->
			<div class="lg:col-span-2">
				<div class="space-y-6">

					<?php
					$row_count = 0;
					foreach ( $cart as $item_key => $item ) :
						$row_count++;
						$line_total = (float) $item['price'] * (int) $item['quantity'];
						$formatted_line = dg_format_price( $line_total );
						?>
						<div class="dg-cart-row dg-glass-panel rounded-2xl p-6"
							 data-dg-item-key="<?php echo esc_attr( $item_key ); ?>">

							<div class="flex gap-5">
								<!-- Product image -->
								<div class="dg-cart-img-wrap flex-shrink-0">
									<?php if ( ! empty( $item['image_url'] ) ) : ?>
										<img src="<?php echo esc_url( $item['image_url'] ); ?>"
											 alt="<?php echo esc_attr( $item['name'] ); ?>"
											 loading="lazy" />
									<?php endif; ?>
								</div>

								<!-- Product info -->
								<div class="flex-1 min-w-0">
									<div class="flex justify-between items-start gap-4">
										<div>
											<h3 class="font-headline text-lg text-primary truncate">
												<?php echo esc_html( $item['name'] ); ?>
											</h3>
											<?php if ( ! empty( $item['size'] ) ) : ?>
												<p class="text-sm text-on-surface-variant mt-1">
													<?php echo esc_html( $item['size'] ); ?>
												</p>
											<?php endif; ?>
											<p class="text-sm font-semibold text-on-surface mt-2">
												<?php echo esc_html( $item['formatted_price'] ); ?>
											</p>
										</div>

										<!-- Remove button -->
										<button class="dg-remove-btn dg-mock-remove"
												title="<?php esc_attr_e( 'Remove item', 'dragon-glow' ); ?>"
												data-item-key="<?php echo esc_attr( $item_key ); ?>">
											<span class="material-symbols-outlined text-lg">delete</span>
										</button>
									</div>

									<!-- Quantity stepper -->
									<div class="flex items-center justify-between mt-4">
										<div class="dg-qty-stepper" data-item-key="<?php echo esc_attr( $item_key ); ?>">
											<button type="button" class="dg-qty-stepper-btn dg-mock-qty-minus"
													data-item-key="<?php echo esc_attr( $item_key ); ?>">
												<span>&#8722;</span>
											</button>
											<span class="dg-qty-value"><?php echo (int) $item['quantity']; ?></span>
											<button type="button" class="dg-qty-stepper-btn dg-mock-qty-plus"
													data-item-key="<?php echo esc_attr( $item_key ); ?>">
												<span>&#43;</span>
											</button>
										</div>

										<p id="dg-mock-price-<?php echo esc_attr( sanitize_html_class( $item_key ) ); ?>"
										   class="font-bold text-primary text-right">
											<?php echo esc_html( $formatted_line ); ?>
										</p>
									</div>
								</div>
							</div>
						</div>
					<?php endforeach; ?>

					<!-- Continue shopping -->
					<a href="<?php echo esc_url( home_url( '/shop/' ) ); ?>"
					   class="inline-flex items-center gap-2 text-sm text-on-surface-variant hover:text-primary transition-colors mt-2">
						<span class="material-symbols-outlined text-lg">arrow_back</span>
						<?php esc_html_e( 'Continue Shopping', 'dragon-glow' ); ?>
					</a>
				</div>
			</div>

			<!-- RIGHT: Order summary -->
			<div class="lg:col-span-1">
				<div class="dg-glass-panel rounded-2xl p-8 sticky top-24">
					<h2 class="font-headline text-xl text-primary mb-6">
						<?php esc_html_e( 'Order Summary', 'dragon-glow' ); ?>
					</h2>

					<?php
					$subtotal = 0;
					foreach ( $cart as $item ) {
						$subtotal += (float) $item['price'] * (int) $item['quantity'];
					}
					$formatted_subtotal = dg_format_price( $subtotal );
					?>

					<div class="space-y-3 mb-6">
						<div class="flex justify-between text-on-surface-variant">
							<span><?php esc_html_e( 'Subtotal', 'dragon-glow' ); ?></span>
							<span id="dg-mock-subtotal"><?php echo esc_html( $formatted_subtotal ); ?></span>
						</div>
						<div class="flex justify-between text-on-surface-variant">
							<span><?php esc_html_e( 'Shipping', 'dragon-glow' ); ?></span>
							<span class="text-primary font-medium">
								<?php
								echo esc_html( $subtotal >= 75
									? __( 'FREE', 'dragon-glow' )
									: __( 'Calculated at checkout', 'dragon-glow' )
								);
								?>
							</span>
						</div>
						<div class="flex justify-between text-sm text-on-surface-variant">
							<span><?php esc_html_e( 'Tax', 'dragon-glow' ); ?></span>
							<span><?php esc_html_e( 'Calculated at checkout', 'dragon-glow' ); ?></span>
						</div>
						<div class="flex justify-between font-bold text-lg text-primary pt-3 border-t border-outline-variant/20">
							<span><?php esc_html_e( 'Estimated Total', 'dragon-glow' ); ?></span>
							<span id="dg-mock-total"><?php echo esc_html( $formatted_subtotal ); ?></span>
						</div>
					</div>

					<?php if ( 1 === count( $cart ) ) : ?>
						<?php
						// Single item — use the same query-arg pattern as template-mock-checkout.php.
						$only_item    = reset( $cart );
						$only_key     = key( $cart );
						$checkout_url = add_query_arg(
							array(
								'dg_mock_checkout' => '1',
								'dg_mock_item'     => rawurlencode( $only_key ),
							),
							dg_get_mock_checkout_url()
						);
						?>
						<a href="<?php echo esc_url( $checkout_url ); ?>"
						   class="dg-shimmer-btn w-full py-4 rounded-2xl font-bold uppercase tracking-widest text-sm flex items-center justify-center gap-2">
							<span class="material-symbols-outlined">lock</span>
							<?php esc_html_e( 'Proceed to Checkout', 'dragon-glow' ); ?>
						</a>
					<?php else : ?>
						<!--
							TODO: Multi-item checkout — template-mock-checkout.php currently handles
							exactly 1 item per checkout session. Implement multi-item support here
							(collect all items from transient, pass them all to the checkout handler)
							before enabling this button.
						-->
						<button class="dg-shimmer-btn w-full py-4 rounded-2xl font-bold uppercase tracking-widest text-sm flex items-center justify-center gap-2 opacity-50 cursor-not-allowed"
								disabled>
							<span class="material-symbols-outlined">lock</span>
							<?php esc_html_e( 'Proceed to Checkout', 'dragon-glow' ); ?>
						</button>
					<?php endif; ?>

					<!-- Trust badges -->
					<div class="mt-6 pt-4 border-t border-outline-variant/20 space-y-3">
						<div class="flex items-center gap-3 text-sm text-on-surface-variant">
							<span class="material-symbols-outlined text-primary text-lg">local_shipping</span>
							<span><?php esc_html_e( 'Free Shipping on orders $75+', 'dragon-glow' ); ?></span>
						</div>
						<div class="flex items-center gap-3 text-sm text-on-surface-variant">
							<span class="material-symbols-outlined text-primary text-lg">verified</span>
							<span><?php esc_html_e( '30-Day Ritual Trial', 'dragon-glow' ); ?></span>
						</div>
						<div class="flex items-center gap-3 text-sm text-on-surface-variant">
							<span class="material-symbols-outlined text-primary text-lg">lock</span>
							<span><?php esc_html_e( 'Secure checkout', 'dragon-glow' ); ?></span>
						</div>
					</div>
				</div>
			</div>
		</div>
	<?php endif; ?>

</main>

<?php get_footer();
