/**
 * Render vùng ảnh thành ASCII art để đọc chữ (số trang, nút).
 */
const sharp = require('sharp');

const USER = 'c:/Users/levie/.cursor/projects/c-du-an-html-Dragon-Glow-WordPress-main/assets/c__Users_levie_AppData_Roaming_Cursor_User_workspaceStorage_ceb529563ca2940597e17f5c004c6ad1_images_image-69e2228b-fe5a-4231-ac82-2e0e40e5a978.png';

(async () => {
    const { data, info } = await sharp(USER).raw().toBuffer({ resolveWithObject: true });
    const { width: w, channels: c } = info;

    const args = process.argv.slice(2);
    const x0 = parseInt(args[0], 10);
    const x1 = parseInt(args[1], 10);
    const y0 = parseInt(args[2], 10);
    const y1 = parseInt(args[3], 10);

    const RAMP = ' .:-=+*#%@';
    for (let y = y0; y <= y1; y++) {
        let line = '';
        for (let x = x0; x <= x1; x++) {
            const i = (y * w + x) * c;
            const l = (data[i] * 0.299 + data[i + 1] * 0.587 + data[i + 2] * 0.114);
            const idx = Math.min(9, Math.max(0, Math.round((255 - l) / 255 * 9)));
            line += RAMP[idx];
        }
        console.log(String(y).padStart(3) + '|' + line);
    }
})();
