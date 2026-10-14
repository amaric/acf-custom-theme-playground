<?php
// Preload critical fonts
function elysian_theme_preload_selected_fonts() {
    $font_url = get_stylesheet_directory_uri() . '/dist/fonts/';

    // List of font files you want to preload
    $fonts_to_preload = [
        'inter/Inter-VariableFont_opsz,wght.woff2'
    ];

    foreach ( $fonts_to_preload as $font ) {
        echo '<link rel="preload" href="' . esc_url( $font_url . $font ) . '" as="font" type="font/woff2" crossorigin>' . "\n";
    }
}
add_action( 'wp_head', 'elysian_theme_preload_selected_fonts', 1 );

// global scripts
function elysian_theme_enqueue_assets()
{
	elysian_theme_enqueue_style_asset('elysian-theme-global-style', 'dist/css/global.css');
	elysian_theme_enqueue_script_asset(
		'elysian-theme-custom-script',
		'dist/js/custom.js',
		[],
		[
			'strategy'  => 'defer',
			'in_footer' => true,
		]
	);
}
add_action('wp_enqueue_scripts', 'elysian_theme_enqueue_assets');

// vendor libraries — registered only; blocks pull them in via block.json handles
function elysian_theme_register_vendor_assets() {
	$swiper_uri = get_template_directory_uri() . '/assets/swiper/';

	wp_register_style('elysian-theme-swiper-style', $swiper_uri . 'swiper-bundle.min.css', [], '12.0.3');
	wp_register_script('elysian-theme-swiper', $swiper_uri . 'swiper-bundle.min.js', [], '12.0.3', ['in_footer' => true]);
}
add_action('init', 'elysian_theme_register_vendor_assets', 5);

// custom page templates scripts
function elysian_theme_enqueue_template_assets() {
  if (is_page_template('template-custom.php')) {
    elysian_theme_enqueue_style_asset('elysian-theme-template-custom-style', 'dist/css/page-templates/template-custom.css');
    elysian_theme_enqueue_script_asset(
      'elysian-theme-template-custom-script',
      'dist/js/page-templates/template-custom.js',
      [],
      [
        'strategy'  => false,
        'in_footer' => true,
      ]
    );
  }
}
add_action('wp_enqueue_scripts', 'elysian_theme_enqueue_template_assets');


//flexible fields assets
function elysian_theme_flexible_assets() {
    if (have_rows('sections')) {
        $loaded_components = array();

        while (have_rows('sections')) {
            the_row();
            $layout = get_row_layout();

            // Track which layouts are used
            if (!in_array($layout, $loaded_components)) {
                $loaded_components[] = $layout;
            }
        }

        // Conditionally load assets based on layouts
        foreach ($loaded_components as $component) {
            switch ($component) {
                case 'hero_section':
                  elysian_theme_enqueue_style_asset('elysian-theme-hero-section-style', 'dist/css/sections/hero-section.css');
                  elysian_theme_enqueue_script_asset(
                      'elysian-theme-hero-section-script',
                      'dist/js/sections/hero-section.js',
                      [],
                      [
                          'strategy'  => false,
                          'in_footer' => true,
                      ]
                  );
                break;
            }
        }
    }
}
add_action('wp_enqueue_scripts', 'elysian_theme_flexible_assets');



// admin editor style for blocks
function elysian_theme_enqueue_editor_styles()
{
	$editor_style = '/dist/css/editor.css';

	add_theme_support('editor-styles');
	add_editor_style($editor_style);
}
add_action('after_setup_theme', 'elysian_theme_enqueue_editor_styles');

// Root font-size for the editor canvas so rem matches the frontend (10px).
// add_editor_style() rewrites html/body to .editor-styles-wrapper, so this
// can't live in editor.css; enqueue_block_assets reaches the iframe untouched.
function elysian_theme_editor_root_font_size()
{
	if (!is_admin()) {
		return;
	}

	wp_register_style('elysian-theme-editor-root', false);
	wp_enqueue_style('elysian-theme-editor-root');
	wp_add_inline_style('elysian-theme-editor-root', 'html{font-size:62.5%}');
}
add_action('enqueue_block_assets', 'elysian_theme_editor_root_font_size');


// Rewrite relative url() paths against $base_url. Inlined CSS resolves relative
// URLs against the page URL, not the stylesheet's original location.
function elysian_theme_absolutize_css_urls($css, $base_url) {
    $base_url = trailingslashit($base_url);

    return preg_replace_callback(
        '/url\(\s*([\'"]?)(.*?)\1\s*\)/i',
        function ($matches) use ($base_url) {
            $url = trim($matches[2]);

            if ($url === '' || preg_match('#^(data:|[a-z][a-z0-9+.-]*:|//|/|\#)#i', $url)) {
                return $matches[0];
            }

            return 'url("' . $base_url . $url . '")';
        },
        $css
    );
}

// Add inline critical css
function elysian_theme_inline_critical_css() {
    $css_file = get_template_directory() . '/dist/css/critical.css';

    if (!file_exists($css_file)) {
        return;
    }

    $css = file_get_contents($css_file);

    // The source map comment would be resolved against the page URL once inlined.
    $css = preg_replace('#/\*\#\s*sourceMappingURL=.*?\*/#s', '', $css);
    $css = elysian_theme_absolutize_css_urls($css, get_template_directory_uri() . '/dist/css/');

    echo '<style id="critical-css">';
    echo $css;
    echo '</style>';
}
add_action('wp_head', 'elysian_theme_inline_critical_css', 1);

// Defer css
function elysian_theme_wp_defer_multiple_styles($html, $handle, $href, $media) {
    if (is_admin()) {
        return $html;
    }

    $defer_styles = ['elysian-theme-global-style'];

    if (!in_array($handle, $defer_styles, true)) {
        return $html;
    }

    $html  = '<link rel="stylesheet" id="' . esc_attr($handle) . '-css"'
           . ' href="' . esc_url($href) . '"'
           . ' media="print" onload="this.media=\'' . esc_attr($media) . '\';this.onload=null;">';
    $html .= '<noscript><link rel="stylesheet" href="' . esc_url($href) . '"'
           . ' media="' . esc_attr($media) . '"></noscript>';

    return $html;
}
add_filter('style_loader_tag', 'elysian_theme_wp_defer_multiple_styles', 10, 4);
