/**
 * Dragon Glow — Account Page JS
 * Toggle between Login form and Register form.
 *
 * @package Dragon_Glow
 */

(function () {
    'use strict';

    // ── Auth Tab Toggle ──────────────────────────────────────
    window.dgToggleAuth = function (view) {
        var loginForm = document.getElementById('dg-login-form');
        var regForm = document.getElementById('dg-register-form');
        var loginTab = document.getElementById('dg-login-tab');
        var regTab = document.getElementById('dg-register-tab');

        if (!loginForm || !regForm) return;

        var showLogin = view === 'login';

        // Toggle forms
        loginForm.classList.toggle('hidden', !showLogin);
        regForm.classList.toggle('hidden', showLogin);

        // Toggle tabs
        [loginTab, regTab].forEach(function (tab) {
            if (!tab) return;
            tab.classList.remove('bg-primary', 'text-white');
            tab.classList.add('text-on-surface-variant');
        });

        var activeTab = showLogin ? loginTab : regTab;
        if (activeTab) {
            activeTab.classList.add('bg-primary', 'text-white');
            activeTab.classList.remove('text-on-surface-variant');
        }

        // Scroll to form on mobile
        if (window.innerWidth < 768) {
            var targetForm = showLogin ? loginForm : regForm;
            if (targetForm) {
                targetForm.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    };

    // ── Password Visibility Toggle ─────────────────────────────
    document.querySelectorAll('.password-toggle').forEach(function (toggle) {
        toggle.addEventListener('click', function () {
            var input = this.parentElement.querySelector('input');
            if (!input) return;

            if (input.type === 'password') {
                input.type = 'text';
                this.querySelector('.material-symbols-outlined').textContent = 'visibility_off';
            } else {
                input.type = 'password';
                this.querySelector('.material-symbols-outlined').textContent = 'visibility';
            }
        });
    });

    // ── Password Strength Indicator ────────────────────────────
    var passwordInput = document.getElementById('reg_password') || document.getElementById('password');
    if (passwordInput) {
        var strengthMeter = document.createElement('div');
        strengthMeter.className = 'password-strength mt-2';

        var strengthText = document.createElement('span');
        strengthText.className = 'text-label-sm';

        strengthMeter.appendChild(strengthText);
        passwordInput.parentElement.appendChild(strengthMeter);

        passwordInput.addEventListener('input', function () {
            var password = this.value;
            var strength = 0;
            var text = '';

            if (password.length >= 8) strength++;
            if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;

            switch (strength) {
                case 0:
                case 1:
                    text = 'Weak';
                    strengthText.style.color = '#ba1a1a';
                    break;
                case 2:
                    text = 'Fair';
                    strengthText.style.color = '#e65100';
                    break;
                case 3:
                    text = 'Good';
                    strengthText.style.color = '#7b5455';
                    break;
                case 4:
                    text = 'Strong';
                    strengthText.style.color = '#2e7d32';
                    break;
            }

            strengthText.textContent = password.length > 0 ? 'Password strength: ' + text : '';
        });
    }

    // ── Remember Me Animation ─────────────────────────────────
    var rememberMe = document.querySelector('input[name="rememberme"]');
    if (rememberMe) {
        rememberMe.addEventListener('change', function () {
            var label = this.nextElementSibling;
            if (label) {
                label.style.opacity = this.checked ? '1' : '0.7';
            }
        });
    }

    // ── Initialize on load ────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        // Default to login view
        if (typeof window.dgToggleAuth === 'function') {
            window.dgToggleAuth('login');
        }

        // Add password toggle buttons
        document.querySelectorAll('input[type="password"]').forEach(function (input) {
            if (!input.closest('.password-wrapper')) {
                var wrapper = document.createElement('div');
                wrapper.className = 'password-wrapper relative';

                input.parentElement.insertBefore(wrapper, input);
                wrapper.appendChild(input);

                var toggle = document.createElement('button');
                toggle.type = 'button';
                toggle.className = 'password-toggle absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant hover:text-primary';
                toggle.innerHTML = '<span class="material-symbols-outlined text-lg">visibility</span>';

                toggle.addEventListener('click', function () {
                    if (input.type === 'password') {
                        input.type = 'text';
                        this.querySelector('.material-symbols-outlined').textContent = 'visibility_off';
                    } else {
                        input.type = 'password';
                        this.querySelector('.material-symbols-outlined').textContent = 'visibility';
                    }
                });

                wrapper.appendChild(toggle);
            }
        });
    });

})();
