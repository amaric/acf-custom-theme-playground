<?php
// Custom actions and filters

// Remove emoji detection
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');

// Remove RSD and WLW manifest links
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wlwmanifest_link');

// Remove oEmbed discovery
remove_action('wp_head', 'wp_oembed_add_discovery_links');
remove_action('wp_head', 'wp_oembed_add_host_js');

// Remove classic theme inline styles
add_action('wp_enqueue_scripts', function () {
  wp_dequeue_style('classic-theme-styles');
  wp_deregister_style('classic-theme-styles');
}, 20);

// Remove block library CSS and global styles (using ACF blocks only)
function elysian_theme_remove_block_css(){
  wp_dequeue_style('wp-block-library');
  wp_dequeue_style('wp-block-library-theme');
  wp_dequeue_style('wc-blocks-style');
  wp_dequeue_style('global-styles');
  wp_dequeue_style('core-block-supports');
}
add_action('wp_enqueue_scripts', 'elysian_theme_remove_block_css', 100);
