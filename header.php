<?php
/**
 * Called via get_header()
 *
 * If this is ever used for the general public or WPML,
 * html lang= should use language_attributes() or bloginfo('language') and maybe meta charset= bloginfo('charset')
 */
global $frenchpress;

/**
 * this block of PHP is for determining the layout
 */
if ( empty( $frenchpress->layout ) ) {
	$frenchpress->layout = is_singular() ? ( is_page() ? $frenchpress->page_layout : $frenchpress->post_layout ) : $frenchpress->index_layout;
	$frenchpress->layout = apply_filters( 'frenchpress_layout', $frenchpress->layout );
}
if ( $frenchpress->layout === 'sidebars' ) {
	ob_start();// need to run sidebar.php to see if sidebar is available, so I store it to global for insert later.
	get_sidebar();
	$frenchpress->sidebar = ob_get_clean();
	if ( ! $frenchpress->sidebar ) {
		if ( $frenchpress->page_layout !== 'sidebars' ) $frenchpress->layout = $frenchpress->page_layout;// most likely
		elseif ( $frenchpress->post_layout !== 'sidebars' ) $frenchpress->layout = $frenchpress->post_layout;// 2nd choice
		else $frenchpress->layout = '';// fallback if both were set to sidebar
	}
}
if ( empty( $frenchpress->layout ) ) {
	$frenchpress->layout = is_single() ? 'content-width' : 'site-width';// last chance, set defaults if still empty
}

