<?php

// disable error messages since they contain clues about if a username exists
add_filter( 'login_errors', function( $errors ){ return "Please try again"; } );

function frenchpress_temp_login_page(){
	// global $wp_query;//$wp_query->query['name']
	if ( is_404() && false !== strpos( $_SERVER['REQUEST_URI'], 'login' ) )
	{
		add_filter('pre_get_document_title', function(){ return get_bloginfo( 'name', 'display' ); }, 999);
		$a = [];
		$a['redirect'] = empty( $_REQUEST['redirect_to'] ) ? admin_url() : urlencode($_REQUEST['redirect_to']);
		// if ( !empty( $_REQUEST['redirect_to'] ) ) $a['redirect'] = urlencode( $_REQUEST['redirect_to'] );
		echo get_header();
		echo "<style>" . file_get_contents( TEMPLATEPATH . "/login.css" ) . "</style>";
		echo "<div id=login>";
		wp_login_form($a);
		echo '<a href="' . wp_lostpassword_url( get_permalink() ) . '" title="Lost Password">Lost Password</a></div>';
		echo "</div>";
		echo get_footer();
		exit;
	}
}
add_action( 'template_redirect', 'frenchpress_temp_login_page' );

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
