// Computed-style regression check for pure CSS refactors.
//
//   npm run styles:snapshot   → record the current rendering
//   (refactor SCSS, npm run build)
//   npm run styles:diff       → compare; 0 differences = nothing visible changed
//
// Records every computed property + box (width, height, left) of every element in
// the header, the footer and each .block, at the edges of each breakpoint, plus the
// mobile menu opened. Options: --path=/page/ (default /), --url=https://…

import { existsSync, mkdirSync, readFileSync, writeFileSync } from 'node:fs';
import { join } from 'node:path';
import { launch, siteUrl, OUT_DIR } from './cdp.mjs';

// One px either side of 'md' (768) and 'lg' (1024) in src/scss/abstracts/_variables.scss.
const WIDTHS = [375, 767, 768, 1023, 1024, 1440];
const MAX_LINES = 60;

const args = process.argv.slice(2);
const mode = args[0];
const pathArg = args.find((a) => a.startsWith('--path='))?.slice(7) || '/';
const slug = pathArg.replace(/[^a-z0-9]+/gi, '-').replace(/^-|-$/g, '') || 'home';
const snapshotFile = join(OUT_DIR, `styles-${slug}.json`);

if (!['snapshot', 'diff'].includes(mode)) {
  console.error('Usage: npm run styles:snapshot | npm run styles:diff  [-- --path=/page/]');
  process.exit(1);
}
if (mode === 'diff' && !existsSync(snapshotFile)) {
  console.error(`No snapshot at ${snapshotFile} — run npm run styles:snapshot first.`);
  process.exit(1);
}

const COLLECT = `(() => {
  const roots = [...document.querySelectorAll('.site-header, .site-footer, .block')];
  const out = {};
  roots.forEach((root, r) => {
    const rootKey = root.classList.contains('block')
      ? [...root.classList].find((c) => c.endsWith('-block')) || 'block'
      : root.classList[0];
    [root, ...root.querySelectorAll('*')].forEach((el, i) => {
      const cs = getComputedStyle(el);
      const props = {};
      for (let k = 0; k < cs.length; k++) {
        const p = cs[k];
        if (p.startsWith('transition') || p.startsWith('animation')) continue;
        props[p] = cs.getPropertyValue(p);
      }
      const b = el.getBoundingClientRect();
      props['(box)'] = [b.width, b.height, b.left].map(Math.round).join(' ');
      // Swiper toggles swiper-slide-active/-next/-prev at runtime — leave them out of the key.
      const cls = [...el.classList].filter((c) => !c.startsWith('swiper-slide-')).join('.');
      out[r + ':' + rootKey + ' #' + i + ' ' + el.tagName.toLowerCase() + (cls ? '.' + cls : '')] = props;
    });
  });
  return out;
})()`;

const browser = await launch();
const current = {};
try {
  const url = new URL(pathArg, siteUrl(args)).href;
  for (const width of WIDTHS) {
    await browser.viewport(width);
    await browser.open(url);
    current[width] = await browser.evaluate(COLLECT);

    if (width === 375) {
      const opened = await browser.evaluate(`(() => {
        const t = document.querySelector('.site-header-toggle');
        if (!t) return false;
        t.click();
        return true;
      })()`);
      if (opened) {
        await browser.wait(600);
        current['375 (menu open)'] = await browser.evaluate(COLLECT);
      }
    }
  }
} finally {
  await browser.close();
}

const elementCount = Object.keys(current[1440] || {}).length;

if (mode === 'snapshot') {
  mkdirSync(OUT_DIR, { recursive: true });
  writeFileSync(snapshotFile, JSON.stringify(current));
  console.log(`✔ Snapshot: ${elementCount} elements × ${Object.keys(current).length} states → ${snapshotFile}`);
  process.exit(0);
}

const before = JSON.parse(readFileSync(snapshotFile, 'utf8'));
const perArea = {};
let total = 0;
let printed = 0;

for (const state of Object.keys(before)) {
  const a = before[state];
  const b = current[state] || {};
  for (const key of new Set([...Object.keys(a), ...Object.keys(b)])) {
    const area = `${state}px  ${key.split(' #')[0].replace(/^\d+:/, '')}`;
    const report = (line) => {
      total++;
      perArea[area] = (perArea[area] || 0) + 1;
      if (printed++ < MAX_LINES) console.log(`  ${state}px  ${key}  ${line}`);
    };
    if (!a[key] || !b[key]) {
      report(a[key] ? 'element removed' : 'element added');
      continue;
    }
    for (const prop of new Set([...Object.keys(a[key]), ...Object.keys(b[key])])) {
      if (a[key][prop] !== b[key][prop]) report(`${prop}: ${a[key][prop]} → ${b[key][prop]}`);
    }
  }
}

if (!total) {
  console.log(`✔ No differences (${elementCount} elements × ${Object.keys(before).length} states).`);
} else {
  if (printed > MAX_LINES) console.log(`  … ${printed - MAX_LINES} more`);
  console.log('\nDifferences by viewport and area:');
  Object.entries(perArea).forEach(([area, n]) => console.log(`  ${String(n).padStart(4)}  ${area}`));
  console.log(`\n✖ ${total} differences.`);
  process.exitCode = 1;
}
