import gulp from 'gulp';
const { src, dest, watch, series, parallel, lastRun } = gulp;
import * as dartSass from 'sass';
import gulpSass from 'gulp-sass';
const sass = gulpSass(dartSass);
import sourcemaps from 'gulp-sourcemaps';
import postcss from 'gulp-postcss';
import autoprefixer from 'autoprefixer';
import terser from 'gulp-terser';
import sharp from 'sharp';
import { optimize as svgoOptimize } from 'svgo';
import { Transform } from 'stream';
import browserSyncModule from 'browser-sync';
const browserSync = browserSyncModule.create();
import { deleteAsync } from 'del';
import plumber from 'gulp-plumber';
import cssnano from 'cssnano';
import favicons from 'gulp-favicons';
import ttf2woff2 from 'gulp-ttf2woff2';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const projectRoot = path.dirname(fileURLToPath(import.meta.url));

// Node's built-in .env loader; throws if the file is absent, and like dotenv it
// never overwrites variables already set in the environment.
try {
  process.loadEnvFile(path.join(projectRoot, '.env'));
} catch {
  // No .env — serve() reports the missing PROXY_URL with a usable message.
}

const proxyURL = process.env.PROXY_URL;

let isWatching = false;

const paths = {
  scss: { src: './src/scss/**/*.scss', dest: './dist/css' },
  js: { src: './src/js/**/*.js', dest: './dist/js' },
  images: { src: './src/img/**/*.{jpg,jpeg,png,svg,gif}', dest: './dist/img' },
  fonts: {
    src: './src/fonts/**/*.ttf',
    dest: './dist/fonts',
    scssOut: './src/scss/base/_fonts.scss',
    cssPath: '../fonts'
  },
  favicon: { src: 'src/favicon/favicon.png', dest: 'dist/favicon' },
  php: './**/*.php'
};

function onError(err) {
  console.error('❌ Error:', err.message);
  this.emit('end');
}

const fromSrc = (glob, opts = {}) => {
  const stream = src(glob, opts);
  return isWatching ? stream.pipe(plumber({ errorHandler: onError })) : stream;
};

const listFiles = (dir, ext) =>
  fs.existsSync(dir)
    ? fs.readdirSync(dir, { recursive: true }).map(String).filter((f) => f.toLowerCase().endsWith(ext))
    : [];

const mtime = (f) => (fs.existsSync(f) ? fs.statSync(f).mtimeMs : 0);
const newestMtime = (files) => files.reduce((m, f) => Math.max(m, mtime(f)), 0);

// ------------------ Clean ------------------ //
const clean = () => deleteAsync(['./dist']);
const cleanCompiled = () => deleteAsync(['./dist/css', './dist/js', './dist/img']);
const cleanMaps = () => deleteAsync(['./dist/css/**/*.map', './dist/js/**/*.map']);

// ------------------ Favicon ------------------ //
function generateFavicon() {
  return fromSrc(paths.favicon.src, { encoding: false })
    .pipe(favicons({
      path: `${paths.favicon.dest}/`,
      icons: {
        favicons: true,
        appleIcon: true,
        android: true,
        appleStartup: false,
        windows: false,
        yandex: false,
        coast: false
      }
    }))
    .pipe(dest(paths.favicon.dest));
}

const cleanFavicon = () => deleteAsync([paths.favicon.dest]);
const favicon = series(cleanFavicon, generateFavicon);

// ------------------ Styles ------------------ //
const compileSass = (srcPath, destPath, minify = false, base = undefined) => {
  const dev = !minify;
  let stream = fromSrc(srcPath, base ? { base } : {});
  if (dev) stream = stream.pipe(sourcemaps.init());
  stream = stream
    .pipe(sass({ loadPaths: ['node_modules'], style: 'expanded' }))
    .pipe(postcss(minify ? [autoprefixer(), cssnano({ preset: 'default' })] : [autoprefixer()]));
  if (dev) stream = stream.pipe(sourcemaps.write('.'));
  stream = stream.pipe(dest(destPath));
  if (dev) stream = stream.pipe(browserSync.stream());
  return stream;
};

const styleDev = () => compileSass(paths.scss.src, paths.scss.dest, false);
const styleProd = () => compileSass(paths.scss.src, paths.scss.dest, true);

const scssPartials = [
  './src/scss/abstracts/**/*.scss',
  './src/scss/base/**/*.scss',
  './src/scss/components/**/*.scss',
  './src/scss/layout/**/*.scss',
  './src/scss/editor/**/*.scss'
];
const scssEntries = [
  './src/scss/critical.scss',
  './src/scss/editor.scss',
  './src/scss/global.scss',
  './src/scss/blocks/**/*.scss',
  './src/scss/sections/**/*.scss',
  './src/scss/page-templates/**/*.scss'
];
const compileEntry = (filePath) => compileSass(filePath, paths.scss.dest, false, './src/scss');

