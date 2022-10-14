<?php
/**
 * Misc functions that aren't really theme-related
 */



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

