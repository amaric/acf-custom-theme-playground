// Screenshot one element at several viewport widths.
//
//   npm run shot -- <selector> [width …] [--path=/some-page/] [--url=https://…]
//   npm run shot -- .testimonial-block            → 1440 + 375
//   npm run shot -- .site-header 375 1024 1440
//
// PNGs land in _dev/tools/visual/out/ (git-ignored).

import { mkdirSync, writeFileSync } from 'node:fs';
import { join } from 'node:path';
import { launch, siteUrl, OUT_DIR } from './cdp.mjs';

const args = process.argv.slice(2);
const selector = args.find((a) => !a.startsWith('--') && !/^\d+$/.test(a));
const widths = args.filter((a) => /^\d+$/.test(a)).map(Number);
const pathArg = args.find((a) => a.startsWith('--path='))?.slice(7) || '/';

if (!selector) {
  console.error('Usage: npm run shot -- <selector> [width …] [--path=/page/] [--url=https://…]');
  process.exit(1);
}

const url = new URL(pathArg, siteUrl(args)).href;
const slug = selector.replace(/[^a-z0-9]+/gi, '-').replace(/^-|-$/g, '') || 'page';
mkdirSync(OUT_DIR, { recursive: true });

const browser = await launch();
try {
  for (const width of widths.length ? widths : [1440, 375]) {
    await browser.viewport(width);
    await browser.open(url);

    // Scroll it into view first so lazy images load, then measure in page coordinates.
    const rect = await browser.evaluate(`(async () => {
      const el = document.querySelector(${JSON.stringify(selector)});
      if (!el) return null;
      // The sticky header would cover the top of the element — pin it back into the
      // flow (it already takes up its space there, so nothing else moves).
      if (!el.closest('.site-header') && !el.querySelector('.site-header')) {
        const style = document.createElement('style');
        style.textContent = '.site-header { position: relative !important; }';
        document.head.append(style);
      }
      el.scrollIntoView({ block: 'start' });
      await new Promise((r) => setTimeout(r, 600));
      const b = el.getBoundingClientRect();
      return { x: b.left + scrollX, y: b.top + scrollY, width: b.width, height: b.height };
    })()`);

    if (!rect) {
      console.error(`✖ ${width}px: no element matches ${selector} on ${url}`);
      process.exitCode = 1;
      continue;
    }

    const { data } = await browser.send('Page.captureScreenshot', {
      format: 'png',
      captureBeyondViewport: true,
      clip: { ...rect, scale: 1 },
    });
    const file = join(OUT_DIR, `${slug}-${width}.png`);
    writeFileSync(file, Buffer.from(data, 'base64'));
    console.log(`✔ ${width}px → ${file} (${Math.round(rect.width)}×${Math.round(rect.height)})`);
  }
} finally {
  await browser.close();
}
