<?php
/************************
* Options Page
**/

add_action( 'rest_api_init', 'frenchpress_register_api_endpoint' );
function frenchpress_register_api_endpoint() {
	register_rest_route( 'frenchpress/v1', '/s', ['methods' => 'POST', 'callback' => 'frenchpress_api_options', 'permission_callback' => function(){ return current_user_can('import');} ] );
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

	$url = rest_url('frenchpress/v1/');
	$nonce = "x.setRequestHeader('X-WP-Nonce','". wp_create_nonce('wp_rest') ."')";
	?>
<div class=wrap>
	<h1>Frenchpress Theme Options</h1>
	<form onsubmit="event.preventDefault();var t=this,b=t.querySelector('button'),x=new XMLHttpRequest;x.open('POST','<?php echo $url.'s'; ?>'),<?php echo $nonce; ?>,x.onload=function(){b.innerHTML=JSON.parse(x.response)},x.send(new FormData(t))">
	<?php

	$fields = array_fill_keys([
		// 'no_drawer',
		'content_width',
		'menu_breakpoint',
		'inline_css',
		'page_titles','hide_archive_prefix',
		'no_blog_thumbnails', 'blog_layout_desktop', 'blog_layout_mobile',
		'blog_excerpt',
		'entry_meta','entry_meta_time','entry_meta_byline',
		'entry_footer',
		'feat_image_bg', 'feat_image_bg_location', 'feat_image_bg_color_overlay', 'title_in_header',
		'disable_comments','avatar_size','comment_form_unstyle','comment_form_website_field',
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
	],
	[ 'type' => '' ]);// default

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

	$fields['nav_position']['before'] = '<h2>Header Layout</h2>';
	$fields['nav_position']['options'] = ["right","top","bottom"];
	$fields['nav_align']['options'] = ["left","right","justified","center"];

	$fields['logo']['type'] = 'text';
	$fields['logo']['show'] = ['use_custom_code_for_branding' => 'empty'];

	$fields['branding_custom_code']['type'] = 'textarea';
	$fields['branding_custom_code']['show'] = 'use_custom_code_for_branding';
	$fields['custom_code_right_of_menu']['type'] = 'textarea';
	$fields['custom_code_right_of_menu']['show'] = 'add_custom_code_right_of_menu';
	$fields['custom_code_right_of_branding']['type'] = 'textarea';
	$fields['custom_code_right_of_branding']['show'] = 'add_custom_code_right_of_branding';
	$fields['add_custom_code_right_of_branding']['show'] = ['nav_position' => ['top','bottom']];

	$fields['menu_breakpoint']['type'] = 'number';
	$fields['content_width']['type'] = 'number';
	$fields['mobile_nav']['options'] = ["fullscreen","slide","tree","none"];// tree not implemented yet.
	$fields['avatar_size']['type'] = 'number';
	$fields['avatar_size']['desc'] = 'in pixels. 0 disables avatars.';

	$fields['logo']['desc'] = 'URL to logo. SVGs will be inlined so the fill color can be manipulated.';


	$options = [ 'frenchpress' => $fields ];// can add additional options groups to save as their own array in the options table

	$values = [];
	foreach ( $options as $g => $fields ) {
		$values += get_option( $g, [] );
	}
	
	$script = '';
	echo '<table class=form-table>';
	foreach ( $options as $g => $fields ) {
		// $values = get_option($g);
		echo "<input type=hidden name='{$g}[x]' value=1>";// hidden field to make sure things still update if all options are empty (defaults)
		foreach ( $fields as $k => $f ) {
			if ( !empty( $f['before'] ) ) echo "<tr><th>" . $f['before'];
			$v = isset( $values[$k] ) ? $values[$k] : '';
			$l = isset( $f['label'] ) ? $f['label'] : str_replace( '_', ' ', $k );
			$size = !empty( $f['size'] ) ? $f['size'] : 'regular';
			$hide = '';
			if ( !empty( $f['show'] ) ) {
				if ( is_string( $f['show'] ) ) $f['show'] = [ $f['show'] => 'any' ];
				foreach( $f['show'] as $target => $cond ) {
					$hide = " style='display:none'";
					$script .= "\ndocument.querySelector('#tr-{$target}').addEventListener('change', function(e){";
					if ( $cond === 'any' ) {
						$script .= "if( e.target.checked !== false && e.target.value )";
						if ( !empty( $values[$target] ) ) $hide = "";
					}
					elseif ( $cond === 'empty' ) {
						$script .= "if( e.target.checked === false || !e.target.value )";
						if ( empty( $values[$target] ) ) $hide = "";
					}
					else {
						$script .= "if( !!~['". implode( "','", (array) $cond ) ."'].indexOf(e.target.value) && e.target.checked!==false)";
						if ( !empty( $values[$target] ) && in_array( $values[$target], (array) $cond ) ) $hide = "";
					}
					$script .= "{document.querySelector('#tr-{$k}').style.display='revert'}";
					$script .= "else{document.querySelector('#tr-{$k}').style.display='none'}";
					$script .= "});";
				}
			}
			if ( empty( $f['type'] ) ) $f['type'] = !empty( $f['options'] ) ? 'radio' : 'checkbox';// checkbox is default

			if ( $f['type'] === 'section' ) { echo "<tbody id='tr-{$k}' {$hide}>"; continue; }
			elseif ( $f['type'] === 'section_end' ) { echo "</tbody>"; continue; }
			else echo "<tr id=tr-{$k} {$hide}><th>";
			
			if ( !empty( $f['callback'] ) && function_exists( __NAMESPACE__ .'\\'. $f['callback'] ) ) {
				echo "<label for='{$g}-{$k}'>{$l}</label><td>";
				call_user_func( __NAMESPACE__ .'\\'. $f['callback'], $g, $k, $v, $f );
	        } else {
				switch ( $f['type'] ) {
					case 'textarea':
						echo "<label for='{$g}-{$k}'>{$l}</label><td><textarea id='{$g}-{$k}' name='{$g}[{$k}]' placeholder='' rows=8 class={$size}-text>{$v}</textarea>";
						break;
					case 'number':
						$size = !empty( $f['size'] ) ? $f['size'] : 'small';
						echo "<label for='{$g}-{$k}'>{$l}</label><td><input id='{$g}-{$k}' name='{$g}[{$k}]' placeholder='' value='{$v}' class={$size}-text type=number>";
						break;
					case 'radio':
						if ( !empty( $f['options'] ) && is_array( $f['options'] ) ) {
							echo "{$l}<td>";
							foreach ( $f['options'] as $ov => $ol ) {
								if ( ! is_string( $ov ) ) $ov = $ol;
								echo "<label><input name='{$g}[{$k}]' value='{$ov}'"; if ( $v == $ov ) echo " checked"; echo " type=radio>{$ol}</label> ";
							}
						}
						break;
					case 'select':
						if ( !empty( $f['options'] ) && is_array( $f['options'] ) ) {
							echo "<label for='{$g}-{$k}'>{$l}</label><td><select id='{$g}-{$k}' name='{$g}[{$k}]'>";
							echo "<option value=''></option>";// placeholder
							foreach ( $f['options'] as $key => $value ) {
								echo "<option value='{$key}'" . selected( $v, $key, false ) . ">{$value}</option>";
							}
							echo "</select>";
						}
						break;
					case 'text':
						echo "<label for='{$g}-{$k}'>{$l}</label><td><input id='{$g}-{$k}' name='{$g}[{$k}]' placeholder='' value='{$v}' class={$size}-text>";
						break;
					case 'checkbox':
					default:
						echo "<label for='{$g}-{$k}'>{$l}</label><td><input id='{$g}-{$k}' name='{$g}[{$k}]'"; if ( $v ) echo " checked"; echo " type=checkbox >";
						break;
				}
			}
			if ( !empty( $f['desc'] ) ) echo "&nbsp; " . $f['desc'];
		}
	}
	if ( $script ) echo "<script>$script</script>";
	echo '</table>';

	?><p><button class=button-primary>Save Changes</button>
	</form>
</div>
<?php
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
			echo "<style>{$style}</style>";
			// wp_add_inline_style( 'frenchpress', $style );
		}
	}
}

