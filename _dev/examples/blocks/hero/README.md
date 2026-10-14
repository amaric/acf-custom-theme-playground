# Hero (`acf/hero`)

A full-width opening section with a background image under a dark overlay, a tag pill, an `<h1>`, intro text and up to two buttons.

## Fields (`group_68a8ec5074512`)
| Field | Type | Notes |
|---|---|---|
| `hero_background` | image (array) | rendered with `elysian_theme_render_image(…, 'full-width-image', …, 'high')`: eager + `fetchpriority=high`, because it is the LCP |
| `hero_tag` | text | pill above the title |
| `hero_title` | text | falls back to `get_the_title()` |
| `hero_text` | textarea | |
| `hero_button_primary` | link | `btn` |
| `hero_button_secondary` | link | `btn btn-outline` |

## Patterns worth copying
- **Image fields:** the template accepts both an array and an ID (`is_array(...) ? ['ID'] : (int)`).
- **Overlay:** the image sits at `z-index: -1` and the overlay is the block's `::before`. `isolation: isolate` keeps both inside the block.
- **Responsive title:** three sizes. The base is desktop (7.2em), with `breakpoint-down('lg')` 5.6em and `breakpoint-down('md')` 4.4em.
- **`hero.js` is an empty stub:** keep or delete it, but drop `script` from the JSON if you delete it.
