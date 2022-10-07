<?php
/**
 * Called from template files via get_template_part( 'template-parts/content', 'none' )
 */
?>
<section class=no-results>
	<div class=page-content>
		<?php
		if ( is_home() && current_user_can( 'publish_posts' ) ) :
			echo "<p>Ready to publish your first post? <a href='" . esc_url( admin_url( 'post-new.php' ) ) . "'>Get started here</a>.";

		elseif ( is_search() ) :
			echo "<p>Sorry, but nothing matched your search terms. Please try again with some different keywords.";
			get_search_form();

		else :
			echo "<p>It seems we can’t find what you’re looking for. Perhaps searching can help.";
			get_search_form();

		endif;
		?>
	</div>
</section>