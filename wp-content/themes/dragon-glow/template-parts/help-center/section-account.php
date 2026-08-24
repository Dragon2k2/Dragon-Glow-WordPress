<?php
/**
 * Dragon Glow — Help Center: Account & Privacy
 * Section header (icon + title) + single-open accordion.
 * Bất kỳ FAQ nào có `has_cta` sẽ render CTA bên dưới answer.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$data    = dg_hc_data();
$section = $data['sections']['account'];
$faqs    = $section['faqs'];
?>
<section class="dg-hc-section" aria-label="<?php echo esc_attr( $section['title'] ); ?>" data-sr-group>
	<header class="dg-hc-section-head" data-sr>
		<div class="dg-hc-section-icon" aria-hidden="true">
			<span class="material-symbols-outlined"><?php echo esc_html( $section['icon'] ); ?></span>
		</div>
		<h2 class="dg-hc-section-title"><?php echo esc_html( $section['title'] ); ?></h2>
	</header>

	<?php if ( empty( $faqs ) ) : ?>
		<p class="dg-hc-section-empty"><?php esc_html_e( 'No entries, yet.', 'dragon-glow' ); ?></p>
	<?php else : ?>
		<div class="dg-hc-accordion" data-hc-group="<?php echo esc_attr( $section['id'] ); ?>">
			<?php foreach ( $faqs as $idx => $faq ) :
				$uid     = 'dg-hc-account-' . $idx;
				$has_cta = ! empty( $faq['has_cta'] );
				?>
			<div class="dg-hc-faq-item" data-hc-item data-sr>
				<button
					type="button"
					class="dg-hc-faq-trigger"
					id="<?php echo esc_attr( $uid . '-t' ); ?>"
					aria-expanded="false"
					aria-controls="<?php echo esc_attr( $uid . '-p' ); ?>"
				>
					<span class="dg-hc-faq-question"><?php echo esc_html( $faq['question'] ); ?></span>
					<span class="dg-hc-faq-icon" aria-hidden="true">
						<span class="material-symbols-outlined">add</span>
					</span>
				</button>
				<div
					class="dg-hc-faq-panel"
					id="<?php echo esc_attr( $uid . '-p' ); ?>"
					role="region"
					aria-labelledby="<?php echo esc_attr( $uid . '-t' ); ?>"
					hidden
				>
					<div class="dg-hc-faq-panel-inner">
						<p class="dg-hc-faq-answer"><?php echo esc_html( $faq['answer'] ); ?></p>
						<?php if ( $has_cta ) : ?>
						<a class="dg-hc-faq-cta" href="<?php echo esc_url( $faq['cta_url'] ?? $data['contact_url'] ); ?>">
							<?php echo esc_html( $faq['cta_label'] ?? __( 'Open Settings', 'dragon-glow' ) ); ?>
						</a>
						<?php endif; ?>
					</div>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</section>