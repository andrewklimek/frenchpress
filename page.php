<?php

get_header();

echo "<main id=main class=site-main>";

while ( have_posts() ) {
	the_post();
	get_template_part( 'template-parts/content', 'page' );
}

do_action('frenchpress_main_bottom');

echo '</main>';

if (!empty($GLOBALS['frenchpress']->sidebar)) echo $GLOBALS['frenchpress']->sidebar;

get_footer();