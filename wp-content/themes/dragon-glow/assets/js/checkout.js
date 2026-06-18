/**
 * Dragon Glow — Checkout Page JS
 * 3-step stepper logic, form validation visual feedback.
 *
 * @package Dragon_Glow
 */

(function () {
    'use strict';

    // ── Initialize Stepper ───────────────────────────────────
    var currentStep = 1;

    window.dgSetStep = function (step) {
        currentStep = step;

        // Update circles
        [1, 2, 3].forEach(function (n) {
            var circle = document.getElementById('dg-step-' + n + '-circle');
            var label = document.getElementById('dg-step-' + n + '-label');

            if (!circle) return;

            circle.classList.remove('step-active', 'step-inactive', 'step-done');

            if (n < step) {
                circle.classList.add('step-done');
            } else if (n === step) {
                circle.classList.add('step-active');
            } else {
                circle.classList.add('step-inactive');
            }

            if (label) {
                label.classList.toggle('font-bold', n === step);
                label.classList.toggle('text-primary', n === step);
                label.classList.toggle('font-medium', n !== step);
                label.classList.toggle('text-on-surface-variant', n !== step);
            }
        });

        // Smooth scroll to form
        var formSection = document.getElementById('customer_details');
        if (formSection) {
            formSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    };

    // ── WooCommerce Checkout Step Integration ─────────────────
    var checkoutForm = document.querySelector('form.checkout');
    if (checkoutForm) {
        // Progressive disclosure based on filled fields
        var billingFields = checkoutForm.querySelectorAll('#billing_field_group .form-row');
        var shippingFields = checkoutForm.querySelectorAll('#shipping_field_group .form-row');

        // Highlight current active section
        function highlightSection(sectionClass) {
            document.querySelectorAll('.form-row').forEach(function (row) {
                row.classList.remove('current-section');
            });

            var section = document.querySelector(sectionClass);
            if (section) {
                section.classList.add('current-section');
            }
        }

        // Auto-advance when fields are filled (optional UX enhancement)
        billingFields.forEach(function (field) {
            var input = field.querySelector('input, select');
            if (input) {
                input.addEventListener('blur', function () {
                    if (this.value && this.checkValidity()) {
                        this.classList.add('valid');
                    }
                });
            }
        });
    }

    // ── Payment Method Selection ──────────────────────────────
    var paymentMethods = document.querySelectorAll('input[name="payment_method"]');
    paymentMethods.forEach(function (method) {
        method.addEventListener('change', function () {
            // Update visual feedback
            document.querySelectorAll('.payment_method').forEach(function (pm) {
                pm.classList.remove('selected-payment');
            });

            if (method.checked) {
                method.closest('.payment_method').classList.add('selected-payment');
            }
        });
    });

    // ── Create Account Toggle ────────────────────────────────
    var createAccountCheckbox = document.querySelector('input#createaccount');
    var createAccountFields = document.querySelector('.create-account');

    if (createAccountCheckbox && createAccountFields) {
        createAccountCheckbox.addEventListener('change', function () {
            createAccountFields.style.display = this.checked ? 'block' : 'none';
        });
    }

    // ── Ship to Different Address Toggle ─────────────────────
    var shipToDiff = document.querySelector('input#ship-to-different-address-checkbox');
    var shippingFields = document.querySelector('.woocommerce-shipping-fields');

    if (shipToDiff && shippingFields) {
        shipToDiff.addEventListener('change', function () {
            shippingFields.style.display = this.checked ? 'block' : 'none';
        });
    }

    // ── Form Validation Visual Feedback ──────────────────────
    var requiredInputs = document.querySelectorAll('.woocommerce-checkout input[required], .woocommerce-checkout select[required]');
    requiredInputs.forEach(function (input) {
        input.addEventListener('invalid', function (e) {
            this.classList.add('invalid');
        });

        input.addEventListener('input', function () {
            if (this.checkValidity()) {
                this.classList.remove('invalid');
                this.classList.add('valid');
            }
        });
    });

    // ── Terms and Conditions Checkbox ─────────────────────────
    var termsCheckbox = document.querySelector('input[name="terms"]');
    if (termsCheckbox) {
        var termsLink = document.querySelector('a.woocommerce-terms-and-conditions-link');
        if (termsLink) {
            termsLink.setAttribute('target', '_blank');
        }
    }

    // ── Initialize on load ───────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        // Set initial step
        if (typeof window.dgSetStep === 'function') {
            window.dgSetStep(1);
        }
    });

})();
