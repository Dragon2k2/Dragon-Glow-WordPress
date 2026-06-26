<?php
/**
 * Dragon Glow — Shipping & Returns: FAQ Mini Accordion
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$faq_items = dg_shipping_returns_data()['faq_mini'];
?>
<section class="dg-sr-faq" aria-labelledby="dg-faq-mini-heading" data-sr-group>
	<div class="dg-sr-faq-header" data-sr>
		<span class="dg-eyebrow"><?php esc_html_e( 'Common questions', 'dragon-glow' ); ?></span>
		<h2 class="dg-sr-faq-title font-display" id="dg-faq-mini-heading">
			<?php esc_html_e( 'Frequently asked about shipping & returns', 'dragon-glow' ); ?>
		</h2>
	</div>

	<ul class="dg-sr-faq-list" role="list">
		<?php foreach ( $faq_items as $idx => $item ) : ?>
		<?php
		$uid   = 'sr-faq-' . $idx;
		$panel = $uid . '-panel';
		?>
		<li class="dg-sr-faq-item" data-sr>
			<button
				class="dg-sr-faq-trigger"
				aria-expanded="false"
				aria-controls="<?php echo esc_attr( $panel ); ?>"
				id="<?php echo esc_attr( $uid ); ?>"
				type="button"
			>
				<span class="dg-sr-faq-question"><?php echo esc_html( $item['question'] ); ?></span>
				<span class="dg-sr-faq-arrow material-symbols-outlined" aria-hidden="true">expand_more</span>
			</button>
			<div
				class="dg-sr-faq-panel"
				id="<?php echo esc_attr( $panel ); ?>"
				role="region"
				aria-labelledby="<?php echo esc_attr( $uid ); ?>"
				hidden
			>
				<p class="dg-sr-faq-answer"><?php echo esc_html( $item['answer'] ); ?></p>
			</div>
		</li>
		<?php endforeach; ?>
	</ul>

	<div class="dg-sr-faq-cta" data-sr>
		<?php
		$faq_page = get_page_by_path( 'faq' );
		$faq_url  = $faq_page ? get_permalink( $faq_page->ID ) : home_url( '/faq/' );
		?>
		<a href="<?php echo esc_url( $faq_url ); ?>" class="dg-sr-faq-more">
			<span><?php esc_html_e( 'View all FAQs', 'dragon-glow' ); ?></span>
			<span class="material-symbols-outlined" aria-hidden="true">arrow_forward</span>
		</a>
	</div>
</section>
