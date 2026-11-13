/**
 * Verify: bỏ overlay ảnh ở section #traceable (Our Ingredients).
 *
 * Site live chưa deploy bản mới → inject đúng phần CSS đã sửa (token + rules)
 * rồi đo contrast thực tế trên pixel thật + chụp screenshot ở 3 breakpoint.
 */
const { chromium } = require('playwright');
const sharp = require('sharp');
const fs = require('fs');
const path = require('path');

const URL = 'https://dragonglow.freehosting.dev/our-ingredients/';
const OUT = path.join(__dirname, 'traceable-verify');
fs.mkdirSync(OUT, { recursive: true });

const VIEWPORTS = [
    { name: '390', width: 390, height: 844 },
    { name: '768', width: 768, height: 1024 },
    { name: '1280', width: 1280, height: 900 },
];

/* Token mới trong style.css :root + rules mới trong our-ingredients.css */
const CSS = `
:root {
    --oi-traceable-card-bg: rgba(252, 249, 248, 0.86);
    --oi-traceable-card-bg-solid: rgba(252, 249, 248, 0.96);
    --oi-traceable-card-border: rgba(208, 197, 175, 0.55);
    --oi-traceable-card-radius: 0.75rem;
    --oi-traceable-card-shadow: 0 24px 60px rgba(28, 27, 27, 0.18);
    --oi-traceable-card-blur: blur(18px);
    --oi-traceable-text: var(--color-on-surface);
    --oi-traceable-text-muted: var(--color-on-surface-variant);
    --oi-traceable-rule: rgba(28, 27, 27, 0.25);
    --oi-traceable-footnote: rgba(28, 27, 27, 0.88);
}
.dg-oi-traceable-overlay { display: none !important; }
.dg-oi-traceable-content {
    position: relative;
    z-index: 1;
    max-width: 600px;
    margin: 0 var(--oi-margin-mobile);
    padding: clamp(1.5rem, 4vw, 2.75rem);
    background: var(--oi-traceable-card-bg-solid);
    border: 1px solid var(--oi-traceable-card-border);
    border-radius: var(--oi-traceable-card-radius);
    box-shadow: var(--oi-traceable-card-shadow);
}
@supports ((backdrop-filter: blur(1px)) or (-webkit-backdrop-filter: blur(1px))) {
    .dg-oi-traceable-content {
        background: var(--oi-traceable-card-bg);
        -webkit-backdrop-filter: var(--oi-traceable-card-blur);
        backdrop-filter: var(--oi-traceable-card-blur);
    }
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

/* WCAG relative luminance */
function relLum(rgb) {
    const c = rgb.map((v) => {
        const s = v / 255;
        return s <= 0.03928 ? s / 12.92 : Math.pow((s + 0.055) / 1.055, 2.4);
    });
    return 0.2126 * c[0] + 0.7152 * c[1] + 0.0722 * c[2];
}

function contrast(l1, l2) {
    const [hi, lo] = l1 > l2 ? [l1, l2] : [l2, l1];
    return (hi + 0.05) / (lo + 0.05);
}

/* Ước lượng luminance nền thực tế từ pixel screenshot:
   lấy percentile cao (nền sáng) trong vùng nền của card, loại pixel chữ tối. */
async function bgLumaFromShot(file, clip) {
    const { data, info } = await sharp(file)
        .extract(clip)
        .raw()
        .toBuffer({ resolveWithObject: true });
    const lums = [];
    for (let i = 0; i < data.length; i += info.channels) {
        lums.push(0.2126 * data[i] + 0.7152 * data[i + 1] + 0.0722 * data[i + 2]);
    }
    lums.sort((a, b) => a - b);
    return lums[Math.floor(lums.length * 0.9)];
}

(async () => {
    const browser = await chromium.launch({ channel: 'chrome' });
    const report = [];

    for (const vp of VIEWPORTS) {
        const ctx = await browser.newContext({ viewport: { width: vp.width, height: vp.height } });
        const page = await ctx.newPage();
        const errors = [];
        page.on('pageerror', (e) => errors.push(String(e.message)));

        await page.goto(URL, { waitUntil: 'networkidle', timeout: 60000 });
        await page.addStyleTag({ content: CSS });
        /* Mô phỏng đúng trạng thái sau khi deploy: div overlay đã bị xoá khỏi template. */
        await page.evaluate(() => {
            const ov = document.querySelector('.dg-oi-traceable-overlay');
            if (ov) ov.remove();
            const el = document.querySelector('#traceable');
            el.scrollIntoView({ block: 'center' });
        });
        await page.waitForTimeout(1200);

        const box = await page.locator('#traceable').boundingBox();
        const shot = path.join(OUT, `traceable-${vp.name}.png`);
        await page.locator('#traceable').screenshot({ path: shot });

        const data = await page.evaluate(() => {
            const q = (s) => document.querySelector(s);
            const cs = (s) => getComputedStyle(q(s));
            const card = q('.dg-oi-traceable-content');
            const cardBox = card.getBoundingClientRect();
            const secBox = q('#traceable').getBoundingClientRect();
            const parseRgb = (str) => {
                const m = str.match(/rgba?\(([^)]+)\)/);
                if (!m) return null;
                const p = m[1].split(',').map((v) => parseFloat(v));
                return { rgb: [p[0], p[1], p[2]], a: p.length > 3 ? p[3] : 1 };
            };
            return {
                overlayExists: !!q('.dg-oi-traceable-overlay'),
                cardBg: cs('.dg-oi-traceable-content').backgroundColor,
                cardRadius: cs('.dg-oi-traceable-content').borderRadius,
                cardBackdrop: cs('.dg-oi-traceable-content').backdropFilter,
                cardRect: { x: cardBox.x, y: cardBox.y, w: cardBox.width, h: cardBox.height },
                secRect: { x: secBox.x, y: secBox.y, w: secBox.width, h: secBox.height },
                headline: cs('.dg-oi-traceable-headline').color,
                body: cs('.dg-oi-traceable-body').color,
                country: cs('.dg-oi-traceable-country').color,
                countryBorder: cs('.dg-oi-traceable-country').borderBottomColor,
                footnote: cs('.dg-oi-traceable-footnote').color,
                overflowX: document.documentElement.scrollWidth > window.innerWidth,
            };
        });

        /* Vùng nền trong card: dải ngang ngay dưới headline, tránh chữ */
        const clip = {
            left: Math.round(data.cardRect.x + 12),
            top: Math.round(data.cardRect.y + 6),
            width: Math.round(Math.min(data.cardRect.w - 24, 240)),
            height: Math.round(Math.min(data.cardRect.h - 12, 40)),
        };
        const bgLuma = await bgLumaFromShot(shot, clip);

        const toLin = (l) => {
            const s = l / 255;
            return s <= 0.03928 ? s / 12.92 : Math.pow((s + 0.055) / 1.055, 2.4);
        };
        const bgLum = 0.2126 * toLin(bgLuma) + 0.7152 * toLin(bgLuma) + 0.0722 * toLin(bgLuma);
        const parse = (s) => s.match(/rgba?\(([^)]+)\)/)[1].split(',').map((v) => parseFloat(v));

        const measure = (colorStr) => {
            const p = parse(colorStr);
            const alpha = p.length > 3 ? p[3] : 1;
            const fg = relLum([p[0], p[1], p[2]]);
            const fgLum = alpha < 1 ? fg * alpha + bgLum * (1 - alpha) : fg;
            return +contrast(fgLum, bgLum).toFixed(2);
        };

        report.push({
            viewport: vp.name,
            overlayExists: data.overlayExists,
            cardBg: data.cardBg,
            cardRadius: data.cardRadius,
            backdrop: data.cardBackdrop,
            cardSize: `${Math.round(data.cardRect.w)}x${Math.round(data.cardRect.h)}`,
            sectionSize: `${Math.round(data.secRect.w)}x${Math.round(data.secRect.h)}`,
            overflowX: data.overflowX,
            bgLuma: +bgLuma.toFixed(1),
            colors: {
                headline: data.headline,
                body: data.body,
                country: data.country,
                footnote: data.footnote,
            },
            contrast: {
                headline: measure(data.headline),
                body: measure(data.body),
                country: measure(data.country),
                footnote: measure(data.footnote),
            },
            errors,
        });

        await ctx.close();
    }

    await browser.close();
    console.log(JSON.stringify(report, null, 2));
})();
