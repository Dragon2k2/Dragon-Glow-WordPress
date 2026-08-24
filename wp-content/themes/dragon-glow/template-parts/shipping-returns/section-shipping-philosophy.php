<?php
/**
 * Dragon Glow — Shipping & Returns: Shipping Philosophy
 * Heading + body + 2 method cards + 3-phase journey timeline.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$data        = dg_shipping_returns_data();
$methods     = $data['shipping_methods'];
$timeline    = $data['timeline'];
?>
<section class="dg-sr-main" aria-label="<?php esc_attr_e( 'Shipping details', 'dragon-glow' ); ?>">

	<div class="dg-sr-philosophy" data-sr-group>
		<h2 class="dg-sr-headline-md" data-sr><?php esc_html_e( 'Shipping Philosophy', 'dragon-glow' ); ?></h2>
		<p class="dg-sr-body-lg" data-sr>
			<?php esc_html_e( 'We believe the unboxing experience begins the moment your order is placed. Our fulfillment process is meticulously managed to ensure your items arrive in pristine condition, wrapped in our signature protective casing.', 'dragon-glow' ); ?>
		</p>

		<div class="dg-sr-methods">
			<?php foreach ( $methods as $method ) : ?>
			<article class="dg-sr-method dg-glass-card" data-sr>
				<span class="dg-sr-method-icon material-symbols-outlined" aria-hidden="true">
					<?php echo esc_html( $method['icon'] ); ?>
				</span>
				<h3 class="dg-sr-method-title"><?php echo esc_html( $method['title'] ); ?></h3>
				<p class="dg-sr-method-time"><?php echo esc_html( $method['time'] ); ?></p>
				<p class="dg-sr-method-price dg-sr-label-sm"><?php echo esc_html( $method['price'] ); ?></p>
			</article>
			<?php endforeach; ?>
		</div>
	</div>

	<div class="dg-sr-journey" data-sr-group>
		<h2 class="dg-sr-headline-sm dg-sr-journey-title" data-sr>
			<?php esc_html_e( 'The Journey of Your Order', 'dragon-glow' ); ?>
		</h2>

		<div class="dg-sr-timeline">
			<?php foreach ( $timeline as $item ) : ?>
			<div class="dg-sr-timeline-item" data-sr>
				<p class="dg-sr-label-sm dg-sr-timeline-label">
					<?php echo esc_html( $item['label'] ); ?>
				</p>
				<h3 class="dg-sr-timeline-title"><?php echo esc_html( $item['title'] ); ?></h3>
				<p class="dg-sr-timeline-body"><?php echo esc_html( $item['body'] ); ?></p>
			</div>
			<?php endforeach; ?>
		</div>
	</div>

</section>