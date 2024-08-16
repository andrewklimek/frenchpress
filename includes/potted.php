<?php

// disable error messages since they contain clues about if a username exists
add_filter( 'login_errors', function( $errors ){ return "Please try again"; } );

function frenchpress_temp_login_page(){
	// global $wp_query;//$wp_query->query['name']
	if ( is_404() && false !== strpos( $_SERVER['REQUEST_URI'], 'login' ) )
	{
		$a = [];
		// I think I need to get this $_REQUEST['redirect_to'] ? But don't need to do the admin_url or filter?
		$redirect = isset( $_REQUEST['redirect_to'] ) && is_string( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : '';
		// $redirect = $requested_redirect_to ?: admin_url();
		// apply_filters( 'login_redirect', $redirect, $requested_redirect_to );
		// $a['redirect'] = urlencode( $redirect );
		if ( $redirect && ! strpos( $redirect, '://' ) ) {
			$redirect = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . '/' . ltrim( $redirect, '/' );
		}
		$a['redirect'] = $redirect;
		// if ( !empty( $_REQUEST['redirect_to'] ) ) $a['redirect'] = urlencode( $_REQUEST['redirect_to'] );
		$header_footer = !empty( $GLOBALS['frenchpress']->header_footer_on_login );
		if ( $header_footer ) {
			add_filter('pre_get_document_title', function(){ return get_bloginfo( 'name', 'display' ); }, 999);
			add_filter( 'frenchpress_title_in_header', '__return_false', 999 );
			echo get_header();
			echo "<style>" . file_get_contents( TEMPLATEPATH . "/login.css" ) . "</style>";
			echo "<div id=login>";
		} else {
			echo '<!doctype html><html lang=en><meta name=viewport content="width=device-width, initial-scale=1">';
			$css = file_get_contents( TEMPLATEPATH . '/style.css' );
			if ( TEMPLATEPATH !== STYLESHEETPATH ) $css .= file_get_contents( STYLESHEETPATH . '/style.css' );
			$css .= file_get_contents( TEMPLATEPATH . "/login.css" );
			if ( TEMPLATEPATH !== STYLESHEETPATH ) $css .= file_get_contents( STYLESHEETPATH . '/login.css' );
			echo "<style>" . frenchpress_minify_css( $css ) . "#login{margin-bottom:99px}</style>";
			// do_action('login_head');
			echo "<body class='fff-center'>";// a way to make it feel centered, maybe not the best way.
			echo "<div id=login>";
			if ( $icon = get_site_icon_url( 192 ) ) {
				echo "<p class=c><img width=96 height=96 src='{$icon}' class='margin:'>";
			}
		}
		wp_login_form($a);

		// code from wp_lostpassword_url() inlined here to avoid plugins changing the Lost Password link, like WooCommerce
		$args = [ 'action' => 'lostpassword' ];
		// $args['redirect_to'] = urlencode( get_permalink() );// this doesnt work on the fake login page, no permalink. Not sure what purpose it serves.
		$wp_login_path = 'wp-login.php';
		if ( is_multisite() ) {
			$blog_details = get_site();
			$wp_login_path = $blog_details->path . $wp_login_path;
		}
		$lostpassword_url = add_query_arg( $args, network_site_url( $wp_login_path, 'login' ) );

		echo "<a href='{$lostpassword_url}' title='Lost Password'>Lost password</a>";
		echo "</div>";
		if ( $header_footer ) {
			echo get_footer();
		}
		exit;
	}
}
add_action( 'template_redirect', 'frenchpress_temp_login_page', 9 );

function frenchpress_login_redirect() {

	if ( !empty( $_REQUEST['interim-login'] ) ) return;// don't mess with the modal that pops up when someone's session expires

	// dont redirect actual login requests!
	if ('POST' === $_SERVER['REQUEST_METHOD'])
	{
		// Interesting chance to detect for spam logins. Too bad "WP Cookie Check" already ran, so spammers may know it's a WP install
		if ( empty( $_SERVER['HTTP_REFERER'] ) || $_SERVER['HTTP_HOST'] !== parse_url( $_SERVER['HTTP_REFERER'], PHP_URL_HOST ) )
		{
			// error_log( "Spam login attempted from {$_SERVER['REMOTE_ADDR']} (referer: {$_SERVER['HTTP_REFERER']})" );
			header( "{$_SERVER['SERVER_PROTOCOL']} 404 Not Found" );
			exit;
		}
		return;
	}

	$url = site_url('login');// the custom login page

	if ( ! empty( $_REQUEST['redirect_to'] ) ) {
		$url = add_query_arg( 'redirect_to', urlencode($_REQUEST['redirect_to']), $url );
	}

	wp_redirect( $url );
	exit;
}
if ( empty( $GLOBALS['frenchpress']->dont_redirect_wplogin ) ) {
	add_action( 'login_form_login', 'frenchpress_login_redirect' );
}
