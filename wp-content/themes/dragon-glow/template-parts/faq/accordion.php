<?php
/**
 * Dragon Glow — FAQ: Accordion
 * Danh sách câu hỏi đánh số (CSS counter) theo từng nhóm + empty-state.
 * Số thứ tự do CSS sinh ra → tự đánh lại khi search lọc bớt item.
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
			data-faq-group
			data-sr
			aria-label="<?php echo esc_attr( $group['label'] ); ?>"
		>
			<h2 class="dg-faq-group-label"><?php echo esc_html( $group['label'] ); ?></h2>

			<ul class="dg-faq-items" role="list">
				<?php
				foreach ( $group['items'] as $i => $item ) :
					$uid = 'faq-' . $group['id'] . '-' . $i;
					?>
					<li class="dg-faq-item" data-faq-item>
						<h3 class="dg-faq-q">
							<button
								class="dg-faq-trigger"
								id="<?php echo esc_attr( $uid . '-t' ); ?>"
								aria-expanded="false"
								aria-controls="<?php echo esc_attr( $uid . '-p' ); ?>"
							>
								<span class="dg-faq-num" aria-hidden="true"></span>
								<span class="dg-faq-q-text"><?php echo esc_html( $item['q'] ); ?></span>
								<span class="dg-faq-mark" aria-hidden="true"></span>
							</button>
						</h3>
						<div
							class="dg-faq-panel"
							id="<?php echo esc_attr( $uid . '-p' ); ?>"
							role="region"
							aria-labelledby="<?php echo esc_attr( $uid . '-t' ); ?>"
							hidden
						>
							<p class="dg-faq-a"><?php echo esc_html( $item['a'] ); ?></p>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
		</section>
	<?php endforeach; ?>

	<?php // Empty-state — hiện khi search không khớp gì. ?>
	<div class="dg-faq-empty" id="dg-faq-empty" hidden>
		<p class="dg-faq-empty-title"><?php esc_html_e( 'Nothing here, yet.', 'dragon-glow' ); ?></p>
		<p class="dg-faq-empty-text"><?php esc_html_e( 'No answer meets those words. Our concierge will.', 'dragon-glow' ); ?></p>
		<a class="dg-faq-empty-link" href="<?php echo esc_url( $faq['contact_url'] ); ?>">
			<?php esc_html_e( 'Reach the concierge', 'dragon-glow' ); ?>
		</a>
	</div>
</div>
