/**
 * So sánh phương án giữ chữ trực tiếp trên ảnh (không nền card):
 *   A) chữ tối + halo sáng (text-shadow trắng)
 *   B) chữ trắng + halo tối (text-shadow đen)
 *
 * Đo: ảnh nền thuần (chữ ẩn) + ảnh có chữ → so pixel glyph với pixel nền liền kề.
 * Báo cả "contrast theo màu nền" (worst-case) lẫn "contrast thực tế đo từ pixel".
 */
const { chromium } = require('playwright');
const sharp = require('sharp');
const fs = require('fs');
const path = require('path');

const URL = 'https://dragonglow.freehosting.dev/our-ingredients/';
const OUT = path.join(__dirname, 'traceable-nobg');
fs.mkdirSync(OUT, { recursive: true });

const BASE = `
.dg-oi-traceable-content {
    position: relative;
    z-index: 1;
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
    .dg-oi-traceable-content {
        margin-left: var(--oi-margin-desktop);
        margin-right: var(--oi-margin-desktop);
    }
}
`;

/* A: chữ tối + halo sáng */
const VAR_A = BASE + `
[data-page="our-ingredients"] .dg-oi-traceable-headline {
    color: #1b1c1a;
    text-shadow: 0 0 14px rgba(252,249,248,0.95), 0 0 34px rgba(252,249,248,0.75), 0 1px 2px rgba(252,249,248,0.9);
}
[data-page="our-ingredients"] .dg-oi-traceable-body {
    color: #504444;
    text-shadow: 0 0 12px rgba(252,249,248,0.95), 0 0 28px rgba(252,249,248,0.8);
}
[data-page="our-ingredients"] .dg-oi-traceable-country {
    color: #1b1c1a;
    border-bottom: 1px solid rgba(28,27,27,0.35);
    text-shadow: 0 0 12px rgba(252,249,248,0.95), 0 0 28px rgba(252,249,248,0.8);
}
[data-page="our-ingredients"] .dg-oi-traceable-footnote {
    color: rgba(28,27,27,0.92);
    text-shadow: 0 0 12px rgba(252,249,248,0.95), 0 0 28px rgba(252,249,248,0.8);
}
`;

/* B: chữ trắng + halo tối */
const VAR_B = BASE + `
[data-page="our-ingredients"] .dg-oi-traceable-headline {
    color: #fcf9f8;
    text-shadow: 0 0 14px rgba(28,27,27,0.9), 0 0 34px rgba(28,27,27,0.7), 0 1px 3px rgba(28,27,27,0.85);
}
[data-page="our-ingredients"] .dg-oi-traceable-body {
    color: #f2eee9;
    text-shadow: 0 0 12px rgba(28,27,27,0.9), 0 0 28px rgba(28,27,27,0.75);
}
[data-page="our-ingredients"] .dg-oi-traceable-country {
    color: #fcf9f8;
    border-bottom: 1px solid rgba(252,249,248,0.5);
    text-shadow: 0 0 12px rgba(28,27,27,0.9), 0 0 28px rgba(28,27,27,0.75);
}
[data-page="our-ingredients"] .dg-oi-traceable-footnote {
    color: rgba(252,249,248,0.92);
    text-shadow: 0 0 12px rgba(28,27,27,0.9), 0 0 28px rgba(28,27,27,0.75);
}
`;

const TARGETS = [
    { key: 'headline', sel: '.dg-oi-traceable-headline' },
    { key: 'body', sel: '.dg-oi-traceable-body' },
    { key: 'country', sel: '.dg-oi-traceable-country' },
    { key: 'footnote', sel: '.dg-oi-traceable-footnote' },
];

function srgbToLin(v) {
    const s = v / 255;
    return s <= 0.04045 ? s / 12.92 : Math.pow((s + 0.055) / 1.055, 2.4);
}
function relLum([r, g, b]) {
    return 0.2126 * srgbToLin(r) + 0.7152 * srgbToLin(g) + 0.0722 * srgbToLin(b);
}
function contrast(l1, l2) {
    const [hi, lo] = l1 > l2 ? [l1, l2] : [l2, l1];
    return (hi + 0.05) / (lo + 0.05);
}
const luma = ([r, g, b]) => 0.2126 * r + 0.7152 * g + 0.0722 * b;

