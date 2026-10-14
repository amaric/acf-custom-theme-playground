<?php
/**
 * Template Name: Flexible fields Template
 */
?>
<?php get_header(); ?>

	<?php
		if( have_rows('sections') ):
			while ( have_rows('sections') ) : the_row();

      
      if( get_row_layout() == 'hero_section' ):
					get_template_part('template-parts/sections/hero-section');
        /*
				elseif( get_row_layout() == 'contact_form_and_info_section' ):
					get_template_part('template-parts/contact-form-and-info-section');
        */
				endif;
			endwhile;
		endif;
	?>

<?php get_footer(); ?>