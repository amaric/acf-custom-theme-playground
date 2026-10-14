<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="icon" type="image/png" sizes="32x32" href="<?php echo esc_url(get_template_directory_uri() . '/dist/favicon/favicon-32x32.png'); ?>" />
	<link rel="icon" type="image/png" sizes="16x16" href="<?php echo esc_url(get_template_directory_uri() . '/dist/favicon/favicon-16x16.png'); ?>" />
	<link rel="shortcut icon" href="<?php echo esc_url(get_template_directory_uri() . '/dist/favicon/favicon.ico'); ?>" />
	<link rel="apple-touch-icon" sizes="180x180" href="<?php echo esc_url(get_template_directory_uri() . '/dist/favicon/apple-touch-icon.png'); ?>" />
	<meta name="apple-mobile-web-app-title" content="<?php echo esc_attr(get_bloginfo('name')); ?>" />
	<link rel="manifest" href="<?php echo esc_url(get_template_directory_uri() . '/dist/favicon/manifest.webmanifest'); ?>" />
	<?php wp_head(); ?>
</head>


<body <?php body_class(); ?>>
	<?php
	$header_logo   = get_field('logo_white', 'option');
	$header_button = get_field('header_button', 'option');
	$mobile_menu   = has_nav_menu('header-menu-mobile') ? 'header-menu-mobile' : 'header-menu';
	?>
	<header class="site-header">
		<div class="container site-header-container">
			<a class="site-header-logo" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php echo esc_attr(get_bloginfo('name')); ?>">
				<?php if (!empty($header_logo['ID'])) : ?>
					<?php echo wp_get_attachment_image($header_logo['ID'], 'full', false, ['alt' => '', 'loading' => 'eager', 'fetchpriority' => 'high']); ?>
				<?php else : ?>
					<span class="site-header-logo-text"><?php echo esc_html(get_bloginfo('name')); ?></span>
				<?php endif; ?>
			</a>

			<nav class="site-header-nav" aria-label="<?php esc_attr_e('Primary', 'elysian-theme'); ?>">
				<?php
				wp_nav_menu([
					'theme_location' => 'header-menu',
					'container'      => '',
					'menu_class'     => 'site-header-menu',
					'fallback_cb'    => false,
				]);
				?>
			</nav>

			<?php if (!empty($header_button['url'])) : ?>
				<?php elysian_theme_render_link($header_button, 'btn-outline-light site-header-btn'); ?>
			<?php endif; ?>

			<button type="button" class="site-header-toggle" aria-expanded="false" aria-controls="site-header-overlay" aria-label="<?php esc_attr_e('Open menu', 'elysian-theme'); ?>" data-label-open="<?php esc_attr_e('Open menu', 'elysian-theme'); ?>" data-label-close="<?php esc_attr_e('Close menu', 'elysian-theme'); ?>">
				<span></span>
				<span></span>
				<span></span>
			</button>
		</div>

		<div class="site-header-overlay" id="site-header-overlay">
			<div class="container site-header-overlay-container">
				<nav aria-label="<?php esc_attr_e('Mobile', 'elysian-theme'); ?>">
					<?php
					wp_nav_menu([
						'theme_location' => $mobile_menu,
						'container'      => '',
						'menu_class'     => 'site-header-overlay-menu',
						'fallback_cb'    => false,
					]);
					?>
				</nav>

				<?php if (!empty($header_button['url'])) : ?>
					<?php elysian_theme_render_link($header_button, 'btn-outline-light site-header-overlay-btn'); ?>
				<?php endif; ?>
			</div>
		</div>
	</header>
