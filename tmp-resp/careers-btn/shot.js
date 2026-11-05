const { chromium } = require('playwright');
const path = require('path');
(async () => {
  const b = await chromium.launch();
  const p = await b.newPage({ viewport: { width: 1280, height: 900 } });
  await p.goto('file:///' + path.resolve('tmp-resp/careers-btn/careers-btn.html').replace(/\\/g, '/'));
  await p.waitForTimeout(300);
  const e = await p.$('#car-close');
  await e.screenshot({ path: 'tmp-resp/careers-btn/el-closing.png' });
  await p.hover('#car-close');
  await p.waitForTimeout(500);
  const h = await p.$('#car-close');
  await h.screenshot({ path: 'tmp-resp/careers-btn/el-closing-hover.png' });
  const s = await p.$('#sus-close');
  await s.screenshot({ path: 'tmp-resp/careers-btn/el-sus-closing.png' });
  await b.close();
})();
