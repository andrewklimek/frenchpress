<?php
/**
 * Called via get_header()
 *
 * If this is ever used for the general public or WPML,
 * html lang= should use language_attributes() or bloginfo('language') and maybe meta charset= bloginfo('charset')
 */
global $frenchpress;
if ( empty( $frenchpress->nav_position ) ) $frenchpress->nav_position = "right";
if ( empty( $frenchpress->nav_align ) ) $frenchpress->nav_align = $frenchpress->nav_position === "right" ? "right" : "center";

?><!doctype html>
<html lang=en class=dnav>
<meta name=viewport content="width=device-width, initial-scale=1">
<?php
wp_head();
?>
<body <?php body_class(); ?>>
<?php do_action( 'frenchpress_body_top' ); ?>
<a class="skip-link screen-reader-text" href=#content>Skip to content</a>
<header id=header class=site-header>
	<?php do_action( 'frenchpress_header_top' );


	$nav = [ 'theme_location' => 'main-menu', 'menu_id' => 'main-menu', 'container' => 0, 'echo' => 0 ];
	if ( $frenchpress->nav_align !== "left" ) {
		if ( $frenchpress->nav_align === "justified" ) $frenchpress->nav_align = "spacebetween";
		$nav['menu_class'] = "menu fff fff-" . $frenchpress->nav_align;
	}
	$nav = wp_nav_menu($nav);

	if ( !empty( $frenchpress->add_custom_code_right_of_menu ) || ( $frenchpress->nav_position === "right" && $frenchpress->nav_align !== "right" ) ) {
		$grow = "fffi fffi-9";
	} elseif ( $frenchpress->nav_position === "right" ) {// $frenchpress->nav_align === "right" implied.  I think this is right...
		$grow = "fffi";
	} else {
		$grow = "";
	}

	if ( in_array( $frenchpress->mobile_nav, ['slide','tree'] ) ) {
		$nav = "<div class=drawer><nav class=main-nav>$nav</nav></div>";
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

	if ( $header_url = get_header_image() ) {
		echo "<div id=header-image><a href='" . home_url() . "'><img src='{$header_url}' class=aligncenter></a></div>";// or should the class be tray?
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

	if ( $site_branding_html ) {
		
		$pad = $logo || !$hide ? '' : ' pad-0';
		
		$site_branding_html = "<div class='site-branding fffi{$pad}'>{$site_branding_html}</div>";
	}

	endif;// branding vs custom code

	$header_main_classes = empty( $frenchpress->full_width_branding ) ? "tray" : "";
	if ( $site_branding_html ) {
		if ( $frenchpress->mobile_nav !== "none" || $frenchpress->add_custom_code_right_of_branding ) {
			$header_main_classes .= " fff fff-middle fff-spacebetween fff-pad fff-nowrap";
		} elseif ( $frenchpress->mobile_nav === "none" && $frenchpress->nav_position === "right" ) {// edge case...
			$header_main_classes .= " fff fff-middle fff-spacebetween fff-pad";
		}
	}
	$header_main_classes = apply_filters( 'frenchpress_class_header_main', $header_main_classes );

	echo "<div id=site-header-main><div class='{$header_main_classes}'>";

	echo $site_branding_html;

	if ( $frenchpress->nav_position === "right" ) echo $nav;

	elseif( $frenchpress->add_custom_code_right_of_branding ) echo "<div class=fffi>{$frenchpress->custom_code_right_of_branding}</div>";

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

?>
</header>
<div id=content class="<?php echo apply_filters( 'frenchpress_class_content', "site-content" ); ?>">
	<div class="content-tray <?php echo ( apply_filters( 'frenchpress_full_width', false ) ) ? "tray--full-width " : "tray "; echo apply_filters( 'frenchpress_class_content_tray', "fff fff-spacearound fff-magic" ); ?>">
