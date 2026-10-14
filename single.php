<?php get_header(); ?>

	<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
		<div class="container">
			<article <?php post_class(); ?> id="post-<?php the_ID(); ?>">
				<div class="entry">
					<?php the_content(); ?>
				</div>

				<?php if (comments_open() || get_comments_number()) :
					comments_template();
				endif; ?>
			</article>
		</div>
	<?php endwhile; endif; ?>

<?php get_footer(); ?>