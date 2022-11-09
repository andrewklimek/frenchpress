<?php

get_header();

echo "<main id=main class=site-main>";

while ( have_posts() ) : the_post();

	/* specific content templates can be made like: content[-single][-custom post type].php */
	$type = get_post_type();
	$type = 'post' !== $type ? "-{$type}" : "";
// 	$format = get_post_format();// post format support removed

	locate_template( [
        "template-parts/content-single{$type}.php",
        "template-parts/content{$type}.php",
	    "template-parts/content.php"
	    ], true, false );

	if ( apply_filters( 'frenchpress_post_navigation', false ) ) :
		// filter the navigation args, for example to go to next post in same category:
		// add_filter( 'frenchpress_post_navigation_args', function() { return array( 'in_same_term' => true ); } );
		the_post_navigation( apply_filters( 'frenchpress_post_navigation_args', array() ) );
	endif;

	if ( $type !== "attachment" && comments_open() || get_comments_number() ) comments_template();

endwhile;

do_action('frenchpress_main_bottom');

echo '</main>';

if (!empty($GLOBALS['frenchpress']->sidebar)) echo $GLOBALS['frenchpress']->sidebar;

get_footer();