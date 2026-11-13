/**
 * Dragon Glow — Gift Cards: shared denomination state
 *
 * Sở hữu DUY NHẤT state "mệnh giá thẻ quà" của trang Gift Cards:
 *  - ảnh preview `.dg-gift-card-image`
 *  - chip mệnh giá active (`[data-amount]`)
 *  - hidden input `input[name="value"]` (giá trị gửi form)
 *  - nhãn giá trên nút "Add to Bag" (`.dg-gift-submit-value`)
 *
 * Vì sao cần module riêng:
 *  - `assets/js/gift-cards.js` là ES module (import Motion từ CDN) → nếu CDN
 *    chặn/chậm thì cả module bị skip, mọi logic chip mệnh giá chết theo.
 *  - `assets/js/gift-cards-zoom.js` là classic script độc lập (lightbox phải
 *    hoạt động tối thiểu) → không import được ES module.
 *  Hai script cần cùng một nguồn sự thật về mệnh giá, nên state được đặt ở
 *  module classic không phụ thuộc gì (`window.DGGiftValue`).
 *
 * API:
 *  - `window.DGGiftValue.get()` → mệnh giá hiện tại (number).
 *  - `window.DGGiftValue.set(amount)` → áp dụng mệnh giá mới, đồng bộ toàn bộ
 *    UI ở trên. Trả `true` nếu state thực sự thay đổi.
 *
 * @package Dragon_Glow
 */

(function () {
	'use strict';

	const root = document.querySelector('.dg-gift');

	if (!root) {
		return;
	}

	const chips = Array.prototype.slice.call(root.querySelectorAll('[data-amount]'));
	const hiddenInput = root.querySelector('input[name="value"]');
	const submitValue = root.querySelector('.dg-gift-submit-value');
	const previewImg = root.querySelector('.dg-gift-card-image');
	const previewBox = root.querySelector('.dg-gift-card-preview');

	if (!hiddenInput) {
		return;
	}

	let current = parseInt(hiddenInput.value, 10) || 0;

	/* Định dạng giá giống hàm PHP `dg_gift_cards_format_price()` (number_format). */
	function formatPrice(amount) {
		return '$' + amount.toLocaleString('en-US');
	}

	/* Ảnh thẻ theo mệnh giá — đọc data-card-{amount} trên container preview. */
	function cardSrcFor(amount) {
		if (!previewBox) {
			return '';
		}
		return previewBox.getAttribute('data-card-' + amount) || '';
	}

	/**
	 * Áp dụng mệnh giá mới cho toàn bộ UI.
	 *
	 * @param {number} amount Mệnh giá (50 / 100 / 250 / 500).
	 * @return {boolean} true nếu state thực sự thay đổi.
	 */
	function set(amount) {
		const value = parseInt(amount, 10);

		if (!value || value === current) {
			return false;
		}

		current = value;

		// Chip mệnh giá.
		chips.forEach(function (chip) {
			const active = parseInt(chip.getAttribute('data-amount'), 10) === value;
			chip.classList.toggle('is-active', active);
			chip.setAttribute('aria-checked', active ? 'true' : 'false');
		});

		// Giá trị gửi form (server dùng để map sang variation).
		hiddenInput.value = String(value);

		// Nhãn giá trên nút "Add to Bag".
		if (submitValue) {
			submitValue.textContent = formatPrice(value);
		}

		// Ảnh preview.
		const src = cardSrcFor(value);
		if (previewImg && src && previewImg.getAttribute('src') !== src) {
			previewImg.src = src;
		}

		return true;
	}

	/* Click chip mệnh giá → cập nhật state. */
	chips.forEach(function (chip) {
		chip.addEventListener('click', function () {
			set(parseInt(chip.getAttribute('data-amount'), 10));
		});
	});

	window.DGGiftValue = {
		get: function () {
			return current;
		},
		set: set
	};
})();
