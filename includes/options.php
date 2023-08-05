<?php
/************************
* Options Page
**/

add_action( 'admin_bar_menu', 'frenchpress_add_option_to_admin_bar' );
function frenchpress_add_option_to_admin_bar( $bar ) {
    $bar->add_node([
        'id'     => 'frenchpress-options',
        'parent' => 'customize',// dropdown under Customize seems appropriate
        'title'  => 'Frenchpress Options',
        'href'   => admin_url('themes.php?page=frenchpress'),
    ]);
}


add_action( 'rest_api_init', 'frenchpress_register_options_endpoint' );
function frenchpress_register_options_endpoint() {
	register_rest_route( 'frenchpress/v1', '/settings', ['methods' => 'POST', 'callback' => 'frenchpress_api_options', 'permission_callback' => function(){ return current_user_can('manage_options');} ] );
}

function frenchpress_api_options( $request ) {
	$data = $request->get_params();
	foreach ( $data as $k => $v ) update_option( $k, array_filter($v, 'strlen') );
	return "Saved";
}


add_action( 'admin_menu', 'frenchpress_admin_menu' );
function frenchpress_admin_menu() {
	add_submenu_page( 'themes.php', 'Frenchpress Theme Options', 'Theme Options', 'import', 'frenchpress', 'frenchpress_options_page', 1 );
}

