const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');
const root = process.cwd();
const theme = path.join(root, 'wp-content/themes/dragon-glow');
const out = __dirname;
const after = process.argv.includes('--after');
(async () => {
  const browser = await chromium.launch({ headless: true, channel: 'chrome' });
  const page = await browser.newPage();
  await page.goto('https://dragonglow.freehosting.dev/cookie-policy/', { waitUntil: 'domcontentloaded' });
  await page.addStyleTag({ content: fs.readFileSync(path.join(theme, 'style.css'), 'utf8') });
  await page.addStyleTag({ content: fs.readFileSync(path.join(theme, 'assets/css/cookie-policy.css'), 'utf8') });
  if (after) {
    // Mirror the translated data-label attributes emitted by the PHP partial.
    await page.evaluate(() => {
      const labels = [...document.querySelectorAll('.dg-cookie-table thead th')].map(e => e.textContent.trim());
      document.querySelectorAll('.dg-cookie-table tbody tr').forEach(row => [...row.cells].forEach((cell, i) => { if (i) cell.dataset.label = labels[i]; }));
    });
  }
  await page.evaluate(() => document.fonts.ready);
  const results = [];
  for (const width of [320, 390, 640, 641, 768, 1024, 1440]) {
    await page.setViewportSize({ width, height: 900 });
    const result = await page.evaluate(() => {
      const wrap = document.querySelector('.dg-cookie-table-scroll');
      const table = wrap.querySelector('table');
      const rect = e => { const r = e.getBoundingClientRect(); return [r.x, r.width, r.height].map(n => Math.round(n * 100) / 100); };
      return { width: innerWidth, wrapper: wrap.clientWidth, scrollWidth: wrap.scrollWidth, overflow: document.documentElement.scrollWidth - innerWidth, rows: table.tBodies[0].rows.length, cells: [...table.tBodies[0].rows].map(row => [...row.cells].map(cell => ({ text: cell.innerText, rect: rect(cell), display: getComputedStyle(cell).display, position: getComputedStyle(cell).position, label: getComputedStyle(cell, '::before').content, clipped: cell.scrollWidth > cell.clientWidth + 1 }))), table: rect(table), hint: getComputedStyle(document.querySelector('.dg-cookie-table-hint')).display };
    });
    results.push(result);
    if (width === 390) {
      await page.locator('.dg-cookie-table-scroll').scrollIntoViewIfNeeded();
      await page.screenshot({ path: path.join(out, after ? 'after-390.png' : 'before-390.png') });
    }
  }
  fs.writeFileSync(path.join(out, after ? 'after.json' : 'before.json'), JSON.stringify(results, null, 2));
  const baseline = after ? JSON.parse(fs.readFileSync(path.join(out, 'before.json'))) : null;
  for (const r of results) {
    if (r.width <= 640) {
      const pass = r.scrollWidth <= r.wrapper + 1 && r.rows === 9 && r.overflow === 0 && r.cells.every(row => row.length === 5 && row.every(c => !c.clipped));
      console.log(`${r.width}px mobile: ${pass ? 'PASS' : 'FAIL'}; wrapper=${r.wrapper}, scroll=${r.scrollWidth}, rows=${r.rows}`);
      if (after && !pass) throw new Error('Mobile content clipped');
    } else if (after) {
      if (JSON.stringify(r) !== JSON.stringify(baseline.find(b => b.width === r.width))) throw new Error(`Regression at ${r.width}px`);
      console.log(`${r.width}px tablet/desktop: unchanged PASS`);
    }
  }
  if (after) {
    await page.emulateMedia({ reducedMotion: 'reduce' });
    await page.setViewportSize({ width: 390, height: 900 });
    console.log('Reduced motion:', await page.locator('.dg-cookie-table').isVisible());
  }
  await browser.close();
})().catch(error => { console.error(error); process.exit(1); });
