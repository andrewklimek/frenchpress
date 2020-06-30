<?php

/* this is define by core for now as TEMPLATEPATH hopefully the dont remove it, see https://core.trac.wordpress.org/ticket/18298 */
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
function frenchpress_scripts() {

	if ( SCRIPT_DEBUG )
	{
		wp_enqueue_style( 'p', TEMPLATE_DIR_U.'/style.css', null, filemtime( TEMPLATEPATH . '/style.css' ) );

		if ( apply_filters( 'frenchpress_drawer', true ) )
		{
			// wp_enqueue_script( 'f', TEMPLATE_DIR_U.'/js/drawer.js', null, filemtime( TEMPLATEPATH.'/a/drawer.js' ), true );

			// which submenu will we use if needed?
			global $frenchpress_drawer;
			wp_enqueue_script( 'f', TEMPLATE_DIR_U."/a/{$frenchpress_drawer['layout']}.js", null, filemtime( TEMPLATEPATH."/a/{$frenchpress_drawer['layout']}.js" ), true );
			wp_enqueue_style( 'f', TEMPLATE_DIR_U."/a/{$frenchpress_drawer['layout']}.css", null, filemtime( TEMPLATEPATH."/a/{$frenchpress_drawer['layout']}.css" ) );
		}

		// lastly add child styles, if child theme active
		if ( TEMPLATEPATH !== STYLESHEETPATH )
			wp_enqueue_style( 'c', get_stylesheet_uri(), null, filemtime( STYLESHEETPATH . '/style.css' ) );
	}
	else
	{
		if ( apply_filters( 'frenchpress_inline_css', true ) )
			add_action( 'wp_print_styles', 'frenchpress_inline_css' );
		else
			wp_enqueue_style( 'p', TEMPLATE_DIR_U.'/m.css', null, null );

		// add_action( 'wp_print_footer_scripts', 'frenchpress_inline_js' );// any reason to do this if we can just defer the script?

		if ( apply_filters( 'frenchpress_drawer', true ) )
		{
			// wp_enqueue_script( 'f', TEMPLATE_DIR_U.'/a/drawer.min.js', null, null, true );

			// which submenu will we use if needed?
			global $frenchpress_drawer;
			wp_enqueue_script( 'f', TEMPLATE_DIR_U."/a/{$frenchpress_drawer['layout']}.min.js", null, null, true );

			if ( ! apply_filters( 'frenchpress_inline_css', true ) )
				wp_enqueue_style( 'f', TEMPLATE_DIR_U."/a/{$frenchpress_drawer['layout']}.css", null, null );
		}

		// lastly add child styles, if child theme active
		if ( ! apply_filters( 'frenchpress_inline_css', true ) ) {
			if ( TEMPLATEPATH !== STYLESHEETPATH )
				wp_enqueue_style( 'c', get_stylesheet_uri(), null, null );
		}
	}

	// wp_enqueue_style( 'frenchpress-print',  TEMPLATE_DIR_U.'/print.css', null, null, 'print' );

	// wp_enqueue_script( 'frenchpress-skip-link-focus-fix', TEMPLATE_DIR_U . '/js/skip-link-focus-fix'.$suffix.'.js', array(), null, true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) wp_enqueue_script( 'comment-reply' );
}
add_action( 'wp_enqueue_scripts', 'frenchpress_scripts' );

add_filter('script_loader_tag', function($tag, $handle) {
	return ( 0 !== strpos( $handle, 'frenchpress' ) ) ? $tag : str_replace( ' src', ' defer src', $tag );
}, 10, 2);

