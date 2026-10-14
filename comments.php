<?php
if (post_password_required()) {
    return;
}
?>

<div id="comments" class="comments-area">

	<?php if (have_comments()) : ?>

		<h2 class="comments-title">
			<?php
			$comment_count = get_comments_number();
			printf(
				esc_html(_n('%d Comment', '%d Comments', $comment_count, 'elysian-theme')),
				$comment_count
			);
			?>
		</h2>

		<ol class="comment-list">
			<?php
			wp_list_comments([
				'style'      => 'ol',
				'short_ping' => true,
				'avatar_size' => 48,
			]);
			?>
		</ol>

		<?php the_comments_navigation(); ?>

	<?php endif; ?>

	<?php if (!comments_open() && get_comments_number() && post_type_supports(get_post_type(), 'comments')) : ?>
		<p class="no-comments"><?php esc_html_e('Comments are closed.', 'elysian-theme'); ?></p>
	<?php endif; ?>

	<?php comment_form(); ?>

</div>
