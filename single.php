<?php

$layout = apply_filters( 'frenchpress_full_width', false ) ? "full-width" : "sidebars";// defaults
// hook for default page layout until I make an option page. Use 'sidebars, 'full-width', or 'no-sidebars'
$layout = apply_filters( 'frenchpress_post_layout', $layout );

get_header();
?>
<main id=primary class="site-main fffi fffi-99<?php if ( $layout === 'full-width' ) echo ' main-full-width' ?>">
<?php
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

get_sidebar();
get_footer();