	<?php
	$footer_logo        = get_field('logo_white', 'option');
	$footer_description = get_field('footer_description', 'option') ?: get_bloginfo('description');
	$footer_copyright   = get_field('copyright_text', 'option') ?: sprintf(
		/* translators: 1: current year, 2: site name */
		__('© %1$s %2$s. All rights reserved.', 'elysian-theme'),
		wp_date('Y'),
		get_bloginfo('name')
	);
	?>
	<footer class="site-footer">
		<div class="container site-footer-container">
			<div class="site-footer-top">
				<div class="site-footer-brand">
					<a class="site-footer-logo" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php echo esc_attr(get_bloginfo('name')); ?>">
						<?php if (!empty($footer_logo['ID'])) : ?>
							<?php echo wp_get_attachment_image($footer_logo['ID'], 'full', false, ['alt' => '', 'loading' => 'lazy']); ?>
						<?php else : ?>
							<span class="site-footer-logo-text"><?php echo esc_html(get_bloginfo('name')); ?></span>
						<?php endif; ?>
					</a>

					<?php if ($footer_description) : ?>
						<p class="site-footer-description"><?php echo esc_html($footer_description); ?></p>
					<?php endif; ?>
				</div>

				<?php if (has_nav_menu('footer-menu-1') || has_nav_menu('footer-menu-2') || has_nav_menu('footer-menu-3')) : ?>
					<nav class="site-footer-columns" aria-label="<?php esc_attr_e('Footer', 'elysian-theme'); ?>">
						<?php
						elysian_theme_render_footer_menu('footer-menu-1');
						elysian_theme_render_footer_menu('footer-menu-2');
						elysian_theme_render_footer_menu('footer-menu-3');
						?>
					</nav>
				<?php endif; ?>
			</div>

			<div class="site-footer-bottom">
				<p class="site-footer-copyright"><?php echo esc_html($footer_copyright); ?></p>

				<?php if (has_nav_menu('footer-social')) : ?>
					<nav class="site-footer-social" aria-label="<?php esc_attr_e('Social', 'elysian-theme'); ?>">
						<?php
						wp_nav_menu([
							'theme_location' => 'footer-social',
							'container'      => '',
							'menu_class'     => 'site-footer-social-menu',
							'fallback_cb'    => false,
						]);
						?>
					</nav>
				<?php endif; ?>
			</div>
		</div>
	</footer>

	<?php wp_footer(); ?>

</body>
</html>
