<?php
/**
 * FAQ block template.
 *
 * Accordion of question/answer pairs; toggled by src/js/blocks/faq.js.
 *
 * @param array  $block      The block settings and attributes.
 * @param string $content    The block inner HTML (empty).
 * @param bool   $is_preview True during backend preview render.
 */

$anchor  = !empty($block['anchor']) ? ' id="' . esc_attr($block['anchor']) . '"' : '';
$classes = 'block faq-block';
if (!empty($block['className'])) {
    $classes .= ' ' . $block['className'];
}

$faq_tag   = get_field('faq_tag');
$faq_title = get_field('faq_title') ?: __('Frequently asked questions', 'elysian-theme');
$faq_text  = get_field('faq_text');
$faq_items = get_field('faq_items') ?: [];

$block_id   = $block['id'] ?? uniqid('faq-');
$plus_icon  = get_theme_file_uri('/dist/img/icons/plus.svg');
$minus_icon = get_theme_file_uri('/dist/img/icons/minus.svg');
?>

<div<?php echo $anchor; ?> class="<?php echo esc_attr($classes); ?>">
    <div class="container faq-block-container">
        <div class="faq-block-header">
            <?php if ($faq_tag) : ?>
                <span class="faq-block-tag"><?php echo esc_html($faq_tag); ?></span>
            <?php endif; ?>
            <h2 class="faq-block-title"><?php echo esc_html($faq_title); ?></h2>
            <?php if ($faq_text) : ?>
                <p class="faq-block-text"><?php echo esc_html($faq_text); ?></p>
            <?php endif; ?>
        </div>

        <?php if (!empty($faq_items)) : ?>
            <div class="faq-block-items">
                <?php foreach ($faq_items as $index => $faq_item) : ?>
                    <?php
                    if (empty($faq_item['question'])) {
                        continue;
                    }
                    $is_open  = 0 === $index;
                    $panel_id = $block_id . '-answer-' . $index;
                    ?>
                    <div class="faq-block-item<?php echo $is_open ? ' is-open' : ''; ?>">
                        <h3 class="faq-block-item-heading">
                            <button type="button" class="faq-block-item-question" aria-expanded="<?php echo $is_open ? 'true' : 'false'; ?>" aria-controls="<?php echo esc_attr($panel_id); ?>">
                                <span><?php echo esc_html($faq_item['question']); ?></span>
                                <img class="faq-block-item-icon faq-block-item-icon-plus" src="<?php echo esc_url($plus_icon); ?>" alt="" width="20" height="20">
                                <img class="faq-block-item-icon faq-block-item-icon-minus" src="<?php echo esc_url($minus_icon); ?>" alt="" width="20" height="20">
                            </button>
                        </h3>
                        <div class="faq-block-item-answer" id="<?php echo esc_attr($panel_id); ?>">
                            <div class="faq-block-item-answer-inner">
                                <div class="faq-block-item-answer-content">
                                    <?php echo wp_kses_post($faq_item['answer'] ?? ''); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
