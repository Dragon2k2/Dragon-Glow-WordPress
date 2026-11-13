/**
 * Đo contrast khi BỎ nền glass card (chữ nằm trực tiếp trên ảnh).
 *
 * Quy trình mỗi viewport:
 *  1. Chụp ảnh section với chữ bị ẩn → lấy nền THUẦN (chính là ảnh) tại bbox từng element.
 *  2. Tính contrast giữa màu chữ và nền ảnh tại đúng vị trí đó.
 *  3. Chụp ảnh có chữ → đo độ tương phản thực tế giữa pixel chữ và pixel nền liền kề
 *     (lấy median của pixel sáng nhất/tối nhất trong bbox chữ).
 */
const { chromium } = require('playwright');
const sharp = require('sharp');
const fs = require('fs');
const path = require('path');

const URL = 'https://dragonglow.freehosting.dev/our-ingredients/';
const OUT = path.join(__dirname, 'traceable-nobg');
fs.mkdirSync(OUT, { recursive: true });

/* CSS: bỏ nền card, chữ nằm trực tiếp trên ảnh */
const NO_BG_CSS = `
:root {
    --oi-traceable-text: var(--color-on-surface);
    --oi-traceable-text-muted: var(--color-on-surface-variant);
    --oi-traceable-rule: rgba(28, 27, 27, 0.25);
    --oi-traceable-footnote: rgba(28, 27, 27, 0.88);
}
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
[data-page="our-ingredients"] .dg-oi-traceable-headline { color: var(--oi-traceable-text); }
[data-page="our-ingredients"] .dg-oi-traceable-body { color: var(--oi-traceable-text-muted); }
[data-page="our-ingredients"] .dg-oi-traceable-country {
    color: var(--oi-traceable-text);
    border-bottom: 1px solid var(--oi-traceable-rule);
}
[data-page="our-ingredients"] .dg-oi-traceable-footnote { color: var(--oi-traceable-footnote); }
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

async function region(file, rect) {
    const left = Math.max(0, Math.round(rect.x));
    const top = Math.max(0, Math.round(rect.y));
    const width = Math.max(1, Math.round(rect.w));
    const height = Math.max(1, Math.round(rect.h));
    const { data, info } = await sharp(file)
        .extract({ left, top, width, height })
        .raw()
        .toBuffer({ resolveWithObject: true });
    const px = [];
    for (let i = 0; i < data.length; i += info.channels) px.push([data[i], data[i + 1], data[i + 2]]);
    return px;
}

function med(arr) {
    const v = [...arr].sort((a, b) => a - b);
    return v[Math.floor(v.length / 2)];
}
function luma([r, g, b]) {
    return 0.2126 * r + 0.7152 * g + 0.0722 * b;
}

(async () => {
    const browser = await chromium.launch({ channel: 'chrome' });
    const out = [];

    for (const vp of [{ name: '390', width: 390, height: 844 }, { name: '768', width: 768, height: 1024 }, { name: '1280', width: 1280, height: 900 }]) {
        const ctx = await browser.newContext({ viewport: { width: vp.width, height: vp.height } });
        const page = await ctx.newPage();
        const errors = [];
        page.on('pageerror', (e) => errors.push(String(e.message)));

        await page.goto(URL, { waitUntil: 'networkidle', timeout: 60000 });
        await page.addStyleTag({ content: NO_BG_CSS });
        await page.evaluate(() => {
            const ov = document.querySelector('.dg-oi-traceable-overlay');
            if (ov) ov.remove();
            document.querySelector('#traceable').scrollIntoView({ block: 'center' });
        });
        await page.waitForTimeout(1000);

        const sec = await page.locator('#traceable').boundingBox();
        const meta = await page.evaluate(({ targets, sec }) => {
            const res = {};
            const card = document.querySelector('.dg-oi-traceable-content');
            const cs = getComputedStyle(card);
            res.cardBg = cs.backgroundColor;
            res.cardPad = cs.padding;
            res.overflowX = document.documentElement.scrollWidth > window.innerWidth;
            targets.forEach((t) => {
                const el = document.querySelector(t.sel);
                if (!el) return;
                const r = el.getBoundingClientRect();
                const s = getComputedStyle(el);
                res[t.key] = {
                    rect: { x: r.x - sec.x, y: r.y - sec.y, w: r.width, h: r.height },
                    color: s.color,
                    fontSize: s.fontSize,
                    fontWeight: s.fontWeight,
                };
            });
            return res;
        }, { targets: TARGETS, sec });

        /* nền thuần (ảnh) */
        await page.evaluate((targets) => {
            targets.forEach((t) => {
                const el = document.querySelector(t.sel);
                if (el) el.style.visibility = 'hidden';
            });
        }, TARGETS);
        await page.waitForTimeout(200);
        const shotBg = path.join(OUT, `bg-${vp.name}.png`);
        await page.locator('#traceable').screenshot({ path: shotBg });

        /* có chữ */
        await page.evaluate((targets) => {
            targets.forEach((t) => {
                const el = document.querySelector(t.sel);
                if (el) el.style.visibility = 'visible';
            });
        }, TARGETS);
        await page.waitForTimeout(200);
        const shotTxt = path.join(OUT, `txt-${vp.name}.png`);
        await page.locator('#traceable').screenshot({ path: shotTxt });

        const row = { viewport: vp.name, cardBg: meta.cardBg, cardPad: meta.cardPad, overflowX: meta.overflowX, texts: {} };

        for (const t of TARGETS) {
            const m = meta[t.key];
            if (!m) continue;
            const bgPx = await region(shotBg, m.rect);
            const txtPx = await region(shotTxt, m.rect);

            const bgLums = bgPx.map(luma).sort((a, b) => a - b);
            const bgWorst = bgLums[Math.floor(bgLums.length * 0.05)];
            const bgBest = bgLums[Math.floor(bgLums.length * 0.95)];
            const bgMed = med(bgLums);

            /* pixel chữ thực tế: trong bbox có chữ, lấy 5% tối nhất (glyph) */
            const txtLums = txtPx.map(luma).sort((a, b) => a - b);
            const glyph = txtLums[Math.floor(txtLums.length * 0.05)];

            const c = m.color.match(/rgba?\(([^)]+)\)/)[1].split(',').map((v) => parseFloat(v));
            const alpha = c.length > 3 ? c[3] : 1;
            const fgRaw = relLum([c[0], c[1], c[2]]);

            const ratioAgainst = (bgLum) => {
                const fg = alpha < 1 ? fgRaw * alpha + bgLum * (1 - alpha) : fgRaw;
                return +contrast(fg, bgLum).toFixed(2);
            };

            row.texts[t.key] = {
                color: m.color,
                fontSize: m.fontSize,
                bgMedian: `rgb(${med(bgPx.map((p) => p))})`,
                bgLumaP05: +bgWorst.toFixed(1),
                bgLumaMed: +bgMed.toFixed(1),
                bgLumaP95: +bgBest.toFixed(1),
                contrastVsWorstBg: ratioAgainst(relLum([bgWorst, bgWorst, bgWorst])),
                contrastVsMedianBg: ratioAgainst(relLum([bgMed, bgMed, bgMed])),
                contrastVsBestBg: ratioAgainst(relLum([bgBest, bgBest, bgBest])),
                measuredGlyphLuma: +glyph.toFixed(1),
            };
        }
        row.errors = errors;
        out.push(row);
        await ctx.close();
    }

    await browser.close();
    console.log(JSON.stringify(out, null, 2));
})();
