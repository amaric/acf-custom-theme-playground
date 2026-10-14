# Elysian Theme

**Theme Name:** Elysian
**Text Domain:** elysian-theme
**Description:** A modular WordPress starter theme built for Advanced Custom Fields (ACF) block-based development with a Gulp 5 build pipeline.
**Author:** Antonio Maric
**Author URI:** [LinkedIn](https://www.linkedin.com/in/antonio-maric/)
**Version:** 1.0

---

## Features

- **A starter theme**: the header, footer, Theme Settings, build and tooling are reusable; blocks are per project
- **Example blocks** in [`_dev/examples/`](_dev/examples/README.md) (hero, CTA, FAQ accordion, Swiper testimonial slider), kept as inert reference copies to start new blocks from
- ACF-powered Gutenberg blocks registered from `block.json`, with per-block CSS/JS that loads only on pages using the block
- Vendor libraries (Swiper 12) registered once and pulled in per block through `block.json` handles
- ACF Options pages for global settings (logo, header button, footer text) and shared block content
- Gulp 5 pipeline: SCSS, JS minification (Terser), image optimization (sharp + svgo), self-hosted fonts, favicon set, BrowserSync
- **Desktop-first** SCSS with color tokens and an explicit cascade order (objects → components → layout regions)
- Visual tooling with no extra dependencies: element screenshots and computed-style regression diffs (headless Chrome)
- `_dev/tools/wp/run.sh`: run PHP against the Local site's database without wp-cli
- Performance: critical CSS inlined, deferred global CSS, font preloading, LCP-aware image loading
- WordPress bloat removal; editor restricted to ACF blocks

---

## Requirements

| Dependency | Version |
|---|---|
| WordPress | 5.0+ |
| Advanced Custom Fields PRO | 5.0+ |
| PHP | 7.4+ |
| Node.js | 22.22.3+, 24.15+ or 26+ |
| npm | 8+ |

---

## Theme Architecture

```
elysian-theme/
├── _dev/                            # Dev only — never deployed (see Deploying)
│   ├── docs/                        # figma-to-block.md, wp-scripting.md
│   ├── examples/blocks/<name>/      # Inert reference blocks: README, demo-content.html, files/
│   ├── tools/
│   │   ├── package.mjs              # npm run package — allowlisted release folder + zip
│   │   ├── visual/                  # shot.mjs, styles.mjs, cdp.mjs (headless Chrome); out/ git-ignored
│   │   └── wp/                      # run.sh + bootstrap.php (PHP against the Local site)
│   └── release/                     # npm run package output (git-ignored)
├── acf-json/                        # ACF field groups + options pages (auto export/import)
│   ├── group_68a9bb9c8bff5.json     # Options: Theme settings
│   ├── group_6920ccd17eed1.json     # Options: Global blocks
│   ├── group_*.json                 # One per live block (location: Block == acf/<name>)
│   └── ui_options_page_*.json       # Options page definitions
├── assets/swiper/                   # Vendored Swiper 12.0.3 (registered, loaded per block)
├── blocks-json/                     # <name>.block.json — metadata for every live block (flat)
├── dist/                            # Compiled output (git-ignored): css/, js/, img/, fonts/, favicon/
├── inc/                             # PHP modules (require_once'd from functions.php)
│   ├── setup.php                    # after_setup_theme: editor styles, image sizes
│   ├── acf.php                      # ACF PRO guard, JSON save path, block registration + category
│   ├── register-scripts-and-preload.php  # Global/vendor/template/flexible assets, critical CSS, CSS defer
│   ├── custom-hooks.php             # Bloat removal (emojis, generator, oEmbed, block styles)
│   ├── extras.php                   # SVG uploads, block whitelist, footer menu column helper
│   ├── menus.php                    # Header, header-mobile, footer 1–3, footer social
│   ├── post-types.php / taxonomies.php / shortcodes.php / widgets.php
│   ├── template-functions.php / woocommerce.php / security.php / admin.php
│   └── optimisation.php             # Empty placeholder
├── src/
│   ├── scss/
│   │   ├── abstracts/               # _variables (tokens, breakpoints), _mixins, _functions
│   │   ├── base/                    # _reset, _fonts (generated), _base
│   │   ├── layout/                  # _grid (objects), _header, _footer, _sidebar (regions)
│   │   ├── components/              # _button, _form, _card, _page-404
│   │   ├── editor/                  # _block-scope (editor only)
│   │   ├── blocks/                  # <name>.scss — one standalone file per block
│   │   ├── sections/                # flexible-content sections
│   │   ├── page-templates/          # template-specific styles
│   │   ├── global.scss              # Main stylesheet (deferred)
│   │   ├── critical.scss            # Inlined into <head>
│   │   └── editor.scss              # Block editor
│   ├── js/
│   │   ├── custom.js                # Global (mobile menu)
│   │   ├── blocks/                  # <name>.js — per block
│   │   ├── sections/ · page-templates/
│   ├── img/icons/                   # SVG icons → dist/img/icons/
│   ├── fonts/<family>/              # TTF → WOFF2 + generated @font-face
│   └── favicon/favicon.png          # → full favicon set
├── template-parts/
│   ├── blocks/                      # <name>.php — render template per live block
│   ├── sections/ · page-templates/
├── header.php / footer.php          # Site header (logo, menu, mobile overlay, button) / footer
├── functions.php                    # Bootstrap + shared helpers
├── index.php · page.php · single.php · archive.php · search.php · 404.php · comments.php
├── template-flexible.php            # Flexible-content page template
├── template-custom.php              # Custom page template
├── gulpfile.mjs · package.json · eslint.config.js
└── style.css                        # Theme header
```

---

## Installation

1. Clone into your WordPress themes directory:

```bash
cd wp-content/themes/
git clone <repository-url> elysian-theme
```

2. Install Node dependencies:

```bash
cd elysian-theme
npm install
```

3. Activate the theme in **Appearance > Themes**.

4. Install and activate **Advanced Custom Fields PRO**. Field groups auto-sync from `acf-json/`.

---

## Build System

### Gulp 5 Pipeline

The build system uses Gulp 5 with the following task pipeline:

| Task | Input | Output | Dev Mode | Prod Mode |
|---|---|---|---|---|
| SCSS | `src/scss/global.scss` | `dist/css/global.css` | Expanded + sourcemaps | Compressed, Autoprefixed |
| Critical SCSS | `src/scss/critical.scss` | `dist/css/critical.css` | Expanded + sourcemaps | Compressed, Autoprefixed |
| Editor SCSS | `src/scss/editor.scss` | `dist/css/editor.css` | Expanded + sourcemaps | Compressed, Autoprefixed |
| JavaScript | `src/js/**/*.js` | `dist/js/**/*.js` | Sourcemaps only | Terser minified, `drop_console` |
| Images | `src/img/**/*` | `dist/img/**/*` | Optimized | Optimized |
| BrowserSync | — | — | Proxies `PROXY_URL` from `.env` | — |

**Image optimization settings:**
- JPEG: sharp with mozjpeg (quality 80, progressive), EXIF orientation baked in
- PNG: sharp, lossless (`compressionLevel: 9`, `palette: false`)
- GIF: sharp (animation kept)
- SVG: svgo `preset-default` (viewBox kept)

### NPM Scripts

| Command | Description |
|---|---|
| `npm start` | Start Gulp watcher with BrowserSync live reload |
| `npm run dev` | Development build with file watching |
| `npm run build` | One-time development build |
| `npm run prod` | Production build (minified, no sourcemaps) |
| `npm run clean` | Delete `dist/` directory |
| `npm run quick` | Quick production build (minified assets only; ensures fonts exist, but no clean/favicon/watch) |
| `npm run lint` / `lint:fix` | Stylelint + ESLint (must exit 0) / auto-fix |
| `npm run shot`, `styles:snapshot`, `styles:diff` | Visual checks — see below |
| `npm run package` | Production build + clean upload-ready copy and zip — see Deploying |

### Browser Support

Defined in `.browserslistrc`:
```
> 1%
last 2 versions
Firefox ESR
not dead
```

---

## PHP Module Architecture

### Bootstrap (`functions.php`)

The theme bootstraps 14 modules via `require_once` from the `/inc/` directory. All theme functions use the `elysian_theme_` prefix for namespace isolation.

### Hook Registration Map

| Hook | Priority | Module | Purpose |
|---|---|---|---|
| `after_setup_theme` | 5 | setup.php | Editor styles, title-tag, custom image sizes |
| `init` | 10 | menus.php | Register nav menu locations |
| `acf/init` | 10 | acf.php | Register ACF blocks |
| `block_categories_all` | 10 | acf.php | Add `elysian_theme_blocks` category |
| `wp_head` | 1 | register-scripts-and-preload.php | Critical CSS inlining |
| `wp_enqueue_scripts` | 10 | register-scripts-and-preload.php | Global CSS/JS assets |
| `wp_enqueue_scripts` | 10 | register-scripts-and-preload.php | Template-specific + flexible field assets |
| `wp_enqueue_scripts` | 100 | custom-hooks.php | Remove block library CSS |
| `acf/settings/save_json` | 10 | acf.php | ACF JSON save path |
| `allowed_block_types_all` | 10 | extras.php | Block type whitelist |
| `upload_mimes` | 10 | extras.php | SVG MIME type support |
| `wp_print_styles` | 100 | custom-hooks.php | Remove WooCommerce block styles |

### Asset Enqueuing Strategy

Assets are loaded conditionally to minimize page weight:

- **Global assets** — Always loaded: `dist/css/global.css`, `dist/js/custom.js`
- **Critical CSS** — `dist/css/critical.css` is inlined into `<head>` on `wp_head` (priority 1) by `register-scripts-and-preload.php`, never enqueued as a `<link>`
- **Block assets** — Per-block CSS/JS declared as `style` / `script` in `blocks-json/<block>.block.json`; WP loads them only on pages containing the block
- **Vendor libraries** — Registered (not enqueued) in `elysian_theme_register_vendor_assets()`; a block pulls one in by listing its handle in `block.json`
- **Template assets** — Loaded conditionally via `is_page_template()` check
- **Flexible field assets** — Loaded conditionally via `have_rows()` + `get_row_layout()` iteration
- **Cache busting** — All assets versioned with `filemtime()` instead of static version strings

---

## Blocks and examples

The live blocks belong to the current project. The patterns are kept as reference copies in
[`_dev/examples/`](_dev/examples/README.md), which WordPress, ACF and Gulp never load:

| Example | Shows |
|---|---|
| [hero](_dev/examples/blocks/hero/README.md) | LCP background image + overlay, `<h1>` with page-title fallback, two link buttons |
| [cta](_dev/examples/blocks/cta/README.md) | Background-image section, editor-only placeholder text |
| [faq](_dev/examples/blocks/faq/README.md) | Repeater, accessible accordion, CSS-only height animation, SVG icons |
| [testimonial](_dev/examples/blocks/testimonial/README.md) | Swiper loaded only with the block, bullets + prev/next buttons, initials avatar |

Each example holds a `README.md` (fields, patterns), a `demo-content.html` (the block as
saved in a page) and a `files/` folder that mirrors the theme root. Restore one with
`cp -R _dev/examples/blocks/<name>/files/ .`.

---

## ACF Options Pages

| Page | Fields |
|---|---|
| Theme Settings | `logo`, `logo_white` (images), `header_button` (link), `footer_description` (textarea), `copyright_text` (text; falls back to `© {year} {site name}. All rights reserved.`) |
| Global Blocks | Shared block content (empty by default) |

Accessed in templates via `get_field('field_name', 'option')`.

---

## Block Editor Configuration

The theme restricts the block editor to ACF blocks only via the `allowed_block_types_all` filter:

```php
// Default: only blocks with 'acf/' prefix
// Exception: FAQ post type also allows core/paragraph, core/list, core/list-item
```

This enforces design system compliance and prevents content authors from using unstyled core blocks.

---

## Registered Menus

| Location | Used for |
|---|---|
| `header-menu` | Desktop header navigation |
| `header-menu-mobile` | Mobile overlay (falls back to `header-menu`) |
| `footer-menu-1`, `-2`, `-3` | Footer link columns; the **menu name** is the column title (`elysian_theme_render_footer_menu()`) |
| `footer-social` | Social links in the footer bottom bar |

---

## Custom Image Sizes

| Name | Width | Height | Crop |
|---|---|---|---|
| `full-width-image` | 1440 | 720 | Yes |
| `image-160-160` | 160 | 160 | Yes (avatars) |

---

## Performance Optimizations

### Font Loading
- Self-hosted WOFF2 via `@font-face` with `font-display: swap`. `src/scss/base/_fonts.scss`
  is **auto-generated** by the Gulp `fonts` task from the TTFs in `src/fonts/<family>/`;
  WOFF2 output mirrors that structure under `dist/fonts/<family>/`
- A `<link rel="preload" as="font" crossorigin>` helper ships **commented out** in
  `inc/register-scripts-and-preload.php` — uncomment it and update the list to match the
  fonts you actually ship

### Critical CSS
- `src/scss/critical.scss` compiles to `dist/css/critical.css` and is inlined into `<head>`
  via `register-scripts-and-preload.php`
- Reduces render-blocking CSS for above-the-fold content

### Image Strategy
- Hero images: `loading="eager"` + `fetchpriority="high"` (LCP optimization)
- All other images: `loading="lazy"` (native browser lazy loading)
- Build-time optimization via sharp (JPEG/PNG/GIF, lossless PNG) and svgo (SVG)

### WordPress Bloat Removal
Handled in `custom-hooks.php`:

| Removed | Hook/Action |
|---|---|
| Emoji detection scripts + styles | `remove_action('wp_head', 'print_emoji_detection_script')` |
| WordPress generator meta tag | `remove_action('wp_head', 'wp_generator')` |
| RSD + WLW manifest links | `remove_action('wp_head', ...)` |
| oEmbed discovery links + scripts | `remove_action('wp_head', ...)` |
| Classic theme inline styles | `wp_dequeue_style('classic-theme-styles')` |
| Global block styles | `wp_dequeue_style('wp-block-styles')` |
| Block library CSS | `wp_dequeue_style('wp-block-library')` |
| WooCommerce block styles | `wp_dequeue_style('wc-blocks-style')` |

---

## SCSS Architecture

**Cascade order** of the entry points (`global.scss`, `critical.scss`):

| # | Layer | Files |
|---|---|---|
| 1 | Abstracts — tokens, mixins | `abstracts/_variables`, `_mixins`, `_functions` |
| 2 | Base — element defaults | `base/_reset`, `_fonts`, `_base` |
| 3 | Objects — layout primitives | `layout/_grid` (`.container`: 1392px max, 32px gutter) |
| 4 | Components — reusable pieces | `components/_button` (`.btn`), `_form`, `_card`, `_page-404` |
| 5 | Layout regions | `layout/_header`, `_footer`, `_sidebar` |

Components come before the layout regions on purpose: at equal specificity the later rule
wins, so a region can adapt a component (`.site-header-btn { display: none }` hides a `.btn`)
without selector hacks. Blocks, sections and page templates are **standalone** entry
files (no underscore) compiled to their own CSS and loaded per use — never `@use` them in
`global.scss`. The footer is not in `critical.scss`, since it sits below the fold.

**Responsive — desktop-first.** Base styles are desktop; smaller screens override with
`@include mixins.breakpoint-down('lg')` (max-width). Breakpoints (`$breakpoints`):
`sm` 576, `md` 768, **`lg` 1024 — the desktop → mobile switch**, `xl` 1200, `xxl` 1400.

**Tokens** (`abstracts/_variables.scss`): `$color-primary`, `$color-white`,
`$color-bg-dark`, `$color-surface`, `$color-text-muted`, `$color-border`; tints via
`rgba($token, 0.1)`. Sizes are `em` on a 62.5% root (`1.4em` = 14px).

---

## Adding a New Block

1. **Start from an example.** Pick the closest one in [`_dev/examples/`](_dev/examples/README.md),
   copy its `files/` under the new name and rename `<old>` → `<new>` (file names,
   `"name": "acf/<new>"`, `<new>-block` classes, field names, ACF group key/title/location).
2. **Metadata** — `blocks-json/<name>.block.json` with `"name": "acf/<name>"`,
   `"category": "elysian_theme_blocks"`, `"style": [ "file:../dist/css/blocks/<name>.css" ]`,
   `"script": [ "file:../dist/js/blocks/<name>.js" ]` and
   `"acf": { "mode": "preview", "renderTemplate": "../template-parts/blocks/<name>.php" }`.
   No PHP change is needed — `inc/acf.php` globs `blocks-json/*.block.json`.
3. **Template** `template-parts/blocks/<name>.php`, **styles** `src/scss/blocks/<name>.scss`,
   **script** `src/js/blocks/<name>.js` (optional; wrap it in `init(scope)` run on `DOMContentLoaded`
   and on ACF's `render_block_preview/type=<name>` so it also works in the editor — see `faq.js`).
4. **Vendor library?** Register it in `elysian_theme_register_vendor_assets()` and list its
   handle before the block's own file in `style` / `script` (see the testimonial example).
5. **ACF field group** located to `Block == acf/<name>` — in WP Admin or via
   [`_dev/tools/wp/run.sh`](_dev/docs/wp-scripting.md); it auto-exports to `acf-json/`.
6. `npm run build`, `npm run lint`, then `npm run shot -- .<name>-block` and compare with the design.

> **The metadata filename must end with the literal string `block.json`.** Core does
> `str_ends_with($path, 'block.json')`; `hero.json` would be read as a *directory* and
> fail silently. `file:` paths and `renderTemplate` resolve against `blocks-json/` —
> hence `../` — and filenames must be unique across blocks. Bump `"version"` when shipping
> style changes (it versions the block CSS).

---

## Adding a Flexible Content Section

1. **Create** the section template at `template-parts/sections/<section-name>.php`
2. **Add** section styles at `src/scss/sections/<section-name>.scss` — it compiles standalone to `dist/css/sections/<section-name>.css`, so do **not** `@use` it in `global.scss`
3. **Add** section scripts at `src/js/sections/<section-name>.js` (optional)
4. **Register** conditional asset loading in [inc/register-scripts-and-preload.php](inc/register-scripts-and-preload.php) using `get_row_layout()` check
5. **Add** the layout to the ACF flexible content field group

---

## Starting a New Project

Quickstart for a new website. Do steps 1–3 yourself; from step 4 on, Claude can do the work.

**1. Site and theme**
1. Create a new site in [Local](https://localwp.com), then install and activate **ACF PRO**.
2. Copy the starter into `wp-content/themes/<project-name>/`. **Choose the folder name now
   and keep it:** it is the theme slug, and it must match on the server, because WordPress
   stores menu locations and theme mods per folder.
3. Start a fresh history: `rm -rf .git && git init`, then add the new project's remote.

**2. Dev environment**
```bash
npm install
cp .env.example .env      # set PROXY_URL to the new .local domain
npm run build
```

**3. WordPress**
- *Appearance → Themes*: activate the theme.
- *ACF → Field Groups → Sync available*: import Theme Settings, Global blocks and the
  options pages. The previous project's block groups appear as well; in step 4 Claude
  removes them **from the database and from `acf-json/`**.
- *Settings → Reading*: choose the front page.

**4. Hand over to Claude.** Open Claude Code in the new theme folder and say *"we're
starting a new project"* (or *"krećemo s novim projektom"*), plus the Figma link. Claude
then runs the checklist in `CLAUDE.md`:
1. It lists the previous project's block files and deletes them **after you confirm**:
   `blocks-json/*.block.json`, `template-parts/blocks/*`, `src/scss/blocks/*`,
   `src/js/blocks/*`, block-only icons, and the block field groups (from the database and
   from `acf-json/`). The header, footer, options pages and `_dev/` stay.
2. It sets the new palette in `src/scss/abstracts/_variables.scss` and replaces
   **Project notes** in `CLAUDE.md` (Figma URL, decisions).
3. It restyles the header and footer from the new design.
4. It builds the blocks one at a time, each from the closest example in
   `_dev/examples/`, and checks each one with `npm run shot`.

**5. Content and deploy**
- Fill in Theme Settings (logo, header button, footer text) and the menus (header,
  `footer-menu-1..3`, `footer-social`). Claude can script this with `_dev/tools/wp/run.sh`.
- Deploy with `npm run package` (see [Deploying](#deploying)).

## Visual checks and WP scripting

| Command | Purpose |
|---|---|
| `npm run shot -- .faq-block [375 1440]` | Screenshot one element per width → `_dev/tools/visual/out/` |
| `npm run styles:snapshot` → `npm run styles:diff` | Prove a CSS refactor changed nothing (computed styles + boxes at every breakpoint edge) |
| `_dev/tools/wp/run.sh -e 'echo home_url();'` | Run PHP with WordPress loaded against the Local site |

Chrome is found automatically (`CHROME_PATH` overrides). See [_dev/docs/wp-scripting.md](_dev/docs/wp-scripting.md)
and [_dev/docs/figma-to-block.md](_dev/docs/figma-to-block.md).

---

## Deploying

**Never upload the working theme folder** — it holds `node_modules`, sources, `_dev/` and
configs. Instead:

```bash
npm run package
```

It runs the production build (`npm run prod`: minified, no sourcemaps, `drop_console`) and
then `_dev/tools/package.mjs`, which copies **only an allowlist** into
`_dev/release/<theme-folder>/` and zips it as `_dev/release/<theme-folder>-<version>.zip`:

| Shipped | Never shipped |
|---|---|
| root `*.php`, `style.css`, `screenshot.png` | `src/`, `node_modules/`, `_dev/` |
| `inc/`, `template-parts/`, `blocks-json/`, `acf-json/` | `package*.json`, `gulpfile.mjs`, lint configs |
| `assets/`, `languages/`, `dist/` (without `*.map`) | `.env*`, `.git*`, `readme.md`, `CLAUDE.md`, `*.map`, `.DS_Store` |

Before zipping it checks that the build exists, that every `block.json` asset and render
template is inside the package, and runs `php -l` on every PHP file — any failure stops
the package.

- **FTP/SFTP:** upload the *contents* of `_dev/release/<theme-folder>/` to
  `wp-content/themes/<theme-folder>/` on the server.
- **ZIP:** *Appearance → Themes → Add New → Upload Theme*, or unzip into `wp-content/themes/`.
- Keep the **same folder name** on the server — WordPress stores menu locations and theme
  mods per theme folder; a renamed folder loses them.
- Bump `Version:` in `style.css` for each release (it names the zip).
- Afterwards run `npm run build` to switch your local `dist/` back to the dev build.

A new top-level folder the theme needs **at runtime** must be added to `ALLOW_DIRS` in
`_dev/tools/package.mjs`; dev-only additions go under `_dev/` and need nothing.

---

## License

This theme is licensed under the [GPLv2 or later](https://www.gnu.org/licenses/gpl-2.0.html), compatible with WordPress theme distribution.
