<?php
/**
 * Disable various core embellishments you may not want (emoji, capital P, archive type in page title)
 *
 * Also a plugin, so versioning: 1.5
 */

/**
 * Remove crazy preset colors, not using site editor.
 * This should possibly only be done if "Classic Editor" is installed or some other test.
 * https://github.com/WordPress/WordPress/blob/8e29ebbc1663e278cff24ff985dbe5a7724d386d/wp-includes/default-filters.php#L574
 */
remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
remove_action( 'wp_footer', 'wp_enqueue_global_styles', 1 );


 /**
  * Remove the default WP logo favicon
  * see https://github.com/WordPress/WordPress/blob/270f2011f8ec7265c3f4ddce39c77ef5b496ed1c/wp-includes/functions.php#L1694
  */
 add_filter( 'get_site_icon_url', function($url) { return false === strpos( $url, 'w-logo-blue' ) ?  $url : ''; } );

 

 /**
  * Disable xml sitemap added in WP 5.5.  If you want it, see below example to remove the user sitemap at least.
  */
add_filter( 'wp_sitemaps_enabled', '__return_false' );
// to just remove the user  sitemap:
// add_filter( 'wp_sitemaps_add_provider', function($prov,$name){ return 'users' === $name ? false : $prov; }, 10, 2 );

/**
 * Remove link to Windows Live Writer manifest file <link rel="wlwmanifest" type="application/wlwmanifest+xml">
 */
remove_action( 'wp_head', 'wlwmanifest_link' );

/**
 * Remove link to comments feed <link rel="alternate" type="application/rss+xml">
 */
add_filter( 'feed_links_show_comments_feed', function(){ return false; } );

/**
 * Remove <meta name="generator" content="WordPress {version}">
 */
// add_filter('get_the_generator_xhtml', function(){ return ''; } );
// use the above filter if this messes up rss or other types.
remove_action( 'wp_head', 'wp_generator' );

/**
 * Remove big un-used css from front end added by WP 5 for the block editor
 */
function disable_gutenberg_block_css() {
	wp_dequeue_style( 'wp-block-library' );
}
add_action( 'wp_enqueue_scripts', 'disable_gutenberg_block_css', 999 );


/**
 * Replace "Powered by Wordpress" H1 on login page
 */
add_filter('login_headerurl', function(){ return home_url(); });
add_filter('login_headertext', function(){ return get_bloginfo( 'name', 'display' ); });



/**
 * System emails sent from admin email & blog name rather than wordpress@ and WordPress
 */
add_filter('wp_mail_from', function($email){
	// if( substr($email,0,10) === 'wordpress@')
		// $email = get_option('admin_email');// this isn't really a good idea. it might not be at this domain, at it may not be desired to be public
	$email = str_replace( 'wordpress@', 'webmaster@', $email );
	return $email;
}, 99);
add_filter('wp_mail_from_name', function($name){
	if($name === 'WordPress')
		$name = str_replace( '&#039;', "'", get_option('blogname') );
	return $name;
}, 99);


/**
 * Disable capital P
 */

foreach ( array( 'the_content', 'the_title', 'wp_title', 'comment_text', 'widget_text_content' ) as $filter ) {
	$priority = has_filter( $filter, 'capital_P_dangit' );
	if ( $priority !== FALSE ) {
		remove_filter( $filter, 'capital_P_dangit', $priority );
	}
}

/**
 * Disable the emoji's
 */
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'embed_head', 'print_emoji_detection_script' );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'admin_print_styles', 'print_emoji_styles' );
remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
add_filter( 'emoji_svg_url', function(){ return false; } );


/**
 * Disable smilies
 */
remove_action( 'init', 'smilies_init', 5 );
foreach ( array( 'the_content', 'the_excerpt', 'the_post_thumbnail_caption', 'comment_text' ) as $filter ) {
	$priority = has_filter( $filter, 'convert_smilies' );
	if ( $priority !== FALSE ) {
		remove_filter( $filter, 'convert_smilies', $priority );
	}
}
// This might be the better way to do it, but only in plugin context
// register_activation_hook( __FILE__, function(){ update_option( 'use_smilies', false ); } );
// register_deactivation_hook( __FILE__, function(){ update_option( 'use_smilies', true ); } );


/**
 * Disable auto <p> insertion
 */

//remove_filter( 'the_content', 'wpautop' );
//remove_filter( 'the_excerpt', 'wpautop' );


// remove wp-embed
function mnml_disable_embeds_code_init() {

// Remove the REST API endpoint.
remove_action( 'rest_api_init', 'wp_oembed_register_route' );

// Turn off oEmbed auto discovery.
add_filter( 'embed_oembed_discover', function(){ return false; } );

// Don't filter oEmbed results.
remove_filter( 'oembed_dataparse', 'wp_filter_oembed_result', 10 );

// Remove oEmbed discovery links.
remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );

// Remove oEmbed-specific JavaScript from the front-end and back-end.
remove_action( 'wp_head', 'wp_oembed_add_host_js' );
// add_filter( 'tiny_mce_plugins', 'disable_embeds_tiny_mce_plugin' );

// Remove all embeds rewrite rules.
// add_filter( 'rewrite_rules_array', 'disable_embeds_rewrites' );

// Remove filter of the oEmbed result before any HTTP requests are made.
remove_filter( 'pre_oembed_result', 'wp_filter_pre_oembed_result', 10 );
}
add_action( 'init', 'mnml_disable_embeds_code_init', 9999 );