function frenchpress_inline_js(){
	echo "<script>" . file_get_contents( TEMPLATE_DIR_U.'/js/main.min.js' ) . "</script>";
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

	// extra CSS for drawers & submenus
	if ( apply_filters( 'frenchpress_drawer', true ) )
	{
		// which submenu will we use if needed?
		global $frenchpress_drawer;
		$css .= file_get_contents( TEMPLATEPATH . "/a/{$frenchpress_drawer['layout']}.css" );
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


function frenchpress_full_width_template( $is_fullwidth ) {
	if ( is_page_template( 'page-templates/page_full-width.php' ) ) {
		$is_fullwidth = true;
	}
	return $is_fullwidth;
}
add_filter( 'frenchpress_full_width', 'frenchpress_full_width_template', 9 );



add_action( 'login_enqueue_scripts', function() {
	// wp_dequeue_style( 'buttons' );
	// wp_dequeue_style( 'open-sans' );
	// wp_dequeue_style( 'dashicons' );
	wp_dequeue_style( 'login' );

	if ( function_exists( 'frenchpress_child_enqueue_scripts' ) ) {
		frenchpress_child_enqueue_scripts();
	}
	wp_enqueue_style( 'theme', get_stylesheet_uri(), array(), null );
	wp_enqueue_style( 'frenchpress-login', TEMPLATE_DIR_U . '/login.css', array(), null );
} );



function frenchpress_mobile_test() {
	$breakpoint = apply_filters( 'frenchpress_menu_breakpoint', 860 );
	if ( ! $breakpoint ) return;
	echo "<script>(function(){var c=document.documentElement.classList;";
	echo "function f(){if(window.innerWidth>{$breakpoint}){c.remove('mnav');c.remove('dopen');c.add('dnav');}else{c.remove('dnav');c.add('mnav');}}";
	echo "f();window.addEventListener('resize',f);})();</script>";
}
// if ( ! apply_filters( 'frenchpress_disable_mobile', false ) ) {
add_action( 'wp_print_scripts', 'frenchpress_mobile_test' );
// }


/**
* Add CSS to [gallery] shortcodes, and the bloat out of style.css
* There are actually core filters for using and modifying inline default CSS
*/
function add_gallery_styling( $style_and_div ) {

	/*
	* Earlier, I used some display and line-height rules to fix spacing between images.
	* This is also an issue on images with captions, if you want the capton to be flush.
	* For now I'm trying a site-wide rule: figure img{display:block}
	* Also I have figure: display: inline-block so that is disabled for now as well.
	*
	* Rules were:
		gallery-caption { display: block; line-height: 1; }
		.gallery-item { line-height: 0; display: inline-block; }
	*/
	$css = frenchpress_minify_css( "<style>
.gallery-item {
	margin: 0;
	text-align: center;
	vertical-align: top;
}
.gallery-columns-1 .gallery-item {width: 100%;}
.gallery-columns-2 .gallery-item, .gallery-item{width: 50%;}
@media (min-width:800px){
	.gallery-columns-3 .gallery-item{width: 33.333%;}
	.gallery-columns-4 .gallery-item, .gallery-item{width: 25%;}
}
@media (min-width:1200px){
	.gallery-columns-5 .gallery-item{width: 20%;}
	.gallery-columns-6 .gallery-item, .gallery-item{width: 16.667%;}
}
@media (min-width:1600px){
	.gallery-columns-7 .gallery-item{width: 14.286%;}
	.gallery-columns-8 .gallery-item{width: 12.5%;}
	.gallery-columns-9 .gallery-item{width: 11.111%;}
}
</style>" );
	return  $css . $style_and_div;
}
add_filter( 'gallery_style', 'add_gallery_styling' );


if ( ! function_exists( 'frenchpress_setup' ) ) :
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function frenchpress_setup() {
	/*
	 * Make theme available for translation.
	 * Translations can be filed in the /languages/ directory.
	 * If you're building a theme based on FrenchPress, use a find and replace
	 * to change 'frenchpress' to the name of your theme in all the template files.
	 */
	load_theme_textdomain( 'frenchpress', TEMPLATEPATH . '/languages' );

	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );

	/*
	 * Let WordPress manage the document title.
	 * By adding theme support, we declare that this theme does not use a
	 * hard-coded <title> tag in the document head, and expect WordPress to
	 * provide it for us.
	 */
	add_theme_support( 'title-tag' );

	/*
	 * Enable support for Post Thumbnails on posts and pages.
	 *
	 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
	 */
	add_theme_support( 'post-thumbnails' );

	/*
	 * Switch default core markup for search form, comment form, and comments
	 * to output valid HTML5.
	 */
	add_theme_support( 'html5', array(
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
	) );

	/*
	 * Enable support for Post Formats.
	 * See https://developer.wordpress.org/themes/functionality/post-formats/
	add_theme_support( 'post-formats', array(
		'chat',
		'aside',
		'gallery',
		'image',
		'video',
		'audio',
		'quote',
		'link',
		'status'
	) );
	 */

	// Set up the WordPress core custom background feature.
	add_theme_support( 'custom-background', array(
		'default-color' => 'FFFDF8',
		'default-image' => '',
	) );

	// Custom Logo
	add_theme_support( 'custom-logo', array( 'flex-width'	=> true ) );

	/*
	 * Styles the visual editor
	 * See https://developer.wordpress.org/reference/functions/add_editor_style/
	 */
	add_editor_style();
}
endif;
add_action( 'after_setup_theme', 'frenchpress_setup' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function frenchpress_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'frenchpress_content_width', 640 );
}
add_action( 'after_setup_theme', 'frenchpress_content_width', 0 );

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function frenchpress_widgets_init() {
	register_sidebar( array(
		'name'		  => 'Sidebar 1',
		'id'			=> 'sidebar-1',
		'before_widget' => '<section id="%1$s" class="widget sidebar-widget %2$s">',
		'after_widget'  => "</section>\n",
		'before_title'  => '<h3 class=widgettitle>',
		'after_title'   => "</h3>\n",
	) );
	register_sidebar( array(
		'name'		  => 'Sidebar 2',
		'id'			=> 'sidebar-2',
		'before_widget' => '<section id="%1$s" class="widget sidebar-widget %2$s">',
		'after_widget'  => "</section>\n",
		'before_title'  => '<h3 class=widgettitle>',
		'after_title'   => "</h3>\n",
	) );
	register_sidebar( array(
		'name'		  => 'Header 1 (very top)',
		'id'			=> 'header-1',
		'description'   => 'Best for secondary menus/nav bars',
		'before_widget' => '<aside id="%1$s" class="widget header-widget %2$s">',
		'after_widget'  => "</aside>\n",
		'before_title'  => '<h3 class=widgettitle>',
		'after_title'   => "</h3>\n",
	) );
	register_sidebar( array(
		'name'		  => 'Header 2',
		'id'			=> 'header-2',
		'before_widget' => '<div id="%1$s" class="widget header-widget fffi %2$s">',
		'after_widget'  => "</div>\n",
		'before_title'  => '<h3 class=widgettitle>',
		'after_title'   => "</h3>\n",
	) );
	register_sidebar( array(
		'name'		  => 'Header 3 (main)',
		'id'			=> 'header-3',
		'description'   => 'Will be in line with the site branding, typical place to put the main menu',
		'before_widget' => '<div id="%1$s" class="widget header-widget fffi %2$s">',
		'after_widget'  => "</div>\n",
		'before_title'  => '<h3 class=widgettitle>',
		'after_title'   => "</h3>\n",
	) );
	register_sidebar( array(
		'name'		  => 'Header 4',
		'id'			=> 'header-4',
		'before_widget' => '<div id="%1$s" class="widget header-widget fffi %2$s">',
		'after_widget'  => "</div>\n",
		'before_title'  => '<h3 class=widgettitle>',
		'after_title'   => "</h3>\n",
	) );
	register_sidebar( array(
		'name'		  => 'Top of Footer',
		'id'			=> 'footer-top',
		'description'   => 'For some banner between content and footer',
		'before_widget' => '',
		'after_widget'  => "",
		'before_title'  => '<h3 class=widgettitle>',
		'after_title'   => "</h3>\n",
	) );
	register_sidebar( array(
		'name'		  => 'Footer 1',
		'id'			=> 'footer-1',
		'before_widget' => '<aside id="%1$s" class="widget footer-widget %2$s">',
		'after_widget'  => "</aside>\n",
		'before_title'  => '<h3 class=widgettitle>',
		'after_title'   => "</h3>\n",
	) );
	register_sidebar( array(
		'name'		  => 'Footer 2',
		'id'			=> 'footer-2',
		'before_widget' => '<aside id="%1$s" class="widget footer-widget %2$s">',
		'after_widget'  => "</aside>\n",
		'before_title'  => '<h3 class=widgettitle>',
		'after_title'   => "</h3>\n",
	) );
	register_sidebar( array(
		'name'		  => 'Footer 3',
		'id'			=> 'footer-3',
		'before_widget' => '<aside id="%1$s" class="widget footer-widget %2$s">',
		'after_widget'  => "</aside>\n",
		'before_title'  => '<h3 class=widgettitle>',
		'after_title'   => "</h3>\n",
	) );
	register_sidebar( array(
		'name'		  => 'Footer 4',
		'id'			=> 'footer-4',
		'before_widget' => '<aside id="%1$s" class="widget footer-widget %2$s">',
		'after_widget'  => "</aside>\n",
		'before_title'  => '<h3 class=widgettitle>',
		'after_title'   => "</h3>\n",
	) );
	register_sidebar( array(
		'name'		  => 'Bottom of Footer',
		'id'			=> 'footer-bottom',
		'description'   => 'Typical place for copyright, theme info, etc. Shortcode [current_year] is available for copyrights.',
		'before_widget' => '<aside id="%1$s" class="widget footer-widget fffi %2$s">',
		'after_widget'  => "</aside>\n",
		'before_title'  => '<h3 class=widgettitle>',
		'after_title'   => "</h3>\n",
	) );
}
add_action( 'widgets_init', 'frenchpress_widgets_init' );


/**
 * Adds markup for the mobile-menu-style drawer
 */
 if ( ! wp_get_nav_menus() ) add_filter( 'frenchpress_drawer', function(){return false;} );

 if ( apply_filters( 'frenchpress_drawer', true ) ) {
	require TEMPLATEPATH . '/inc/drawer.php';
 } else {
	// this won't be the long-term solution I'm sure.
	// This is just for sites with no drawer and might even be better defined in child theme to the exact pixel width.
	add_action('wp_print_styles',function(){echo '<style>.mnav .site-header .menu-item > a{padding:12px}</style>';});
 }

/**
 * [frenchpress] builder-style shortcode
 */
require TEMPLATEPATH . '/inc/shortcodes.php';

/**
 * Remove core bull
 */
if ( ! function_exists( 'remove_type_from_archive_title' ) ) {
	require TEMPLATEPATH . '/inc/disembellish.php';
}

/**
 * Implement the Custom Header feature.
 */
require TEMPLATEPATH . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require TEMPLATEPATH . '/inc/template-tags.php';

/**
 * HTML5 Cleanup and various other goodies
 */
require TEMPLATEPATH . '/inc/extras.php';

/**
 * Customizer additions.
 */
require TEMPLATEPATH . '/inc/customizer.php';

require TEMPLATEPATH . '/inc/potted.php';

/**
 * Custom walker with no <li>.
 */
// require TEMPLATEPATH . '/inc/walker_no_list.php';


/**
 * WooCommerce Support
 */
if ( class_exists( 'WooCommerce' ) ) {
	require TEMPLATEPATH . '/woocommerce/woocommerce.php';
}
