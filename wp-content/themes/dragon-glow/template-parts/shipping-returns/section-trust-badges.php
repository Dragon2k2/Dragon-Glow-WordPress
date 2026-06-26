<?php
/**
 * Dragon Glow — Shipping & Returns: Trust Badges
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$trust_badges = dg_shipping_returns_data()['trust_badges'];
?>
<section class="dg-sr-trust" aria-label="<?php esc_attr_e( 'Trust assurances', 'dragon-glow' ); ?>">
	<div class="dg-sr-trust-inner" data-sr-group>
		<?php foreach ( $trust_badges as $badge ) : ?>
		<div class="dg-trust-badge" data-sr>
			<span class="dg-trust-icon material-symbols-outlined" aria-hidden="true">
				<?php echo esc_html( $badge['icon'] ); ?>
			</span>
			<div class="dg-trust-text">
				<span class="dg-trust-label"><?php echo esc_html( $badge['label'] ); ?></span>
				<span class="dg-trust-desc"><?php echo esc_html( $badge['desc'] ); ?></span>
			</div>
		</div>
		<?php endforeach; ?>
	</div>
</section>
