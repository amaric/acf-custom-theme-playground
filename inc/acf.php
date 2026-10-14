<?php
// ACF check if plugin is activated
function elysian_theme_require_acf() {
	if ( class_exists( 'ACF' ) ) {
		return;
	}

	// wp-admin: informative notice, dashboard stays usable.
	add_action( 'admin_notices', function () {
		echo '<div class="notice notice-error"><p>'
			. esc_html__( 'Elysian theme requires the Advanced Custom Fields PRO plugin to be installed and active.', 'elysian-theme' )
			. '</p></div>';
	} );

	// Front-end: stop rendering and show a message.
	add_action( 'template_redirect', function () {
		wp_die(
			esc_html__( 'This site requires the Advanced Custom Fields PRO plugin to be active.', 'elysian-theme' ),
			esc_html__( 'Plugin required', 'elysian-theme' ),
			[ 'response' => 503 ]
		);
	} );
}
add_action( 'after_setup_theme', 'elysian_theme_require_acf' );

//ACF filters, JSON save/load paths, field helpers
function elysian_theme_acf_json_save_point($path)
{
	return get_stylesheet_directory() . '/acf-json';
}
add_filter('acf/settings/save_json', 'elysian_theme_acf_json_save_point');


// Add custom block category
add_filter('block_categories_all', 'elysian_theme_add_block_category', 10, 2);
function elysian_theme_add_block_category($categories, $post)
{
	return array_merge(
		$categories,
		[
			[
				'slug'  => 'elysian_theme_blocks',
				'title' => __('Elysian Blocks', 'elysian-theme'),
				'icon'  => null,
			],
		]
	);
}

// Add custom blocks
add_action('acf/init', 'elysian_theme_register_acf_blocks');
function elysian_theme_register_acf_blocks()
{
	$metadata_files = glob(get_theme_file_path('/blocks-json/*.block.json')) ?: [];

	foreach ($metadata_files as $metadata_file) {
		register_block_type($metadata_file);
	}
}
