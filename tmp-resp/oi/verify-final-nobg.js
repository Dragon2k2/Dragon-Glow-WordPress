/**
 * Kiểm chứng bản sửa thật (đọc rule từ file trên disk, không hard-code tay):
 *  - Trích các rule liên quan `dg-oi-traceable` từ assets/css/our-ingredients.css
 *  - Trích token `--oi-traceable-*` từ style.css
 *  - Inject lên site live, bỏ overlay, đo contrast thực tế + kiểm tra layout.
 */
const { chromium } = require('playwright');
const sharp = require('sharp');
const fs = require('fs');
const path = require('path');

const ROOT = path.join(__dirname, '..', '..');
const CSS_FILE = path.join(ROOT, 'wp-content/themes/dragon-glow/assets/css/our-ingredients.css');
const STYLE_FILE = path.join(ROOT, 'wp-content/themes/dragon-glow/style.css');
const URL = 'https://dragonglow.freehosting.dev/our-ingredients/';
const OUT = path.join(__dirname, 'traceable-nobg');
fs.mkdirSync(OUT, { recursive: true });

/* --- trích token --oi-traceable-* từ style.css --- */
const styleCss = fs.readFileSync(STYLE_FILE, 'utf8');
const tokenBlock = (styleCss.match(/--oi-traceable-[a-z-]+:[^;]+;/g) || []).join('\n');

/* --- trích các rule chứa dg-oi-traceable từ file CSS thật --- */
const oiCss = fs.readFileSync(CSS_FILE, 'utf8');
const rules = [];
{
    /* bắt các block rule (kể cả nested @media) chứa "dg-oi-traceable" */
    const re = /([^{}]+)\{([^{}]*)\}/g;
    let m;
    while ((m = re.exec(oiCss)) !== null) {
        const sel = m[1].trim();
        if (!sel.includes('dg-oi-traceable')) continue;
        rules.push(`${sel} {${m[2]}}`);
    }
}
const injected = `:root {\n${tokenBlock}\n}\n${rules.join('\n\n')}`;

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
const median = (a) => { const v = [...a].sort((x, y) => x - y); return v[Math.floor(v.length / 2)]; };

async function region(file, rect) {
    const { data, info } = await sharp(file).extract({
        left: Math.max(0, Math.round(rect.x)), top: Math.max(0, Math.round(rect.y)),
        width: Math.max(1, Math.round(rect.w)), height: Math.max(1, Math.round(rect.h)),
    }).raw().toBuffer({ resolveWithObject: true });
    const px = [];
    for (let i = 0; i < data.length; i += info.channels) px.push([data[i], data[i + 1], data[i + 2]]);
    return px;
}

const VPS = [
    { name: '320', width: 320, height: 700 },
    { name: '390', width: 390, height: 844 },
    { name: '640', width: 640, height: 900 },
    { name: '768', width: 768, height: 1024 },
    { name: '1024', width: 1024, height: 800 },
    { name: '1280', width: 1280, height: 900 },
];

(async () => {
    const browser = await chromium.launch({ channel: 'chrome' });
    const report = { injectedRuleCount: rules.length, tokens: tokenBlock.split('\n').length, viewports: [] };

    for (const vp of VPS) {
        const ctx = await browser.newContext({ viewport: { width: vp.width, height: vp.height } });
        const page = await ctx.newPage();
        const errors = [];
        page.on('pageerror', (e) => errors.push(String(e.message)));

        await page.goto(URL, { waitUntil: 'networkidle', timeout: 60000 });
        await page.addStyleTag({ content: injected });
        await page.evaluate(() => {
            const ov = document.querySelector('.dg-oi-traceable-overlay');
            if (ov) ov.remove();
            document.querySelector('#traceable').scrollIntoView({ block: 'center' });
        });
        await page.waitForTimeout(1000);

        const sec = await page.locator('#traceable').boundingBox();
        const meta = await page.evaluate(({ targets, sec }) => {
            const card = document.querySelector('.dg-oi-traceable-content');
            const cs = getComputedStyle(card);
            const res = {
                cardBackground: cs.backgroundColor,
                cardBackdrop: cs.backdropFilter || cs.webkitBackdropFilter,
                cardBorder: cs.borderTopWidth + ' ' + cs.borderTopStyle,
                cardShadow: cs.boxShadow,
                cardPadding: cs.padding,
                overflowX: document.documentElement.scrollWidth > window.innerWidth,
                scrollWidth: document.documentElement.scrollWidth,
                innerWidth: window.innerWidth,
            };
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
                    textShadow: s.textShadow !== 'none',
                };
            });
            return res;
        }, { targets: TARGETS, sec });

        const shot = path.join(OUT, `final-${vp.name}.png`);
        await page.locator('#traceable').screenshot({ path: shot });

        const row = { viewport: vp.name, cardBackground: meta.cardBackground, cardBackdrop: meta.cardBackdrop, cardBorder: meta.cardBorder, cardShadow: meta.cardShadow, cardPadding: meta.cardPadding, overflowX: meta.overflowX, texts: {} };

        for (const t of TARGETS) {
            const m = meta[t.key];
            if (!m) continue;
            const px = await region(shot, m.rect);
            const lums = px.map(luma).sort((a, b) => a - b);
            const glyphLuma = lums[Math.floor(lums.length * 0.03)];
            const haloSet = lums.filter((v) => v >= lums[Math.floor(lums.length * 0.6)]);
            const haloLuma = median(haloSet);
            const c = m.color.match(/rgba?\(([^)]+)\)/)[1].split(',').map((v) => parseFloat(v));
            const alpha = c.length > 3 ? c[3] : 1;
            const fgRaw = relLum([c[0], c[1], c[2]]);
            const bgL = relLum([haloLuma, haloLuma, haloLuma]);
            const fgEff = alpha < 1 ? fgRaw * alpha + bgL * (1 - alpha) : fgRaw;
            const ratio = +contrast(fgEff, bgL).toFixed(2);
            const fs = parseFloat(m.fontSize);
            const large = fs >= 24 || (fs >= 18.66 && parseInt(m.fontWeight, 10) >= 700);
            row.texts[t.key] = {
                fontSize: m.fontSize,
                fontWeight: m.fontWeight,
                hasHalo: m.textShadow,
                effectiveContrast: ratio,
                wcagAA: large ? ratio >= 3 : ratio >= 4.5,
            };
        }
        row.errors = errors;
        report.viewports.push(row);
        await ctx.close();
    }

    await browser.close();
    console.log(JSON.stringify(report, null, 2));
})();
