/**
 * Extract the theme's Tailwind CDN config from the PHP source and emit it as
 * a JS file, so the static test harness resolves the same design tokens
 * (bg-primary, text-on-primary, min-w-[20px], …) the live site uses.
 *
 * Run: node tmp-resp/gen-tailwind-config.js
 */
const fs = require('fs');
const path = require('path');

const ROOT = path.resolve(__dirname, '..');
const SRC = path.join(ROOT, 'wp-content/themes/dragon-glow/inc/enqueue/tailwind-config.php');
const OUT = path.join(ROOT, 'tmp-resp/nav-cart-badge/tailwind-config.js');

const php = fs.readFileSync(SRC, 'utf8');
const m = php.match(/<<<'JS'\r?\n([\s\S]*?)\r?\nJS;/);
if (!m) {
  console.error('Could not extract the tailwind config heredoc from ' + SRC);
  process.exit(1);
}

fs.mkdirSync(path.dirname(OUT), { recursive: true });
fs.writeFileSync(OUT, m[1], 'utf8');
console.log('Wrote ' + OUT + ' (' + m[1].length + ' bytes)');
