# Testimonial (`acf/testimonial`)

"Client stories": a header (tag, h2, intro) and a **Swiper** carousel of quote cards, with bullet pagination and prev/next buttons. The original design is in the Elysian-web Figma file, node `17:4`.

## Fields (`group_3d9959fb642bc`)
| Field | Type | Notes |
|---|---|---|
| `testimonial_tag` | text | pill |
| `testimonial_title` | text | falls back to "What our clients say" |
| `testimonial_text` | textarea | |
| `testimonial_items` | repeater | `quote` (textarea), `author_name`, `author_role`, `author_image` (image ID, optional), `badge` (e.g. "Featured") |

## Patterns worth copying
- **Vendor library only where it's used:**
  - Swiper is *registered* (not enqueued) in `elysian_theme_register_vendor_assets()` in `inc/register-scripts-and-preload.php`
  - `blocks-json/testimonial.block.json` lists the handles before its own files: `"style": ["elysian-theme-swiper-style", "file:…"]` and `"script": ["elysian-theme-swiper", "file:…"]`
  - Swiper therefore loads before `testimonial.js` and only on pages with the block

  For another library, add a `wp_register_*` pair in that function and reference the handle the same way.
- **JS (`testimonial.js`):**
  - looks up the controls *inside each block*, so several sliders on one page don't clash
  - guards against `typeof Swiper === 'undefined'`
- **Controls:**
  - real `<button>`s with `aria-label`
  - Swiper adds `swiper-button-disabled`, which is styled
  - the bullets are restyled from plain CSS inside `.testimonial-block-pagination`
  - the controls aren't rendered when there is a single slide
- **Equal-height cards:** `.swiper-slide { height: auto; }` plus the author row `margin-top: auto`.
- **Avatar fallback:** with no image, the avatar shows the initials of `author_name`, built with `mb_` functions so non-ASCII names work.
- **Figma pagination is drawn as an image:** it is implemented as Swiper bullets, not as an asset.
