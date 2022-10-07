<?php

get_header();
?>
<main id=primary class="site-main fffi fffi-99">
<?php
if ( have_posts() ) :

	if ( is_home() && ! is_front_page() ) :
		echo "<header class=page-header>";
		
		$_post = get_queried_object();
		
		if ( $_post->post_content ) {
		    echo apply_filters( 'the_content', $_post->post_content );
		}
		elseif (  ! apply_filters( 'frenchpress_title_in_header', false ) ) {
			echo "<h1 class='title";
			if ( ! apply_filters( 'frenchpress_blog_title', 'show it' ) ) echo ' screen-reader-text';// THIS SHOULD BE AN OPTION
			echo "'>" . apply_filters( 'single_post_title', $_post->post_title, $_post ) . "</h1>";// function to normally use is single_post_title()
		}
		
		echo "</header>";
	endif;
	
	echo "<style>.post{margin-bottom:48px}</style>";
    echo "<div class=loop>";
	/* Start the Loop */
	while ( have_posts() ) : the_post();

		/* specific content templates can be made like: content-post_format.php */
		get_template_part( 'template-parts/content' );// get_post_format()  post format support removed

	endwhile;

	echo frenchpress_posts_nav();

    echo "</div>";// loop
    
else :

	get_template_part( 'template-parts/content', 'none' );

endif;

do_action('frenchpress_main_bottom');

echo '</main>';

get_sidebar();
get_footer();