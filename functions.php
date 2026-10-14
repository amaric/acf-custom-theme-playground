<?php
require_once get_template_directory() . '/inc/setup.php';
require_once get_template_directory() . '/inc/woocommerce.php';

require_once get_template_directory() . '/inc/post-types.php';
require_once get_template_directory() . '/inc/taxonomies.php';
require_once get_template_directory() . '/inc/menus.php';

require_once get_template_directory() . '/inc/acf.php';

require_once get_template_directory() . '/inc/register-scripts-and-preload.php';
require_once get_template_directory() . '/inc/optimisation.php';

require_once get_template_directory() . '/inc/template-functions.php';
require_once get_template_directory() . '/inc/custom-hooks.php';
require_once get_template_directory() . '/inc/shortcodes.php';
require_once get_template_directory() . '/inc/widgets.php';
require_once get_template_directory() . '/inc/extras.php';
require_once get_template_directory() . '/inc/security.php';


function elysian_theme_asset_path($relative_path) {
    return get_template_directory() . '/' . ltrim($relative_path, '/');
}

function elysian_theme_asset_url($relative_path) {
    return get_template_directory_uri() . '/' . ltrim($relative_path, '/');
}

function elysian_theme_asset_version($relative_path) {
    $asset_path = elysian_theme_asset_path($relative_path);

    if (!file_exists($asset_path)) {
        return null;
    }

    return filemtime($asset_path);
}

function elysian_theme_enqueue_style_asset($handle, $relative_path, $deps = [], $media = 'all') {
    $asset_path = elysian_theme_asset_path($relative_path);

    if (!file_exists($asset_path)) {
        return false;
    }

    wp_enqueue_style(
        $handle,
        elysian_theme_asset_url($relative_path),
        $deps,
        filemtime($asset_path),
        $media
    );

    return true;
}

function elysian_theme_enqueue_script_asset($handle, $relative_path, $deps = [], $args = []) {
    $asset_path = elysian_theme_asset_path($relative_path);

    if (!file_exists($asset_path)) {
        return false;
    }

    wp_enqueue_script(
        $handle,
        elysian_theme_asset_url($relative_path),
        $deps,
        filemtime($asset_path),
        $args
    );

    return true;
}

function elysian_theme_render_image($image_id, $block_id = '', $size = 'full', $class = '', $priority = 'auto') {

    if (!$image_id) return;

    static $block_index = [];
    static $current_block = null;
    static $image_in_block = 0;

    if ($block_id !== $current_block) {
        $current_block = $block_id;
        $image_in_block = 0;

        if (!isset($block_index[$block_id])) {
            $block_index[$block_id] = count($block_index) + 1;
        }
    }

    $image_in_block++;

    $current_block_number = $block_index[$block_id];

    $attrs = [];

    if ($priority === 'auto') {

        if ($current_block_number === 1) {
            $attrs['loading'] = 'eager';
            $attrs['fetchpriority'] = 'high';
        } elseif ($current_block_number === 2) {
            $attrs['loading'] = 'eager';
            $attrs['fetchpriority'] = 'low';
        } else {
            $attrs['loading'] = 'lazy';
        }

    } else {
        $attrs['fetchpriority'] = $priority;
        $attrs['loading'] = ($priority === 'high') ? 'eager' : 'lazy';
    }

    if ($class) {
        $attrs['class'] = $class;
    }

    echo wp_get_attachment_image($image_id, $size, false, $attrs);
}

function elysian_theme_render_link( $link, $class = '' ) {
    if( !$link || !is_array( $link ) ) {
        return;
    }

    $link_url = $link['url'];
    $link_title = $link['title'];
    $link_target = $link['target'] ? $link['target'] : '_self';
    ?>
    <a class="btn <?php echo esc_attr( $class ); ?>"
       href="<?php echo esc_url( $link_url ); ?>"
       target="<?php echo esc_attr( $link_target ); ?>">
        <?php echo esc_html( $link_title ); ?>
    </a>
    <?php
}
