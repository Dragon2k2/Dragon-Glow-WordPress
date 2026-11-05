/**
 * Dragon Glow — Gift Cards: card-preview placement (mobile + tablet)
 *
 * Standalone classic script — deliberately has NO dependencies (no Motion
 * import) so the placement still applies when the Motion CDN is slow, blocked
 * or unreachable. `assets/js/gift-cards.js` is an ES module with a static
 * `import` from jsDelivr; when that import fails the browser skips the whole
 * module, which would leave the card preview in the desktop position.
 *
 * On mobile (≤ 640px) and tablet (641–1023px) the main card preview is moved
 * out of the visual column and re-inserted directly after the "Value"
 * fieldset, so the reading order becomes:
 *
 *   Format → Value → [card preview] → Recipient Details
 *
 * Rationale: the shopper picks a denomination, then immediately sees the
 * card that denomination produces. Both mobile and tablet use the single
 * column layout, so the preview spans the full width inside the form.
 *
 * From 1024px up the layout splits into two columns (7fr / 5fr) and the
 * preview returns to the sticky visual column on the left — untouched.
 *
 * The preview contains no focusable element (only an <img> and two
 * non-interactive <span> badges), so relocating it does not change the tab
 * order (WCAG 2.4.3).
 *
 * @package Dragon_Glow
 */

(function () {
	'use strict';

	const root = document.querySelector('.dg-gift');
	if (!root) {
		return;
	}

	const preview = root.querySelector('.dg-gift-card-preview');
	const valueField = root.querySelector('.dg-gift-field--value');
	if (!preview || !valueField) {
		return;
	}

	// Node gốc (visual column) để restore khi viewport lớn lại.
	const origin = preview.parentNode;
	const originNext = preview.nextSibling;

	// Move trên mobile (≤640px) và tablet (641–1023px) — cả hai đều là layout
	// 1 cột nên preview nằm trong form sẽ full-width, đúng như mong muốn.
	// Từ 1024px trở lên layout tách 2 cột (7fr/5fr) và preview quay lại cột
	// visual sticky bên trái.
	const mq = window.matchMedia('(max-width: 1023px)');
	let moved = false;

	function apply() {
		const shouldMove = mq.matches;
		if (shouldMove === moved) {
			return;
		}
		if (shouldMove) {
			// Ngay sau fieldset Value, trong cùng form (giữ nguyên context form).
			valueField.parentNode.insertBefore(preview, valueField.nextSibling);
		} else {
			// Restore về vị trí gốc trong visual column.
			origin.insertBefore(preview, originNext);
		}
		moved = shouldMove;
	}

	apply();

	if (typeof mq.addEventListener === 'function') {
		mq.addEventListener('change', apply);
	} else if (typeof mq.addListener === 'function') {
		mq.addListener(apply); // Safari < 14
	}
})();
