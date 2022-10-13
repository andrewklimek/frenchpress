<?php

$frenchpress = (object) get_option('frenchpress');
// could add a filter to this global on the 'wp' action hook, (when conditional tags are ready) or just at the top of header.php


/* this is define by core for now as TEMPLATEPATH, along with STYLESHEETPATH. hopefully they dont remove it. see https://core.trac.wordpress.org/ticket/18298 */
// define( 'TEMPLATE_DIR', get_template_directory() );
define( 'TEMPLATE_DIR_U', get_template_directory_uri() );

if(!function_exists('poo')){function poo( $var, $note='', $file='_debug.txt', $time='m-d H:i:s' ){
	// if(true===WP_DEBUG_LOG)
	if ( $note ) $note = "***{$note}***\n";
	file_put_contents(WP_CONTENT_DIR ."/". $file, "\n[". date($time) ."] ". $note . var_export($var,true), FILE_APPEND);
}}

/**
 * Enqueue scripts and styles.
 */
add_action( 'wp_enqueue_scripts', 'frenchpress_scripts' );
function frenchpress_scripts() {

	global $frenchpress;
	if ( empty( $frenchpress->mobile_nav ) ) $frenchpress->mobile_nav = 'fullscreen';

	if ( SCRIPT_DEBUG )
	{
		wp_enqueue_style( 'frenchpress', TEMPLATE_DIR_U.'/style.css', null, filemtime( TEMPLATEPATH . '/style.css' ) );

		if ( $frenchpress->mobile_nav === 'fullscreen' )
		{
			wp_enqueue_style( 'frenchpress-menu', TEMPLATE_DIR_U."/a/overlay-menu.css", null, filemtime( TEMPLATEPATH."/a/overlay-menu.css" ) );
		}
		elseif ( in_array( $frenchpress->mobile_nav, ['slide','tree'] ) )
		{
			$layout = frenchpress_has_submenus() ? "sub-" . $frenchpress->mobile_nav : "drawer";
			wp_enqueue_script( 'frenchpress-menu', TEMPLATE_DIR_U."/a/{$layout}.js", null, filemtime( TEMPLATEPATH."/a/{$layout}.js" ), true );
			wp_enqueue_style( 'frenchpress-menu', TEMPLATE_DIR_U."/a/{$layout}.css", null, filemtime( TEMPLATEPATH."/a/{$layout}.css" ) );
		}

		// lastly add child styles, if child theme active
		if ( TEMPLATEPATH !== STYLESHEETPATH )
			wp_enqueue_style( 'frenchpress-child', get_stylesheet_uri(), null, filemtime( STYLESHEETPATH . '/style.css' ) );
	}
	else
	{
		if ( empty( $frenchpress->inline_css ) )
			wp_enqueue_style( 'frenchpress', TEMPLATE_DIR_U.'/m.css', null, filemtime( TEMPLATEPATH."/m.css" ) );
		else
			add_action( 'wp_print_styles', 'frenchpress_inline_css' );

		if ( $frenchpress->mobile_nav === 'fullscreen' )
		{
			if ( empty( $frenchpress->inline_css ) )
				wp_enqueue_style( 'frenchpress-menu', TEMPLATE_DIR_U."/a/overlay-menu.css", null, filemtime( TEMPLATEPATH."/a/overlay-menu.css" ) );
		}
		elseif ( in_array( $frenchpress->mobile_nav, ['slide','tree'] ) )
		{
			add_action('wp_print_scripts','frenchpress_print_script');

			if ( empty( $frenchpress->inline_css ) ) {
				$layout = frenchpress_has_submenus() ? "sub-" . $frenchpress->mobile_nav : "drawer";
				wp_enqueue_style( 'frenchpress-menu', TEMPLATE_DIR_U."/a/{$layout}.css", null, filemtime( TEMPLATEPATH."/a/{$layout}.css" ) );
			}
		}

		// lastly add child styles, if child theme active
		if ( empty( $frenchpress->inline_css ) ) {
			if ( TEMPLATEPATH !== STYLESHEETPATH )
				wp_enqueue_style( 'frenchpress-child', get_stylesheet_uri(), null, filemtime( STYLESHEETPATH . '/style.css' ) );
		}
	}

	// set max width based on $GLOBALS['content_width'] which can be set on the options page and defaults to 1080
	wp_add_inline_style('frenchpress',".tray{max-width:". 24 * 2 + $GLOBALS['content_width'] ."px}");// tray margin is hardcoaded at 24px for now.

	// wp_enqueue_style( 'frenchpress-print',  TEMPLATE_DIR_U.'/print.css', null, null, 'print' );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) wp_enqueue_script( 'comment-reply' );

}


