/**
 * Chẩn đoán: modal Return (shipping-returns) — nút Close (×) và Cancel.
 * Mục tiêu: xác định vì sao click không hoạt động.
 */
const { chromium } = require('playwright');
const path = require('path');

const URL = 'https://dragonglow.freehosting.dev/shipping-returns/';

(async () => {
    const browser = await chromium.launch({ channel: 'chrome' });
    const ctx = await browser.newContext({ viewport: { width: 1280, height: 900 } });
    const page = await ctx.newPage();
    const errors = [];
    const consoleErrs = [];
    page.on('pageerror', (e) => errors.push(String(e.message)));
    page.on('console', (m) => { if (m.type() === 'error') consoleErrs.push(m.text()); });

    await page.goto(URL, { waitUntil: 'networkidle', timeout: 60000 });
    await page.waitForTimeout(800);

    const state = await page.evaluate(() => {
        const modal = document.getElementById('dg-return-modal');
        const trigger = document.querySelector('[data-return-trigger]');
        const closeBtn = document.querySelector('.dg-rm-close');
        const cancelBtn = document.querySelector('.dg-rm-btn--secondary');
        return {
            modalExists: !!modal,
            modalHidden: modal ? modal.hasAttribute('hidden') : null,
            triggerExists: !!trigger,
            closeBtnExists: !!closeBtn,
            cancelBtnExists: !!cancelBtn,
            scriptTags: [...document.querySelectorAll('script[type="module"]')].map((s) => s.src || '(inline)'),
            classicScripts: [...document.querySelectorAll('script[src]')].map((s) => s.src).filter((s) => s.includes('shipping') || s.includes('main')),
        };
    });

    /* mở modal bằng trigger */
    await page.evaluate(() => document.querySelector('[data-return-trigger]')?.click());
    await page.waitForTimeout(700);

    const afterOpen = await page.evaluate(() => {
        const modal = document.getElementById('dg-return-modal');
        return {
            hidden: modal.hasAttribute('hidden'),
            dataOpen: modal.hasAttribute('data-open'),
            bodyLock: document.body.classList.contains('dg-rm-open'),
            visible: modal ? getComputedStyle(modal).display : null,
        };
    });

    /* click nút Close (×) */
    let closeResult = null;
    if (afterOpen && !afterOpen.hidden) {
        await page.locator('.dg-rm-close').click();
        await page.waitForTimeout(700);
        closeResult = await page.evaluate(() => {
            const modal = document.getElementById('dg-return-modal');
            return {
                hidden: modal.hasAttribute('hidden'),
                dataOpen: modal.hasAttribute('data-open'),
                bodyLock: document.body.classList.contains('dg-rm-open'),
            };
        });
    }

    /* mở lại rồi click Cancel */
    await page.evaluate(() => document.querySelector('[data-return-trigger]')?.click());
    await page.waitForTimeout(700);
    const opened2 = await page.evaluate(() => !document.getElementById('dg-return-modal').hasAttribute('hidden'));

    let cancelResult = null;
    if (opened2) {
        await page.locator('.dg-rm-btn--secondary').click();
        await page.waitForTimeout(700);
        cancelResult = await page.evaluate(() => {
            const modal = document.getElementById('dg-return-modal');
            return {
                hidden: modal.hasAttribute('hidden'),
                dataOpen: modal.hasAttribute('data-open'),
                bodyLock: document.body.classList.contains('dg-rm-open'),
            };
        });
    }

    console.log(JSON.stringify({ state, afterOpen, closeResult, opened2, cancelResult, errors, consoleErrs }, null, 2));
    await browser.close();
})();
