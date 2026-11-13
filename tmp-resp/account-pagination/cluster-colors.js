/**
 * Đo màu từng cụm riêng lẻ trong một dải ngang.
 */
const sharp = require('sharp');

const USER = 'c:/Users/levie/.cursor/projects/c-du-an-html-Dragon-Glow-WordPress-main/assets/c__Users_levie_AppData_Roaming_Cursor_User_workspaceStorage_ceb529563ca2940597e17f5c004c6ad1_images_image-69e2228b-fe5a-4231-ac82-2e0e40e5a978.png';

(async () => {
    const { data, info } = await sharp(USER).raw().toBuffer({ resolveWithObject: true });
    const { width: w, channels: c } = info;

    const y0 = parseInt(process.argv[2], 10);
    const y1 = parseInt(process.argv[3], 10);

    // Cụm: pixel khác nền, gap <= 3px gộp lại (để tách từng glyph số)
    const cols = [];
    for (let x = 0; x < w; x++) {
        let n = 0, r = 0, g = 0, b = 0, min = 255;
        for (let y = y0; y <= y1; y++) {
            const i = (y * w + x) * c;
            const R = data[i], G = data[i + 1], B = data[i + 2];
            const l = R * 0.299 + G * 0.587 + B * 0.114;
            if (l < 240) { n++; r += R; g += G; b += B; min = Math.min(min, l); }
        }
        cols.push({ n, r, g, b, min });
    }

    const runs = [];
    let s = -1, gap = 0;
    for (let x = 0; x < w; x++) {
        if (cols[x].n > 0) { if (s === -1) s = x; gap = 0; }
        else if (s !== -1) { gap++; if (gap > 3) { runs.push([s, x - gap]); s = -1; gap = 0; } }
    }
    if (s !== -1) runs.push([s, w - 1]);

    console.log(`dải y=${y0}..${y1} — ${runs.length} cụm (gap<=3px)`);
    for (const [a, b] of runs) {
        let n = 0, r = 0, g = 0, bb = 0, mn = 999;
        for (let x = a; x <= b; x++) { n += cols[x].n; r += cols[x].r; g += cols[x].g; bb += cols[x].b; mn = Math.min(mn, cols[x].min); }
        console.log(`  x=${String(a).padStart(4)}-${String(b).padStart(4)} w=${String(b - a + 1).padStart(3)}  avg=${n ? [Math.round(r / n), Math.round(g / n), Math.round(bb / n)].join(',') : '-'}  minLuma=${mn}`);
    }
})();
