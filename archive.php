<?php

frenchpress_add_inline_style( frenchpress_style_loop() );

get_header();

echo "<main id=main class=site-main>";

if ( have_posts() ) :

	if ( ! apply_filters( 'frenchpress_title_in_header', false ) ) :
	?>
	<header class=page-header>
		<?php
		$skip_the_rest = apply_filters( 'frenchpress_archive_header', false );
		if ( ! $skip_the_rest ) :
			the_archive_title( '<h1 class=title>', '</h1>' );
			the_archive_description( '<div class=taxonomy-description>', '</div>' );
		endif;
		?>
	</header>
	<?php
	endif;

    echo "<div class=loop>";
	/* Start the Loop */
	while ( have_posts() ) : the_post();

		/* specific content templates can be made like: content-custom_post_type.php or content-post_format.php (post format only applies to "post" type) */
// 		$type = get_post_type();
// 		if ( 'post' === $type ) $type = get_post_format();// post format support removed
		get_template_part( 'template-parts/content', get_post_type() );

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