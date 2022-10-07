<?php
/**
 * Called from template files via get_template_part( 'template-parts/content', 'page' )
 */
?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php
	if ( !is_front_page() && !empty($GLOBALS['frenchpress']->page_titles) && ! apply_filters( 'frenchpress_title_in_header', false ) ) :

		echo "<header class=page-header><h1 class=title>" . get_the_title() . "</h1></header>";

	endif;
	
	echo "<div class=page-content>";
		the_content();

		wp_link_pages( array(
			'before' => '<div class=page-links>Pages:',
			'after'  => '</div>',
		) );
		?>
	</div>
</article>