/**
 * Dragon Glow — Contact Page JS
 * AJAX form submit + FAQ accordion toggle
 *
 * @package Dragon_Glow
 */

(function () {
    'use strict';

    // ── Contact Form Submit ───────────────────────────────────
    var contactForm = document.getElementById('dg-contact-form');
    if (contactForm) {
        contactForm.addEventListener('submit', function (e) {
            e.preventDefault();

            var btn = contactForm.querySelector('button[type="submit"]');
            var originalText = btn.textContent;
            btn.textContent = '...';
            btn.disabled = true;

            var formData = new FormData(contactForm);

            fetch(dgAjax.url, {
                method: 'POST',
                body: formData
            })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                var msg = document.getElementById('dg-contact-msg');
                if (msg) {
                    msg.textContent = res.data ? res.data.message : '';
                    msg.classList.remove('hidden', 'text-error', 'text-tertiary');
                    msg.classList.add(res.success ? 'text-tertiary' : 'text-error');
                }

                if (res.success) {
                    // Reset form
                    contactForm.reset();

                    // Show success animation
                    btn.textContent = '✓ Sent!';
                    btn.classList.add('bg-green-500');

                    setTimeout(function () {
                        btn.textContent = originalText;
                        btn.classList.remove('bg-green-500');
                        btn.disabled = false;
                    }, 3000);
                } else {
                    btn.textContent = originalText;
                    btn.disabled = false;
                }
            })
            .catch(function (err) {
                console.error('Contact form error:', err);

                var msg = document.getElementById('dg-contact-msg');
                if (msg) {
                    msg.textContent = 'An error occurred. Please try again.';
                    msg.classList.remove('hidden', 'text-tertiary');
                    msg.classList.add('text-error');
                }

                btn.textContent = originalText;
                btn.disabled = false;
            });
        });
    }

    // ── FAQ Accordion ────────────────────────────────────────
    document.querySelectorAll('.dg-accordion-item').forEach(function (item) {
        var trigger = item.querySelector('.dg-accordion-trigger');
        if (trigger) {
            trigger.addEventListener('click', function () {
                var isOpen = item.classList.contains('active');

                // Close all
                document.querySelectorAll('.dg-accordion-item').forEach(function (i) {
                    i.classList.remove('active');
                    var content = i.querySelector('.accordion-content');
                    var arrow = i.querySelector('.arrow');
                    if (content) {
                        content.style.maxHeight = '0';
                    }
                    if (arrow) {
                        arrow.style.transform = '';
                    }
                });

                // Open clicked (if was closed)
                if (!isOpen) {
                    item.classList.add('active');
                    var content = item.querySelector('.accordion-content');
                    var arrow = item.querySelector('.arrow');
                    if (content) {
                        content.style.maxHeight = content.scrollHeight + 'px';
                    }
                    if (arrow) {
                        arrow.style.transform = 'rotate(180deg)';
                    }
                }
            });
        }
    });

    // Open first accordion by default
    var firstAccordion = document.querySelector('.dg-accordion-item');
    if (firstAccordion) {
        setTimeout(function () {
            firstAccordion.classList.add('active');
            var content = firstAccordion.querySelector('.accordion-content');
            var arrow = firstAccordion.querySelector('.arrow');
            if (content) {
                content.style.maxHeight = content.scrollHeight + 'px';
            }
            if (arrow) {
                arrow.style.transform = 'rotate(180deg)';
            }
        }, 500);
    }

    // ── Form Input Animation ─────────────────────────────────
    var formInputs = document.querySelectorAll('#dg-contact-form input, #dg-contact-form textarea');
    formInputs.forEach(function (input) {
        // Focus animation
        input.addEventListener('focus', function () {
            this.classList.add('focused');
        });

        input.addEventListener('blur', function () {
            if (!this.value) {
                this.classList.remove('focused');
            }
        });

        // Character count for textarea
        if (input.tagName === 'TEXTAREA') {
            var charCount = document.createElement('span');
            charCount.className = 'char-count text-label-sm text-on-surface-variant';
            charCount.style.cssText = 'position: absolute; right: 0; bottom: -20px;';
            input.parentElement.style.position = 'relative';
            input.parentElement.appendChild(charCount);

            charCount.textContent = '0 / 1000';

            input.addEventListener('input', function () {
                var len = this.value.length;
                charCount.textContent = len + ' / 1000';
                charCount.style.color = len > 900 ? '#ba1a1a' : '';
            });
        }
    });

})();
