/**
 * Dragon Glow — Mobile Menu Animation
 * Slide-in drawer từ phải qua trái với Motion vanilla API
 * 
 * @package Dragon_Glow
 */

import { animate, stagger } from "https://cdn.jsdelivr.net/npm/motion@11/+esm";

(function () {
    'use strict';

    // Check prefers-reduced-motion
    const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // Get elements
    const menuToggle = document.getElementById('dg-mobile-menu-toggle');
    const mobileMenu = document.getElementById('dg-mobile-menu');
    const backdrop = document.getElementById('dg-mobile-menu-backdrop');
    const menuClose = document.getElementById('dg-mobile-menu-close');
    const menuItems = document.querySelectorAll('.dg-mobile-menu__list .menu-item');

    if (!menuToggle || !mobileMenu || !backdrop || !menuClose) return;

    let isOpen = false;

    /**
     * Open mobile menu với slide-in animation
     */
    function openMenu() {
        if (isOpen) return;
        isOpen = true;

        // Show elements — set visibility before animation
        backdrop.style.visibility = 'visible';
        backdrop.classList.add('active');

        // Update ARIA
        menuToggle.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';  // Prevent scroll

        if (prefersReduced) {
            // No animation — instant show
            mobileMenu.style.visibility = 'visible';
            mobileMenu.style.transform = 'translateX(0)';
        } else {
            // Reset initial state trước khi animate để tránh CSS conflict
            mobileMenu.style.transform = 'translateX(100%)';
            mobileMenu.style.visibility = 'visible';

            // Slide in từ phải qua trái — timing mượt mà
            animate(
                mobileMenu,
                { x: ['100%', '0%'] },
                { 
                    duration: 0.45,  // 450ms — cân bằng giữa smooth và responsive
                    ease: [0.22, 1, 0.36, 1]  // ease-out-expo — smooth acceleration
                }
            );

            // Stagger menu items — fade-in lần lượt
            animate(
                menuItems,
                { opacity: [0, 1], x: [20, 0] },
                { 
                    duration: 0.4,
                    delay: stagger(0.04, { start: 0.15 }),  // Start sau khi drawer vào 0.15s
                    ease: 'easeOut'
                }
            );
        }
    }

    /**
     * Close mobile menu với slide-out animation
     */
    function closeMenu() {
        if (!isOpen) return;
        isOpen = false;

        // Update ARIA
        menuToggle.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';  // Restore scroll

        // Hide backdrop
        backdrop.classList.remove('active');

        if (prefersReduced) {
            // No animation — instant hide
            mobileMenu.style.transform = 'translateX(100%)';
            mobileMenu.style.visibility = 'hidden';
            backdrop.style.visibility = 'hidden';
        } else {
            // Slide out sang phải — timing khớp với open animation
            const menuAnimation = animate(
                mobileMenu,
                { x: ['0%', '100%'] },
                { 
                    duration: 0.45,  // 450ms — khớp với open
                    ease: [0.64, 0, 0.78, 0]  // ease-in-expo — mirror open easing
                }
            );

            menuAnimation.finished.then(() => {
                // Hide sau khi animation xong
                mobileMenu.style.visibility = 'hidden';
                backdrop.style.visibility = 'hidden';
            });

            // Fade-out menu items
            animate(
                menuItems,
                { opacity: [1, 0] },
                { 
                    duration: 0.25,
                    ease: 'easeIn'
                }
            );
        }
    }

    // Toggle menu khi click hamburger button
    menuToggle.addEventListener('click', function (e) {
        e.stopPropagation();
        if (isOpen) {
            closeMenu();
        } else {
            openMenu();
        }
    });

    // Close menu khi click close button
    menuClose.addEventListener('click', function (e) {
        e.stopPropagation();
        closeMenu();
    });

    // Close khi click backdrop
    backdrop.addEventListener('click', closeMenu);

    // Close khi click bên ngoài menu (không bao gồm toggle button)
    document.addEventListener('click', function (e) {
        if (isOpen && 
            !mobileMenu.contains(e.target) && 
            !menuToggle.contains(e.target)) {
            closeMenu();
        }
    });

    // Close khi press ESC
    document.addEventListener('keydown', function (e) {
        if (isOpen && e.key === 'Escape') {
            closeMenu();
        }
    });

    // Optimistic active transfer — bấm mục mới thì mục cũ nhả active ngay
    // trong lúc trình duyệt đang load trang mới (server chưa kịp render
    // current-menu-item). Chỉ đổi class, không chặn navigation.
    const menuLinks = mobileMenu.querySelectorAll('.dg-mobile-menu__list .menu-item a');
    menuLinks.forEach(function (link) {
        link.addEventListener('click', function (e) {
            // Bỏ qua mở tab mới (Ctrl/Cmd+click, chuột giữa, target=_blank):
            // trang hiện tại không unload nên giữ nguyên active cũ.
            if (e.ctrlKey || e.metaKey || e.shiftKey || e.altKey || e.button === 1 || link.target === '_blank') return;
            const clickedItem = link.closest('.menu-item');
            if (!clickedItem || clickedItem.classList.contains('current-menu-item')) return;
            menuItems.forEach(function (item) {
                item.classList.remove('current-menu-item', 'current_page_item');
                const oldLink = item.querySelector('a');
                if (oldLink) oldLink.removeAttribute('aria-current');
            });
            clickedItem.classList.add('current-menu-item');
            link.setAttribute('aria-current', 'page');
        });
    });

})();
