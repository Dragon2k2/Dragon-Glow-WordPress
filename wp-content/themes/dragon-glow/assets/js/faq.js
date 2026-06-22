/**
 * Dragon Glow — FAQ Accordion & Search
 *
 * @package Dragon_Glow
 */
(function () {
    'use strict';

    /* ── Scroll reveal for groups ─────────────────────────────────────────────── */
    var groups = document.querySelectorAll('.dg-faq-group');
    if (groups.length) {
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('dg-faq-group-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });
        groups.forEach(function (group) { observer.observe(group); });
    }

    /* ── Accordion ────────────────────────────────────────────────────────────── */
    var accordion = document.getElementById('dg-faq-accordion');
    if (!accordion) return;

    var triggers = accordion.querySelectorAll('.dg-faq-trigger');
    var panels   = accordion.querySelectorAll('.dg-faq-panel');

    function getTargetPanel(trigger) {
        var panelId = trigger.getAttribute('aria-controls');
        return panelId ? document.getElementById(panelId) : null;
    }

    function openPanel(trigger, panel) {
        trigger.setAttribute('aria-expanded', 'true');
        panel.hidden = false;
        panel.classList.add('dg-panel-open');
    }

    function closePanel(trigger, panel) {
        trigger.setAttribute('aria-expanded', 'false');
        panel.classList.remove('dg-panel-open');
        // Delay removing hidden so CSS transition plays out
        panel.addEventListener('transitionend', function onEnd() {
            panel.removeAttribute('hidden');
            panel.removeEventListener('transitionend', onEnd);
        });
    }

    function closeAllPanels() {
        triggers.forEach(function (t) { t.setAttribute('aria-expanded', 'false'); });
        panels.forEach(function (p) {
            p.classList.remove('dg-panel-open');
            p.removeAttribute('hidden');
            p.style.maxHeight = '';
        });
    }

    triggers.forEach(function (trigger) {
        trigger.addEventListener('click', function () {
            var panel = getTargetPanel(trigger);
            if (!panel) return;
            var isOpen = trigger.getAttribute('aria-expanded') === 'true';

            if (isOpen) {
                closePanel(trigger, panel);
            } else {
                openPanel(trigger, panel);
            }
        });

        // Keyboard support
        trigger.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                trigger.click();
            }
        });
    });

    /* ── Search / filter ───────────────────────────────────────────────────────── */
    var searchInput  = document.getElementById('dg-faq-search');
    var clearBtn     = document.getElementById('dg-faq-search-clear');
    var emptyState   = document.getElementById('dg-faq-empty');
    var noResultsMsg = document.getElementById('dg-faq-search-count');

    if (searchInput) {
        function normalise(str) {
            return str.toLowerCase().replace(/['']/g, "'");
        }

        function filterFAQs() {
            var query = normalise(searchInput.value.trim());
            var hasQuery = query.length > 0;
            var visibleGroups = 0;
            var visibleItems = 0;

            // Show/hide clear button
            if (clearBtn) {
                clearBtn.classList.toggle('hidden', !hasQuery);
            }

            // Walk each group
            groups.forEach(function (group) {
                var listItems = group.querySelectorAll('.dg-faq-item');
                var visibleInGroup = 0;

                listItems.forEach(function (item) {
                    var question = item.querySelector('.dg-faq-question-text');
                    var answer   = item.querySelector('.dg-faq-answer');
                    if (!question) return;

                    var questionText = normalise(question.textContent || '');
                    var answerText   = answer ? normalise(answer.textContent || '') : '';
                    var matches = questionText.includes(query) || answerText.includes(query);

                    item.classList.toggle('dg-hidden', !matches);
                    if (matches) {
                        visibleInGroup++;
                        visibleItems++;
                    }
                });

                group.classList.toggle('dg-hidden', visibleInGroup === 0);
                if (visibleInGroup > 0) visibleGroups++;
            });

            // Toggle empty / no-results states
            if (emptyState) {
                emptyState.classList.toggle('hidden', visibleGroups > 0);
            }
            if (noResultsMsg) {
                noResultsMsg.classList.toggle('dg-visible', hasQuery && visibleItems === 0);
            }
        }

        searchInput.addEventListener('input', filterFAQs);

        if (clearBtn) {
            clearBtn.addEventListener('click', function () {
                searchInput.value = '';
                searchInput.focus();
                filterFAQs();
            });
        }
    }
})();
