/**
 * So sánh chữ ký pixel giữa ảnh người dùng và ảnh live.
 * Mục tiêu: xác định ảnh người dùng chụp vùng nào, và phân trang có nằm trong đó không.
 */
const sharp = require('sharp');
const path = require('path');

const USER = 'c:/Users/levie/.cursor/projects/c-du-an-html-Dragon-Glow-WordPress-main/assets/c__Users_levie_AppData_Roaming_Cursor_User_workspaceStorage_ceb529563ca2940597e17f5c004c6ad1_images_image-69e2228b-fe5a-4231-ac82-2e0e40e5a978.png';
const LIVE = process.argv[2];

function signature(file) {
    return sharp(file).raw().toBuffer({ resolveWithObject: true }).then(({ data, info }) => {
        const { width: w, height: h, channels: c } = info;
        const rows = [];
        for (let y = 0; y < h; y++) {
            let dark = 0, colored = 0, nonBg = 0;
            for (let x = 0; x < w; x++) {
                const i = (y * w + x) * c;
                const r = data[i], g = data[i + 1], b = data[i + 2];
                const l = r * 0.299 + g * 0.587 + b * 0.114;
                if (l < 100) dark++;
                if (Math.abs(r - g) > 25 || Math.abs(g - b) > 25 || Math.abs(r - b) > 25) colored++;
                if (l < 235) nonBg++;
            }
            rows.push({ y, dark, colored, nonBg });
        }
        return { w, h, rows };
    });
}

(async () => {
    const u = await signature(USER);
    const l = await signature(LIVE);

    console.log(`USER ${u.w}x${u.h}   LIVE ${l.w}x${l.h}`);
    console.log('\n--- USER: các dòng có nội dung (nonBg > 30) ---');
    u.rows.filter(r => r.nonBg > 30).forEach(r => console.log(`  y=${String(r.y).padStart(3)} dark=${String(r.dark).padStart(4)} colored=${String(r.colored).padStart(4)} nonBg=${String(r.nonBg).padStart(4)}`));

    console.log('\n--- LIVE: các dòng có nội dung (nonBg > 30) ---');
    l.rows.filter(r => r.nonBg > 30).forEach(r => console.log(`  y=${String(r.y).padStart(3)} dark=${String(r.dark).padStart(4)} colored=${String(r.colored).padStart(4)} nonBg=${String(r.nonBg).padStart(4)}`));

    // Tìm hàng nội dung đầu tiên & cuối cùng để so đối chiếu vùng.
    const first = (s) => { const r = s.rows.find(x => x.nonBg > 30); return r ? r.y : -1; };
    const last = (s) => { const r = [...s.rows].reverse().find(x => x.nonBg > 30); return r ? r.y : -1; };
    console.log(`\nUSER: nội dung từ y=${first(u)} đến y=${last(u)}`);
    console.log(`LIVE: nội dung từ y=${first(l)} đến y=${last(l)}`);
})();
