<?php
/**
 * Dragon Glow — Shared image lightbox markup
 *
 * Modal xem ảnh ở kích thước đầy đủ, dùng chung cho mọi gallery ảnh của
 * theme (single product + gift cards). Controller:
 * assets/js/lib/zoom-lightbox.js — gắn qua data attribute:
 *
 *   [data-dg-zoom-root]     container modal (toggle `.is-open`)
 *   [data-dg-zoom-image]    <img> hiển thị trong modal
 *   [data-dg-zoom-counter]  bộ đếm "3 / 7"
 *   [data-dg-zoom-prev]     nút ảnh trước
 *   [data-dg-zoom-next]     nút ảnh sau
 *   [data-dg-zoom-close]    nút đóng
 *
 * Template partial này PHẢI được include SAU `</main>`: `position: fixed`
 * sẽ bị neo sai bởi `transform` của animation reveal nếu nằm trong <main>.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;
?>
<div
	id="dg-zoom-modal"
	class="dg-zoom-modal"
	data-dg-zoom-root
	role="dialog"
	aria-modal="true"
	aria-hidden="true"
	aria-label="<?php esc_attr_e( 'Image zoom view', 'dragon-glow' ); ?>"
	tabindex="-1"
>
	<button
		type="button"
		class="dg-zoom-modal__close"
		data-dg-zoom-close
		aria-label="<?php esc_attr_e( 'Close zoom', 'dragon-glow' ); ?>"
	>
		<span class="material-symbols-outlined" aria-hidden="true">close</span>
	</button>

	<button
		type="button"
		class="dg-zoom-modal__nav dg-zoom-modal__nav--prev"
		data-dg-zoom-prev
		aria-label="<?php esc_attr_e( 'Previous image', 'dragon-glow' ); ?>"
	>
		<span class="material-symbols-outlined" aria-hidden="true">chevron_left</span>
	</button>

	<button
		type="button"
		class="dg-zoom-modal__nav dg-zoom-modal__nav--next"
		data-dg-zoom-next
		aria-label="<?php esc_attr_e( 'Next image', 'dragon-glow' ); ?>"
	>
		<span class="material-symbols-outlined" aria-hidden="true">chevron_right</span>
	</button>

	<div class="dg-zoom-modal__stage">
		<img
			class="dg-zoom-modal__image"
			data-dg-zoom-image
			src=""
			alt=""
		/>
	</div>

	<div class="dg-zoom-modal__counter" data-dg-zoom-counter aria-live="polite">1 / 1</div>
</div>
