/**
 * Render vùng chữ trong từng pill thành ASCII để đọc số.
 * Ngưỡng: pixel có luma < 200 = nét chữ.
 */
const sharp = require('sharp');

const USER = 'c:/Users/levie/.cursor/projects/c-du-an-html-Dragon-Glow-WordPress-main/assets/c__Users_levie_AppData_Roaming_Cursor_User_workspaceStorage_ceb529563ca2940597e17f5c004c6ad1_images_image-69e2228b-fe5a-4231-ac82-2e0e40e5a978.png';

(async () => {
    const { data, info } = await sharp(USER).raw().toBuffer({ resolveWithObject: true });
    const { width: w, channels: c } = info;

    const ranges = JSON.parse(process.argv[2]);
    const thresh = parseFloat(process.argv[3] || '200');

    for (const [a, b] of ranges) {
        console.log(`\n===== pill x=${a}..${b} =====`);
        for (let y = 360; y <= 381; y++) {
            let line = '';
            for (let x = a; x <= b; x++) {
                const i = (y * w + x) * c;
                const l = data[i] * 0.299 + data[i + 1] * 0.587 + data[i + 2] * 0.114;
                line += l < thresh ? '#' : (l < 225 ? '+' : '.');
            }
            console.log(String(y) + '|' + line);
        }
    }
})();
