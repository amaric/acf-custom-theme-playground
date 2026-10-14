<?php
function elysian_theme_register_menus() {
    register_nav_menus([
        'header-menu'        => __('Header Menu', 'elysian-theme'),
        'header-menu-mobile' => __('Header Menu Mobile', 'elysian-theme'),
        'footer-menu-1'      => __('Footer Menu 1', 'elysian-theme'),
        'footer-menu-2'      => __('Footer Menu 2', 'elysian-theme'),
        'footer-menu-3'      => __('Footer Menu 3', 'elysian-theme'),
        'footer-social'      => __('Footer Social', 'elysian-theme'),
    ]);
}
add_action('init', 'elysian_theme_register_menus');
