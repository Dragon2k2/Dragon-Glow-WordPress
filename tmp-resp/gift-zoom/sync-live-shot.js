/**
 * Live screenshot: lightbox mở + state bên ngoài sau khi đóng.
 */
const { chromium } = require('playwright');
const path = require('path');

const URL = 'https://dragonglow.freehosting.dev/gift-cards/';

(async () => {
    const browser = await chromium.launch();
    const ctx = await browser.newContext({ viewport: { width: 1280, height: 1000 } });
    const page = await ctx.newPage();

    await page.goto(URL, { waitUntil: 'networkidle', timeout: 60000 });
    await page.waitForTimeout(1500);

    await page.locator('.dg-gift-card-image').click();
    await page.waitForTimeout(700);
    await page.locator('.dg-gift-zoom-modal__nav--next').click();
    await page.waitForTimeout(700);
    await page.screenshot({ path: path.join(__dirname, 'live-sync-modal.png') });

    await page.keyboard.press('Escape');
    await page.waitForTimeout(700);
    await page.screenshot({ path: path.join(__dirname, 'live-sync-after-close.png'), fullPage: false });

    await browser.close();
    console.log('ok');
})();