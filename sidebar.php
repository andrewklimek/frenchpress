<?php
/**
 * Called via get_sidebar()
 */
if ( apply_filters( 'frenchpress_sidebar', true ) ) {

	if ( is_active_sidebar( 'sidebar-1' ) ) : ?>
	<aside id=secondary class="widget-area fffi">
		<?php dynamic_sidebar( 'sidebar-1' ); ?>
	</aside>
	<?php endif;
	if ( is_active_sidebar( 'sidebar-2' ) ) : ?>
	<aside id=tertiary class="widget-area fffi">
		<?php dynamic_sidebar( 'sidebar-2' ); ?>
	</aside>
	<?php endif;
}