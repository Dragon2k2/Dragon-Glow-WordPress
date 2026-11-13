/**
 * Dragon Glow — Gift Cards: Zoom Lightbox
 *
 * Mở/đóng modal xem ảnh thẻ quà ở kích thước đầy đủ, có nút chuyển ảnh
 * (prev/next) và bộ đếm — dùng chung controller với trang single product
 * (`assets/js/lib/zoom-lightbox.js`).
 *
 * Standalone classic script — KHÔNG phụ thuộc Motion (giống
 * `gift-cards-card-preview.js`): nếu CDN Motion chặn/chậm thì ES module
 * `gift-cards.js` bị skip toàn bộ, lightbox vẫn phải hoạt động.
 *
 * Gallery = 4 mệnh giá ($50 / $100 / $250 / $500), khai báo qua
 * `data-card-{amount}` trên `.dg-gift-card-preview` (xem section-bento.php).
 * Ảnh đang chọn được suy ra từ `src` hiện tại của `.dg-gift-card-image` →
 * mở modal đúng mệnh giá user vừa chọn.
 *
 * Chuyển ảnh trong lightbox → đồng bộ TOÀN BỘ state mệnh giá của trang
 * (ảnh preview + chip active + hidden input form + nhãn giá nút "Add to Bag")
 * qua `window.DGGiftValue.set()`. Bắt buộc: nếu chỉ đổi ảnh mà form vẫn
 * giữ mệnh giá cũ, đơn hàng sẽ gửi sai mệnh giá so với ảnh user đã xem.
 *
 * @package Dragon_Glow
 */

(function () {
	'use strict';

	const modal = document.querySelector('[data-dg-zoom-root]');
	const preview = document.querySelector('.dg-gift-card-preview');
	const previewImg = preview ? preview.querySelector('.dg-gift-card-image') : null;

	if (!modal || !window.DGZoomLightbox) {
		return;
	}

	/* ── Gallery: ảnh theo thứ tự mệnh giá trong data-card-{amount} ────────── */
	const gallery = [];

	if (preview) {
		// data-card-{amount} — duyệt theo thứ tự attribute xuất hiện trong DOM
		// (PHP render từ mảng $preview['cards'] nên thứ tự là 50 → 100 → 250 → 500).
		Array.prototype.forEach.call(preview.attributes, function (attr) {
			const match = /^data-card-(\d+)$/.exec(attr.name);
			if (match) {
				gallery.push({ src: attr.value, amount: parseInt(match[1], 10) });
			}
		});
	}

	/**
	 * Index của ảnh đang hiển thị trên preview (mệnh giá user vừa chọn).
	 *
	 * Nguồn chuẩn là `window.DGGiftValue.get()` (state mệnh giá mà form đang
	 * gửi) — đọc `src` của ảnh chỉ là fallback, vì `src` có thể chưa kịp cập
	 * nhật nếu ảnh chưa load xong.
	 *
	 * @return {number} Index trong gallery, -1 nếu không khớp.
	 */
	function resolveCurrentIndex() {
		if (gallery.length === 0) {
			return -1;
		}

		// 1. Theo state mệnh giá (đáng tin cậy nhất).
		if (window.DGGiftValue && typeof window.DGGiftValue.get === 'function') {
			const amount = window.DGGiftValue.get();
			for (let i = 0; i < gallery.length; i++) {
				if (gallery[i].amount === amount) {
					return i;
				}
			}
		}

		// 2. Theo `src` của ảnh preview (gift-value.js set `img.src = ...` khi
		// đổi mệnh giá); `currentSrc` có thể chưa cập nhật nếu ảnh chưa load xong.
		if (previewImg) {
			const current = previewImg.getAttribute('src') || previewImg.currentSrc || previewImg.src;
			for (let i = 0; i < gallery.length; i++) {
				if (gallery[i].src === current) {
					return i;
				}
			}
		}

		return -1;
	}

	window.DGZoomLightbox.create({
		root: modal,
		scope: document.querySelector('.dg-gift') || document,
		items: gallery,
		resolveIndex: resolveCurrentIndex,

		/**
		 * Đồng bộ state mệnh giá của trang khi chuyển ảnh trong lightbox.
		 *
		 * @param {number} index Index hiện tại trong gallery.
		 * @param {Object} item  Item của gallery (`{ src, amount }`).
		 */
		onChange: function (index, item) {
			if (window.DGGiftValue && typeof window.DGGiftValue.set === 'function') {
				window.DGGiftValue.set(item.amount);
			}
		},
	});
})();
