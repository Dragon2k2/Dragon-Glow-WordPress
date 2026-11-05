const fs = require('fs');
const s = fs.readFileSync('wp-content/themes/dragon-glow/template-parts/global/header-nav.php', 'utf8');
const m = [...s.matchAll(/id\s*=\s*(["'])([\s\S]*?)\1/g)];
console.log('ids found:', m.map((x) => x[2]));
