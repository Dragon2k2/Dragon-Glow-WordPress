<?php
/**
 * Dragon Glow — FAQ: Accordion
 * Glass-panel cards trong 12-col grid; một card active tại một thời điểm.
 * Mỗi nhóm có data-faq-group khớp với category id ở sidebar.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$faq = dg_faq_data();
?>
<div class="dg-faq-list" id="dg-faq-list">

	<?php foreach ( $faq['groups'] as $group ) : ?>
		<section
			class="dg-faq-group"
			data-faq-group="<?php echo esc_attr( $group['id'] ); ?>"
			data-sr
			aria-label="<?php echo esc_attr( $group['label'] ); ?>"
		>
			<ul class="dg-faq-items" role="list">
				<?php
				foreach ( $group['items'] as $i => $item ) :
					$uid = 'faq-' . $group['id'] . '-' . $i;
					?>
					<li class="dg-faq-item" data-faq-item>
						<article class="dg-faq-card dg-faq-card--ghost">
							<button
								type="button"
								class="dg-faq-trigger"
								id="<?php echo esc_attr( $uid . '-t' ); ?>"
								aria-expanded="false"
								aria-controls="<?php echo esc_attr( $uid . '-p' ); ?>"
							>
								<h3 class="dg-faq-q"><?php echo esc_html( $item['q'] ); ?></h3>
								<span class="dg-faq-icon" aria-hidden="true">
									<span class="material-symbols-outlined">expand_more</span>
								</span>
							</button>
							<div
								class="dg-faq-panel"
								id="<?php echo esc_attr( $uid . '-p' ); ?>"
								role="region"
								aria-labelledby="<?php echo esc_attr( $uid . '-t' ); ?>"
								hidden
							>
								<p class="dg-faq-a"><?php echo esc_html( $item['a'] ); ?></p>
							</div>
						</article>
					</li>
				<?php endforeach; ?>
			</ul>
		</section>
	<?php endforeach; ?>

	<?php // Empty-state — hiện khi search không khớp gì. ?>
	<div class="dg-faq-empty" id="dg-faq-empty" hidden>
		<p class="dg-faq-empty-title"><?php esc_html_e( 'Nothing here, yet.', 'dragon-glow' ); ?></p>
		<p class="dg-faq-empty-text"><?php esc_html_e( 'No answer meets those words. We will write one.', 'dragon-glow' ); ?></p>
		<a class="dg-faq-empty-link" href="<?php echo esc_url( $faq['contact_url'] ); ?>">
			<?php esc_html_e( 'Reach the concierge', 'dragon-glow' ); ?>
		</a>
	</div>
</div>