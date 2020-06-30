<?php
/**
 * Template Name: Full-Width
 */

get_header();
?>
<main id=primary class="site-main main-full-width fffi fffi-99">
	<?php
	while ( have_posts() ) : the_post();

		get_template_part( 'template-parts/content', 'page' );

		// If comments are open or we have at least one comment, load up the comment template.
		if ( comments_open() || get_comments_number() ) :
			comments_template();
		endif;

	endwhile; // End of the loop.

do_action('frenchpress_main_bottom');

echo '</main>';

get_footer();