<?php

frenchpress_add_inline_style( frenchpress_style_loop() );

get_header();

echo "<main id=main class=site-main>";

	if ( ! apply_filters( 'frenchpress_title_in_header', false ) ) :
	?>
	<header class=page-header>
		<h1 class=title>Search Results for: <span><?php echo get_search_query() ?></span></h1>
	</header>
	<?php
	endif;

if ( have_posts() ) :

	echo "<div class=loop>";
	/* Start the Loop */
	while ( have_posts() ) : the_post();

		/**
		 * Run the loop for the search to output the results.
		 * If you want to overload this in a child theme then include a file
		 * called content-search.php and that will be used instead.
		 */
		get_template_part( 'template-parts/content', 'search' );

	endwhile;

	if ( empty( $GLOBALS['frenchpress']->no_blog_thumbnails ) &&	 $GLOBALS['frenchpress']->blog_layout_desktop === "grid" ) {	
		echo "<div class=post></div><div class=post></div><div class=post></div>";// placeholder divs to make the grid uniform (flex stretches last row when its not full)
	}

    echo "</div>";// loop

	the_posts_navigation();

else :

	get_template_part( 'template-parts/content', 'none' );

endif;

do_action('frenchpress_main_bottom');

echo '</main>';

get_sidebar();
get_footer();