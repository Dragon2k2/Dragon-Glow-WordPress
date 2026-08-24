/**
 * Dragon Glow — Help Center
 * ES Module — Motion vanilla API (motion.dev). KHÔNG React.
 *
 * Gồm: reveal + stagger, accordion single-open (animate height),
 * icon rotate (plus → close), live search (filter items + groups).
 * Tôn trọng prefers-reduced-motion.
 *
 * @package Dragon_Glow
 */
import { animate, inView, stagger } from "https://cdn.jsdelivr.net/npm/motion@11/+esm";

(function () {
	'use strict';

	const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	const root = document.querySelector('[data-page="help-center"]');
	if (!root) {
		return;
	}

	const EASE = [0.2, 0, 0, 1];

	initReveal();
	initAccordion();
	initSearch();

	/* ── Reveal: stagger các phần tử data-sr ──────────────────────────────── */
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
				animate(kids, { opacity: [0, 1], y: [22, 0] }, { duration: 0.7, ease: EASE, delay: stagger(0.07) });
			}, { amount: 0.2 });
		});

		root.querySelectorAll('[data-sr]:not([data-sr-group] [data-sr])').forEach(function (el) {
			if (reduce) {
				el.style.opacity = '1';
				return;
			}
			inView(el, function () {
				animate(el, { opacity: [0, 1], y: [22, 0] }, { duration: 0.65, ease: EASE });
			}, { amount: 0.15 });
		});
	}

	/* ── Accordion: chỉ một card open tại một thời điểm ────────────────────── */
	function initAccordion() {
		const triggers = root.querySelectorAll('.dg-hc-faq-trigger');
		if (!triggers.length) {
			return;
		}
		triggers.forEach(function (trigger) {
			const item = trigger.closest('.dg-hc-faq-item');
			if (!item) {
				return;
			}
			const panelId = trigger.getAttribute('aria-controls');
			const panel = panelId ? document.getElementById(panelId) : item.querySelector('.dg-hc-faq-panel');
			if (!panel) {
				return;
			}

			trigger.addEventListener('click', function () {
				const isOpen = trigger.getAttribute('aria-expanded') === 'true';
				if (isOpen) {
					closeCard(trigger, item, panel);
				} else {
					closeAll();
					openCard(trigger, item, panel);
				}
			});

			trigger.addEventListener('keydown', function (e) {
				if (e.key === 'Enter' || e.key === ' ') {
					e.preventDefault();
					trigger.click();
				}
			});
		});
	}

	function closeAll() {
		root.querySelectorAll('.dg-hc-faq-item.is-open').forEach(function (item) {
			const trig = item.querySelector('.dg-hc-faq-trigger');
			const panel = item.querySelector('.dg-hc-faq-panel');
			if (trig && panel) {
				closeCard(trig, item, panel);
			}
		});
	}

	function openCard(trigger, item, panel) {
		trigger.setAttribute('aria-expanded', 'true');
		item.classList.add('is-open');
		panel.hidden = false;
		rotateIcon(item, true);
		if (reduce) {
			panel.style.height = 'auto';
			panel.style.opacity = '1';
			return;
		}
		// force reflow: đọc offsetHeight buộc browser layout lại trước scrollHeight
		void panel.offsetHeight;
		const target = panel.scrollHeight;
		animate(panel, { height: ['0px', target + 'px'], opacity: [0, 1] }, { duration: 0.45, ease: EASE })
			.then(function () { panel.style.height = 'auto'; });
	}

	function closeCard(trigger, item, panel) {
		trigger.setAttribute('aria-expanded', 'false');
		item.classList.remove('is-open');
		rotateIcon(item, false);
		if (reduce) {
			panel.hidden = true;
			panel.style.height = '';
			panel.style.opacity = '';
			return;
		}
		const current = panel.scrollHeight;
		animate(panel, { height: [current + 'px', '0px'], opacity: [1, 0] }, { duration: 0.3, ease: EASE })
			.then(function () {
				panel.hidden = true;
				panel.style.height = '';
				panel.style.opacity = '';
			});
	}

	function rotateIcon(item, isOpen) {
		const icon = item.querySelector('.dg-hc-faq-icon');
		if (!icon) {
			return;
		}
		if (reduce) {
			icon.style.transform = isOpen ? 'rotate(45deg)' : '';
			return;
		}
		animate(icon, { rotate: isOpen ? 45 : 0 }, { duration: 0.4, ease: EASE });
	}

	/* ── Live search: lọc item + ẩn group rỗng + empty-state ───────────────── */
	function initSearch() {
		const input = document.getElementById('dg-hc-search');
		const status = document.getElementById('dg-hc-search-status');
		const empty = document.getElementById('dg-hc-empty');
		if (!input) {
			return;
		}

		const items = Array.from(root.querySelectorAll('[data-hc-item]'));
		const groups = Array.from(root.querySelectorAll('[data-hc-group]'));
		const haystacks = items.map(function (it) {
			return normalise(it.textContent || '');
		});

		let timer;

		function run() {
			const q = normalise(input.value.trim());
			const hasQuery = q.length > 0;

			let visible = 0;
			items.forEach(function (it, i) {
				const match = !hasQuery || haystacks[i].indexOf(q) !== -1;
				it.hidden = !match;
				if (match) {
					visible++;
				}
			});

			groups.forEach(function (g) {
				const any = g.querySelector('[data-hc-item]:not([hidden])') !== null;
				g.hidden = !any;
			});

			if (empty) {
				empty.hidden = !(hasQuery && visible === 0);
			}
			if (status) {
				status.textContent = hasQuery
					? visible + (visible === 1 ? ' result' : ' results')
					: '';
			}
		}

		input.addEventListener('input', function () {
			window.clearTimeout(timer);
			timer = window.setTimeout(run, 120);
		});
	}

	function normalise(str) {
		return str.toLowerCase().replace(/[‘’]/g, "'");
	}
})();