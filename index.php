<?php

frenchpress_add_inline_style( frenchpress_style_loop() );

get_header();

echo "<main id=main class=site-main>";

if ( have_posts() ) :

	/* This block is for the Posts page as selected in the settings > reading 
	 * it displays the content and title of the page, so you can use that page for intro text to the blog section if you want */
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
	
    echo "<div class=loop>";
	/* Start the Loop */
	while ( have_posts() ) : the_post();

		/* specific content templates can be made like: content-post_format.php */
		get_template_part( 'template-parts/content' );// get_post_format()  post format support removed

	endwhile;

    echo "</div>";// loop

	echo frenchpress_posts_nav();
    
else :

	get_template_part( 'template-parts/content', 'none' );

endif;

do_action('frenchpress_main_bottom');

echo '</main>';

if (!empty($GLOBALS['frenchpress']->sidebar)) echo $GLOBALS['frenchpress']->sidebar;

get_footer();