/**
 * Dragon Glow — Checkout Page JS
 *
 * Adds UX niceties to the WooCommerce checkout form:
 *   - Visual validation feedback on required fields
 *   - Open Terms & Conditions link in a new tab
 *   - Toggle "Create account" extra fields
 *   - Highlight the selected payment method
 *   - Re-bind all of the above after each WC `updated_checkout` AJAX refresh
 *
 * The checkout form is rendered by WooCommerce itself (via the
 * `dg_checkout_page_content` filter); this file only attaches behaviour
 * to its standard markup.
 *
 * @package Dragon_Glow
 */

(function () {
    'use strict';

    // ── Visual validation on blur / input ───────────────────────────────
    function bindValidation() {
        var inputs = document.querySelectorAll(
            '.woocommerce-checkout input[required], .woocommerce-checkout select[required]'
        );
        inputs.forEach(function (input) {
            input.addEventListener('invalid', function () {
                this.classList.add('invalid');
            });
            input.addEventListener('input', function () {
                if (this.checkValidity()) {
                    this.classList.remove('invalid');
                    this.classList.add('valid');
                } else {
                    this.classList.remove('valid');
                }
            });
        });
    }

    // ── Terms and Conditions ─────────────────────────────────────────────
    function bindTerms() {
        var termsLink = document.querySelector('a.woocommerce-terms-and-conditions-link');
        if (termsLink) {
            termsLink.setAttribute('target', '_blank');
        }
        var termsCb = document.querySelector('input[name="terms"]');
        if (termsCb) {
            termsCb.addEventListener('invalid', function (e) {
                e.preventDefault();
            });
        }
    }

    // ── Create Account toggle ────────────────────────────────────────────
    function bindCreateAccount() {
        var cb     = document.querySelector('input#createaccount');
        var fields = document.querySelector('.create-account');
        if (cb && fields) {
            cb.addEventListener('change', function () {
                fields.style.display = this.checked ? 'block' : 'none';
            });
        }
    }

    // ── Payment method selection visual feedback ────────────────────────
    function bindPaymentSelection() {
        document.querySelectorAll('input[name="payment_method"]').forEach(function (input) {
            input.addEventListener('change', function () {
                document.querySelectorAll('.payment_method').forEach(function (pm) {
                    pm.classList.remove('selected-payment');
                });
                if (this.checked) {
                    this.closest('.payment_method').classList.add('selected-payment');
                }
            });
        });
    }

    // ── Customize Select2 placeholders for Country/State fields ─────────
    function customizeSelect2Placeholders() {
        if (typeof jQuery === 'undefined') {
            return;
        }
        
        // Wait for WooCommerce to initialize Select2
        setTimeout(function () {
            // Country/Region field
            var countryField = jQuery('#billing_country');
            if (countryField.length && countryField.data('select2')) {
                countryField.data('select2').$dropdown.find('.select2-search__field').attr('placeholder', 'Search countries...');
            }
            
            // State/Province field
            var stateField = jQuery('#billing_state');
            if (stateField.length && stateField.data('select2')) {
                stateField.data('select2').$dropdown.find('.select2-search__field').attr('placeholder', 'Search states...');
            }
            
            // Shipping fields (if "Ship to different address" is enabled)
            var shippingCountryField = jQuery('#shipping_country');
            if (shippingCountryField.length && shippingCountryField.data('select2')) {
                shippingCountryField.data('select2').$dropdown.find('.select2-search__field').attr('placeholder', 'Search countries...');
            }
            
            var shippingStateField = jQuery('#shipping_state');
            if (shippingStateField.length && shippingStateField.data('select2')) {
                shippingStateField.data('select2').$dropdown.find('.select2-search__field').attr('placeholder', 'Search states...');
            }
        }, 100);
    }

    // ── Restrict ZIP/Phone fields to numeric input only ──────────────────
    function bindNumericOnly() {
        var numericFields = document.querySelectorAll(
            '#billing_postcode, #billing_phone, #shipping_postcode, #shipping_phone'
        );

        numericFields.forEach(function (field) {
            if (!field) return;

            // Strip non-digits on paste
            field.addEventListener('paste', function (e) {
                var pasted = (e.clipboardData || window.clipboardData).getData('text');
                var digitsOnly = pasted.replace(/\D/g, '');
                e.preventDefault();
                var start = this.selectionStart;
                var end = this.selectionEnd;
                var currentValue = this.value;
                var newValue = currentValue.substring(0, start) + digitsOnly + currentValue.substring(end);
                // Limit to reasonable length (20 chars for phone, 10 for postcode)
                var maxLen = this.id.indexOf('phone') !== -1 ? 20 : 10;
                this.value = newValue.substring(0, maxLen);
                this.setSelectionRange(start + digitsOnly.length, start + digitsOnly.length);
                // Trigger input event for WC validation
                this.dispatchEvent(new Event('input', { bubbles: true }));
            });

            // Block non-digit keys
            field.addEventListener('keydown', function (e) {
                // Allow: backspace, delete, tab, escape, enter, arrow keys
                if (
                    e.key === 'Backspace' || e.key === 'Delete' ||
                    e.key === 'Tab' || e.key === 'Escape' ||
                    e.key === 'Enter' ||
                    (e.key >= 37 && e.key <= 40) // Arrow keys
                ) {
                    return;
                }
                // Allow Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
                if ((e.ctrlKey || e.metaKey) && /^[acvx]$/i.test(e.key)) {
                    return;
                }
                // Block if not a digit
                if (!/^\d$/.test(e.key)) {
                    e.preventDefault();
                }
            });

            // Strip non-digits on input (defensive)
            field.addEventListener('input', function () {
                var digitsOnly = this.value.replace(/\D/g, '');
                if (this.value !== digitsOnly) {
                    this.value = digitsOnly;
                    this.dispatchEvent(new Event('input', { bubbles: true }));
                }
            });
        });
    }

    // ── Re-bind after each WooCommerce AJAX refresh ──────────────────────
    function bindWooCommerceEvents() {
        if (typeof jQuery === 'undefined') {
            return;
        }
        jQuery(document.body).on('updated_checkout', function () {
            bindValidation();
            bindPaymentSelection();
            customizeSelect2Placeholders();
            bindNumericOnly();
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        bindValidation();
        bindTerms();
        bindCreateAccount();
        bindPaymentSelection();
        bindWooCommerceEvents();
        customizeSelect2Placeholders();
        bindNumericOnly();
    });
})();