function frenchpress_options_page() {

	$fields = array_fill_keys([
		// 'no_drawer',
		'inline_css',
		'site_width','content_width','sidebar_width','menu_breakpoint',
		'post_layout','page_layout','index_layout',
		'sidebar_position_desktop','sidebar_position_mobile','sidebar_centered_content',
		'no_blog_thumbnails', 'blog_layout_desktop', 'blog_layout_mobile','blog_excerpt',
		'entry_meta','entry_meta_time','entry_meta_byline',
		'entry_footer',
		'page_titles','hide_archive_prefix',
		'feat_image_bg', 'feat_image_bg_location', 'feat_image_bg_color_overlay', 'title_in_header',
		'disable_comments','avatar_size','comment_form_unstyle','comment_form_website_field','comment_dates',
		'mini_toolbar',
		'boring_404',
		'dont_style_login',
		'dont_redirect_wplogin',
		'mobile_nav',
		'nav_position', 'nav_align',
		'logo',
		'use_custom_code_for_branding', 'branding_custom_code',
		'add_custom_code_right_of_menu', 'custom_code_right_of_menu',
		'add_custom_code_right_of_branding', 'custom_code_right_of_branding',
		'full_width_nav', 'full_width_branding',
		'use_custom_sidebar','custom_sidebar',
		'custom_css',
	],
	[ 'type' => '' ]);// default

	$fields['page_layout']['options'] = ["sidebars","content-width","site-width","full-width"];
	$fields['post_layout'] = $fields['index_layout'] = $fields['page_layout'];

	$fields['sidebar_position_desktop']['options'] = ["right","left"];
	$fields['sidebar_position_mobile']['options'] = ["bottom","top"];

	$fields['blog_layout_desktop']['options'] = ["list","grid"];
	$fields['blog_layout_mobile']['options'] = ["list","grid"];
	$fields['blog_layout_desktop']['show'] = ['no_blog_thumbnails' => 'empty'];
	$fields['blog_layout_mobile']['show'] = ['no_blog_thumbnails' => 'empty'];

	$fields['blog_excerpt']['options'] = ["excerpt","fulltext","none"];

	$fields['feat_image_bg_location']['type'] = 'text';
	$fields['feat_image_bg_location']['show'] = 'feat_image_bg';
	$fields['feat_image_bg_location']['desc'] = 'CSS selector to apply the bg image, eg: body, #header, or #header-title (if option below is checked)';
	$fields['feat_image_bg_color_overlay']['type'] = 'text';
	$fields['feat_image_bg_color_overlay']['show'] = 'feat_image_bg';
	$fields['feat_image_bg_color_overlay']['desc'] = 'any CSS color, but use something with opacity value, like: rgba(46,30,47,.9)';
	$fields['entry_meta_time']['show'] = 'entry_meta';
	$fields['entry_meta_byline']['show'] = 'entry_meta';

	$fields['avatar_size']['show'] = ['disable_comments' => 'empty'];
	$fields['comment_form_unstyle']['show'] = ['disable_comments' => 'empty'];
	$fields['comment_form_website_field']['show'] = ['disable_comments' => 'empty'];
	$fields['comment_dates']['show'] = ['disable_comments' => 'empty'];

	$fields['nav_position']['before'] = '<h2>Header Layout</h2>';
	$fields['nav_position']['options'] = ["right","top","bottom"];
	$fields['nav_align']['options'] = ["left","right","justified","center"];

	$fields['logo']['type'] = 'text';
	$fields['logo']['show'] = ['use_custom_code_for_branding' => 'empty'];

	$fields['branding_custom_code']['type'] = 'code';
	$fields['branding_custom_code']['show'] = 'use_custom_code_for_branding';
	$fields['custom_code_right_of_menu']['type'] = 'code';
	$fields['custom_code_right_of_menu']['show'] = 'add_custom_code_right_of_menu';
	$fields['custom_code_right_of_branding']['type'] = 'code';
	$fields['custom_code_right_of_branding']['show'] = 'add_custom_code_right_of_branding';
	$fields['add_custom_code_right_of_branding']['show'] = ['nav_position' => ['top','bottom']];
	$fields['custom_sidebar']['type'] = 'code';
	$fields['custom_sidebar']['show'] = 'use_custom_sidebar';

	$fields['menu_breakpoint']['type'] = 'number';
	$fields['site_width']['type'] = $fields['content_width']['type'] = $fields['sidebar_width']['type'] = 'number';
	$fields['mobile_nav']['options'] = ["fullscreen","slide","tree","none"];// tree not implemented yet.
	$fields['avatar_size']['type'] = 'number';
	$fields['avatar_size']['desc'] = 'in pixels. 0 disables avatars.';

	$fields['logo']['desc'] = 'URL to logo. SVGs will be inlined so the fill color can be manipulated.';

	$fields['custom_css']['type'] = 'code';

	/**
	 *  Build Settings Page using framework in settings_page.php
	 **/
	$options = [ 'frenchpress' => $fields ];// can add additional options groups to save as their own array in the options table
	$endpoint = rest_url('frenchpress/v1/settings');
	$title = "Frenchpress Theme Options";
	require( __DIR__.'/settings-page.php' );// needs $options, $endpoint, $title
}


// function frenchpress_full_width( $full_width ) {
// 	if ( in_category( 'review' ) ) $full_width = true;
// 	return $full_width;
// }
// add_filter( 'frenchpress_full_width', 'frenchpress_full_width' );
// add_filter( 'frenchpress_full_width', '__return_true' );// full width layout

// add_filter( 'frenchpress_featured_image', '__return_true' );// show featured images on single posts

// set_post_thumbnail_size( '850', '850' );

// update_option( 'medium_large_size_w', 1024 );// width of medium_large picture size. seems more useful than 768 this time. ONly has to run once to update options table

// add_filter('show_admin_bar', function(){ return false; });
// add_filter('show_admin_bar', function($b){ return current_user_can('administrator') ? $b : false; });

// add_filter( 'frenchpress_main_menu_slug', function(){ return 'main-menu'; } );// which menu should get the mobile drawer?  default is menu with "main" in slug, or failing that, the first menu created

// add_action( 'wp_head', function(){ echo '<meta name="google-site-verification" content="Shav8DEq9RGnBjvv4i0iYkNqUkQQZ3Wpy5xSNTmMGl0">'; });

// add_filter( 'frenchpress_main_menu_align', function(){ return 'left'; } );