add_filter('script_loader_tag', function($tag, $handle) {
	return ( 0 !== strpos( $handle, 'frenchpress' ) ) ? $tag : str_replace( ' src', ' defer src', $tag );
}, 10, 2);

function frenchpress_print_script(){
	$layout = frenchpress_has_submenus() ? "sub-" . $GLOBALS['frenchpress']->mobile_nav : "drawer";
	$ver = "?var=" . filemtime( TEMPLATEPATH . "/a/{$layout}.min.js" );
	// echo "<script src='". TEMPLATE_DIR_U . "/a/{$layout}.min.js{$ver}' async></script>";
	echo "<script src='/wp-content/themes/frenchpress/a/{$layout}.min.js{$ver}' async></script>";
}


/***
* Inline & minify CSS
*/
function frenchpress_inline_css() {
	// get parent styles
	$css = file_get_contents( TEMPLATEPATH . '/style.css' );

	/* Remove specific Blocks from CSS
	$remove_blocks = [ "DRAWER", "SUBMENU" ];
	foreach ( $remove_blocks as $block ) {
		$css = preg_replace("|\/\* {$block} \*\/[\s\S]+?\/\* END {$block} \*\/|", "", $css );
	}
	*/

	// set max width based on $GLOBALS['content_width'] which can be set on the options page and defaults to 1080
	$css = str_replace( '/*max-width placeholder*/', "max-width:{$GLOBALS['content_width']}px;", $css );

	// extra CSS for drawers & submenus
	if ( empty( $GLOBALS['frenchpress']->mobile_nav ) || $GLOBALS['frenchpress']->mobile_nav === 'fullscreen' )
	{
		$css .= file_get_contents( TEMPLATEPATH . "/a/overlay-menu.css" );
	}
	elseif ( in_array( $GLOBALS['frenchpress']->mobile_nav, ['slide','tree'] ) )
	{
		$layout = frenchpress_has_submenus() ? "sub-" . $GLOBALS['frenchpress']->mobile_nav : "drawer";
		$css .= file_get_contents( TEMPLATEPATH . "/a/{$layout}.css" );
	}

	// append child styles, if child theme active
	if ( TEMPLATEPATH !== STYLESHEETPATH ) $css .= file_get_contents( STYLESHEETPATH . '/style.css' );

	$css = frenchpress_minify_css( $css );

	echo "<style>{$css}</style>";
}

function frenchpress_minify_css( $css ) {
	// remove comments (preg_replace) and spaces (str_replace)
	return str_replace(
		["\r","\n","\t",'   ','  ',': ','; ',', ',' {','{ ',' }','} ',';}'],
		[  '',  '',  '',   '', ' ', ':', ';', ',', '{', '{', '}', '}', '}'],
		preg_replace('|\/\*[\s\S]*?\*\/|','',$css)
	);
}


/**
 * Style Login to match theme
 */
if ( empty( $GLOBALS['frenchpress']->dont_style_login ) ) {
	add_action( 'login_enqueue_scripts', function() {
		wp_dequeue_style( 'login' );
		if ( function_exists( 'frenchpress_child_enqueue_scripts' ) ) {
			frenchpress_child_enqueue_scripts();
		}
		wp_enqueue_style( 'theme', get_stylesheet_uri(), array(), null );
		wp_enqueue_style( 'frenchpress-login', TEMPLATE_DIR_U . '/login.css', array(), null );
	} );
}


/**
 * Set the content width in pixels
 */
function frenchpress_content_width() {
	
	$GLOBALS['content_width'] = !empty($GLOBALS['frenchpress']->content_width) ? $GLOBALS['frenchpress']->content_width : 1200;
}
add_action( 'after_setup_theme', 'frenchpress_content_width', 0 );// 0 to make it available to lower priority callbacks


function frenchpress_mobile_test() {
	$breakpoint = isset( $GLOBALS['frenchpress']->menu_breakpoint ) ? $GLOBALS['frenchpress']->menu_breakpoint : 860;
	if ( ! $breakpoint ) return;
	echo "<script>(function(){var c=document.documentElement.classList;";
	echo "function f(){if(!window.innerWidth){setTimeout(f,50)}else if(window.innerWidth>{$breakpoint}){c.remove('mnav');c.remove('dopen');c.add('dnav');}else{c.remove('dnav');c.add('mnav');}}";
	echo "f();window.addEventListener('resize',f);})();</script>";
}
// if ( ! apply_filters( 'frenchpress_disable_mobile', false ) ) {
add_action( 'wp_print_scripts', 'frenchpress_mobile_test' );
// }


