<?php
/**
 * Called from template files via get_template_part( 'template-parts/content', 'search' )
 */
?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<header class=entry-header>
		<?php
		the_title( sprintf( '<h3 class=title><a href="%s" rel=bookmark>', esc_url( get_permalink() ) ), '</a></h3>' );

		if ( 'post' === get_post_type() ) frenchpress_entry_meta();
			?>
	</header>
	<div class=entry-summary>
		<?php the_excerpt(); ?>
	</div>
	<footer class=entry-footer>
		<?php frenchpress_entry_footer(); ?>
	</footer>
</article>