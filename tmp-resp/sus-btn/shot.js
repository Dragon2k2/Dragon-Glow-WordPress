const { chromium } = require('playwright');
const path = require('path');
(async () => {
  const b = await chromium.launch();
  const p = await b.newPage({ viewport: { width: 1280, height: 900 } });
  await p.goto('file:///' + path.resolve('tmp-resp/sus-btn/sus-btn.html').replace(/\\/g, '/'));
  await p.waitForTimeout(300);
  for (const id of ['#sus', '#sus-close', '#oi', '#car']) {
    const el = await p.$(id);
    await el.screenshot({ path: 'tmp-resp/sus-btn/el-' + id.replace('#', '') + '.png' });
  }
  await p.hover('#sus');
  await p.waitForTimeout(500);
  const h = await p.$('#sus');
  await h.screenshot({ path: 'tmp-resp/sus-btn/el-sus-hover.png' });
  await b.close();
})();
