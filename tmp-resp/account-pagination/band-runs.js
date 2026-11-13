/**
 * Đo chính xác các cụm trong 1 dải ngang: vị trí, rộng, màu trung bình.
 */
const sharp = require('sharp');

const USER = 'c:/Users/levie/.cursor/projects/c-du-an-html-Dragon-Glow-WordPress-main/assets/c__Users_levie_AppData_Roaming_Cursor_User_workspaceStorage_ceb529563ca2940597e17f5c004c6ad1_images_image-69e2228b-fe5a-4231-ac82-2e0e40e5a978.png';

(async () => {
    const { data, info } = await sharp(USER).raw().toBuffer({ resolveWithObject: true });
    const { width: w, channels: c } = info;

    const y0 = parseInt(process.argv[2], 10);
    const y1 = parseInt(process.argv[3], 10);
    const thresh = parseFloat(process.argv[4] || '235');

    // Mỗi cột: số pixel "nội dung" + tổng RGB
    const cols = [];
    for (let x = 0; x < w; x++) {
        let n = 0, r = 0, g = 0, b = 0;
        for (let y = y0; y <= y1; y++) {
            const i = (y * w + x) * c;
            const R = data[i], G = data[i + 1], B = data[i + 2];
            const l = R * 0.299 + G * 0.587 + B * 0.114;
            if (l < thresh) { n++; r += R; g += G; b += B; }
        }
        cols.push({ n, r, g, b });
    }

    const runs = [];
    let start = -1, gap = 0;
    for (let x = 0; x < w; x++) {
        if (cols[x].n > 0) { if (start === -1) start = x; gap = 0; }
        else if (start !== -1) { gap++; if (gap > 8) { runs.push([start, x - gap]); start = -1; gap = 0; } }
    }
    if (start !== -1) runs.push([start, w - 1]);

    console.log(`dải y=${y0}..${y1}, ngưỡng ${thresh}`);
    for (const [a, b] of runs) {
        let n = 0, r = 0, g = 0, b2 = 0, mn = 999, mx = -1;
        for (let x = a; x <= b; x++) {
            const cN = cols[x].n; n += cN; r += cols[x].r; g += cols[x].g; b2 += cols[x].b;
            if (cN > 0) { mn = Math.min(mn, x); mx = Math.max(mx, x); }
        }
        const avg = n ? [Math.round(r / n), Math.round(g / n), Math.round(b2 / n)] : [0, 0, 0];
        console.log(`  x=${String(a).padStart(4)}-${String(b).padStart(4)} rộng=${String(b - a + 1).padStart(4)}  màuTB=${avg.join(',')}`);
    }
})();
