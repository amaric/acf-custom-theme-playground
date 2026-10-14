<?php get_header(); ?>

	<div class="page-404">
		<div class="container">
			<div class="error-404-text">
				<h1 class="page-title"><?php esc_html_e('Oops! Page Not Found', 'elysian-theme'); ?></h1>
				
				<p class="error-message">
					<?php esc_html_e('The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.', 'elysian-theme'); ?>
				</p>

				<div class="error-404-actions">
					<a href="<?php echo esc_url(home_url('/')); ?>" class="button">
						<?php esc_html_e('Go to Homepage', 'elysian-theme'); ?>
					</a>
				</div>
			</div>
		</div>
	</div>

<?php get_footer(); ?>