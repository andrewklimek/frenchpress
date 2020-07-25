<?php
/**
 * Called from template files via get_template_part( 'template-parts/content' )
 *
 * Also used as fallback when a more specific template does not exist for the 2nd argument of get_template_part()
 */
?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<header class=entry-header>
		<?php

			the_title( '<div><h2 class=title><a href="' . esc_url( get_permalink() ) . '" rel=bookmark>', '</a></h2></div>' );

			if ( apply_filters( 'frenchpress_entry_meta_non_single', false ) ) frenchpress_entry_meta();

			// Filter for displaying featured image. 2nd arg is bool for "is_singular"
			if ( has_post_thumbnail() && apply_filters( 'frenchpress_featured_image', 'show it', false ) ) {
				echo '<figure class=featured-image>' . get_the_post_thumbnail( null, 'medium' ) . '</figure>';
			}
		?>
	</header>
	<?php if ( (is_home() && apply_filters( 'frenchpress_blog_excerpts', true )) || (is_archive() && apply_filters( 'frenchpress_archive_excerpts', true )) ) : ?>
	<div class=entry-summary>
		<?php the_excerpt(); ?>
	</div>
	<?php else : ?>
	<div class=entry-content>
		<?php
			the_content( 'Continue reading <span class=meta-nav>&rarr;</span>' );

			wp_link_pages( array(
				'before' => '<div class=page-links>Pages:',
				'after'  => '</div>',
			) );
		?>
	</div>
	<?php endif; ?>
</article>