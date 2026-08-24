/**
 * Dragon Glow — Contact Page JS
 * AJAX form submit + FAQ accordion + form input animation + parallax.
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

            var btn = document.getElementById('dg-submit-btn');
            var originalText = btn.innerHTML;
            var msg = document.getElementById('dg-contact-msg');

            // Loading state.
            btn.innerHTML = '...';
            btn.disabled = true;
            btn.classList.add('opacity-50');

            var formData = new FormData(contactForm);

            fetch(dgAjax.url, {
                method: 'POST',
                body: formData
            })
                .then(function (response) { return response.json(); })
                .then(function (data) {
                    // Simulate delay for UX.
                    setTimeout(function () {
                        if (data.success) {
                            // Success state.
                            btn.innerHTML = '✓ Message Received';
                            btn.classList.remove('opacity-50', 'bg-primary');
                            btn.classList.add('bg-secondary', 'text-on-secondary');

                            msg.classList.remove('hidden', 'text-error');
                            msg.classList.add('text-tertiary');
                            msg.textContent = (data.data && data.data.message)
                                ? data.data.message
                                : 'Thank you! We\'ll be in touch soon.';

                            // Reset after 3 seconds.
                            setTimeout(function () {
                                btn.innerHTML = originalText;
                                btn.disabled = false;
                                btn.classList.remove('opacity-50', 'bg-secondary', 'text-on-secondary');
                                btn.classList.add('bg-primary', 'text-on-primary');
                                msg.classList.add('hidden');
                                contactForm.reset();
                            }, 3000);
                        } else {
                            // Error state.
                            btn.innerHTML = originalText;
                            btn.disabled = false;
                            btn.classList.remove('opacity-50');

                            msg.classList.remove('hidden', 'text-tertiary');
                            msg.classList.add('text-error');
                            msg.textContent = (typeof data.data === 'string')
                                ? data.data
                                : 'Something went wrong. Please try again.';
                        }
                    }, 1500);
                })
                .catch(function (error) {
                    console.error('Error:', error);
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                    btn.classList.remove('opacity-50');

                    msg.classList.remove('hidden', 'text-tertiary');
                    msg.classList.add('text-error');
                    msg.textContent = 'Something went wrong. Please try again.';
                });
        });
    }

    // ── FAQ Accordion ────────────────────────────────────────
    document.querySelectorAll('.dg-accordion-item').forEach(function (item) {
        var trigger = item.querySelector('.dg-accordion-trigger');
        if (trigger) {
            trigger.addEventListener('click', function () {
                var isOpen = item.classList.contains('active');

                // Close all.
                document.querySelectorAll('.dg-accordion-item').forEach(function (i) {
                    i.classList.remove('active');
                    var content = i.querySelector('.accordion-content');
                    var arrow = i.querySelector('.arrow');
                    if (content) content.style.maxHeight = '0';
                    if (arrow) arrow.style.transform = '';
                });

                // Open clicked (if was closed).
                if (!isOpen) {
                    item.classList.add('active');
                    var content = item.querySelector('.accordion-content');
                    var arrow = item.querySelector('.arrow');
                    if (content) content.style.maxHeight = content.scrollHeight + 'px';
                    if (arrow) arrow.style.transform = 'rotate(180deg)';
                }
            });
        }
    });

    // Open first accordion by default.
    var firstAccordion = document.querySelector('.dg-accordion-item');
    if (firstAccordion) {
        setTimeout(function () {
            firstAccordion.classList.add('active');
            var content = firstAccordion.querySelector('.accordion-content');
            var arrow = firstAccordion.querySelector('.arrow');
            if (content) content.style.maxHeight = content.scrollHeight + 'px';
            if (arrow) arrow.style.transform = 'rotate(180deg)';
        }, 500);
    }

    // ── Form Input Animation ─────────────────────────────────
    var formInputs = document.querySelectorAll('#dg-contact-form input, #dg-contact-form textarea');
    formInputs.forEach(function (input) {
        // Focus animation.
        input.addEventListener('focus', function () {
            this.classList.add('focused');
        });

        input.addEventListener('blur', function () {
            if (!this.value) this.classList.remove('focused');
        });

        // Character count for textarea.
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

    // ── Parallax on hero product image ───────────────────────
    var parallaxImg = document.getElementById('contact-parallax-img');
    if (parallaxImg) {
        window.addEventListener('scroll', function () {
            var speed = 0.05;
            var yPos = -(window.pageYOffset * speed);
            parallaxImg.style.transform = 'translateY(' + yPos + 'px) scale(1.05)';
        });
    }
})();
