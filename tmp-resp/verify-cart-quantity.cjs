const { chromium } = require('playwright');
const fs = require('fs');
const assert = require('node:assert/strict');
const money = value => `<span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol">$</span>${value.toFixed(2)}</bdi></span>`;
const snapshot = quantities => ({ subtotal: quantities.key * 32 + quantities.other * 10, total: quantities.key * 32 + quantities.other * 10, decimals: 2, decimal: '.', thousand: ',', lines: Object.fromEntries(Object.entries(quantities).map(([key, quantity]) => [key, { quantity, subtotal: quantity * (key === 'key' ? 32 : 10), total: quantity * (key === 'key' ? 32 : 10) }])) });
const summary = quantities => `<span id="dg-cart-subtotal" data-qty-pricing='${JSON.stringify(snapshot(quantities))}'>${money(snapshot(quantities).subtotal)}</span>`;
const row = (key, unit) => `<div id="dg-row-${key}" class="dg-cart-row" data-quantity="1"><input type="checkbox" data-dg-cart-select value="${key}"><div class="dg-qty-stepper" data-cart-key="${key}" data-qty="1"><button type="button" class="dg-qty-stepper-btn dg-qty-decrease" disabled>−</button><span class="dg-qty-value">1</span><button type="button" class="dg-qty-stepper-btn dg-qty-increase">+</button></div><div class="dg-cart-row__price-cell"><span>${money(unit)}</span></div></div>`;
(async () => {
    const browser = await chromium.launch({ channel: 'chrome', headless: true });
    try {
        const page = await browser.newPage();
        const errors = [];
        page.on('pageerror', error => errors.push(error.message));
        let quantities, requests, fail;
        await page.route('https://cart.test/**', async route => {
            if (route.request().resourceType() === 'document') {
                quantities = { key: 1, other: 1 }; requests = []; fail = false;
                return route.fulfill({ contentType: 'text/html', body: `<html><body><form id="dg-cart-form"><input type="checkbox" data-dg-cart-select-all>${row('key', 32)}${row('other', 10)}</form><aside class="dg-cart-summary"><div id="dg-cart-summary-totals">${summary(quantities)}</div><div id="dg-cart-total"><strong>${money(42)}</strong></div><button data-dg-cart-checkout-with-selected>Checkout</button></aside><div id="dg-cart-toasts"></div><script>window.dgAjax={url:'https://cart.test/ajax',nonce:'test'};window.DGCart={removeMany(){},clear(){}};</script><script>${fs.readFileSync('wp-content/themes/dragon-glow/assets/js/cart.js', 'utf8')}</script></body></html>` });
            }
            const body = route.request().postData();
            const match = body.match(/name="quantity"\r\n\r\n(\d+)/);
            if (!match) return route.fulfill({ contentType: 'application/json', body: '{"success":true,"data":{}}' });
            const key = body.match(/name="cart_item_key"\r\n\r\n([^\r]+)/)[1];
            const quantity = Number(match[1]);
            const rejected = fail;
            requests.push({ key, quantity });
            await new Promise(resolve => setTimeout(resolve, 450));
            if (!rejected) quantities[key] = quantity;
            return route.fulfill({ contentType: 'application/json', body: JSON.stringify(rejected ? { success: false, data: { message: 'Stock limit' } } : { success: true, data: { quantity, summary_html: summary(quantities), total_html: `<strong>${money(snapshot(quantities).total)}</strong>`, line_totals: Object.fromEntries(Object.entries(snapshot(quantities).lines).map(([key, line]) => [key, money(line.subtotal)])), cart_count: Object.values(quantities).reduce((a, b) => a + b, 0) } }) });
        });
        for (const width of [320, 390, 640, 768, 1024, 1280]) {
            await page.setViewportSize({ width, height: 900 });
            await page.emulateMedia({ reducedMotion: width === 640 ? 'reduce' : 'no-preference' });
            await page.goto('https://cart.test/');
            assert.equal(await page.locator('#dg-cart-total').textContent(), '$0.00');
            await page.locator('[data-dg-cart-select][value="key"]').check();
            assert.equal(await page.locator('#dg-cart-total').textContent(), '$32.00');
            await page.locator('[data-dg-cart-select-all]').check();
            assert.equal(await page.locator('#dg-cart-total').textContent(), '$42.00');
            const change = async (key, direction, expected) => {
                const result = await page.evaluate(({ key, direction }) => {
                    const start = performance.now();
                    document.querySelector(`#dg-row-${key} .dg-qty-${direction}`).click();
                    return { total: document.querySelector('#dg-cart-total').textContent, subtotal: document.querySelector('#dg-cart-subtotal').textContent, elapsed: performance.now() - start };
                }, { key, direction });
                assert.equal(result.total, expected);
                assert.equal(result.subtotal, expected);
                assert.ok(result.elapsed < 50, `Immediate paint took ${result.elapsed}ms`);
            };
            await change('key', 'increase', '$74.00');
            await change('key', 'increase', '$106.00');
            await change('key', 'decrease', '$74.00');
            assert.equal(requests.length, 0, 'Prices must update before request');
            await page.waitForFunction(() => !document.querySelector('.dg-cart-summary').hasAttribute('aria-busy'));
            assert.deepEqual(requests, [{ key: 'key', quantity: 2 }]);
            await change('key', 'increase', '$106.00');
            await page.waitForTimeout(230);
            await change('key', 'increase', '$138.00');
            await change('other', 'increase', '$148.00');
            await page.waitForTimeout(300);
            assert.equal(await page.locator('#dg-cart-total').textContent(), '$148.00', 'Earlier response must not revert latest total');
            await page.waitForFunction(() => !document.querySelector('.dg-cart-summary').hasAttribute('aria-busy'));
            assert.equal(await page.locator('#dg-cart-total').textContent(), '$148.00');
            assert.deepEqual(quantities, { key: 4, other: 2 });
            fail = true;
            await change('key', 'increase', '$180.00');
            await page.waitForFunction(() => document.querySelector('#dg-cart-toasts').textContent.includes('Stock limit'));
            assert.equal(await page.locator('#dg-cart-total').textContent(), '$148.00');
            assert.equal(await page.locator('#dg-row-key .dg-qty-value').textContent(), '4');
            assert.equal(await page.locator('[data-dg-cart-checkout-with-selected]').isDisabled(), false);
            fail = false;
            await page.locator('[data-dg-cart-select][value="other"]').uncheck();
            assert.equal(await page.locator('#dg-cart-total').textContent(), '$128.00');
            await change('other', 'increase', '$128.00');
            await page.waitForFunction(() => !document.querySelector('.dg-cart-summary').hasAttribute('aria-busy'));
            assert.equal(await page.locator('#dg-cart-total').textContent(), '$128.00');
            assert.equal(await page.locator('#dg-row-other .dg-cart-row__price-cell').textContent(), '$30.00');
            await page.locator('[data-dg-cart-select][value="key"]').uncheck();
            assert.equal(await page.locator('#dg-cart-total').textContent(), '$0.00');
            assert.equal(await page.locator('#dg-cart-subtotal').textContent(), '$0.00');
            await page.locator('[data-dg-cart-select-all]').check();
            assert.equal(await page.locator('#dg-cart-total').textContent(), '$158.00');
            await page.locator('[data-dg-cart-select-all]').uncheck();
            assert.equal(await page.locator('#dg-cart-total').textContent(), '$0.00');
            assert.equal(page.url(), 'https://cart.test/');
            assert.deepEqual(errors, []);
            console.log('PASS', width, 'synchronous price, burst coalescing, multiple rows, stale response, rollback, reduced motion');
        }
    } finally { await browser.close(); }
})().catch(error => { console.error(error); process.exitCode = 1; });