?><!doctype html>
<html lang=en class=dnav>
<meta name=viewport content="width=device-width, initial-scale=1">
<?php
wp_head();
?>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href=#content>Skip to content</a>
<header id=header class=site-header>
	<?php do_action( 'frenchpress_header_top' );


	$nav = [ 'theme_location' => 'main-menu', 'menu_id' => 'main-menu', 'container' => 0, 'echo' => 0, 'fallback_cb' => false, 'item_spacing' => 'discard', 'walker' => new Frenchpress_Menu_Walker() ];
	if ( $frenchpress->nav_align !== "left" ) {
		if ( $frenchpress->nav_align === "justified" ) $frenchpress->nav_align = "spacebetween";
		$nav['menu_class'] = "menu fff fff-" . $frenchpress->nav_align;
	}
	$nav = wp_nav_menu($nav);

	if ( $nav ) {

	if ( !empty( $frenchpress->add_custom_code_right_of_menu ) || ( $frenchpress->nav_position === "right" && $frenchpress->nav_align !== "right" ) ) {
		$grow = "fffi fffi-9";
	} elseif ( $frenchpress->nav_position === "right" ) {// $frenchpress->nav_align === "right" implied.  I think this is right...
		$grow = "fffi";
	} else {
		$grow = "";
	}

	if ( in_array( $frenchpress->mobile_nav, ['slide','tree'] ) ) {
		$drawer_class = empty( $frenchpress->desktop_drawer ) ? "drawer" : "'drawer desk-drawer'";
		$drawer_bottom = apply_filters( 'frenchpress_drawer_bottom', '' );
		$nav = "<div class={$drawer_class}><nav class=main-nav>$nav</nav>$drawer_bottom</div>";
		if ( $grow ) $nav = "<div class='{$grow}'>$nav</div>";
	} else {
		$nav = $grow ? "<nav class='main-nav {$grow}'>$nav</nav>" : "<nav class=main-nav>$nav</nav>";
	}
	
	if ( !empty( $frenchpress->add_custom_code_right_of_menu ) ) {
		if ( $frenchpress->nav_position !== "right" ) {
			$tray = empty( $frenchpress->full_width_nav ) ? "tray " : "";
			$nav = "<div class=main-nav-wrap><div class='{$tray}fff fff-middle fff-nowrap fff-pad fff-spacebetween'>{$nav}<div class=fffi>{$frenchpress->custom_code_right_of_menu}</div></div></div>";
		} else {
			$nav = "{$nav}<div class=fffi>{$frenchpress->custom_code_right_of_menu}</div>";
		}
	} else {
		if ( $frenchpress->nav_position !== "right" && $frenchpress->nav_align !== "center" ) {
			if ( empty( $frenchpress->full_width_nav ) ) $nav = "<div class=tray>$nav</div>";
			$nav = "<div class=main-nav-wrap>$nav</nav>";
		}
	}

	if ( $frenchpress->nav_position === "top" ) echo $nav;

	} else {// no menu set
		$frenchpress->nav_position = "top";
	}

	/* BRANDING */
	// start building .site-branding.  Keep track of if anything is displayed so I can remove padding if not
	$site_branding_html = "";

	if ( !empty( $frenchpress->use_custom_code_for_branding ) && !empty( $frenchpress->branding_custom_code ) ) :

		$site_branding_html = "<div class='site-branding fffi fffi-9'>" . do_shortcode( $frenchpress->branding_custom_code ) . "</div>";

	else :

	if ( !empty( $frenchpress->logo ) ) {
		$logo = $frenchpress->logo;
		if ( substr( $logo, -4 ) === '.svg' ) {
			if ( false !== strpos( $logo, '//' ) ) {
				$logo = explode( '/', str_replace( '//', '', $logo ), 2 )[1];// strip root
			}
			// above would be wrong if site is not at root... below would work.
			// $logo = explode( '//', $logo );
			// $logo = !empty($logo[1]) ? str_replace( explode( '//', get_option('siteurl') )[1], '', $logo[1] ) : $logo[0];
			$logo = file_get_contents( ABSPATH . ltrim( $logo, '/') );
			if ( ! strpos( $logo, 'style=' ) ) $logo = str_replace( '<svg', '<svg style="display:block"', $logo );// TODO: this could handle stuff with styles...
		} else {
			$logo = "<img src='" . esc_attr( $logo ) . "'>";
		}
	} else {
		/**
		* Filter to insert whatever (SVG logos)
		* e.g.: add_filter( 'frenchpress_site_branding', function(){return file_get_contents( __DIR__ .'/logo.svg' );} );
		* This could be moved to 'get_custom_logo' filter as of v4.5...
		* it would run a little extra code checking for a custom logo first, and would let those take precedence
		*/
		$logo = apply_filters( 'frenchpress_site_branding', '' );
	}

	if ( $logo && false === strpos( $logo, "</a>" ) ) {
		// add home link if a link was not supplied at the filter.
		// get_custom_logo() adds class=custom-logo-link but I won't for now.
		$logo = "<a href='" . home_url() . "'>{$logo}</a>";
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

		$home_link = '<a href="'. home_url() .'">'. get_bloginfo( 'name' ) .'</a>';

		$site_branding_html .= is_front_page() ? "<h1 class='site-title{$hide}'>{$home_link}</h1>" : "<div class='site-title h2{$hide}'>{$home_link}</div>";

		$description = get_bloginfo( 'description', 'display' );

		if ( $description || is_customize_preview() ) {
			$site_branding_html .= "<p class='site-description{$hide}'>{$description}</p>";
		}

	}

	// dont even need the fff stuff if using no moblie menu
	if ( $site_branding_html ) {
		if ( empty( $frenchpress->branding_align ) || $frenchpress->branding_align === "left" ) $align = "";
		elseif ( $frenchpress->branding_align === "center" ) $align = " c";
		elseif ( $frenchpress->branding_align === "right" ) $align = " r";
		$pad = $logo || !$hide ? '' : ' pad-0';// TODO when is padding even applied? Might need padding on branding only rows.
		$fff =  $frenchpress->mobile_nav === "none" && $frenchpress->nav_position !== "right" ? '' : ' fffi fffi-auto';
		$site_branding_html = "<div class='site-branding{$fff}{$pad}{$align}'>{$site_branding_html}</div>";
		// TODO: also if centering, hamburger should have position: absolute; right: 12px;
	}

	endif;// branding vs custom code

	$header_main_classes = empty( $frenchpress->full_width_branding ) ? "tray" : "";
	if ( $site_branding_html ) {
		if ( $frenchpress->mobile_nav !== "none" || !empty( $frenchpress->add_custom_code_right_of_branding ) ) {
			$header_main_classes .= " fff fff-middle fff-spacebetween fff-pad";// fff-nowrap";
		} elseif ( $frenchpress->mobile_nav === "none" && $frenchpress->nav_position === "right" ) {// edge case...
			$header_main_classes .= " fff fff-middle fff-spacebetween fff-pad";
		}
	}
	$header_main_classes = apply_filters( 'frenchpress_class_header_main', $header_main_classes );

	echo "<div class=site-header-main><div class='{$header_main_classes}'>";

	echo $site_branding_html;

	if ( $frenchpress->nav_position === "right" ) echo $nav;

	elseif( !empty( $frenchpress->add_custom_code_right_of_branding ) ) echo "<div class=fffi>{$frenchpress->custom_code_right_of_branding}</div>";

	/**
	* Menu Drawer Button
	*/
	if ( empty( $frenchpress->mobile_nav ) || $frenchpress->mobile_nav === 'fullscreen' ) : ?>
		<div id=menu-open role=button aria-controls=main-menu class=fffi onclick="document.documentElement.classList.toggle('dopen')">
			<span id=menu-open-label class=screen-reader-text>Menu</span>
			<div class=menubun></div><div class=menubun></div><div class=menubun></div></div>
	<?php elseif ( in_array( $frenchpress->mobile_nav, ['slide','tree'] ) ) : ?>
		<div id=menu-open role=button aria-controls=main-menu aria-expanded=false class=fffi>
			<span id=menu-open-label class=screen-reader-text>Menu</span>
			<div class=menubun></div><div class=menubun></div><div class=menubun></div></div>
	<?php endif;

	echo '</div>';//.tray
echo '</div>';//.site-header-main

if ( $frenchpress->nav_position === "bottom" ) echo $nav;

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

echo "</header>";


if ( $frenchpress->layout === 'full-width' ) $content_class = "full-width";
elseif ( $frenchpress->layout === 'content-width' ) $content_class = "tray content-width";
else $content_class = "tray site-width";

$content_class = apply_filters( 'frenchpress_class_content', "site-content $content_class" );

echo "<div id=content class='{$content_class}'>";