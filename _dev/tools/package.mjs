// Builds an upload-ready copy of the theme from an ALLOWLIST, so dev files
// (node_modules, src, _dev, configs, .env …) can never reach the server.
//
//   npm run package   → npm run prod, then this script
//
// Output (git-ignored):
//   _dev/release/<slug>/               upload the CONTENTS of this folder over FTP
//   _dev/release/<slug>-<version>.zip  or upload this via Appearance → Themes → Add → Upload
//
// <slug> is the theme folder name. Keep it identical on the server: WordPress stores
// menu locations and theme mods per theme folder, so a renamed folder loses them.
//
// Adding a new top-level folder the theme needs at RUNTIME? Add it to ALLOW_DIRS below.
// Dev-only things go under _dev/ and need nothing here.

import { execFileSync } from 'node:child_process';
import { cpSync, existsSync, mkdirSync, readdirSync, readFileSync, rmSync, statSync } from 'node:fs';
import { basename, dirname, extname, join, relative, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const THEME_ROOT = resolve(dirname(fileURLToPath(import.meta.url)), '../..');
const RELEASE_DIR = join(THEME_ROOT, '_dev/release');

// What the theme needs on the server — nothing else is copied.
const ALLOW_DIRS = ['inc', 'template-parts', 'blocks-json', 'acf-json', 'assets', 'languages', 'dist'];
const ALLOW_ROOT_FILES = (name) => name.endsWith('.php') || ['style.css', 'screenshot.png', 'screenshot.jpg'].includes(name);
const SKIP = (name) => name === '.DS_Store' || name.endsWith('.map');

const slug = basename(THEME_ROOT);
const styleCss = readFileSync(join(THEME_ROOT, 'style.css'), 'utf8');
const version = (styleCss.match(/^\s*\*?\s*Version:\s*(.+)$/mi)?.[1] || '0.0.0').trim();
const target = join(RELEASE_DIR, slug);
const zipFile = join(RELEASE_DIR, `${slug}-${version}.zip`);

const errors = [];
const fail = (msg) => errors.push(msg);

// 1. Fresh copy
rmSync(target, { recursive: true, force: true });
rmSync(zipFile, { force: true });
mkdirSync(target, { recursive: true });

for (const name of readdirSync(THEME_ROOT)) {
  if (ALLOW_ROOT_FILES(name) && statSync(join(THEME_ROOT, name)).isFile()) {
    cpSync(join(THEME_ROOT, name), join(target, name));
  }
}
for (const dir of ALLOW_DIRS) {
  if (!existsSync(join(THEME_ROOT, dir))) continue;
  cpSync(join(THEME_ROOT, dir), join(target, dir), {
    recursive: true,
    filter: (src) => !SKIP(basename(src)),
  });
}

// 2. Sanity checks
const walk = (dir) => readdirSync(dir, { withFileTypes: true })
  .flatMap((e) => (e.isDirectory() ? walk(join(dir, e.name)) : [join(dir, e.name)]));
const files = walk(target);

for (const required of ['dist/css/global.css', 'dist/css/critical.css', 'functions.php']) {
  if (!existsSync(join(target, required))) fail(`missing ${required} — did the build run?`);
}

// block.json asset + template paths must resolve inside the package
for (const file of files.filter((f) => f.endsWith('block.json'))) {
  const meta = JSON.parse(readFileSync(file, 'utf8'));
  const refs = [meta.style, meta.editorStyle, meta.script, meta.viewScript, meta.editorScript]
    .flat().filter((r) => typeof r === 'string' && r.startsWith('file:')).map((r) => r.slice(5));
  if (meta.acf?.renderTemplate) refs.push(meta.acf.renderTemplate);
  for (const ref of refs) {
    const abs = resolve(dirname(file), ref);
    if (!abs.startsWith(target) || !existsSync(abs)) fail(`${relative(target, file)} → ${ref} is not in the package`);
  }
}

// php -l every PHP file
for (const file of files.filter((f) => extname(f) === '.php')) {
  try {
    execFileSync('php', ['-l', file], { stdio: 'pipe' });
  } catch (e) {
    fail(`php -l ${relative(target, file)}: ${e.stdout?.toString().trim() || e.message}`);
  }
}

if (errors.length) {
  console.error(`✖ Package NOT created:\n  ${errors.join('\n  ')}`);
  process.exit(1);
}

// 3. Zip with the theme folder at the top level
execFileSync('zip', ['-rqX', zipFile, slug], { cwd: RELEASE_DIR });

const bytes = files.reduce((sum, f) => sum + statSync(f).size, 0);
const mb = (n) => `${(n / 1024 / 1024).toFixed(2)} MB`;
console.log(`✔ ${slug} ${version}: ${files.length} files, ${mb(bytes)} (zip ${mb(statSync(zipFile).size)})`);
console.log(`  contents: ${readdirSync(target).sort().join('  ')}`);
console.log(`  folder:   ${relative(THEME_ROOT, target)}/   ← FTP: upload what is INSIDE this folder`);
console.log(`  zip:      ${relative(THEME_ROOT, zipFile)}   ← or Appearance → Themes → Upload`);
console.log('  Local dist/ is now the production build — run `npm run build` to go back to dev.');