// add_filter( 'user_can_richedit' , '__return_false' );// disable visual editor
// add_filter( 'excerpt_length', function(){ return 80; } );// number of words in auto-generated excerpts (default 55)

// add_filter( 'frenchpress_sidebar', function($show){ return ( is_archive() || is_home() ) ? false : $show; } );
// add_filter( 'frenchpress_page_layout', function(){ return 'sidebars'; } );// 'sidebars, 'full-width', or 'no-sidebars' (default is no-sidebars on pages)

// add_filter( 'frenchpress_post_navigation', '__return_true' );// show next / prev post at the bottom of articles
// add_filter( 'frenchpress_blog_excerpts', '__return_false' );// show full post on blog home
// add_filter( 'frenchpress_archive_excerpts', '__return_false' );// show full post in archives

// No Meta
// function frenchpress_entry_footer() { return; }

// set_post_thumbnail_size( 150, 150 );

// add_filter( 'frenchpress_site_branding', function(){return file_get_contents( __DIR__ .'/logo.svg' );} );

// add_filter( 'frenchpress_class_header_main', function(){ return "tray fff fff-middle fff-spacebetween fff-nowrap"; } );

// add_filter( 'frenchpress_title_in_header', '__return_true' );
// add_action('frenchpress_header_bottom', function(){ echo '<h1>' . wp_title('', false) . '</h1>'; } );


/**
 *  Mini toolbar with icons for "edit" and "dashboard"
 */
if ( !empty( $GLOBALS['frenchpress']->mini_toolbar ) ) {

	if ( current_user_can( 'edit_posts' ) ) {

		show_admin_bar(false);

		add_action( 'wp_footer', 'frenchpress_mini_toolbar' );// putting in footer.php at this time
		function frenchpress_mini_toolbar(){

			echo '<span class=mini-adminbar style="position:fixed;bottom:0;right:0;background:#fff;opacity:.5;line-height:0">';
			if ( is_singular() )
				echo '<a href="' . get_edit_post_link() . '"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" style="fill:#000;width:20px;margin:5px"><path d="M13.89 3.39l2.71 2.72c.46.46.42 1.24.03 1.64l-8.01 8.02-5.56 1.16 1.16-5.58s7.6-7.63 7.99-8.03c.39-.39 1.22-.39 1.68.07zm-2.73 2.79l-5.59 5.61 1.11 1.11 5.54-5.65zm-2.97 8.23l5.58-5.6-1.07-1.08-5.59 5.6z"/></svg></a>';
			echo '<a href="' . get_admin_url() . '"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" style="fill:#000;width:20px;margin:5px"><path d="M3.76 16h12.48c1.1-1.37 1.76-3.11 1.76-5 0-4.42-3.58-8-8-8s-8 3.58-8 8c0 1.89.66 3.63 1.76 5zM10 4c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zM6 6c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm8 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm-5.37 5.55L12 7v6c0 1.1-.9 2-2 2s-2-.9-2-2c0-.57.24-1.08.63-1.45zM4 10c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm12 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm-5 3c0-.55-.45-1-1-1s-1 .45-1 1 .45 1 1 1 1-.45 1-1z"></path></svg></a>';
			echo '</span>';
		}
	}
}


/**
 * remove archive page prefix in title
 */
if ( !empty( $GLOBALS['frenchpress']->hide_archive_prefix ) ) {
	add_filter( 'get_the_archive_title_prefix', '__return_false' );// WP 5.5
}


/**
 *  Featured image as bg image
 */
if ( !empty( $GLOBALS['frenchpress']->title_in_header ) ) {
	add_filter( 'frenchpress_title_in_header', '__return_true' );
}

/**
 *  Featured image as bg image
 */
