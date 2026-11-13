/**
 * Trích xuất màu các pixel tối nhất trong từng pill (đọc chữ số).
 */
const sharp = require('sharp');

const USER = 'c:/Users/levie/.cursor/projects/c-du-an-html-Dragon-Glow-WordPress-main/assets/c__Users_levie_AppData_Roaming_Cursor_User_workspaceStorage_ceb529563ca2940597e17f5c004c6ad1_images_image-69e2228b-fe5a-4231-ac82-2e0e40e5a978.png';

(async () => {
    const { data, info } = await sharp(USER).raw().toBuffer({ resolveWithObject: true });
    const { width: w, channels: c } = info;

    const ranges = JSON.parse(process.argv[2]);
    for (const [a, b] of ranges) {
        const pts = [];
        let minL = 999, minC = null;
        for (let y = 359; y <= 382; y++) {
            for (let x = a; x <= b; x++) {
                const i = (y * w + x) * c;
                const R = data[i], G = data[i + 1], B = data[i + 2];
                const l = R * 0.299 + G * 0.587 + B * 0.114;
                if (l < minL) { minL = l; minC = [R, G, B, x, y]; }
                if (l < 200) pts.push([x, y, R, G, B]);
            }
        }
        console.log(`\nx=${a}-${b} (w=${b - a + 1})  pixelTối nhất: rgb(${minC[0]},${minC[1]},${minC[2]}) luma=${minL.toFixed(0)} @(${minC[3]},${minC[4]})`);
        console.log(`  số pixel tối(<200) = ${pts.length}`);
        if (pts.length) {
            const xs = pts.map(p => p[0]);
            console.log(`  x của chữ: ${Math.min(...xs)}..${Math.max(...xs)}  (bề rộng chữ ${Math.max(...xs) - Math.min(...xs) + 1}px)`);
            const avg = [0, 0, 0];
            pts.forEach(p => { avg[0] += p[2]; avg[1] += p[3]; avg[2] += p[4]; });
            console.log(`  màu chữ TB: ${avg.map(v => Math.round(v / pts.length)).join(',')}`);
        }
    }
})();
