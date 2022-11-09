<?php
/**
 * Called via get_sidebar()
 */
$sidebar = apply_filters( 'frenchpress_custom_sidebar', '' );

global $frenchpress;

if ( $sidebar ) {
	echo '<aside id=side class="widget-area sidebar">';
	echo $sidebar;
	echo '</aside>';
} else {
	if ( is_active_sidebar( 1 ) ) {
		echo '<aside id=side class="widget-area sidebar">';
		dynamic_sidebar( 1 );
		echo '</aside>';
	} else {
		return;
	}
}

/**
 * Set up custom style
 */
$style = "";

$sidebar_width = $frenchpress->sidebar_width;
$content_width = $frenchpress->content_width;
$site_width = $frenchpress->site_width;

$site_width_override = "";
if ( !empty( $frenchpress->sidebar_centered_content ) ) {

	if ( $sidebar_width && $content_width ) {
		$site_width = 2 * $sidebar_width + $content_width;
		$site_width_override = "max-width:{$site_width}px;";
	} elseif ( $sidebar_width && $site_width ) {
		$content_width = $site_width - 2 * $sidebar_width;
	} elseif ( $content_width && $site_width ) {
		$sidebar_width = ( $site_width - $content_width ) / 2;
	}
}

if ( $frenchpress->sidebar_position_mobile !== "top" ) {
	$style .= "@media(min-width:783px){";
}
$style .= "#content{display:flex;{$site_width_override}";
if ( $frenchpress->sidebar_position_desktop === "left" ) {
	$style .= "flex-direction:row-reverse;";
}
if ( !empty( $frenchpress->sidebar_centered_content ) ) {
	$style .= "justify-content:flex-end;";
} elseif ( $content_width ) {
	$style .= "justify-content:space-between;";
}
if ( $content_width ) {
	$style .= "} #main { width:{$content_width}px;";
}
$style .= "} #side { flex:0 0 {$sidebar_width}px; }";
if ( $frenchpress->sidebar_position_mobile === "top" ) {
	$style .= "@media(max-width:782px) {"
		. "#content{ flex-direction: column-reverse;";
	if ( $content_width ) {
		$style .= "width:{$content_width}px;";
	}
	$style .= "}";
}
$style .= "}";

// Need to adjust padding for this layout... not sure if there's a more clever way of integratign above b/c of the media queries
$style .= "#content{padding:0}#main,#side{padding:0 24px}";

frenchpress_add_inline_style( $style );