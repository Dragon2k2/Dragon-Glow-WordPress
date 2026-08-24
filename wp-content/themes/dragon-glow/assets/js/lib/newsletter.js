/**
 * Dragon Glow — Newsletter
 * Footer subscribe button + dedicated newsletter form submission.
 *
 * Depends on dgAjax (localized on dg-main).
 *
 * @package Dragon_Glow
 */

(function () {
    'use strict';

    // ── Newsletter (footer) ────────────────────────────────────
    var subscribeBtn = document.getElementById('footer-subscribe-btn');
    if (subscribeBtn) {
        subscribeBtn.addEventListener('click', function () {
            var emailInput = document.getElementById('footer-email');
            var email = emailInput ? emailInput.value.trim() : '';
            if (!email) return;

            var formData = new FormData();
            formData.append('action', 'dg_newsletter');
            formData.append('email', email);
            formData.append('nonce', dgAjax.nonce);

            subscribeBtn.textContent = '...';

            fetch(dgAjax.url, {
                method: 'POST',
                body: formData
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    subscribeBtn.textContent = '✓';
                    if (emailInput) emailInput.value = '';
                    var msg = document.getElementById('footer-newsletter-msg');
                    if (msg) {
                        msg.textContent = data.data.message;
                        msg.classList.remove('hidden');
                        msg.classList.add('text-tertiary');
                    }
                } else {
                    subscribeBtn.textContent = '!';
                    var msg = document.getElementById('footer-newsletter-msg');
                    if (msg) {
                        msg.textContent = data.data.message;
                        msg.classList.remove('hidden');
                        msg.classList.add('text-error');
                    }
                }
            })
            .catch(function () {
                subscribeBtn.textContent = 'Join';
            });
        });
    }

    // ── Newsletter Form (dedicated) ───────────────────────────
    var newsletterForm = document.getElementById('dg-newsletter-form');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var emailInput = newsletterForm.querySelector('input[type="email"]');
            var email = emailInput ? emailInput.value.trim() : '';
            var btn = newsletterForm.querySelector('button[type="submit"]');

            if (!email) return;

            var originalText = btn.textContent;
            btn.textContent = '...';
            btn.disabled = true;

            var formData = new FormData();
            formData.append('action', 'dg_newsletter');
            formData.append('email', email);
            formData.append('nonce', dgAjax.nonce);

            fetch(dgAjax.url, {
                method: 'POST',
                body: formData
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                var msg = document.getElementById('dg-newsletter-msg');
                if (msg) {
                    msg.textContent = data.data ? data.data.message : '';
                    msg.classList.remove('hidden', 'text-error', 'text-tertiary');
                    msg.classList.add(data.success ? 'text-tertiary' : 'text-error');
                }
                if (data.success) {
                    newsletterForm.reset();
                }
            })
            .finally(function () {
                btn.textContent = originalText;
                btn.disabled = false;
            });
        });
    }

})();