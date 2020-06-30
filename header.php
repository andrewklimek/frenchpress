<?php
/**
 * Called via get_header()
 *
 * If this is ever used for the general public or WPML,
 * html lang= should use language_attributes() or bloginfo('language') and maybe meta charset= bloginfo('charset')
 *
 * I was using this filter... no longer needed?
 * <html lang=en class="<?php echo apply_filters( 'frenchpress_class_html', "xnav" ); ?>">
 */

?><!doctype html>
<html lang=en>
<meta charset=utf-8>
<meta name=viewport content="width=device-width, initial-scale=1">
<?php
wp_head();
?>
<body <?php body_class( "fff fff-column fff-none" ); ?>>
<?php do_action( 'frenchpress_body_top' ); ?>
<a class="skip-link screen-reader-text" href=#content>Skip to content</a>
<header id=header class="site-header fffi">
	<?php do_action( 'frenchpress_header_top' ); ?>
	<?php
	if ( is_active_sidebar( 'header-1' ) ) : ?>
		<div id=header-1 class=widget-area role=complementary>
			<div class=tray>
				<?php dynamic_sidebar( 'header-1' ); ?>
			</div>
		</div>
	<?php
	endif;
	if ( is_active_sidebar( 'header-2' ) ) : ?>
		<div id=header-2 class=widget-area role=complementary>
			<div class="tray fff">
				<?php dynamic_sidebar( 'header-2' ); ?>
			</div>
		</div>
	<?php
	endif;
	?>
<div id=site-header-main>
	<div class="<?php echo apply_filters( 'frenchpress_class_header_main', "tray fff fff-middle fff-spacebetween fff-nowrap fff-pad" ); ?>">
	<?php

	/* BRANDING */
	// start building .site-branding.  Keep track of if anything is displayed so I can remove padding if not
	$site_branding_html = "";

	/**
	* Filter to insert whatever (SVG logos) and optionally skip the rest of this PHP block
	* e.g.:
	* add_filter( 'frenchpress_site_branding', function(){return file_get_contents( __DIR__ .'/logo.svg' );} );
	*
	* This could be moved to 'get_custom_logo' filter as of v4.5...
	* it would run a little extra code checking for a custom logo first, and would let those take precedence
	*/
	$logo = apply_filters( 'frenchpress_site_branding', '' );

	if ( $logo && false === strpos( $logo, "</a>" ) ) {
		// add home link if a link was not supplied at the filter.
		// get_custom_logo() adds class=custom-logo-link but I won't for now.
		$logo = "<a href='" . home_url() . "' rel=home>{$logo}</a>";
	}
	elseif ( ! $logo ) {
		$logo = get_custom_logo();
	}
	if ( $logo ) {
		$site_branding_html .= "<div id=logo>{$logo}</div>";
	}

	// check if the site header & description were hidden in the customizer, add screen-reader-text class for CSS hiding
	$hide = display_header_text() ? '' : ' screen-reader-text';

	if ( ! $hide || is_customize_preview() ) {// For now I am not even going to bother with hidden elements, homepages probably want custom and/or visible h1

		$home_link = '<a href="'. home_url() .'" rel=home>'. get_bloginfo( 'name' ) .'</a>';

		$site_branding_html .= is_front_page() ? "<h1 class='site-title{$hide}'>{$home_link}</h1>" : "<div class='site-title h2{$hide}'>{$home_link}</div>";

		$description = get_bloginfo( 'description', 'display' );

		if ( $description || is_customize_preview() ) {
			$site_branding_html .= "<p class='site-description{$hide}'>{$description}</p>";
		}

	}

	if ( $site_branding_html ) {

		$pad = $logo || !$hide ? '' : ' pad-0';

		echo "<div class='site-branding fffi{$pad} fffi-9'>{$site_branding_html}</div>";
	}

	/* END OF BRANDING */


	if ( is_active_sidebar( 'header-3' ) ) : ?>
		<div id=header-3 class=fffi>
			<?php dynamic_sidebar( 'header-3' ); ?>
		</div>
	<?php
	endif;//is_active_sidebar( 'header-3' )

	/**
	* Menu Drawer Button
	*/
	if ( apply_filters( 'frenchpress_drawer', true ) ) : ?>
		<div id=menu-open role=button aria-controls=main-menu aria-expanded=false class=fffi>
			<span id=menu-open-label class=screen-reader-text>Menu</span>
			<div class=menubun></div><div class=menubun></div><div class=menubun></div></div>
	<?php endif;

	echo '</div>';//.tray
echo '</div>';//.site-header-main

// I'm using this sort of odd method to there a way to count number of widgets and remove flex classes if just one
// Might not work if there's some dynamic widget display rules... though it may only need apply_filters( 'sidebars_widgets', $sidebars_widgets ) to work.
// can do without the extra is_active function
// if ( is_active_sidebar( 'header-4' ) ) :
global $sidebars_widgets;
if ( !empty( $sidebars_widgets['header-4'] ) ) : ?>
	<div id=header-4 class=widget-area role=complementary>
		<div class="tray<?php if( isset( $sidebars_widgets['header-4'][1] ) ) echo ' fff fff-middle fff-spacebetween fff-pad'; ?>">
			<?php dynamic_sidebar( 'header-4' ); ?>
		</div>
	</div>
<?php
endif;

/**
 * Structurally lame, but sometimes we need to print the <h1> in the header for stylistic reasons,
 * to have a full-width background that also spans above the sidebar
 * use add_filter( 'frenchpress_title_in_header', '__return_true' ); to activate
 */
if ( !is_front_page() && apply_filters( 'frenchpress_title_in_header', false ) ) {

	// basically an optimized version of using trim(wp_title('', false))
	// $title = false;
	// if ( is_single() || is_home() || is_page() ) $title = single_post_title( '', false );// default
	if ( ! $title = single_post_title( '', false ) ) {// returns nothing if get_queried_object()->post_title is not set
		if ( is_search() ) $title = sprintf( 'Search Results for &#8220;%s&#8221;', get_search_query() );
		elseif ( is_archive() ) $title = get_the_archive_title();
		elseif ( is_404() ) $title = 'Page not found';
	}
	if ( $title ) {
		echo '<div id=header-title><div class="tray header-title-tray"><h1 class=title>' . $title . '</h1></div></div>';
	} else {
		add_filter( 'frenchpress_title_in_header', '__return_false', 99 );// weird, couldn't get the title.  Put it back to normal
	}

}

do_action( 'frenchpress_header_bottom' );

?>
</header>
<div id=content class="<?php echo apply_filters( 'frenchpress_class_content', "site-content fffi fffi-noshrink" ); ?>">
	<div class="content-tray <?php echo ( apply_filters( 'frenchpress_full_width', false ) ) ? "tray--full-width " : "tray "; echo apply_filters( 'frenchpress_class_content_tray', "fff fff-spacearound fff-magic" ); ?>">
