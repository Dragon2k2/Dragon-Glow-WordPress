/**
 * Phân tích cụm pixel theo từng dải ngang của ảnh người dùng,
 * để xác định chính xác vùng nào có nội dung và vùng nào trống.
 */
const sharp = require('sharp');

const USER = 'c:/Users/levie/.cursor/projects/c-du-an-html-Dragon-Glow-WordPress-main/assets/c__Users_levie_AppData_Roaming_Cursor_User_workspaceStorage_ceb529563ca2940597e17f5c004c6ad1_images_image-69e2228b-fe5a-4231-ac82-2e0e40e5a978.png';

(async () => {
    const { data, info } = await sharp(USER).raw().toBuffer({ resolveWithObject: true });
    const { width: w, height: h, channels: c } = info;

    const isContent = (r, g, b) => {
        const l = r * 0.299 + g * 0.587 + b * 0.114;
        return l < 235; // khác nền sáng
    };

    // Với mỗi dải ngang, in các cụm pixel nội dung (gap > 6px bị bỏ qua).
    const bands = [[51, 93], [111, 133], [145, 180], [191, 195], [206, 228], [254, 275], [302, 323], [360, 383], [384, 407], [408, 458], [459, 513]];

    for (const [a, b] of bands) {
        const cols = new Array(w).fill(0);
        for (let y = a; y <= b; y++) {
            for (let x = 0; x < w; x++) {
                const i = (y * w + x) * c;
                if (isContent(data[i], data[i + 1], data[i + 2])) cols[x]++;
            }
        }
        const runs = [];
        let start = -1, gap = 0;
        for (let x = 0; x < w; x++) {
            if (cols[x] > 0) {
                if (start === -1) start = x;
                gap = 0;
            } else if (start !== -1) {
                gap++;
                if (gap > 6) { runs.push([start, x - gap]); start = -1; gap = 0; }
            }
        }
        if (start !== -1) runs.push([start, w - 1]);
        const total = runs.reduce((s, r) => s + (r[1] - r[0] + 1), 0);
        console.log(`band y=${String(a).padStart(3)}-${String(b).padStart(3)}  cụm=${runs.length}  tổngpx=${total}`);
        console.log('   ' + runs.map(r => `${r[0]}-${r[1]}(${r[1] - r[0] + 1})`).join(' '));
    }
})();
