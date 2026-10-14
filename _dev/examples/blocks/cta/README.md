# CTA (`acf/cta`)

A centred call-to-action on a background image, with a title, text and one button.

## Fields (`group_6ac60fa4b7416`)
| Field | Type | Notes |
|---|---|---|
| `background_image` | image (ID) | `elysian_theme_render_image(…, 'full', 'cta-block-bg')`, auto loading strategy |
| `title` | text | |
| `text` | textarea | output through `wp_kses_post()`, so basic inline HTML is allowed |
| `button` | link | `btn` |

## Patterns worth copying
- **Editor placeholders:** `$is_preview ? __('CTA block title', …) : ''` shows placeholder copy only in the editor, so an empty block is visible there but renders nothing on the front end.
- **Overlay:** a translucent `rgba($color-bg-dark, 0.85)` layer over the image.

## Don't copy
- **Field names without a prefix** (`title`, `text`, `button`). This is the older style. Newer blocks prefix every field with the block name (`faq_title`, `testimonial_items`) to avoid collisions; do that.
- **`cta.js` is an empty stub.**
