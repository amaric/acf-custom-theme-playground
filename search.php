<?php get_header(); ?>

	<div class="container">
		<header class="search-header">
			<h1 class="search-title">
				<?php printf(esc_html__('Search results for: %s', 'elysian-theme'), '<span>' . esc_html(get_search_query()) . '</span>'); ?>
			</h1>
		</header>

		<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

			<article <?php post_class(); ?> id="post-<?php the_ID(); ?>">
				<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>

				<div class="entry-summary">
					<?php the_excerpt(); ?>
				</div>
			</article>

		<?php endwhile; ?>

		<div class="pagination">
			<?php the_posts_pagination(); ?>
		</div>

		<?php else : ?>

			<p><?php esc_html_e('No results found. Please try a different search term.', 'elysian-theme'); ?></p>

			<?php get_search_form(); ?>

		<?php endif; ?>
	</div>

<?php get_footer(); ?>