/**
 * Sets up theme defaults and registers support for various WordPress features.
 * Note that this function is hooked into the after_setup_theme hook, which runs before the init hook. 
 * The init hook is too late for some features, such as indicating support for post thumbnails.
 */
if ( ! function_exists( 'frenchpress_setup' ) ) :
function frenchpress_setup() {

	// load_theme_textdomain( 'frenchpress', TEMPLATEPATH . '/languages' );

	// add_theme_support( 'automatic-feed-links' );// Add default posts and comments RSS feed links to head.

	add_theme_support( 'title-tag' );// this theme does not use a hard-coded <title> tag; let WP generate it

	add_theme_support( 'post-thumbnails' );
	// sually you need to add a size like so: set_post_thumbnail_size( '850', '850' );
	// Might make option for this. For now, use the large size for feat images unless child theme specifies one. There's also a "medium_large" that's 768. could change get_option('medium_large_size_w');
	add_filter( 'post_thumbnail_size', function($size){ return 'post-thumbnail' !== $size || has_image_size($size) ? $size : 'large'; } );

	// finally removes type attribute from script and styles: https://make.wordpress.org/core/2019/10/15/miscellaneous-developer-focused-changes-in-5-3/
	add_theme_support( 'html5', ['comment-list','comment-form','search-form','gallery','caption','style','script','navigation-widgets'] );

	// Enable support for Post Formats. https://developer.wordpress.org/themes/functionality/post-formats/
	// add_theme_support( 'post-formats', ['chat','aside','gallery','image','video','audio','quote','link','status'] );

	// Set up the WordPress core custom background feature.
	add_theme_support( 'custom-background', ['default-color' => 'FFFDF8', 'default-image' => ''] );
	
	add_theme_support( 'custom-logo', [ 'header-text' => ['site-title', 'site-description'] ] );

	remove_theme_support( 'widgets-block-editor' );// https://developer.wordpress.org/block-editor/how-to-guides/widgets/opting-out/

	add_editor_style();// Styles the visual editor. https://developer.wordpress.org/reference/functions/add_editor_style/

	register_nav_menus([ 'main-menu' => 'Primary' ]);
}
endif;
add_action( 'after_setup_theme', 'frenchpress_setup' );


/**
 * Register widget areas
 */
function frenchpress_widgets_init() {
	register_sidebar([
		'name'          => 'Sidebar 1',
		'id'            => 'sidebar-1',
		'before_widget' => '<section id="%1$s" class="widget sidebar-widget %2$s">',
		'after_widget'  => "</section>\n",
		'before_title'  => '<h3 class=widgettitle>',
		'after_title'   => "</h3>\n",
	]);
	register_sidebar([
		'name'          => 'Sidebar 2',
		'id'            => 'sidebar-2',
		'before_widget' => '<section id="%1$s" class="widget sidebar-widget %2$s">',
		'after_widget'  => "</section>\n",
		'before_title'  => '<h3 class=widgettitle>',
		'after_title'   => "</h3>\n",
	]);
	register_sidebar([
		'name'          => 'Top of Footer',
		'id'            => 'footer-top',
		'description'   => 'For some banner between content and footer',
		'before_widget' => '',
		'after_widget'  => "",
		'before_title'  => '<h3 class=widgettitle>',
		'after_title'   => "</h3>\n",
	]);
	register_sidebar([
		'name'          => 'Footer 1',
		'id'            => 'footer-1',
		'before_widget' => '<aside id="%1$s" class="widget footer-widget %2$s">',
		'after_widget'  => "</aside>\n",
		'before_title'  => '<h3 class=widgettitle>',
		'after_title'   => "</h3>\n",
	]);
	register_sidebar([
		'name'          => 'Footer 2',
		'id'            => 'footer-2',
		'before_widget' => '<aside id="%1$s" class="widget footer-widget %2$s">',
		'after_widget'  => "</aside>\n",
		'before_title'  => '<h3 class=widgettitle>',
		'after_title'   => "</h3>\n",
	]);
	register_sidebar([
		'name'          => 'Footer 3',
		'id'            => 'footer-3',
		'before_widget' => '<aside id="%1$s" class="widget footer-widget %2$s">',
		'after_widget'  => "</aside>\n",
		'before_title'  => '<h3 class=widgettitle>',
		'after_title'   => "</h3>\n",
	]);
	register_sidebar([
		'name'          => 'Footer 4',
		'id'            => 'footer-4',
		'before_widget' => '<aside id="%1$s" class="widget footer-widget %2$s">',
		'after_widget'  => "</aside>\n",
		'before_title'  => '<h3 class=widgettitle>',
		'after_title'   => "</h3>\n",
	]);
	register_sidebar([
		'name'          => 'Bottom of Footer',
		'id'            => 'footer-bottom',
		'description'   => 'Typical place for copyright, theme info, etc. Shortcode [current_year] is available for copyrights.',
		'before_widget' => '<aside id="%1$s" class="widget footer-widget fffi %2$s">',
		'after_widget'  => "</aside>\n",
		'before_title'  => '<h3 class=widgettitle>',
		'after_title'   => "</h3>\n",
	]);
}
add_action( 'widgets_init', 'frenchpress_widgets_init' );


