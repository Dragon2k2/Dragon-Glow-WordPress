<?php
/**
 * Dragon Glow — Gift Cards: Bento Layout (visual preview)
 *
 * Left column (col-7):
 *  - Main physical card imagery (4:3, có tag động theo format đang chọn)
 *  - Ảnh thẻ đổi theo mệnh giá đang chọn (data-card-{amount} cho JS).
 *  - Bento row: ảnh envelope + tile "Digital Delivery"
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$data    = dg_gift_cards_data();
$formats = $data['formats'];
$preview = $data['preview'];
$values  = $data['values'];

// Format mặc định = first (digital). JS sẽ đồng bộ khi user toggle.
// Amount mặc định = $100 (khớp section-config) → ảnh card-$100 render trước, không nhấp nháy.
$default_format = $formats[0];
$default_amount = $values[1]['amount'];
?>
<div class="dg-gift-bento">
	<!-- Main Physical / Digital Card Imagery -->
	<div
		class="dg-gift-card-preview"
		data-default-format="<?php echo esc_attr( $default_format['id'] ); ?>"
		data-sr
		<?php foreach ( $preview['cards'] as $amount => $src ) : ?>
		data-card-<?php echo esc_attr( (string) $amount ); ?>="<?php echo esc_url( $src ); ?>"
		<?php endforeach; ?>
	>
		<img
			class="dg-gift-card-image"
			src="<?php echo esc_url( $preview['cards'][ $default_amount ] ?? $preview['main_src'] ); ?>"
			alt="<?php echo esc_attr( $preview['main_alt'] ); ?>"
			loading="eager"
			decoding="async"
		/>
		<span
			class="dg-gift-card-tag"
			data-tag-for="physical"
		>
			<?php echo esc_html( $formats[1]['tag_label'] ); ?>
		</span>
		<span
			class="dg-gift-card-tag dg-gift-card-tag--digital is-hidden"
			data-tag-for="digital"
		>
			<?php echo esc_html( $formats[0]['tag_label'] ); ?>
		</span>
	</div>

	<!-- Secondary Visuals (Bento row) -->
	<div class="dg-gift-bento-row">
		<div class="dg-gift-card-secondary" data-sr>
			<img
				class="dg-gift-card-secondary-image"
				src="<?php echo esc_url( $preview['second_src'] ); ?>"
				alt="<?php echo esc_attr( $preview['second_alt'] ); ?>"
				loading="lazy"
				decoding="async"
			/>
		</div>
		<div class="dg-gift-card-digital-tile" data-sr>
			<span class="material-symbols-outlined dg-gift-card-digital-icon" aria-hidden="true">mark_email_read</span>
			<h3 class="dg-gift-card-digital-title"><?php esc_html_e( 'Digital Delivery', 'dragon-glow' ); ?></h3>
			<p class="dg-gift-card-digital-text"><?php esc_html_e( 'Instant elegance to their inbox.', 'dragon-glow' ); ?></p>
		</div>
	</div>
</div>
