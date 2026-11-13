/**
 * So sánh TRƯỚC / SAU của section #traceable (Our Ingredients) trên site live.
 *
 * BEFORE = CSS cũ (HEAD): overlay tối ảnh + màu chữ cũ.
 * AFTER  = CSS mới: overlay bị xoá + glass card + token mới (CSS live đã có bản mới).
 *
 * Đo contrast chính xác: chụp ảnh nền thuần (ẩn chữ) rồi lấy median màu nền
 * tại đúng bbox từng element → tính WCAG contrast với màu chữ computed.
 */
const { chromium } = require('playwright');
const sharp = require('sharp');
const fs = require('fs');
const path = require('path');

const URL = 'https://dragonglow.freehosting.dev/our-ingredients/';
const OUT = path.join(__dirname, 'traceable-contrast');
fs.mkdirSync(OUT, { recursive: true });

/* CSS cũ (từ git HEAD) — chỉ phần liên quan section traceable */
const OLD_CSS = `
.dg-oi-traceable-overlay {
    position: absolute;
    inset: 0;
    background: rgba(49, 48, 48, 0.6);
    mix-blend-mode: multiply;
}
.dg-oi-traceable-content {
    position: relative;
    z-index: 1;
    padding: var(--oi-section-gap) var(--oi-margin-mobile);
    max-width: 600px;
    background: transparent;
    border: 0;
    border-radius: 0;
    box-shadow: none;
    backdrop-filter: none;
    -webkit-backdrop-filter: none;
}
@media (min-width: 768px) {
    .dg-oi-traceable-content { padding-left: var(--oi-margin-desktop); }
}
[data-page="our-ingredients"] .dg-oi-traceable-headline { color: inherit; }
[data-page="our-ingredients"] .dg-oi-traceable-body { color: var(--color-on-surface-variant); }
[data-page="our-ingredients"] .dg-oi-traceable-country {
    color: inherit;
    border-bottom: 1px solid rgba(252, 249, 248, 0.3);
}
[data-page="our-ingredients"] .dg-oi-traceable-footnote { color: rgba(220, 217, 217, 0.8); }
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

async function bgColorAt(file, rect) {
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
    return [0, 1, 2].map((c) => {
        const v = px.map((p) => p[c]).sort((a, b) => a - b);
        return v[Math.floor(v.length / 2)];
    });
}

(async () => {
    const browser = await chromium.launch({ channel: 'chrome' });
    const out = {};

    for (const mode of ['before', 'after']) {
        out[mode] = [];
        for (const vp of [{ name: '390', width: 390, height: 844 }, { name: '1280', width: 1280, height: 900 }]) {
            const ctx = await browser.newContext({ viewport: { width: vp.width, height: vp.height } });
            const page = await ctx.newPage();
            const errors = [];
            page.on('pageerror', (e) => errors.push(String(e.message)));

            await page.goto(URL, { waitUntil: 'networkidle', timeout: 60000 });

            if (mode === 'before') {
                await page.addStyleTag({ content: OLD_CSS });
            } else {
                /* mô phỏng template mới: div overlay đã bị xoá khỏi markup */
                await page.evaluate(() => {
                    const ov = document.querySelector('.dg-oi-traceable-overlay');
                    if (ov) ov.remove();
                });
            }

            await page.evaluate(() => document.querySelector('#traceable').scrollIntoView({ block: 'center' }));
            await page.waitForTimeout(900);

            const sec = await page.locator('#traceable').boundingBox();
            const meta = await page.evaluate(({ targets, sec }) => {
                const res = { overlayPresent: !!document.querySelector('.dg-oi-traceable-overlay') };
                const card = document.querySelector('.dg-oi-traceable-content');
                const cs = getComputedStyle(card);
                res.cardBg = cs.backgroundColor;
                res.cardRadius = cs.borderRadius;
                res.cardBackdrop = cs.backdropFilter;
                const cr = card.getBoundingClientRect();
                res.cardRect = { x: cr.x - sec.x, y: cr.y - sec.y, w: cr.width, h: cr.height };
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

            /* ảnh nền thuần */
            await page.evaluate((targets) => {
                targets.forEach((t) => {
                    const el = document.querySelector(t.sel);
                    if (el) el.style.visibility = 'hidden';
                });
            }, TARGETS);
            await page.waitForTimeout(250);
            const shot = path.join(OUT, `${mode}-${vp.name}.png`);
            await page.locator('#traceable').screenshot({ path: shot });

            const row = { viewport: vp.name, overlayPresent: meta.overlayPresent, cardBg: meta.cardBg, cardRadius: meta.cardRadius, backdrop: meta.cardBackdrop, overflowX: meta.overflowX, texts: {} };
            for (const t of TARGETS) {
                const m = meta[t.key];
                if (!m) continue;
                const bg = await bgColorAt(shot, m.rect);
                const c = m.color.match(/rgba?\(([^)]+)\)/)[1].split(',').map((v) => parseFloat(v));
                const alpha = c.length > 3 ? c[3] : 1;
                const bgLum = relLum(bg);
                const fgRaw = relLum([c[0], c[1], c[2]]);
                const fgLum = alpha < 1 ? fgRaw * alpha + bgLum * (1 - alpha) : fgRaw;
                const fs = parseFloat(m.fontSize);
                const bold = parseInt(m.fontWeight, 10) >= 700;
                const large = fs >= 24 || (fs >= 18.66 && bold);
                const ratio = contrast(fgLum, bgLum);
                row.texts[t.key] = {
                    color: m.color,
                    bg: `rgb(${bg.join(',')})`,
                    fontSize: m.fontSize,
                    ratio: +ratio.toFixed(2),
                    aa: large ? ratio >= 3 : ratio >= 4.5,
                    aaa: large ? ratio >= 4.5 : ratio >= 7,
                };
            }
            row.errors = errors;
            out[mode].push(row);
            await ctx.close();
        }
    }

    await browser.close();
    console.log(JSON.stringify(out, null, 2));
})();