/**
 * INCLUDES
 */
require TEMPLATEPATH . '/includes/potted.php';

/**
 * Settings Page
 */
require TEMPLATEPATH . '/includes/options.php';

/**
 * [frenchpress] builder-style shortcode
 */
require TEMPLATEPATH . '/includes/shortcodes.php';

/**
 * Remove core bull
 */
if ( ! function_exists( 'mnml_disable_embeds_code_init' ) ) {
	require TEMPLATEPATH . '/includes/disembellish.php';
}

/**
 * Side Menu Drawer
 */
if ( empty( $GLOBALS['frenchpress']->mobile_nav ) || $GLOBALS['frenchpress']->mobile_nav === 'fullscreen' ) {
	add_action('wp_before_admin_bar_render',function(){echo '<style>.mnav #main-menu{padding-top:32px!important} @media(max-width:782px){.mnav #main-menu{padding-top:46px!important}}</style>';});
} elseif ( $GLOBALS['frenchpress']->mobile_nav === 'none' ) {
	// this won't be the long-term solution I'm sure.
	// This is just for sites with no drawer and might even be better defined in child theme to the exact pixel width.
	add_action('wp_print_styles',function(){echo '<style>.mnav .site-header .menu-item > a{padding:12px}</style>';});
} else {//if ( in_array( $GLOBALS['frenchpress']->mobile_nav, ['slide','tree'] ) ) {
	// require TEMPLATEPATH . '/includes/drawer.php';
	add_action('wp_before_admin_bar_render',function(){echo '<style>.mnav .drawer,.desk-drawer{padding-top:32px!important} @media(max-width:782px){.mnav .drawer{padding-top:46px!important}}</style>';});
}

/**
 * Implement the Custom Header feature... only because it gives the checkbox to hide tite title and description.  Images won't even be displayed atm.
 */
require TEMPLATEPATH . '/includes/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require TEMPLATEPATH . '/includes/template-tags.php';

/**
 * HTML5 Cleanup and various other goodies
 */
require TEMPLATEPATH . '/includes/extras.php';

/**
 * Customizer additions.
 */
require TEMPLATEPATH . '/includes/customizer.php';

/**
 * Custom walker with no <li>.
 */
// require TEMPLATEPATH . '/includes/walker-no-list.php';
// use it:
// function frenchpress_walker($args){
// 	$args['walker'] = new Walker_No_List;
// 	$args['items_wrap'] = '%3$s';
// 	return $args;
// }
// add_filter('wp_nav_menu_args','frenchpress_walker');


add_action('wp_update_nav_menu', 'frenchpress_check_submenu_on_menu_update', 10, 2 );

function frenchpress_check_submenu_on_menu_update( $menu_id, $data='' ){
	// this hook runs twice on save, one time $data isn't passed
	if ( !$data || empty( $_POST['menu-locations']['main-menu'] ) ) return;
	
	if( empty( $_POST['menu-item-parent-id'] ) ) {//          parent info is missing? Just delete theme mod so it can be set on next page load
		remove_theme_mod('has_submenus');
	} elseif( array_filter($_POST['menu-item-parent-id']) ) {// there is a menu item with a parent. Set theme mod that we have submenus
		set_theme_mod('has_submenus', 1 );
	} else {//                                                    there are no menu item with a parent. Set theme mod that we don't have submenus
		set_theme_mod('has_submenus', 0 );
	}
}

function frenchpress_has_submenus() {
	$mods = get_theme_mods();

	if ( isset( $mods['has_submenus'] ) ) return $mods['has_submenus'];

	poo("finding if it has submenus");

	if ( empty( $mods['nav_menu_locations']['main-menu'] ) ) return false;

	$has_submenus = 0;
	$menu_items = wp_get_nav_menu_items( $mods['nav_menu_locations']['main-menu'] );
	foreach( $menu_items as $item ) {
		if ( $item->menu_item_parent > 0 ) {
			$has_submenus = 1;
			break;
		}
	}
	set_theme_mod('has_submenus', $has_submenus );

	poo($has_submenus);
	
	return $has_submenus;
}