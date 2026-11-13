const sharp = require('sharp');

const SRC = 'c:/Users/levie/AppData/Local/Temp/cursor/screenshots/page-2026-11-07T23-16-49-300Z.png';
const OUT = 'tmp-resp/account-pagination/after-fix-1536.jpg';

(async () => {
	await sharp(SRC)
		.extract({ left: 350, top: 370, width: 1100, height: 130 })
		.resize({ width: 1100 })
		.jpeg({ quality: 92 })
		.toFile(OUT);
	console.log('ok');
})();
