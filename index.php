<?php

get_header();
?>
<main id=primary class="site-main fffi fffi-99">
<?php
if ( have_posts() ) :

	if ( is_home() && ! is_front_page() && ! apply_filters( 'frenchpress_title_in_header', false ) ) :
		?>
		<header class=page-header>
			<h1 class="title<?php if ( ! apply_filters( 'frenchpress_blog_title', 'show it' ) ) echo ' screen-reader-text'; ?>"><?php single_post_title(); ?></h1>
		</header>
	<?php
	endif;

	/* Start the Loop */
	while ( have_posts() ) : the_post();

		/*
		 * Include the Post-Format-specific template for the content.
		 * If you want to override this in a child theme, then include a file
		 * called content-___.php (where ___ is the Post Format name) and that will be used instead.
		 */
		get_template_part( 'template-parts/content', get_post_format() );

	endwhile;

	echo frenchpress_posts_nav();

else :

	get_template_part( 'template-parts/content', 'none' );

endif;

do_action('frenchpress_main_bottom');

echo '</main>';

get_sidebar();
get_footer();