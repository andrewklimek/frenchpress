<?php
/**
 * Misc functions that aren't really theme-related
 */


 /**
  * WP adds a style attribute with width to figures when using captions shortcode to contain long captions.
  * either live with adding "figure" to the list of things getting max-width:100% or use this weird filter:
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
 * This doesnt seem smart... makes it hard to remove more text when you want to.
 * Customize the [...] at the end of excerpts
 * but... when using the_excerpt(), manual excerpts don’t get the "more" text but auto-generated excerpts do... it’s weird.
 * So I am passing a blank string (or maybe ...) to the 'excerpt_more' filter and instead adding the more link via 'wp_trim_excerpt'
 */
// add_filter( 'excerpt_more', function(){ return '&hellip;'; } );
function frenchpress_excerpt_more( $excerpt ) {
	return $excerpt . sprintf( ' <a class=read-more href="%1$s">%2$s</a>',
		get_permalink( get_the_ID() ),
		'Continue reading <span class=meta-nav>&rarr;</span>'
	);
}
// add_filter( 'wp_trim_excerpt', 'frenchpress_excerpt_more' );


/**
 * Wrap the archive type in archive titles with a span so they can be hidden or styled
 * Examples:
 *   hide all:
 *	 span.archive-title-prefix {display: none;}
 * replace specific:
 *	 body.archive.author header.page-header h1::before {content: "All Posts By ";}
 *	 body.archive.author span.archive-title-prefix {display: none;}
 function wrap_archive_title_prefix( $title ){
	 $p = explode( ': ', $title, 2 );
	 if ( !empty( $p[1] ) ) {
		 $title = "<span class=archive-title-prefix>". $p[0] .": </span>". $p[1];
		}
		return $title;
	}
	add_filter( 'get_the_archive_title', 'wrap_archive_title_prefix' );
*/

// as of 5.5 you can modify the prefix with this hook
add_filter('get_the_archive_title_prefix','__return_false');




/**
 * Below are a waste of PHP processing if pages aren't cached,
 * so at least check for WP_CACHE which is generally defined at the top of wp-config
 */
if ( WP_CACHE ) :

/**
 * Post classes
 *
 * @param array $classes Classes for the body element.
 * @return array
 */
function frenchpress_post_classes( $classes ) {
	// Remove hAtom class.. not exactly worth it if not caching
	$classes = array_diff( $classes, ['hentry'] );

	return $classes;
}
add_filter( 'post_class', 'frenchpress_post_classes' );


endif;// WP_CACHE