if ( !empty( $GLOBALS['frenchpress']->feat_image_bg ) ) {
	add_action( 'wp_head', 'frenchpress_feat_image_bg' );
	function frenchpress_feat_image_bg(){

		global $wp_query;
		if ( empty($wp_query->queried_object_id) ) return;// or get_queried_object_id() with no global needed.. returns 0 if no id

		$id = $wp_query->queried_object_id;

		// $image_url = $image_url ? $image_url[0] : '/wp-content/uploads/2016/08/london-slim-dark-1024x172.jpg';// default pic moved to CSS
		if ( $image_url = wp_get_attachment_image_src( get_post_thumbnail_id( $id ), 'large' ) ) {
			$el = !empty( $GLOBALS['frenchpress']->feat_image_bg_location ) ? $GLOBALS['frenchpress']->feat_image_bg_location : "body";
			$style = "{$el}{background-image:url({$image_url[0]})}";

			if ( !empty( $GLOBALS['frenchpress']->feat_image_bg_color_overlay ) ) {
				$color = esc_attr( $GLOBALS['frenchpress']->feat_image_bg_color_overlay );
				$style = "{$el}{background:linear-gradient(0deg,{$color},{$color}),url({$image_url[0]}) center/cover}";
			}
			frenchpress_add_inline_style( $style );
		}
	}
}

/**
 *  Style comment form & remove website field
 */
if ( empty( $GLOBALS['frenchpress']->comment_form_unstyle ) )
{
	// disable the cookie thing... might TODO some solution if a person wants cookies. See https://github.com/WordPress/WordPress/blob/cf9793f0dfaf5a9565302690566007cf65389e8d/wp-includes/comment-template.php#L2406
	remove_action( 'set_comment_cookies', 'wp_set_comment_cookies', 10, 3 );

	function frenchpress_comment_form_fields( $fields ){

		$req = false === strpos( $fields['email'], 'required' ) ? '' : '*';// i guess just to avoid using get_option( 'require_name_email' );

		$fields['author'] = "<input id=author name=author placeholder='Name*' maxlength=245 autocomplete=name required>";

		$fields['email'] = "<input id=email name=email type=email placeholder='Email*' maxlength=100 autocomplete=email required>";

		if ( empty( $GLOBALS['frenchpress']->comment_form_website_field ) ) {
			unset( $fields['url'] );
			add_action('pre_comment_on_post', 'frenchpress_block_comments_with_url', 1 );
		} else {
			$fields['url'] = "<input id=url name=url type=url placeholder=Website maxlength=200>";
		}

		$fields['comment'] = "<textarea id=comment name=comment placeholder=Comment rows=5 maxlength=65525 required></textarea>";

		return $fields;
	}
	add_filter( 'comment_form_fields', 'frenchpress_comment_form_fields' );

	add_filter( 'comment_form_defaults', function( $defaults ){
		$defaults['submit_button'] = '<button name="%1$s" type=submit id="%2$s" class="%3$s">%4$s</button>';
		$defaults['submit_field'] = '%1$s %2$s';
		return $defaults;
	});

	add_action( 'comment_form_before', function(){
		echo "<style>.comment-form{display:flex;flex-wrap:wrap;gap:12px;justify-content:flex-end}.comment-form>textarea{width:100%}.comment-form>input{flex:auto;width:13em}</style>";
	});
}
elseif ( empty( $GLOBALS['frenchpress']->comment_form_website_field ) )// remove URL field from comment form
{
	add_filter( 'comment_form_fields', function($fields){
		unset($fields['url']);
		if ( !empty( $fields['cookies'] ) ) {
    		$fields['cookies'] = str_replace( 'name, email, and website', 'name and email', $fields['cookies'] );
		}
		return $fields;
	} );

	add_action('pre_comment_on_post', 'frenchpress_block_comments_with_url', 1 );
}

function frenchpress_block_comments_with_url(){
	if ( !empty( $_POST['url'] ) ) {
		error_log( "Blocked a comment because it had a URL:\n" . var_export( $_POST, true ) );
		die;
	}
}