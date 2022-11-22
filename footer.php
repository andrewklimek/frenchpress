<?php
/**
 * Called via get_footer()
 */

do_action('frenchpress_content_bottom');

echo '</div>';// #content

do_action('frenchpress_footer_before');

echo '<footer id=footer class=site-footer>';

do_action('frenchpress_footer_top');

if ( is_active_sidebar( 'footer-top' ) ) : ?>
	<div id=footer-top class=widget-area>
		<div class="tray footer-top-tray">
			<?php dynamic_sidebar( 'footer-top' ); ?>
		</div>
	</div>
<?php
endif;

if ( is_active_sidebar( 'footer-1' ) || is_active_sidebar( 'footer-2' ) || is_active_sidebar( 'footer-3' ) || is_active_sidebar( 'footer-4' ) ) :

	$number_of_widget_areas = 0;

	ob_start();

	if ( is_active_sidebar( 'footer-1' ) ) {
		++$number_of_widget_areas;
		echo '<div id=footer-1 class="widget-area fffi">';
		dynamic_sidebar( 'footer-1' );
		echo '</div>';
	}
	if ( is_active_sidebar( 'footer-2' ) ) {
		++$number_of_widget_areas;
		echo '<div id=footer-2 class="widget-area fffi">';
		dynamic_sidebar( 'footer-2' );
		echo '</div>';
	}
	if ( is_active_sidebar( 'footer-3' ) ) {
		++$number_of_widget_areas;
		echo '<div id=footer-3 class="widget-area fffi">';
		dynamic_sidebar( 'footer-3' );
		echo '</div>';
	}
	if ( is_active_sidebar( 'footer-4' ) ) {
		++$number_of_widget_areas;
		echo '<div id=footer-4 class="widget-area fffi">';
		dynamic_sidebar( 'footer-4' );
		echo '</div>';
	}
	$widgets = ob_get_clean();

	echo "<div class='tray footer-tray fff fff-pad fff-x{$number_of_widget_areas}'>{$widgets}</div>";

endif;

do_action('frenchpress_footer_bottom');

// I'm using this sort of odd method to there a way to count number of widgets and remove flex classes if just one
// Might not work if there's some dynamic widget display rules... though it may only need apply_filters( 'sidebars_widgets', $sidebars_widgets ) to work.
// can do without the extra is_active function
// if ( is_active_sidebar( 'footer-bottom' ) ) :
global $sidebars_widgets;
if ( !empty( $sidebars_widgets['footer-bottom'] ) )
{
    $multiple_widget_css = '';
	if ( isset( $sidebars_widgets['footer-bottom'][1] ) ) {
		$multiple_widget_css = ' fff fff-middle fff-spacebetween';
		// TODO this might be an option, and might want a custom class for nowrapping stuff. Also might be excessive and better in a child theme
		echo "<style>@media(min-width:783px){.footer-bottom-tray{flex-wrap:nowrap}}</style>";
	}
	echo "<div id=footer-bottom><div class='footer-bottom-tray tray fff-pad{$multiple_widget_css}'>";
	dynamic_sidebar( 'footer-bottom' );
	echo "</div></div>";
}

echo "</footer><div id=wp_footer>";

if ( in_array( $GLOBALS['frenchpress']->mobile_nav, ['slide','tree'] ) ) echo "<div id=mask></div>";

wp_footer();

?></div>