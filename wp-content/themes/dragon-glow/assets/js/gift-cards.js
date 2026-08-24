/**
 * Dragon Glow — Gift Cards
 * Vanilla JS. ES Module — dùng Motion (motion.dev) cho stagger reveal.
 *
 * Gồm:
 *  - Reveal: stagger các phần tử data-sr (Motion inView + animate + stagger).
 *  - Format toggle: Digital ↔ Physical, đổi preview tag + active state.
 *  - Value chips: chọn $50/$100/$250/$500, đồng bộ label "Add to Bag — $X".
 *  - Form submit:
 *      · WC bật  → DGCart.add({productId, size: 'digital|physical-amount'})
 *      · WC tắt  → push vào localStorage giỏ mock + điều hướng /cart/.
 *
 * Tôn trọng prefers-reduced-motion.
 *
 * @package Dragon_Glow
 */

import { animate, inView, stagger } from "https://cdn.jsdelivr.net/npm/motion@11/+esm";

(function () {
	'use strict';

	const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	const root = document.querySelector('.dg-gift');
	if (!root) {
		return;
	}

	const EASE = [0.22, 1, 0.36, 1];

	initReveal();
	initFormatToggle();
	initValueChips();
	initSubmit();

	/* ── Reveal: stagger các phần tử data-sr ───────────────────────────────── */
	function initReveal() {
		root.querySelectorAll('[data-sr-group]').forEach(function (group) {
			const kids = Array.from(group.querySelectorAll(':scope > [data-sr]'));
			if (!kids.length) {
				return;
			}
			if (reduce) {
				kids.forEach(function (k) { k.style.opacity = '1'; });
				return;
			}
			inView(group, function () {
				animate(
					kids,
					{ opacity: [0, 1], y: [22, 0] },
					{ duration: 0.6, ease: EASE, delay: stagger(0.06) }
				);
			}, { amount: 0.2 });
		});

		root.querySelectorAll('.dg-gift [data-sr]:not([data-sr-group] [data-sr])').forEach(function (el) {
			if (reduce) {
				el.style.opacity = '1';
				return;
			}
			inView(el, function () {
				animate(el, { opacity: [0, 1], y: [22, 0] }, { duration: 0.6, ease: EASE });
			}, { amount: 0.2 });
		});
	}

	/* ── Format toggle ──────────────────────────────────────────────────────── */
	function initFormatToggle() {
		const formatButtons = root.querySelectorAll('[data-format]');
		const hiddenInput = root.querySelector('input[name="format"]');
		const preview = root.querySelector('.dg-gift-card-preview');
		if (!formatButtons.length || !hiddenInput) {
			return;
		}

		formatButtons.forEach(function (btn) {
			btn.addEventListener('click', function () {
				const format = btn.getAttribute('data-format');
				if (!format) {
					return;
				}
				formatButtons.forEach(function (b) {
					const active = b === btn;
					b.classList.toggle('is-active', active);
					b.setAttribute('aria-checked', active ? 'true' : 'false');
				});
				hiddenInput.value = format;
				updatePreviewTag(format, preview);
			});
		});
	}

	function updatePreviewTag(format, preview) {
		if (!preview) {
			return;
		}
		preview.querySelectorAll('[data-tag-for]').forEach(function (tag) {
			tag.classList.toggle('is-hidden', tag.getAttribute('data-tag-for') !== format);
		});
	}

	/* ── Value chips ────────────────────────────────────────────────────────── */
	function initValueChips() {
		const valueButtons = root.querySelectorAll('[data-amount]');
		const hiddenInput = root.querySelector('input[name="value"]');
		const submitValue = root.querySelector('.dg-gift-submit-value');
		const previewImg = root.querySelector('.dg-gift-card-image');
		const previewBox = root.querySelector('.dg-gift-card-preview');
		if (!valueButtons.length || !hiddenInput) {
			return;
		}

		valueButtons.forEach(function (btn) {
			btn.addEventListener('click', function () {
				const amount = parseInt(btn.getAttribute('data-amount'), 10);
				if (!amount) {
					return;
				}
				valueButtons.forEach(function (b) {
					const active = b === btn;
					b.classList.toggle('is-active', active);
					b.setAttribute('aria-checked', active ? 'true' : 'false');
				});
				hiddenInput.value = String(amount);
				if (submitValue) {
					submitValue.textContent = '$' + amount.toLocaleString('en-US');
				}
				swapCardImage(previewImg, previewBox, amount);
			});
		});
	}

	/* Đổi ảnh preview theo mệnh giá — đọc data-card-{amount} trên container. */
	function swapCardImage(img, box, amount) {
		if (!img || !box) {
			return;
		}
		const next = box.getAttribute('data-card-' + amount);
		if (!next || img.getAttribute('src') === next) {
			return;
		}
		img.src = next;
	}

	/* ── Submit ─────────────────────────────────────────────────────────────── */
	function initSubmit() {
		const form = root.querySelector('.dg-gift-config');
		const feedback = root.querySelector('.dg-gift-feedback');
		if (!form) {
			return;
		}

		form.addEventListener('submit', function (e) {
			e.preventDefault();

			// Clear previous feedback
			clearFieldErrors(form);
			setFeedback(feedback, '', '');

			if (!validateForm(form)) {
				setFeedback(feedback, form.dataset.i18nInvalid || 'Please fill in the required fields.', 'error');
				return;
			}

			const data = collectFormData(form);
			const submitBtn = form.querySelector('.dg-gift-submit');
			if (submitBtn) {
				submitBtn.disabled = true;
			}

			const wcActive = form.getAttribute('data-wc-active') === '1';

			if (wcActive) {
				submitWooCommerce(form, data, feedback, submitBtn);
			} else {
				submitMock(form, data, feedback, submitBtn);
			}
		});
	}

	function validateForm(form) {
		let ok = true;
		const to = form.querySelector('#dg-gift-to');
		const email = form.querySelector('#dg-gift-email');

		if (to && !to.value.trim()) {
			markFieldError(to, true);
			ok = false;
		}
		if (email) {
			const v = email.value.trim();
			if (!v || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)) {
				markFieldError(email, true);
				ok = false;
			}
		}
		return ok;
	}

	function markFieldError(input, hasError) {
		const wrap = input.closest('.dg-gift-input');
		if (!wrap) {
			return;
		}
		wrap.classList.toggle('dg-gift-input--has-error', !!hasError);
	}

	function clearFieldErrors(form) {
		form.querySelectorAll('.dg-gift-input--has-error').forEach(function (el) {
			el.classList.remove('dg-gift-input--has-error');
		});
	}

	function collectFormData(form) {
		const fd = new FormData(form);
		return {
			format: fd.get('format') || 'digital',
			amount: parseInt(fd.get('value'), 10) || 0,
			to: (fd.get('recipient_to') || '').toString().trim(),
			email: (fd.get('recipient_email') || '').toString().trim(),
			message: (fd.get('recipient_message') || '').toString().trim(),
		};
	}

	function setFeedback(el, text, type) {
		if (!el) {
			return;
		}
		el.textContent = text || '';
		el.classList.remove('is-success', 'is-error');
		if (type === 'success') {
			el.classList.add('is-success');
		} else if (type === 'error') {
			el.classList.add('is-error');
		}
	}

	/* WC path: gọi DGCart.add — server xử lý variation, gắn vào WC cart */
	function submitWooCommerce(form, data, feedback, submitBtn) {
		const productId = parseInt(form.getAttribute('data-product-id'), 10) || 0;
		if (!productId || typeof window.DGCart !== 'object') {
			setFeedback(feedback, form.dataset.i18nError || 'Something went wrong. Please try again.', 'error');
			enableSubmit(submitBtn);
			return;
		}

		// size = "{format}-{amount}" — variation resolution phía server sẽ map sang SKU.
		const size = data.format + '-' + data.amount;

		window.DGCart.add({
			productId: productId,
			size: size,
			quantity: 1,
		}).then(function (resp) {
			if (resp && resp.success) {
				setFeedback(feedback, form.dataset.i18nAdded || 'Added to bag.', 'success');
				// Optional: điều hướng sang /cart/ — tạm thời để user xem lại form
				setTimeout(function () {
					window.location.href = (window.dgAjax && window.dgAjax.cartUrl) || '/cart/';
				}, 800);
			} else {
				setFeedback(feedback, (resp && resp.data && resp.data.message) || form.dataset.i18nError || 'Could not add to bag.', 'error');
				enableSubmit(submitBtn);
			}
		}).catch(function () {
			setFeedback(feedback, form.dataset.i18nNetwork || 'Network error. Please try again.', 'error');
			enableSubmit(submitBtn);
		});
	}

	/* Mock path: push vào localStorage + điều hướng /cart/ */
	function submitMock(form, data, feedback, submitBtn) {
		try {
			const key = 'dg_mock_cart';
			const raw = window.localStorage.getItem(key);
			const list = raw ? JSON.parse(raw) : [];
			list.push({
				id: parseInt(form.getAttribute('data-mock-product-id'), 10) || 0,
				slug: 'gift-card-' + data.format + '-' + data.amount,
				title: form.dataset.mockTitle || 'Dragon Glow Gift Card',
				format: data.format,
				amount: data.amount,
				to: data.to,
				email: data.email,
				message: data.message,
				quantity: 1,
				added_at: Date.now(),
			});
			window.localStorage.setItem(key, JSON.stringify(list));
		} catch (err) {
			// localStorage unavailable — vẫn cho user tiếp tục
			console.warn('[dg-gift] mock cart storage failed', err);
		}

		setFeedback(feedback, form.dataset.i18nAdded || 'Added to bag.', 'success');
		setTimeout(function () {
			window.location.href = (window.dgAjax && window.dgAjax.cartUrl) || '/cart/';
		}, 600);
	}

	function enableSubmit(btn) {
		if (btn) {
			btn.disabled = false;
		}
	}

})();