/**
 *  Style comment form & remove website field
 */
if ( empty( $GLOBALS['frenchpress']->comment_form_unstyle ) )
{
	function frenchpress_comment_form_fields( $fields ){

		$req = false === strpos( $fields['email'], 'required' ) ? '' : '*';

		$fields['author'] = '<div class="fff fff-magic fff-pad">'
			. str_replace(
			['p class="', '</p>', 'label', 'size="30"'],
			['span class="fffi ', '</span>', 'label class="screen-reader-text"', "placeholder='Name{$req}' style='width:100%'"],
			$fields['author']
			);

		$fields['email'] = str_replace(
			['p class="', '</p>', 'label', 'size="30"'],
			['span class="fffi ', '</span>', 'label class="screen-reader-text"', "placeholder='Email{$req}' style='width:100%'"],
			$fields['email']
			);

		if ( empty( $GLOBALS['frenchpress']->comment_form_website_field ) ) {
			$fields['email'] .= '</div>';
			unset( $fields['url'] );
			if ( !empty( $fields['cookies'] ) ) {
				$fields['cookies'] = str_replace( 'name, email, and website', 'name and email', $fields['cookies'] );
			}
		} else {
			$fields['url'] = str_replace(
				['p class="', '</p>', 'label', 'size="30"'],
				['span class="fffi ', '</span>', 'label class="screen-reader-text"', "placeholder='Website' style='width:100%'"],
				$fields['url']
				) . '</div>';
		}
		$fields['comment'] = str_replace( ['label', 'cols="45"'], ['label class="screen-reader-text"', "placeholder='Comment' style='width:100%'"], $fields['comment'] );

		return $fields;
	}
	add_filter( 'comment_form_fields', 'frenchpress_comment_form_fields' );
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
}