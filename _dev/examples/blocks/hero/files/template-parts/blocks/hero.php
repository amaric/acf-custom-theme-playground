<?php
/**
 * Hero block template.
 *
 * @param array  $block      The block settings and attributes.
 * @param string $content    The block inner HTML (empty).
 * @param bool   $is_preview True during backend preview render.
 */

$anchor  = !empty($block['anchor']) ? ' id="' . esc_attr($block['anchor']) . '"' : '';
$classes = 'block hero-block';
if (!empty($block['className'])) {
    $classes .= ' ' . $block['className'];
}

$hero_tag              = get_field('hero_tag');
$hero_title            = get_field('hero_title') ?: get_the_title();
$hero_text             = get_field('hero_text');
$hero_background       = get_field('hero_background');
$hero_button_primary   = get_field('hero_button_primary');
$hero_button_secondary = get_field('hero_button_secondary');

$image_id = is_array($hero_background) ? ($hero_background['ID'] ?? 0) : (int) $hero_background;
?>

<div<?php echo $anchor; ?> class="<?php echo esc_attr($classes); ?>">
    <?php elysian_theme_render_image($image_id, $block['id'] ?? '', 'full-width-image', 'hero-block-bg', 'high'); ?>

    <div class="container hero-block-container">
        <div class="hero-block-content">
            <?php if ($hero_tag) : ?>
                <span class="hero-block-tag"><?php echo esc_html($hero_tag); ?></span>
            <?php endif; ?>
            <?php if ($hero_title) : ?>
                <h1 class="hero-block-title"><?php echo esc_html($hero_title); ?></h1>
            <?php endif; ?>
            <?php if ($hero_text) : ?>
                <p class="hero-block-text"><?php echo esc_html($hero_text); ?></p>
            <?php endif; ?>
        </div>

        <?php if (!empty($hero_button_primary['url']) || !empty($hero_button_secondary['url'])) : ?>
            <div class="hero-block-buttons">
                <?php if (!empty($hero_button_primary['url'])) : ?>
                    <?php elysian_theme_render_link($hero_button_primary, 'hero-block-btn'); ?>
                <?php endif; ?>
                <?php if (!empty($hero_button_secondary['url'])) : ?>
                    <?php elysian_theme_render_link($hero_button_secondary, 'btn-outline hero-block-btn'); ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
