<?php
function elysian_theme_allow_svg_uploads($mimes)
{
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
}
add_filter('upload_mimes', 'elysian_theme_allow_svg_uploads');

function elysian_theme_render_footer_menu( $location ) {

    $locations = get_nav_menu_locations();

    if ( empty( $locations[$location] ) ) {
        return;
    }

    $menu_obj = wp_get_nav_menu_object( $locations[$location] );

    if ( ! $menu_obj ) {
        return;
    }

    echo '<div class="site-footer-column">';
    echo '<p class="site-footer-column-title">' . esc_html( $menu_obj->name ) . '</p>';

    wp_nav_menu([
        'theme_location' => $location,
        'container'      => '',
        'menu_class'     => 'site-footer-menu',
        'fallback_cb'    => false,
    ]);
    echo '</div>';
}

function elysian_theme_show_only_acf_blocks( $allowed_blocks, $editor_context ) {
    // Get all registered blocks
    $registered_blocks = WP_Block_Type_Registry::get_instance()->get_all_registered();
    $allowed_blocks = array();
    
    // Check if we're editing a 'faq' post type
    $is_faq = false;
    if ( isset( $editor_context->post ) && isset( $editor_context->post->post_type ) ) {
        $is_faq = $editor_context->post->post_type === 'faq';
    }
    
    if ( $is_faq == false ) {
        // Loop through all blocks and only allow ACF blocks
        foreach ( $registered_blocks as $name => $block ) {
            // ACF blocks start with 'acf/'
            if ( strpos( $name, 'acf/' ) === 0 ) {
                $allowed_blocks[] = $name;
            }
        }
    }
    
    // Allow paragraph block only for FAQ post type
    if ( $is_faq ) {
        $allowed_blocks[] = 'core/paragraph';
        $allowed_blocks[] = 'core/list';        // Both ul and ol
        $allowed_blocks[] = 'core/list-item';   // Individual list items
    }
    
    return $allowed_blocks;
}
add_filter( 'allowed_block_types_all', 'elysian_theme_show_only_acf_blocks', 10, 2 );