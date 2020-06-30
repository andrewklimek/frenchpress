<?php
/**
 * Called from template files via get_template_part( 'template-parts/content', 'page' )
 */
?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php
	if ( !is_front_page() && apply_filters( 'frenchpress_page_titles', true )  && ! apply_filters( 'frenchpress_title_in_header', false ) ) :
	?>
	<header class=page-header>
		<?php the_title( '<h1 class=title>', '</h1>' ); ?>
	</header>
	<?php
	endif;
	?>
	<div class=page-content>
		<?php
			the_content();

			wp_link_pages( array(
				'before' => '<div class=page-links>Pages:',
				'after'  => '</div>',
			) );
		?>
	</div>
</article>