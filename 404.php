<?php
/**
* Sometimes you don't want this overly helpful 404 page.
* You can return some custom HTML on this filter, or return true to have a blank 404 page.
*
* "True" is passed by default if you have the "Discourage search engines from indexing this site"
* option ticked in WP "Reading" settings ( get_option( 'blog_public' ) == '0' )
* because it is assumed this is a private blog (like a portal) and you don't want the 404 suggestions.
*/
$short_circuit_404 = !empty( $GLOBALS['frenchpress']->boring_404 ) || get_option('blog_public') == '0';
$short_circuit_404 = apply_filters( 'frenchpress_short_circuit_404', $short_circuit_404 );

// Setup styles for a blank 404 page:
if ( $short_circuit_404 === true )
{
	// center it vertically by adding classes to the #content div
	add_filter( 'frenchpress_class_content', function($class){ return $class . " fff fff-middle"; } );
}

get_header();

?>
<main id=main class=site-main>
	<article><?php

	if ( $short_circuit_404 === true ) :

		// True was passed on the filter or this is a private blog.
		// just print a massive 404 and close the article
		echo "<h1 class=title style=font-size:15vh;text-align:center;opacity:.4>404</h1>";

	else :

		if ( ! apply_filters( 'frenchpress_title_in_header', false ) ) {

			echo "<header class=page-header><h1 class=title>Not Found</h1></header>";

		}

		echo '<div class=page-content>';

		if ( $short_circuit_404 ) :// already checked if this === true above, so now this must be actual html to print.

			echo $short_circuit_404;

		else :

			$phrase = esc_html( trim( $_SERVER['REQUEST_URI'], "/" ) );

			if ( isset( $_SERVER['HTTP_REFERER'] ) ) {

				echo '<p>Unfortunately, you clicked a broken link.';

			} else {

				echo "<p>The page “{$phrase}” does not exist on this site.&nbsp; Please double-check the spelling.";

			}



			$request = $GLOBALS['wp_query']->query;
			// $request = explode( '/', parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ) );
			$search = preg_replace('/\W+/', '+', urldecode( end( $request ) ) );

			$query = new WP_Query( array( 's' => $search, 'posts_per_page' => 10, 'nopaging' => true ) );

			if ( $query->have_posts() ) {

				echo "<h2>Search results for “{$phrase}”:</h2>";

				get_search_form();

				while ( $query->have_posts() ) {

					$query->the_post();

					get_template_part( 'template-parts/content', 'search' );

				}

				wp_reset_postdata();

			} else {

				get_search_form();
			}

		endif;

		echo "</div>";

	endif;

	echo "</article>";

do_action('frenchpress_main_bottom');

echo '</main>';

get_footer();