async function region(file, rect) {
    const { data, info } = await sharp(file)
        .extract({
            left: Math.max(0, Math.round(rect.x)),
            top: Math.max(0, Math.round(rect.y)),
            width: Math.max(1, Math.round(rect.w)),
            height: Math.max(1, Math.round(rect.h)),
        })
        .raw()
        .toBuffer({ resolveWithObject: true });
    const px = [];
    for (let i = 0; i < data.length; i += info.channels) px.push([data[i], data[i + 1], data[i + 2]]);
    return px;
}

(async () => {
    const browser = await chromium.launch({ channel: 'chrome' });
    const out = [];

    for (const variant of [{ key: 'A', css: VAR_A }, { key: 'B', css: VAR_B }]) {
        for (const vp of [{ name: '390', width: 390, height: 844 }, { name: '768', width: 768, height: 1024 }, { name: '1280', width: 1280, height: 900 }]) {
            const ctx = await browser.newContext({ viewport: { width: vp.width, height: vp.height } });
            const page = await ctx.newPage();
            await page.goto(URL, { waitUntil: 'networkidle', timeout: 60000 });
            await page.addStyleTag({ content: variant.css });
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
                    res[t.key] = { rect: { x: r.x - sec.x, y: r.y - sec.y, w: r.width, h: r.height }, color: getComputedStyle(el).color, fontSize: getComputedStyle(el).fontSize };
                });
                return res;
            }, { targets: TARGETS, sec });

            await page.evaluate((targets) => targets.forEach((t) => { const el = document.querySelector(t.sel); if (el) el.style.visibility = 'hidden'; }), TARGETS);
            await page.waitForTimeout(200);
            const shotBg = path.join(OUT, `${variant.key}-bg-${vp.name}.png`);
            await page.locator('#traceable').screenshot({ path: shotBg });

            await page.evaluate((targets) => targets.forEach((t) => { const el = document.querySelector(t.sel); if (el) el.style.visibility = 'visible'; }), TARGETS);
            await page.waitForTimeout(200);
            const shotTxt = path.join(OUT, `${variant.key}-txt-${vp.name}.png`);
            await page.locator('#traceable').screenshot({ path: shotTxt });

            const row = { variant: variant.key, viewport: vp.name, texts: {} };
            for (const t of TARGETS) {
                const m = meta[t.key];
                if (!m) continue;
                const bgPx = await region(shotBg, m.rect);
                const txtPx = await region(shotTxt, m.rect);
                const bgLums = bgPx.map(luma).sort((a, b) => a - b);
                const p05 = bgLums[Math.floor(bgLums.length * 0.05)];
                const p50 = bgLums[Math.floor(bgLums.length * 0.5)];

                /* pixel glyph: 3% extreme theo hướng đối lập nền trung vị */
                const txtLums = txtPx.map(luma).sort((a, b) => a - b);
                const glyphDark = txtLums[Math.floor(txtLums.length * 0.03)];
                const glyphLight = txtLums[Math.floor(txtLums.length * 0.97)];

                const c = m.color.match(/rgba?\(([^)]+)\)/)[1].split(',').map((v) => parseFloat(v));
                const alpha = c.length > 3 ? c[3] : 1;
                const fgRaw = relLum([c[0], c[1], c[2]]);

                const ratioVs = (bgL) => {
                    const fg = alpha < 1 ? fgRaw * bgL * 0 + (fgRaw * alpha + bgL * (1 - alpha)) : fgRaw;
                    return +contrast(fg, bgL).toFixed(2);
                };

                /* đo thực tế: pixel glyph đậm nhất vs pixel nền lân cận (median) */
                const realRatio = variant.key === 'A'
                    ? +contrast(relLum([glyphDark, glyphDark, glyphDark]), relLum([p50, p50, p50])).toFixed(2)
                    : +contrast(relLum([glyphLight, glyphLight, glyphLight]), relLum([p50, p50, p50])).toFixed(2);

                row.texts[t.key] = {
                    color: m.color,
                    fontSize: m.fontSize,
                    bgP05: +p05.toFixed(1),
                    bgP50: +p50.toFixed(1),
                    contrastVsWorstBg: ratioVs(relLum([p05, p05, p05])),
                    contrastVsMedianBg: ratioVs(relLum([p50, p50, p50])),
                    realPixelContrast: realRatio,
                };
            }
            out.push(row);
            await ctx.close();
        }
    }

    await browser.close();
    console.log(JSON.stringify(out, null, 2));
})();
