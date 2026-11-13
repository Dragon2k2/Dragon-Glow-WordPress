/**
 * Verify gallery lightbox (prev/next + counter + image-click trigger) on mock.
 */
const { chromium } = require('playwright');
const path = require('path');

(async () => {
    const browser = await chromium.launch();
    const ctx = await browser.newContext({ viewport: { width: 1280, height: 900 } });
    const page = await ctx.newPage();
    const errors = [];
    page.on('pageerror', (e) => errors.push('PAGEERROR: ' + e.message));

    await page.goto('file:///' + path.join(__dirname, 'gallery-mock.html').replace(/\\/g, '/'));
    await page.waitForTimeout(1200);

    // 1. Click the image itself → modal opens.
    await page.locator('.dg-gift-card-image').click();
    await page.waitForTimeout(600);

    const state1 = await page.evaluate(() => {
        const m = document.getElementById('dg-gift-zoom-modal');
        const img = document.getElementById('dg-gift-zoom-image');
        const c = document.getElementById('dg-gift-zoom-counter');
        const prev = document.querySelector('.dg-gift-zoom-modal__nav--prev');
        const next = document.querySelector('.dg-gift-zoom-modal__nav--next');
        return {
            open: m.classList.contains('is-open'),
            display: getComputedStyle(m).display,
            ariaHidden: m.getAttribute('aria-hidden'),
            src: img.getAttribute('src'),
            counter: c.textContent,
            prevHidden: prev.hidden,
            nextHidden: next.hidden,
            prevDisplay: getComputedStyle(prev).display,
            nextDisplay: getComputedStyle(next).display,
            bodyOverflow: document.body.style.overflow,
        };
    });

    // 2. Next → moves to card-$250
    await page.locator('.dg-gift-zoom-modal__nav--next').click();
    await page.waitForTimeout(400);
    const state2 = await page.evaluate(() => ({
        src: document.getElementById('dg-gift-zoom-image').getAttribute('src'),
        counter: document.getElementById('dg-gift-zoom-counter').textContent,
    }));

    // 3. Next → card-$500
    await page.locator('.dg-gift-zoom-modal__nav--next').click();
    await page.waitForTimeout(400);
    const state3 = await page.evaluate(() => ({
        src: document.getElementById('dg-gift-zoom-image').getAttribute('src'),
        counter: document.getElementById('dg-gift-zoom-counter').textContent,
    }));

    // 4. Next again → wraps to card-$50
    await page.locator('.dg-gift-zoom-modal__nav--next').click();
    await page.waitForTimeout(400);
    const state4 = await page.evaluate(() => ({
        src: document.getElementById('dg-gift-zoom-image').getAttribute('src'),
        counter: document.getElementById('dg-gift-zoom-counter').textContent,
    }));

    // 5. Prev → wraps back to card-$500
    await page.locator('.dg-gift-zoom-modal__nav--prev').click();
    await page.waitForTimeout(400);
    const state5 = await page.evaluate(() => ({
        src: document.getElementById('dg-gift-zoom-image').getAttribute('src'),
        counter: document.getElementById('dg-gift-zoom-counter').textContent,
    }));

    // 6. Keyboard ArrowRight
    await page.keyboard.press('ArrowRight');
    await page.waitForTimeout(300);
    const state6 = await page.evaluate(() => ({
        src: document.getElementById('dg-gift-zoom-image').getAttribute('src'),
        counter: document.getElementById('dg-gift-zoom-counter').textContent,
    }));

    // 7. Escape closes
    await page.keyboard.press('Escape');
    await page.waitForTimeout(300);
    const state7 = await page.evaluate(() => ({
        display: getComputedStyle(document.getElementById('dg-gift-zoom-modal')).display,
        bodyOverflow: document.body.style.overflow,
    }));

    // 8. Zoom button still opens (regression) at current denomination
    await page.locator('.dg-zoom-btn').click();
    await page.waitForTimeout(400);
    const state8 = await page.evaluate(() => ({
        open: document.getElementById('dg-gift-zoom-modal').classList.contains('is-open'),
        counter: document.getElementById('dg-gift-zoom-counter').textContent,
    }));

    await page.screenshot({ path: path.join(__dirname, 'gallery-modal.png') });

    console.log(JSON.stringify({ state1, state2, state3, state4, state5, state6, state7, state8, errors }, null, 2));
    await browser.close();
})();
