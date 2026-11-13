/**
 * Dragon Glow — Gift Cards: Zoom Lightbox
 *
 * Mở/đóng modal xem ảnh thẻ quà ở kích thước đầy đủ, có nút chuyển ảnh
 * (prev/next) và bộ đếm — mirror trải nghiệm lightbox của trang single product
 * (assets/js/product.js).
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

	const modal = document.getElementById('dg-gift-zoom-modal');
	const zoomImg = document.getElementById('dg-gift-zoom-image');

	if (!modal || !zoomImg) {
		return;
	}

	const preview = document.querySelector('.dg-gift-card-preview');
	const previewImg = preview ? preview.querySelector('.dg-gift-card-image') : null;
	const closeBtn = modal.querySelector('.dg-gift-zoom-modal__close');
	const prevBtn = modal.querySelector('.dg-gift-zoom-modal__nav--prev');
	const nextBtn = modal.querySelector('.dg-gift-zoom-modal__nav--next');
	const counter = document.getElementById('dg-gift-zoom-counter');

	let lastFocused = null;

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

	let currentIndex = 0;

	function resolveInitialIndex() {
		if (!previewImg || gallery.length === 0) {
			return 0;
		}
		// `src` attribute là nguồn chuẩn (gift-cards.js set `img.src = ...` khi
		// đổi mệnh giá); `currentSrc` có thể chưa cập nhật nếu ảnh chưa load xong.
		const current = previewImg.getAttribute('src') || previewImg.currentSrc || previewImg.src;
		for (let i = 0; i < gallery.length; i++) {
			if (gallery[i].src === current) {
				return i;
			}
		}
		return 0;
	}

	/* ── Render ảnh hiện tại ───────────────────────────────────────────────── */
	function render() {
		const item = gallery[currentIndex];

		if (!item) {
			return;
		}

		zoomImg.src = item.src;
		zoomImg.alt = previewImg ? previewImg.alt : '';

		// Re-trigger fade/scale khi chuyển ảnh (nếu không ảnh sẽ nhảy đột ngột).
		zoomImg.classList.remove('is-visible');
		void zoomImg.offsetWidth;
		zoomImg.classList.add('is-visible');

		// Đồng bộ state mệnh giá của trang (ảnh preview + chip + form + giá).
		if (window.DGGiftValue && typeof window.DGGiftValue.set === 'function') {
			window.DGGiftValue.set(item.amount);
		}

		if (counter) {
			counter.textContent = (currentIndex + 1) + ' / ' + gallery.length;
		}

		// Chỉ 1 ảnh → ẩn nút chuyển + bộ đếm.
		const single = gallery.length <= 1;
		if (prevBtn) {
			prevBtn.hidden = single;
		}
		if (nextBtn) {
			nextBtn.hidden = single;
		}
		if (counter) {
			counter.hidden = single;
		}
	}

	function goTo(index) {
		if (gallery.length === 0) {
			return;
		}
		currentIndex = (index + gallery.length) % gallery.length;
		render();
	}

	function prev() {
		goTo(currentIndex - 1);
	}

	function next() {
		goTo(currentIndex + 1);
	}

	/* ── Open ──────────────────────────────────────────────────────────────── */
	function open() {
		if (gallery.length === 0 && !previewImg) {
			return;
		}

		currentIndex = resolveInitialIndex();
		render();

		lastFocused = document.activeElement;

		modal.classList.add('is-open');
		modal.setAttribute('aria-hidden', 'false');
		document.body.style.overflow = 'hidden';

		if (closeBtn) {
			closeBtn.focus();
		} else {
			modal.focus();
		}
	}

	/* ── Close ─────────────────────────────────────────────────────────────── */
	function close() {
		if (!modal.classList.contains('is-open')) {
			return;
		}

		modal.classList.remove('is-open');
		modal.setAttribute('aria-hidden', 'true');
		document.body.style.overflow = '';

		if (lastFocused && typeof lastFocused.focus === 'function') {
			lastFocused.focus();
		}
		lastFocused = null;
	}

	/* ── Events ────────────────────────────────────────────────────────────── */
	if (closeBtn) {
		closeBtn.addEventListener('click', close);
	}

	if (prevBtn) {
		prevBtn.addEventListener('click', function (e) {
			e.stopPropagation();
			prev();
		});
	}

	if (nextBtn) {
		nextBtn.addEventListener('click', function (e) {
			e.stopPropagation();
			next();
		});
	}

	// Click ra ngoài backdrop (target = chính modal) → đóng.
	modal.addEventListener('click', function (e) {
		if (e.target === modal) {
			close();
		}
	});

	// Click vào ảnh preview → mở lightbox (ngoài nút zoom).
	if (previewImg) {
		previewImg.addEventListener('click', function () {
			open();
		});

		// Keyboard: Enter / Space kích hoạt (ảnh có role="button" + tabindex="0").
		previewImg.addEventListener('keydown', function (e) {
			if (e.key === 'Enter' || e.key === ' ' || e.key === 'Spacebar') {
				e.preventDefault();
				open();
			}
		});
	}

	// Phím tắt: ← / → chuyển ảnh, Escape đóng.
	document.addEventListener('keydown', function (e) {
		if (!modal.classList.contains('is-open')) {
			return;
		}
		if (e.key === 'Escape') {
			close();
		} else if (e.key === 'ArrowLeft') {
			prev();
		} else if (e.key === 'ArrowRight') {
			next();
		}
	});

	// Public API — nút zoom trong template dùng `onclick="dgOpenZoom()"`,
	// mirror với trang single product (woocommerce/content-single-product.php).
	// Guard: nếu trang đang có lightbox sản phẩm (`#dg-zoom-modal`) thì giữ nguyên
	// bản `dgOpenZoom` của product.js — trang Gift Cards (page template) không
	// bao giờ chạy song song với trang single product.
	if (!document.getElementById('dg-zoom-modal')) {
		window.dgOpenZoom = open;
	}
})();
