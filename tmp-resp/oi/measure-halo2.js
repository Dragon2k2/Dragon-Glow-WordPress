/**
 * Đo chính xác hiệu quả text-shadow halo.
 *
 * Với chữ TỐI trên ảnh: nền hiệu dụng quanh glyph = trung vị các pixel SÁNG
 * trong bbox (chính là halo), không phải median của ảnh nền thô.
 * Contrast thực = contrast(pixel glyph tối nhất, median pixel halo).
 */
const { chromium } = require('playwright');
const sharp = require('sharp');
const fs = require('fs');
const path = require('path');

const URL = 'https://dragonglow.freehosting.dev/our-ingredients/';
const OUT = path.join(__dirname, 'traceable-nobg');
fs.mkdirSync(OUT, { recursive: true });

const HALOS = {
    soft: '0 0 12px rgba(252,249,248,0.9), 0 0 26px rgba(252,249,248,0.7)',
    strong: '0 0 10px rgba(252,249,248,1), 0 0 22px rgba(252,249,248,0.95), 0 0 40px rgba(252,249,248,0.85)',
    stronger: '0 0 8px rgba(252,249,248,1), 0 0 18px rgba(252,249,248,1), 0 0 32px rgba(252,249,248,0.95), 0 0 52px rgba(252,249,248,0.9)',
};

function buildCss(halo, textColor, bodyColor) {
    return `
.dg-oi-traceable-content {
    position: relative; z-index: 1;
    max-width: 600px;
    margin: 0 var(--oi-margin-mobile);
    padding: 0;
    background: transparent !important;
    border: 0 !important;
    border-radius: 0 !important;
    box-shadow: none !important;
    backdrop-filter: none !important;
    -webkit-backdrop-filter: none !important;
}
@media (min-width: 768px) {
    .dg-oi-traceable-content { margin-left: var(--oi-margin-desktop); margin-right: var(--oi-margin-desktop); }
}
[data-page="our-ingredients"] .dg-oi-traceable-headline { color: ${textColor}; text-shadow: ${halo}; }
[data-page="our-ingredients"] .dg-oi-traceable-body { color: ${bodyColor}; text-shadow: ${halo}; }
[data-page="our-ingredients"] .dg-oi-traceable-country { color: ${textColor}; border-bottom: 1px solid rgba(28,27,27,0.35); text-shadow: ${halo}; }
[data-page="our-ingredients"] .dg-oi-traceable-footnote { color: rgba(28,27,27,0.92); text-shadow: ${halo}; }
`;
}

const TARGETS = [
    { key: 'headline', sel: '.dg-oi-traceable-headline' },
    { key: 'body', sel: '.dg-oi-traceable-body' },
    { key: 'country', sel: '.dg-oi-traceable-country' },
    { key: 'footnote', sel: '.dg-oi-traceable-footnote' },
];

function srgbToLin(v) { const s = v / 255; return s <= 0.04045 ? s / 12.92 : Math.pow((s + 0.055) / 1.055, 2.4); }
function relLum([r, g, b]) { return 0.2126 * srgbToLin(r) + 0.7152 * srgbToLin(g) + 0.0722 * srgbToLin(b); }
function contrast(l1, l2) { const [hi, lo] = l1 > l2 ? [l1, l2] : [l2, l1]; return (hi + 0.05) / (lo + 0.05); }
const luma = ([r, g, b]) => 0.2126 * r + 0.7152 * g + 0.0722 * b;

async function region(file, rect) {
    const { data, info } = await sharp(file).extract({
        left: Math.max(0, Math.round(rect.x)), top: Math.max(0, Math.round(rect.y)),
        width: Math.max(1, Math.round(rect.w)), height: Math.max(1, Math.round(rect.h)),
    }).raw().toBuffer({ resolveWithObject: true });
    const px = [];
    for (let i = 0; i < data.length; i += info.channels) px.push([data[i], data[i + 1], data[i + 2]]);
    return px;
}
const median = (a) => { const v = [...a].sort((x, y) => x - y); return v[Math.floor(v.length / 2)]; };

(async () => {
    const browser = await chromium.launch({ channel: 'chrome' });
    const out = [];

    for (const [haloName, halo] of Object.entries(HALOS)) {
        const css = buildCss(halo, '#1b1c1a', '#504444');
        for (const vp of [{ name: '390', width: 390, height: 844 }, { name: '768', width: 768, height: 1024 }, { name: '1280', width: 1280, height: 900 }]) {
            const ctx = await browser.newContext({ viewport: { width: vp.width, height: vp.height } });
            const page = await ctx.newPage();
            await page.goto(URL, { waitUntil: 'networkidle', timeout: 60000 });
            await page.addStyleTag({ content: css });
            await page.evaluate(() => {
                const ov = document.querySelector('.dg-oi-traceable-overlay');
                if (ov) ov.remove();
                document.querySelector('#traceable').scrollIntoView({ block: 'center' });
            });
            await page.waitForTimeout(900);

            const sec = await page.locator('#traceable').boundingBox();
            const meta = await page.evaluate(({ targets, sec }) => {
                const res = {};
                targets.forEach((t) => {
                    const el = document.querySelector(t.sel);
                    if (!el) return;
                    const r = el.getBoundingClientRect();
                    res[t.key] = { rect: { x: r.x - sec.x, y: r.y - sec.y, w: r.width, h: r.height }, color: getComputedStyle(el).color, fontSize: getComputedStyle(el).fontSize, weight: getComputedStyle(el).fontWeight };
                });
                return res;
            }, { targets: TARGETS, sec });

            const shotTxt = path.join(OUT, `halo-${haloName}-${vp.name}.png`);
            await page.locator('#traceable').screenshot({ path: shotTxt });

            const row = { halo: haloName, viewport: vp.name, texts: {} };
            for (const t of TARGETS) {
                const m = meta[t.key];
                if (!m) continue;
                const txtPx = await region(shotTxt, m.rect);
                const lums = txtPx.map(luma).sort((a, b) => a - b);
                /* glyph: 3% tối nhất */
                const glyphLuma = lums[Math.floor(lums.length * 0.03)];
                /* halo: median của các pixel nằm trên 60% percentile (nền sáng quanh glyph) */
                const haloSet = lums.filter((v) => v >= lums[Math.floor(lums.length * 0.6)]);
                const haloLuma = median(haloSet);
                const c = m.color.match(/rgba?\(([^)]+)\)/)[1].split(',').map((v) => parseFloat(v));
                const alpha = c.length > 3 ? c[3] : 1;
                const fgRaw = relLum([c[0], c[1], c[2]]);
                const fgEff = alpha < 1 ? fgRaw * alpha + relLum([haloLuma, haloLuma, haloLuma]) * (1 - alpha) : fgRaw;
                const ratio = +contrast(fgEff, relLum([haloLuma, haloLuma, haloLuma])).toFixed(2);
                row.texts[t.key] = {
                    fontSize: m.fontSize,
                    weight: m.weight,
                    glyphLuma: +glyphLuma.toFixed(1),
                    haloLuma: +haloLuma.toFixed(1),
                    effectiveContrast: ratio,
                    largeText: parseFloat(m.fontSize) >= 24 || (parseFloat(m.fontSize) >= 18.66 && parseInt(m.weight, 10) >= 700),
                };
            }
            out.push(row);
            await ctx.close();
        }
    }
    await browser.close();
    console.log(JSON.stringify(out, null, 2));
})();
