<?php
/**
 * CSS that only needs to be inserted for certain templates and shortcodes
 */


/**
 * WP adds a style attribute with width to figures when using captions shortcode to contain long captions.
 * either live with adding "figure" or ".wp-caption" to the list of things getting max-width:100% or use this weird filter:
 * https://core.trac.wordpress.org/ticket/49601
 */
add_filter( 'img_caption_shortcode_width', 'frenchpress_img_caption_width_to_max_width' );
function frenchpress_img_caption_width_to_max_width( $width ){
	add_filter('do_shortcode_tag', function( $out, $tag ) {
		if ( $tag === 'caption' || $tag === 'wp_caption' )
			$out = str_replace( 'style="width', 'style="max-width', $out );
		return $out;
	}, 10, 2 );
	return $width;
}

/**
* Add CSS to [gallery] shortcodes, and the bloat out of style.css
* There are actually core filters for using and modifying inline default CSS
*/
function add_gallery_styling( $style_and_div ) {

	$css = frenchpress_minify_css( "<style>
.gallery-item {
	margin: 0;
	text-align: center;
	vertical-align: top;
	display: inline-block;
	width: 50%;
}
.gallery-columns-1 .gallery-item {width: 100%;}
@media (min-width:600px){
	.gallery-columns-3 .gallery-item, .gallery-columns-6 .gallery-item, .gallery-columns-9 .gallery-item, .gallery-item {width: 33.333%;}
	.gallery-columns-4 .gallery-item {width: 50%;}
}
@media (min-width:800px){
	.gallery-columns-4 .gallery-item, .gallery-item {width: 25%;}
}
@media (min-width:1200px){
	.gallery-columns-5 .gallery-item {width: 20%;}
	.gallery-columns-6 .gallery-item, .gallery-item {width: 16.667%;}
}
@media (min-width:1600px){
	.gallery-columns-7 .gallery-item {width: 14.286%;}
	.gallery-columns-8 .gallery-item {width: 12.5%;}
	.gallery-columns-9 .gallery-item {width: 11.111%;}
}
</style>" );
	return  $css . $style_and_div;
}
add_filter( 'gallery_style', 'add_gallery_styling' );
