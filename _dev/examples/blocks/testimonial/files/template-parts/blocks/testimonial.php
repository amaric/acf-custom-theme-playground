<?php
/**
 * Testimonial block template.
 *
 * Client stories header plus a Swiper carousel of testimonial cards;
 * initialised by src/js/blocks/testimonial.js.
 *
 * @param array  $block      The block settings and attributes.
 * @param string $content    The block inner HTML (empty).
 * @param bool   $is_preview True during backend preview render.
 */

$anchor  = !empty($block['anchor']) ? ' id="' . esc_attr($block['anchor']) . '"' : '';
$classes = 'block testimonial-block';
if (!empty($block['className'])) {
    $classes .= ' ' . $block['className'];
}

$testimonial_tag   = get_field('testimonial_tag');
$testimonial_title = get_field('testimonial_title') ?: __('What our clients say', 'elysian-theme');
$testimonial_text  = get_field('testimonial_text');
$testimonial_items = array_filter(get_field('testimonial_items') ?: [], function ($item) {
    return !empty($item['quote']);
});

$block_id      = $block['id'] ?? '';
$chevron_left  = get_theme_file_uri('/dist/img/icons/chevron-left.svg');
$chevron_right = get_theme_file_uri('/dist/img/icons/chevron-right.svg');
?>

<div<?php echo $anchor; ?> class="<?php echo esc_attr($classes); ?>">
    <div class="container testimonial-block-container">
        <div class="testimonial-block-header">
            <?php if ($testimonial_tag) : ?>
                <span class="testimonial-block-tag"><?php echo esc_html($testimonial_tag); ?></span>
            <?php endif; ?>
            <h2 class="testimonial-block-title"><?php echo esc_html($testimonial_title); ?></h2>
            <?php if ($testimonial_text) : ?>
                <p class="testimonial-block-text"><?php echo esc_html($testimonial_text); ?></p>
            <?php endif; ?>
        </div>

        <?php if (!empty($testimonial_items)) : ?>
            <div class="testimonial-block-slider">
                <div class="swiper testimonial-block-swiper">
                    <div class="swiper-wrapper">
                        <?php foreach ($testimonial_items as $testimonial_item) : ?>
                            <?php
                            $author_name = $testimonial_item['author_name'] ?? '';
                            $initials    = '';
                            foreach (array_slice(preg_split('/\s+/u', trim($author_name), -1, PREG_SPLIT_NO_EMPTY), 0, 2) as $word) {
                                $initials .= mb_strtoupper(mb_substr($word, 0, 1));
                            }
                            ?>
                            <figure class="swiper-slide testimonial-block-card">
                                <div class="testimonial-block-card-top">
                                    <span class="testimonial-block-card-mark" aria-hidden="true">&ldquo;</span>
                                    <?php if (!empty($testimonial_item['badge'])) : ?>
                                        <span class="testimonial-block-tag"><?php echo esc_html($testimonial_item['badge']); ?></span>
                                    <?php endif; ?>
                                </div>

                                <blockquote class="testimonial-block-card-quote">
                                    <p><?php echo esc_html($testimonial_item['quote']); ?></p>
                                </blockquote>

                                <?php if ($author_name) : ?>
                                    <figcaption class="testimonial-block-author">
                                        <span class="testimonial-block-author-avatar" aria-hidden="true">
                                            <?php if (!empty($testimonial_item['author_image'])) : ?>
                                                <?php elysian_theme_render_image($testimonial_item['author_image'], $block_id, 'image-160-160', 'testimonial-block-author-image', 'low'); ?>
                                            <?php else : ?>
                                                <?php echo esc_html($initials); ?>
                                            <?php endif; ?>
                                        </span>
                                        <span class="testimonial-block-author-meta">
                                            <span class="testimonial-block-author-name"><?php echo esc_html($author_name); ?></span>
                                            <?php if (!empty($testimonial_item['author_role'])) : ?>
                                                <span class="testimonial-block-author-role"><?php echo esc_html($testimonial_item['author_role']); ?></span>
                                            <?php endif; ?>
                                        </span>
                                    </figcaption>
                                <?php endif; ?>
                            </figure>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php if (count($testimonial_items) > 1) : ?>
                    <div class="testimonial-block-controls">
                        <div class="testimonial-block-pagination"></div>
                        <div class="testimonial-block-navigation">
                            <button type="button" class="testimonial-block-nav testimonial-block-prev" aria-label="<?php esc_attr_e('Previous testimonial', 'elysian-theme'); ?>">
                                <img src="<?php echo esc_url($chevron_left); ?>" alt="" width="18" height="18">
                            </button>
                            <button type="button" class="testimonial-block-nav testimonial-block-next" aria-label="<?php esc_attr_e('Next testimonial', 'elysian-theme'); ?>">
                                <img src="<?php echo esc_url($chevron_right); ?>" alt="" width="18" height="18">
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
