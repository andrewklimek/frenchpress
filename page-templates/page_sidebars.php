<?php
/**
 * Template Name: Page with Sidebars
 */

get_header();

echo "<main id=primary class='site-main fffi fffi-99'>";

while ( have_posts() ) : the_post();

		get_template_part( 'template-parts/content', 'page' );

		if ( comments_open() || get_comments_number() ) comments_template();

	endwhile;

do_action('frenchpress_main_bottom');

echo '</main>';

get_sidebar();
get_footer();