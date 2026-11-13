/**
 * Cluster các cột có nét trong dải phân trang rồi render từng cluster thành ASCII.
 */
const sharp = require('sharp');

const USER = 'c:/Users/levie/.cursor/projects/c-du-an-html-Dragon-Glow-WordPress-main/assets/c__Users_levie_AppData_Roaming_Cursor_User_workspaceStorage_ceb529563ca2940597e17f5c004c6ad1_images_image-69e2228b-fe5a-4231-ac82-2e0e40e5a978.png';

(async () => {
    const { data, info } = await sharp(USER).raw().toBuffer({ resolveWithObject: true });
    const { width: w, channels: c } = info;

    const y0 = 358, y1 = 384;

    // Tính "ink" = số pixel tối trong cột (bỏ qua nền pill sáng).
    const cols = [];
    for (let x = 400; x < 840; x++) {
        let dark = 0, sum = 0;
        for (let y = y0; y <= y1; y++) {
            const i = (y * w + x) * c;
            const l = data[i] * 0.299 + data[i + 1] * 0.587 + data[i + 2] * 0.114;
            if (l < 160) dark++;
            sum += l;
        }
        cols.push({ x, dark, avg: sum / (y1 - y0 + 1) });
    }

    // Cluster: cột có dark >= 2, gộp liền kề (gap <= 2).
    const groups = [];
    let cur = null;
    let lastInk = -99;
    for (const col of cols) {
        if (col.dark >= 2) {
            if (cur && col.x - lastInk <= 3) { cur.end = col.x; }
            else { if (cur) groups.push(cur); cur = { start: col.x, end: col.x }; }
            lastInk = col.x;
        }
    }
    if (cur) groups.push(cur);

    console.log('clusters:', groups.length);
    groups.forEach((g, gi) => {
        console.log(`\n===== #${gi + 1}  x=${g.start}..${g.end} (w=${g.end - g.start + 1}) =====`);
        for (let y = y0; y <= y1; y++) {
            let line = '';
            for (let x = g.start - 2; x <= g.end + 2; x++) {
                const i = (y * w + x) * c;
                const l = data[i] * 0.299 + data[i + 1] * 0.587 + data[i + 2] * 0.114;
                line += l < 160 ? '#' : (l < 225 ? '+' : '.');
            }
            console.log(line);
        }
    });
})();
