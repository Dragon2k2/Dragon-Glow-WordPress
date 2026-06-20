/**
 * Dragon Glow — Checkout Page JS
 * 3-step stepper logic, form validation, payment DOM extraction, review sync.
 *
 * @package Dragon_Glow
 */

(function () {
    'use strict';

    // ── State ─────────────────────────────────────────────────────────────
    var currentStep = 1;
    var PANE_CLASS  = 'dg-step-pane';

    // ── Core: set active step ────────────────────────────────────────────
    window.dgSetStep = function (step) {
        currentStep = Math.min(3, Math.max(1, parseInt(step, 10) || 1));

        // Update stepper circles & connectors.
        [1, 2, 3].forEach(function (n) {
            var circle    = document.getElementById('dg-step-' + n + '-circle');
            var label     = document.getElementById('dg-step-' + n + '-label');
            var connector = document.querySelector('#dg-step-group-' + n + ' .dg-step-connector');

            if (circle) {
                circle.classList.remove('step-active', 'step-inactive', 'step-done');
                if (n < currentStep) {
                    circle.classList.add('step-done');
                } else if (n === currentStep) {
                    circle.classList.add('step-active');
                } else {
                    circle.classList.add('step-inactive');
                }
            }

            if (label) {
                label.classList.toggle('font-bold',      n === currentStep);
                label.classList.toggle('text-primary',   n === currentStep);
                label.classList.toggle('font-medium',    n !== currentStep);
                label.classList.toggle('text-on-surface-variant', n !== currentStep);
            }

            // Connector to next step: green when current step is done.
            if (connector) {
                if (n < currentStep) {
                    connector.classList.add('!bg-primary');
                } else {
                    connector.classList.remove('!bg-primary');
                }
            }
        });

        // Show / hide step panes.
        document.querySelectorAll('.' + PANE_CLASS).forEach(function (pane) {
            var paneStep = parseInt(pane.dataset.step, 10);
            if (paneStep === currentStep) {
                pane.classList.remove('hidden');
            } else {
                pane.classList.add('hidden');
            }
        });

        // Smooth scroll to top of stepper.
        var stepper = document.getElementById('dg-checkout-stepper');
        if (stepper) {
            stepper.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    };

    // ── Navigation: next / back / jump ──────────────────────────────────
    function goToStep(step) {
        // Validate current step before advancing.
        if (step > currentStep) {
            if (!validateStep(currentStep)) {
                return;
            }
        }
        window.dgSetStep(step);
    }

    // Validate visible required fields in a given step pane.
    function validateStep(step) {
        var pane = document.getElementById('dg-pane-' + step);
        if (!pane) {
            return true;
        }

        var inputs   = pane.querySelectorAll('input[required], select[required], textarea[required]');
        var isValid  = true;

        inputs.forEach(function (input) {
            // Skip hidden inputs (e.g. from the WC shipping fields block that
            // is toggled off by default).
            if (!input.offsetParent) {
                return;
            }

            if (!input.checkValidity()) {
                input.classList.add('invalid');
                isValid = false;
            } else {
                input.classList.remove('invalid');
            }
        });

        if (!isValid) {
            // Focus the first invalid field.
            var firstInvalid = pane.querySelector('.invalid');
            if (firstInvalid) {
                firstInvalid.focus();
            }
        }

        return isValid;
    }

    // ── Payment section extraction & relocation ──────────────────────────
    // WooCommerce renders BOTH cart/totals AND payment methods inside
    // #order_review. We extract just the payment block and move it into
    // the Step 2 pane so it sits in the left column.
    // This must run on every 'updated_checkout' because WooCommerce replaces
    // #order_review innerHTML via AJAX whenever the cart changes.

    function extractPaymentSection() {
        var orderReview = document.getElementById('order_review');
        var paymentPane = document.getElementById('dg-payment-section');
        if (!orderReview || !paymentPane) {
            return;
        }

        // The #payment div contains the payment method list + Place Order button.
        var paymentDiv = orderReview.querySelector('#payment');
        if (paymentDiv) {
            paymentPane.innerHTML = '';
            paymentPane.appendChild(paymentDiv);
        }
    }

    function syncReviewStep() {
        var reviewPane = document.getElementById('dg-pane-3');
        if (!reviewPane || currentStep !== 3) {
            return;
        }

        // Shipping address — pull from the billing fields in Step 1.
        var shippingEl = document.getElementById('dg-review-shipping');
        if (shippingEl) {
            var nameEl  = document.getElementById('billing_first_name_field');
            var addr1El = document.getElementById('billing_address_1_field');
            var cityEl  = document.getElementById('billing_city_field');
            var stateEl = document.getElementById('billing_state_field');
            var zipEl   = document.getElementById('billing_postcode_field');
            var countryEl = document.getElementById('billing_country_field');

            var parts = [];
            function fieldVal(id) {
                var f = document.getElementById(id);
                return f ? (f.querySelector('input, select') || {}).value : '';
            }

            var firstName = fieldVal('billing_first_name');
            var lastName  = fieldVal('billing_last_name');
            var addr1     = fieldVal('billing_address_1');
            var city      = fieldVal('billing_city');
            var state     = fieldVal('billing_state');
            var zip       = fieldVal('billing_postcode');
            var country   = fieldVal('billing_country');

            var lines = [];
            if (firstName || lastName) { lines.push((firstName + ' ' + lastName).trim()); }
            if (addr1) { lines.push(addr1); }
            var csz = [city, state, zip].filter(Boolean).join(', ');
            if (csz) { lines.push(csz); }
            if (country) {
                var countryField = document.getElementById('billing_country_field');
                var countryLabel = countryField && countryField.querySelector('select')
                    ? countryField.querySelector('select option[value="' + country + '"]')
                    : null;
                lines.push(countryLabel ? countryLabel.textContent.trim() : country);
            }

            if (lines.length > 0) {
                shippingEl.innerHTML = '<div class="space-y-1">' +
                    lines.map(function (l) { return '<p>' + l + '</p>'; }).join('') +
                    '</div>';
            } else {
                shippingEl.innerHTML = '<p class="text-on-surface-variant text-sm italic">' +
                    '<?php echo esc_js( __( 'No shipping address entered.', 'dragon-glow' ) ); ?>' +
                    '</p>';
            }
        }

        // Payment method — pull from the checked radio in the payment pane.
        var paymentEl = document.getElementById('dg-review-payment');
        if (paymentEl) {
            var checked = document.querySelector('#dg-payment-section input[name="payment_method"]:checked');
            if (checked) {
                var label = checked.closest('.payment_method');
                if (label) {
                    var titleEl = label.querySelector('label[for]') || label.querySelector('label');
                    var title   = titleEl ? titleEl.textContent.trim() : '<?php echo esc_js( __( 'Selected payment method', 'dragon-glow' ) ); ?>';
                    paymentEl.innerHTML = '<p>' + title + '</p>';
                }
            } else {
                paymentEl.innerHTML = '<p class="text-on-surface-variant text-sm italic">' +
                    '<?php echo esc_js( __( 'No payment method selected.', 'dragon-glow' ) ); ?>' +
                    '</p>';
            }
        }
    }

    // ── Bind navigation buttons ─────────────────────────────────────────
    function bindNavigation() {
        // "Continue" buttons (step-next).
        document.querySelectorAll('.dg-step-next').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                var target = parseInt(this.dataset.goto, 10);
                goToStep(target);
            });
        });

        // "Back" buttons.
        document.querySelectorAll('.dg-step-back').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                var target = parseInt(this.dataset.goto, 10);
                goToStep(target);
            });
        });

        // "Edit" jump links (Review step → jump back to Shipping or Payment).
        document.querySelectorAll('.dg-jump-step').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                var target = parseInt(this.dataset.goto, 10);
                window.dgSetStep(target);
            });
        });
    }

    // ── Ship-to-different-address toggle ───────────────────────────────
    function bindShipToDifferent() {
        var checkbox = document.getElementById('ship-to-different-address-checkbox');
        var container = document.getElementById('ship-to-different-address');
        if (!checkbox || !container) {
            return;
        }

        function update() {
            var isChecked = checkbox.checked;
            container.classList.toggle('hidden', !isChecked);
            container.setAttribute('aria-hidden', isChecked ? 'false' : 'true');
        }

        checkbox.addEventListener('change', update);
        update();
    }

    // ── Visual validation on blur / input ───────────────────────────────
    function bindValidation() {
        var inputs = document.querySelectorAll(
            '.woocommerce-checkout input[required], .woocommerce-checkout select[required]'
        );
        inputs.forEach(function (input) {
            input.addEventListener('invalid', function (e) {
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
        var termsCb = document.querySelector('input[name="terms"]');
        var termsLink = document.querySelector('a.woocommerce-terms-and-conditions-link');
        if (termsLink) {
            termsLink.setAttribute('target', '_blank');
        }
        // Suppress default browser tooltip — WooCommerce handles its own.
        if (termsCb) {
            termsCb.addEventListener('invalid', function (e) {
                e.preventDefault();
            });
        }
    }

    // ── Create Account toggle ────────────────────────────────────────────
    function bindCreateAccount() {
        var cb    = document.querySelector('input#createaccount');
        var fields = document.querySelector('.create-account');
        if (cb && fields) {
            cb.addEventListener('change', function () {
                fields.style.display = this.checked ? 'block' : 'none';
            });
        }
    }

    // ── Review step: when Step 3 becomes visible, sync its content ─────
    function onStepChange() {
        if (currentStep === 3) {
            syncReviewStep();
        }
    }

    // ── Update sidebar totals from WooCommerce AJAX refresh ─────────────
    // WC fires 'updated_checkout' after any cart/country/shipping/coupon change.
    // Re-sync payment extraction + review pane each time.
    function bindWooCommerceEvents() {
        jQuery(document.body).on('updated_checkout', function () {
            extractPaymentSection();
            bindPaymentSelection(); // re-bind payment radio handlers.
            if (currentStep === 3) {
                syncReviewStep();
            }
        });
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

    // ── Bootstrap on DOM ready ─────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        bindNavigation();
        bindShipToDifferent();
        bindValidation();
        bindTerms();
        bindCreateAccount();
        bindPaymentSelection();
        bindWooCommerceEvents();

        // Initial state: only Step 1 visible.
        window.dgSetStep(1);

        // Extract payment section once WC has rendered #order_review.
        // In case DOMContentLoaded fires before WC's AJAX replacement,
        // also try immediately.
        extractPaymentSection();
    });

    // Expose goToStep for external / inline use.
    window.dgGoToStep = goToStep;

})();
