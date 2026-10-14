# Figma → block: the working playbook

How a section of a Figma file becomes a finished ACF block in this theme. Follow it
top to bottom; each step names the tool or file it uses.

## 1. Read the design
- **Load the guidance:** load the `figma-design-to-code` skill, then call `get_design_context`
  (fileKey + nodeId from the URL, `node-id=17-4` → `17:4`).
- **Treat the output as reference:** the React/Tailwind code it returns is only a reference for values. Never paste it.
- **Keep the screenshot:** the screenshot it returns is the visual target.
- **Variables:** try `get_variable_defs` on the node. If Figma defines Variables, use their
  names for the tokens. If it returns `{}`, the file has none; map the values to the
  existing tokens yourself.
- **Assets:**
  - Download only the real assets (icons, logos) with `curl -L` into `src/img/icons/`. Asset URLs expire after 7 days.
  - **Pagination dots, sliders and decorative UI drawn as images are implemented in
    code**, never shipped as an image.
  - If an asset already exists in the theme (e.g. the logo is the `logo_white` option), reuse it.

## 2. Pick the closest example
Open [_dev/examples/README.md](../examples/README.md) and start from the example whose
patterns match: repeater, accordion, slider, background image. Copy its `files/` under
the new name and rename (see that README).

## 3. Translate the values
| Figma | Theme |
|---|---|
| `#0d0d0d` / `#141414` / `#a3a3a3` / `#fff` / white 10% | `$color-bg-dark` / `$color-surface` / `$color-text-muted` / `$color-white` / `$color-border` |
| `#62ac99` and its 10% tint | `$color-primary`, `rgba($color-primary, 0.1)` |
| `text-[14px]` | `font-size: 1.4em` (root is 62.5% = 10px) |
| `px-[100px]` frame padding | `.container` (1392px max, 32px gutter); don't copy the frame padding |
| absolute positions | flex/grid; never absolute positioning unless the design truly overlaps |

New repeated colors go into `abstracts/_variables.scss` as a token first.

## 4. Write the files desktop-first
- **SCSS:**
  - the base rule is the Figma desktop value
  - the mobile override goes inside `@include mixins.breakpoint-down('lg')` (1024px), or `'md'` for a third step
  - if a property exists only on desktop (e.g. `justify-content: space-between`), reset it in the down block (`normal`)
- **Mobile values:** Figma has desktop frames only, so derive mobile by stacking and scaling down. Typical values: section padding 140 → 80, h2 48 → 36px, card padding 40 → 24, quote 28 → 20px.
- **Template:**
  - the `$anchor` / `$classes` preamble
  - prefixed field names (`<block>_title`)
  - every output escaped
  - every string through `__()` / `esc_html_e()` with `elysian-theme`
- **JS:** vanilla. Scope the lookups to each block instance, and add guards. Load it with `"script"` (front end + editor), not `"viewScript"`, and follow the `init(scope)` pattern in `src/js/blocks/faq.js` so it also runs in the editor.

## 5. Create the ACF field group by script
This is the same as saving in WP Admin: ACF writes `acf-json/group_*.json` itself. Use the
recipe in [wp-scripting.md](wp-scripting.md#create-an-acf-field-group) and give the group
and every field a fresh unique key.

## 6. Put the block on a page with the Figma copy
Insert it into the front page content after the closest related block ([wp-scripting.md](wp-scripting.md#insert-a-block-into-a-page)).
Don't touch the existing blocks. Then save a copy of the serialized block as
`_dev/examples/blocks/<name>/demo-content.html` if the block becomes an example.

## 7. Verify
```bash
npm run build && npm run lint          # lint must exit 0
npm run shot -- .<name>-block          # 1440 + 375 → _dev/tools/visual/out/
```
- Compare the 1440 PNG against the Figma screenshot (spacing, sizes, colours, assets), and check 375 for overflow and stacking.
- Check interaction through the browser, not by reading code. Open `_dev/tools/visual/cdp.mjs` and evaluate clicks if needed: next/prev, the accordion toggle, the mobile menu.
- `curl -sk $PROXY_URL | grep -iE 'fatal|warning:|notice:'` must print nothing.
- For a pure CSS refactor, run `npm run styles:snapshot` **before** and `npm run styles:diff` after; the diff must show 0 differences.

## Pitfalls already hit
- **Base `p` override:** `base/_base.scss` styles bare `p` (1.6em, `#666`). A classed `<p>` must set its own `font-size` and `color`, or `inherit`. This made the footer copyright too big.
- **`.btn` beats layout:** the order is objects → components → layout regions, so `.site-header-btn { display: none }` wins over `.btn` at equal specificity. Keep new partials in that order.
- **Element-only screenshots:** the sticky header covers screenshots of elements. `npm run shot` handles that already; a hand-rolled script must too.
- **Mobile emulation:** `--window-size=375` in headless Chrome is *not* a mobile viewport. Use CDP `Emulation.setDeviceMetricsOverride`, as `_dev/tools/visual/cdp.mjs` does.
- **Block CSS version:** block CSS is versioned from `"version"` in block.json, so bump it when shipping style changes.
- **Block JS dead in the editor:** two causes, both needed fixing. `viewScript` loads on the front end only (use `script`). And ACF injects and re-renders the preview after load, so binding on `DOMContentLoaded` alone finds nothing. Hook `window.acf.addAction('render_block_preview/type=<name>', …)` **inside** the `DOMContentLoaded` listener: block scripts print in `<head>` before ACF's JS, so a top-level `if (window.acf)` is always false.
