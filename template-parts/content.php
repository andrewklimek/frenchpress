<?php
/**
 * Called from template files via get_template_part( 'template-parts/content' )
 *
 * Also used as fallback when a more specific template does not exist for the 2nd argument of get_template_part()
 */
?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php
	    // Filter for displaying featured image. 2nd arg is bool for "is_singular"
		if ( empty( $GLOBALS['frenchpress']->no_blog_thumbnails ) && has_post_thumbnail() ) {
			// 	echo get_the_post_thumbnail( null, 'thumb', ['class' => 'featured-image'] );
			echo '<figure class=featured-image>' . get_the_post_thumbnail( null, 'thumb' ) . '</figure>';
        }
        
        echo "<div class=post-text>";

		the_title( '<h2 class=title><a href="' . esc_url( get_permalink() ) . '">', '</a></h2>' );

		frenchpress_entry_meta();// Maybe add a option to disable meta on index separately form single post

		$excerpt_type = !empty( $GLOBALS['frenchpress']->blog_excerpt ) ? $GLOBALS['frenchpress']->blog_excerpt : 'excerpt';

		if ( $excerpt_type === 'excerpt' ) {
			echo "<div class=entry-summary>";
			// add_filter( 'the_excerpt', function($excerpt){ return "{$excerpt}<p><a class=button href='" . esc_url( get_permalink() ) . "'>Read More</a></p>"; } );// could be an option but what about class
			// also the [...] is modified here https://developer.wordpress.org/reference/hooks/excerpt_more/
			the_excerpt();
			echo "</div>";

		} elseif ( $excerpt_type === 'fulltext' ) {
			echo "<div class=entry-content>";
			
			the_content( 'Continue reading <span class=meta-nav>&rarr;</span>' );

			wp_link_pages( ['before' => '<div class=page-links>Pages:', 'after' => '</div>'] );
			echo "</div>";
		}
	?>
	</div>
</article>