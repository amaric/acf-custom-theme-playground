<?php
/**
 * CTA block template.
 *
 * @param array  $block      The block settings and attributes.
 * @param string $content    The block inner HTML (empty).
 * @param bool   $is_preview True during backend preview render.
 */

$anchor  = !empty($block['anchor']) ? ' id="' . esc_attr($block['anchor']) . '"' : '';
$classes = 'block cta-block';
if (!empty($block['className'])) {
    $classes .= ' ' . $block['className'];
}

$is_preview       = $is_preview ?? false;
$background_image = (int) get_field('background_image');
$cta_title        = get_field('title') ?: ($is_preview ? __('CTA block title', 'elysian-theme') : '');
$cta_text         = get_field('text') ?: ($is_preview ? __('CTA block text', 'elysian-theme') : '');
$cta_button       = get_field('button');
?>

<div<?php echo $anchor; ?> class="<?php echo esc_attr($classes); ?>">
    <?php elysian_theme_render_image($background_image, $block['id'] ?? '', 'full', 'cta-block-bg'); ?>

    <div class="container cta-block-container">
        <?php if ($cta_title) : ?>
            <h2 class="cta-block-title title-md"><?php echo esc_html($cta_title); ?></h2>
        <?php endif; ?>
        <?php if ($cta_text) : ?>
            <p class="cta-block-text"><?php echo wp_kses_post($cta_text); ?></p>
        <?php endif; ?>
        <?php if (!empty($cta_button['url'])) : ?>
            <?php elysian_theme_render_link($cta_button, 'cta-block-btn'); ?>
        <?php endif; ?>
    </div>
</div>
