<?php
// Register sidebars and widget areas

function elysian_theme_widgets_init() {
    register_sidebar([
        'name'          => esc_html__('Sidebar', 'elysian-theme'),
        'id'            => 'sidebar-1',
        'description'   => esc_html__('Add widgets here to appear in the sidebar.', 'elysian-theme'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ]);

    register_sidebar([
        'name'          => esc_html__('Footer', 'elysian-theme'),
        'id'            => 'footer-1',
        'description'   => esc_html__('Add widgets here to appear in the footer.', 'elysian-theme'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ]);
}
add_action('widgets_init', 'elysian_theme_widgets_init');