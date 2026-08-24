<?php
/**
 * Dragon Glow — Shipping & Returns: Returns & FAQs (sticky)
 * Sticky glass card with Initiate a Return CTA, two FAQ accordions,
 * and a Track an Order link.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$data    = dg_shipping_returns_data();
$returns = $data['returns'];
$faqs    = $data['faqs'];
?>
<aside class="dg-sr-side" aria-label="<?php esc_attr_e( 'Returns and FAQs', 'dragon-glow' ); ?>">
	<div class="dg-glass-card dg-sr-returns-card" data-sr-group>

		<h2 class="dg-sr-headline-sm" data-sr><?php echo esc_html( $returns['title'] ); ?></h2>
		<p class="dg-sr-body-md dg-sr-returns-body" data-sr>
			<?php echo esc_html( $returns['body'] ); ?>
		</p>

		<button class="dg-sr-btn dg-sr-returns-cta" type="button" data-return-trigger data-sr>
			<?php echo esc_html( $returns['cta_text'] ); ?>
		</button>

		<div class="dg-sr-faq-list" role="list" data-sr-group>
			<?php foreach ( $faqs as $idx => $faq ) : ?>
			<?php
			$uid      = 'dg-sr-faq-' . $idx;
			$panel_id = $uid . '-panel';
			?>
			<div class="dg-sr-faq-item" role="listitem" data-sr>
				<button
					class="dg-sr-faq-trigger"
					type="button"
					id="<?php echo esc_attr( $uid ); ?>"
					aria-expanded="false"
					aria-controls="<?php echo esc_attr( $panel_id ); ?>"
				>
					<span class="dg-sr-faq-question"><?php echo esc_html( $faq['question'] ); ?></span>
				</button>
				<div
					class="dg-sr-faq-panel"
					id="<?php echo esc_attr( $panel_id ); ?>"
					role="region"
					aria-labelledby="<?php echo esc_attr( $uid ); ?>"
				>
					<p class="dg-sr-faq-answer"><?php echo esc_html( $faq['answer'] ); ?></p>
				</div>
			</div>
			<?php endforeach; ?>
		</div>

	</div>
</aside>