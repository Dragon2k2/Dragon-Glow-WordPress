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
            btn.classList.add('is-loading');

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
                            btn.classList.remove('is-loading');
                            btn.classList.add('is-success');

                            msg.hidden = false;
                            msg.classList.remove('is-error');
                            msg.classList.add('is-success');
                            msg.textContent = (data.data && data.data.message)
                                ? data.data.message
                                : 'Thank you! We\'ll be in touch soon.';

                            // Reset after 3 seconds.
                            setTimeout(function () {
                                btn.innerHTML = originalText;
                                btn.disabled = false;
                                btn.classList.remove('is-success');
                                msg.hidden = true;
                                contactForm.reset();
                                resetCharCount();
                            }, 3000);
                        } else {
                            // Error state.
                            btn.innerHTML = originalText;
                            btn.disabled = false;
                            btn.classList.remove('is-loading');

                            msg.hidden = false;
                            msg.classList.remove('is-success');
                            msg.classList.add('is-error');
                            msg.textContent = (data.data && data.data.message)
                                ? data.data.message
                                : 'Something went wrong. Please try again.';
                        }
                    }, 1500);
                })
                .catch(function (error) {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                    btn.classList.remove('is-loading');

                    msg.hidden = false;
                    msg.classList.remove('is-success');
                    msg.classList.add('is-error');
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
    });

    // ── Message Character Counter ────────────────────────────
    // Counter element is rendered by template-contact.php; styling lives in contact.css.
    var messageArea = document.getElementById('contact-message');
    var charCount = document.getElementById('dg-contact-count');

    function updateCharCount() {
        if (!messageArea || !charCount) return;
        var len = messageArea.value.length;
        charCount.textContent = len + ' / 1000';
        charCount.classList.toggle('is-warn', len > 900);
    }

    function resetCharCount() {
        updateCharCount();
    }

    if (messageArea) {
        messageArea.addEventListener('input', updateCharCount);
    }

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
