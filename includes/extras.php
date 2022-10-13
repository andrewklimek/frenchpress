<?php
/**
 * Misc functions that aren't really theme-related
 */


 /**
  * WP adds a style attribute with width to figures when using captions shortcode to contain long captions.
  * either live with adding "figure" to the list of things getting max-width:100% or use this weird filter:
  *
  * should be changed in core shouldnt it? https://github.com/WordPress/WordPress/blob/c488a71e1196594088723a5753ed886a6b84d017/wp-includes/media.php#L2289
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
 * Below are a waste of PHP processing if pages aren't cached,
 * so at least check for WP_CACHE which is generally defined at the top of wp-config
 */
if ( WP_CACHE ) :

	// Remove hAtom class.. not exactly worth it if not caching
	add_filter( 'post_class', function($classes){ return array_diff( $classes, ['hentry'] ); } );
	
endif;// WP_CACHE


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
add_filter('get_the_archive_title_prefix','__return_false');// SHOULD BE AN OPTION



/**
 * Below are a waste of PHP processing if pages aren't cached,
 * so at least check for WP_CACHE which is generally defined at the top of wp-config
 */
if ( WP_CACHE ) :

	// Remove hAtom class.. not exactly worth it if not caching
	add_filter( 'post_class', function($classes){ return array_diff( $classes, ['hentry'] ); } );
		
endif;// WP_CACHE