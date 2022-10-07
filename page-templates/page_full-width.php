<?php
/**
 * Template Name: Full-Width
 */

add_filter( 'frenchpress_full_width', '__return_true' );

get_header();

echo "<main id=primary class='site-main main-full-width fffi fffi-99'>";

	while ( have_posts() ) : the_post();

		get_template_part( 'template-parts/content', 'page' );

		if ( comments_open() || get_comments_number() ) comments_template();

	endwhile;

do_action('frenchpress_main_bottom');

echo '</main>';

get_footer();