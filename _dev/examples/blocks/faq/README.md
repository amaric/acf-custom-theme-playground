# FAQ (`acf/faq`)

A header (tag, h2, intro text) above an accordion of question/answer pairs. The first item is open.

## Fields (`group_eb63d3744bfac`)
| Field | Type | Notes |
|---|---|---|
| `faq_tag` | text | pill |
| `faq_title` | text | falls back to "Frequently asked questions" |
| `faq_text` | textarea | |
| `faq_items` | repeater | `question` (text), `answer` (wysiwyg → `wp_kses_post`) |

## Patterns worth copying
- **Accessible accordion:**
  - the question is a `<button aria-expanded aria-controls>` inside an `<h3>`
  - the panel `id` is built from `$block['id']` + the index, so it stays unique when the block is used twice on a page
- **Animation without JS heights:** the panel is `display: grid; grid-template-rows: 0fr`, and `.is-open` sets it to `1fr`. The inner wrapper has `overflow: hidden; min-height: 0`.
- **JS (`faq.js`):** ~10 lines. It only toggles `.is-open` and `aria-expanded`, and all styling stays in SCSS.
- **Icons:** `plus.svg` and `minus.svg` live in `src/img/icons/`. They are referenced as `dist/img/icons/*.svg` (built by Gulp) and swapped with CSS.
- **Header styles** (tag pill, h2, intro) are shared verbatim with `testimonial`. If a third block needs them, consider a `components/_section-header.scss` partial.
