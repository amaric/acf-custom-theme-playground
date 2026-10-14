# Example blocks

Reference copies of blocks built with this starter. **Nothing in `_dev/examples/` is loaded:**
WordPress only registers `blocks-json/*.block.json`, ACF only syncs `acf-json/`, and Gulp
and lint only read `src/` (and `_dev/tools/`). These copies stay put when a new project wipes the
live blocks.

Each example has the same layout:

```
_dev/examples/blocks/<name>/
  README.md           what it does, its fields, the patterns worth copying
  demo-content.html   the block as saved in post content (Figma copy)
  files/              mirrors the theme root: blocks-json/, template-parts/, src/, acf-json/
```

To restore one, copy its `files/` over the theme root. Copy only `files/`: copying the
example folder itself would put its `README.md` over the theme's `readme.md`, because
macOS treats them as the same name.

```bash
cp -R _dev/examples/blocks/faq/files/ .
npm run build
```

Then open **ACF → Field Groups → Sync available** to import the field group. To get the
Figma demo content back, paste `demo-content.html` into the Code editor. Image IDs in it
point to the original site's media, so pick the images again.

To start a **new** block from an example, copy its files under the new name and rename
`<old>` → `<new>` everywhere: the file names, `"name": "acf/<new>"`, the `<new>-block*`
classes, the field names, and the ACF group `key` / `title` / location. Give the group and
every field a **fresh key**: ACF keys must be unique.

## Which example shows what

| Example | Reach for it when you need… |
|---|---|
| [hero](blocks/hero/) | an LCP background image (`elysian_theme_render_image(…, 'high')`), an `<h1>` that falls back to the page title, a tag pill, two ACF link buttons (`btn` + `btn-outline`), a dark overlay through `::before` |
| [cta](blocks/cta/) | a centred section on a background image, editor-only placeholder text via `$is_preview`, a single button |
| [faq](blocks/faq/) | a header (tag, h2, intro), a **repeater**, an accessible accordion (`<button aria-expanded aria-controls>`), small vanilla JS, a CSS-only height animation (`grid-template-rows: 0fr → 1fr`), SVG icons from `dist/img/icons/` |
| [testimonial](blocks/testimonial/) | a **vendor library loaded only with the block** (Swiper through `block.json` handles), a slider with bullets and prev/next `<button>`s, initials avatar fallback, controls hidden for a single item, semantic `<figure>/<blockquote>/<figcaption>` |

Every example follows the starter's conventions:
- desktop-first `breakpoint-down('lg')`
- color tokens
- `em` sizes on the 10px root
- escaped output
- the `$anchor` / `$classes` preamble

For the full Figma → block workflow, see [_dev/docs/figma-to-block.md](../docs/figma-to-block.md).
