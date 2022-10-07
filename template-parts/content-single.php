<?php
/**
 * Called from template files via get_template_part( 'template-parts/content', 'single' )
 */
?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<header class=page-header>
		<?php

		if ( ! apply_filters( 'frenchpress_title_in_header', false ) ) {
			the_title( '<h1 class=title>', '</h1>' );
		}

		frenchpress_entry_meta();

	echo "</header>";
	
	// Filter for displaying featured image. 2nd arg is bool for "is_singular"
	if ( !empty( $_GLOBALS['frenchpress']->feat_image_under_post_title ) && apply_filters( 'frenchpress_featured_image', 'show it', true ) && has_post_thumbnail() ) {
		echo '<figure class=featured-image>' . get_the_post_thumbnail() . '</figure>';
	}
	?>
	<div class=entry-content>
		<?php the_content(); ?>
	</div>
	<?php frenchpress_entry_footer(); ?>
</article>