// ------------------ JavaScript ------------------ //
const compileJS = (minify = false) => {
  const dev = !minify;
  let stream = fromSrc(paths.js.src);
  if (dev) stream = stream.pipe(sourcemaps.init());
  if (minify) stream = stream.pipe(terser({ compress: { drop_console: true } }));
  if (dev) stream = stream.pipe(sourcemaps.write('.'));
  return stream.pipe(dest(paths.js.dest));
};

const jsDev = () => compileJS(false);
const jsProd = () => compileJS(true);

// ------------------ Images ------------------ //
// svgo 4 dropped removeViewBox from preset-default, so viewBox is kept without an override.
const SVGO_CONFIG = {
  plugins: [{ name: 'preset-default', params: { overrides: { cleanupIds: false } } }]
};

// `.rotate()` bakes EXIF orientation into the pixels before sharp strips metadata,
// otherwise phone photos come out rotated. `palette: false` is required for lossless
// PNG — sharp defaults to palette:true, which quantises anything over 256 colours.
const optimizeImages = () => {
  let processed = 0;
  let improved = 0;
  let saved = 0;
  return new Transform({
    objectMode: true,
    async transform(file, _enc, cb) {
      if (file.isNull() || file.isDirectory() || !file.contents) return cb(null, file);
      const ext = path.extname(file.path).toLowerCase();
      const before = file.contents.length;
      try {
        let out;
        if (ext === '.svg') {
          out = Buffer.from(svgoOptimize(file.contents.toString('utf8'), SVGO_CONFIG).data, 'utf8');
        } else if (ext === '.jpg' || ext === '.jpeg') {
          out = await sharp(file.contents).rotate().jpeg({ quality: 80, progressive: true, mozjpeg: true }).toBuffer();
        } else if (ext === '.png') {
          out = await sharp(file.contents).rotate().png({ compressionLevel: 9, effort: 10, palette: false }).toBuffer();
        } else if (ext === '.gif') {
          out = await sharp(file.contents, { animated: true }).gif().toBuffer();
        } else {
          return cb(null, file);
        }
        if (out.length < before) {
          file.contents = out;
          saved += before - out.length;
          improved++;
        }
        processed++;
        cb(null, file);
      } catch (err) {
        cb(err);
      }
    },
    flush(cb) {
      console.log(`images: optimized ${improved}/${processed} files (saved ${(saved / 1024).toFixed(2)} kB)`);
      cb();
    }
  });
};

const runImages = (self) =>
  fromSrc(paths.images.src, { since: lastRun(self), encoding: false })
    .pipe(optimizeImages())
    .pipe(dest(paths.images.dest));

const imagesDev = () => runImages(imagesDev);
const imagesProd = () => runImages(imagesProd);

// ------------------ Fonts ------------------ //
const fontWeights = {
  thin: 100, extralight: 200, light: 300, regular: 400,
  medium: 500, semibold: 600, bold: 700, extrabold: 800, black: 900
};

const woff2Of = (ttfRel) => ttfRel.replace(/\.ttf$/i, '.woff2');

const FONT_STALE_TOLERANCE_MS = 2000;
const staleTtfs = () => listFiles('./src/fonts', '.ttf').filter((rel) => {
  const out = path.join(paths.fonts.dest, woff2Of(rel));
  return !fs.existsSync(out) || mtime(path.join('./src/fonts', rel)) - mtime(out) > FONT_STALE_TOLERANCE_MS;
}).map((rel) => path.join('./src/fonts', rel));

const buildFonts = () => {
  const stale = staleTtfs();
  if (!stale.length) return Promise.resolve();
  return fromSrc(stale, { base: './src/fonts', encoding: false })
    .pipe(ttf2woff2())
    .pipe(dest(paths.fonts.dest));
};

const pruneOrphanWoff2 = () => {
  const sources = new Set(listFiles('./src/fonts', '.ttf').map(woff2Of));
  const orphans = listFiles(paths.fonts.dest, '.woff2')
    .filter((rel) => !sources.has(rel))
    .map((rel) => path.join(paths.fonts.dest, rel));
  return orphans.length ? deleteAsync(orphans) : Promise.resolve();
};

// Reads the `fvar` table of a TTF and returns { tag: [min, max] } per axis, or null for static fonts
function readVariableAxes(ttfPath) {
  const buf = fs.readFileSync(ttfPath);
  const numTables = buf.readUInt16BE(4);
  for (let i = 0; i < numTables; i++) {
    const rec = 12 + i * 16;
    if (buf.toString('latin1', rec, rec + 4) !== 'fvar') continue;
    const fvar = buf.readUInt32BE(rec + 8);
    const axesOffset = fvar + buf.readUInt16BE(fvar + 4);
    const axisCount = buf.readUInt16BE(fvar + 8);
    const axisSize = buf.readUInt16BE(fvar + 10);
    const axes = {};
    for (let a = 0; a < axisCount; a++) {
      const axis = axesOffset + a * axisSize;
      const tag = buf.toString('latin1', axis, axis + 4);
      axes[tag] = [buf.readInt32BE(axis + 4) / 65536, buf.readInt32BE(axis + 12) / 65536];
    }
    return axes;
  }
  return null;
}

function parseFontName(name) {
  let style = 'normal';
  let weight = 400;
  const familyParts = [];
  name.replace(/[-_ ]?VariableFont(_[A-Za-z0-9,]+)?/i, '').split(/[-_ ]/).forEach((part) => {
    let key = part.toLowerCase();
    let isMeta = false;
    if (key.endsWith('italic') || key.endsWith('oblique')) {
      style = 'italic';
      key = key.replace(/(italic|oblique)$/, '');
      isMeta = true;
    }
    if (key !== '' && fontWeights[key] !== undefined) {
      weight = fontWeights[key];
      isMeta = true;
    }
    if (!isMeta) familyParts.push(part);
  });
  const family = familyParts.join(' ') || name;
  return { family, weight, style };
}

function fontFace(done) {
  const files = listFiles(paths.fonts.dest, '.woff2');
  let scss = '// Auto-generated by gulp `fonts` task — do not edit by hand\n\n';
  files.forEach((file) => {
    const rel = file.split(path.sep).join('/'); // e.g. lato/Lato-Black.woff2
    const name = path.basename(file, '.woff2');
    const { family, weight, style } = parseFontName(name);
    const ttf = path.join('./src/fonts', file.replace(/\.woff2$/i, '.ttf'));
    const axes = fs.existsSync(ttf) ? readVariableAxes(ttf) : null;
    scss += `@font-face {\n`;
    scss += `  font-family: '${family}';\n`;
    scss += `  src: url('${paths.fonts.cssPath}/${rel}') format('woff2');\n`;
    scss += `  font-weight: ${axes?.wght ? axes.wght.join(' ') : weight};\n`;
    if (axes?.wdth) scss += `  font-stretch: ${axes.wdth.map((v) => `${v}%`).join(' ')};\n`;
    scss += `  font-style: ${style};\n`;
    scss += `  font-display: swap;\n`;
    scss += `}\n\n`;
  });
  fs.writeFileSync(paths.fonts.scssOut, scss);
  done();
}

const fonts = series(pruneOrphanWoff2, buildFonts, fontFace);

// ------------------ BrowserSync ------------------ //
const serve = (done) => {
  if (!proxyURL) {
    done(new Error('PROXY_URL is not set — copy .env.example to .env and set your local site URL.'));
    return;
  }
  browserSync.init({
    proxy: proxyURL,
    notify: false,
    open: true,
    ...(process.env.BS_BROWSER ? { browser: process.env.BS_BROWSER } : {})
  });
  done();
};
const reload = (done) => { browserSync.reload(); done(); };

// ------------------ Watch ------------------ //
const cleanOrphan = (srcDir, destDir, ext) => (changed) => {
  const rel = path.relative(path.resolve(projectRoot, srcDir), path.resolve(changed));
  let out = path.join(projectRoot, destDir, rel);
  if (ext) out = out.replace(/\.[^.]+$/, ext);
  return deleteAsync([out, `${out}.map`], { force: true });
};

const watchFiles = () => {
  watch(scssPartials, styleDev);
  watch(scssEntries)
    .on('add', compileEntry)
    .on('change', compileEntry)
    .on('unlink', cleanOrphan('src/scss', 'dist/css', '.css'));
  watch(paths.js.src, series(jsDev, reload))
    .on('unlink', cleanOrphan('src/js', 'dist/js'));
  watch(paths.images.src, series(imagesDev, reload))
    .on('unlink', cleanOrphan('src/img', 'dist/img'));
  watch(paths.fonts.src, fonts);
  watch(paths.favicon.src, series(favicon, reload));
  watch(paths.php, { ignored: /node_modules/ }, reload);
};

// ------------------ Exports ------------------ //
const listTtfFiles = () => listFiles('./src/fonts', '.ttf').map((f) => path.join('./src/fonts', f));

const ensureFonts = (done) => {
  const scssOut = paths.fonts.scssOut;
  const ttfs = listTtfFiles();
  const woff2 = listFiles(paths.fonts.dest, '.woff2');
  const fresh = fs.existsSync(scssOut) && woff2.length === ttfs.length && newestMtime(ttfs) <= mtime(scssOut);
  if (fresh) { done(); return; }
  fonts(done);
};

const ensureFavicon = (done) => {
  const dir = paths.favicon.dest;
  const outputs = fs.existsSync(dir)
    ? fs.readdirSync(dir).filter((f) => !f.startsWith('.'))
    : [];
  const fresh = outputs.length > 0 && mtime(paths.favicon.src) <= newestMtime(outputs.map((f) => path.join(dir, f)));
  if (fresh) { done(); return; }
  favicon(done);
};

const enableWatchMode = (done) => { isWatching = true; done(); };
const build = series(parallel(ensureFonts, ensureFavicon), parallel(styleDev, jsDev, imagesDev));
const quick = series(ensureFonts, cleanMaps, parallel(styleProd, jsProd, imagesProd));
const prod = series(cleanCompiled, parallel(ensureFonts, ensureFavicon), parallel(styleProd, jsProd, imagesProd));
const dev = series(enableWatchMode, build, serve, watchFiles);

export { clean, favicon, fonts, build, prod, quick, dev };
export default dev;
