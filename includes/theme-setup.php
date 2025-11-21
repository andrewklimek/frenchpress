<?php
/**
 * Setup various options and features based on settings
 */

/**
 * Migrate old 'frenchpress' option to individual options
 */
$frenchpress = get_option( 'frenchpress', [] );
if ( $frenchpress ) {
	foreach ($frenchpress as $key => $value) {
		if ($key === 'x') continue;
		update_option("frenchpress_{$key}", $value, true);
	}
	delete_option('frenchpress');// clean up old option
	update_option('frenchpress_backup', $frenchpress, false);
}


/**
 * Load settings into global $frenchpress
 */
$GLOBALS['frenchpress'] = (object) frenchpress_settings();


/**
 * Side Menu Drawer TODO clean this up
 */
if ( empty( $GLOBALS['frenchpress']->mobile_nav ) || $GLOBALS['frenchpress']->mobile_nav === 'fullscreen' ) {
	add_action('wp_before_admin_bar_render',function(){echo '<style>.mnav #main-menu{padding-top:32px!important} @media(max-width:782px){.mnav #main-menu{padding-top:46px!important}}</style>';});
} elseif ( $GLOBALS['frenchpress']->mobile_nav === 'none' ) {
	// this won't be the long-term solution I'm sure.
	// This is just for sites with no drawer and might even be better defined in child theme to the exact pixel width.
	frenchpress_add_inline_style( '.mnav .site-header .menu-item > a{padding:12px}' );
} else {//if ( in_array( $GLOBALS['frenchpress']->mobile_nav, ['slide','tree'] ) ) {
	add_action('wp_before_admin_bar_render',function(){echo '<style>.mnav .drawer,.desk-drawer{padding-top:32px!important} @media(max-width:782px){.mnav .drawer{padding-top:46px!important}}</style>';});
	if ( !empty( $GLOBALS['frenchpress']->desktop_drawer ) ) {
		// TODO do I need to override .dnav #menu-open { display: none; } here or can I exclude that rule in the first place?
		frenchpress_add_inline_style( '.dnav.dopen body{padding-left:270px}body{transition:padding .4s}.dnav #menu-open{display:inline-block}' );
	}
}


if ( empty( $content_width ) ) $content_width = $GLOBALS['frenchpress']->content_width;// WP global used for things


if ( !empty( $GLOBALS['frenchpress']->use_simple_menu ) ) {

	add_filter( 'pre_wp_nav_menu', function( $output, $args ){

		$nav = "<ul id={$args->menu_id} class='{$args->menu_class}'>";

		$lines = preg_split( '<[\r\n]>', $GLOBALS['frenchpress']->simple_menu, -1, PREG_SPLIT_NO_EMPTY );

		$current = $maybe_current = '';
		foreach ( $lines as $line ) {
			$parts = explode( '|', $line );
			$uri = trim( $parts[0] );
			$items[ $uri ] = $parts[1] ?? ucwords( str_replace( '-', ' ', trim( $uri, ' /' ) ) ) ?: 'Home';
			if ( ! $current && false !== strpos( $_SERVER['REQUEST_URI'], $uri ) ) {
				if ( trim( $_SERVER['REQUEST_URI'], '/' ) === trim( $uri, '/' ) ) {
					$current = $uri;
				} elseif ( strlen( $uri ) > strlen( $maybe_current ) ) {
					$maybe_current = $uri;
				}
			}
		}
		if ( ! $current ) $current = $maybe_current;
		foreach ( $items as $uri => $label ) {
			$current_class = $current === $uri ? ' current-menu-item' : '';
			$nav .= "<li class='menu-item{$current_class}'><a href='{$uri}'>{$label}</a>";
		}

		return $nav;
	}, 10, 2 );
}


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


/*
 *  Dark mode / theme selector
 * 
 * script:
function setTheme(isDark) {
	document.documentElement.dataset.dark = isDark;
	localStorage.dark = isDark;
};
document.querySelector('#theme').onclick = () => setTheme(document.documentElement.dataset.dark == 'false');
setTheme( localStorage.dark===null ? window.matchMedia('(prefers-color-scheme:dark)').matches : localStorage.dark );
*/
if ( !empty( $GLOBALS['frenchpress']->dark_theme ) ) {
	add_action('frenchpress_header_top',function(){
		?>	
		<div id=theme>&#x25D0;</div>
		<script>function setTheme(e){document.documentElement.dataset.dark=e,localStorage.dark=e}document.querySelector("#theme").onclick=()=>setTheme("false"==document.documentElement.dataset.dark),setTheme(null===localStorage.dark?window.matchMedia("(prefers-color-scheme:dark)").matches:localStorage.dark);</script>
		<?php
	});
	add_action( 'wp_enqueue_scripts', 'theme_theme' );
	function theme_theme(){

		$css = <<<DARKMODE
:root {
	color-scheme: light dark;
	background: var(--bg);
	color: var(--fg);
}
:root[data-dark] {
	color-scheme: light;
	--bg: #fffdfa;/* background */
	--gb: #ccc;/* grey background */
	--gf: #666;/* grey foreground */
	--fg: #333;/* foreground */
}
:root[data-dark=true] {
	color-scheme: dark;
    --bg: #222;
    --gb: #555;
    --gf: #aaa;
    --fg: #ddd;
}
#logo svg {
	fill: var(--fg);
}
#theme {
	position: absolute;
	padding: 12px;
	right: 0;
	line-height: 1;
	cursor: default;
	font-size: 20px;
}
#theme:hover {
	transform: rotate(180deg);
}
DARKMODE;
			frenchpress_add_inline_style( $css );
	}
}


/**
 *  Slide in images
 */
if ( !empty( $GLOBALS['frenchpress']->slide_in_images ) ) {
add_action( 'wp_footer', 'frenchpress_slide_in_imgs' );
function frenchpress_slide_in_imgs() {
if ( is_single() ) return;
print <<<SLIDEIN
<style>img{transition:.5s ease-out}img.fade{transform:translateY(24px);opacity:0}</style>
<script>(function(){
const io = new IntersectionObserver((entries) => {
	entries.forEach((e) => {
		// if (e.isIntersecting || e.boundingClientRect.top < 0) {
		if (e.boundingClientRect.top < e.rootBounds.bottom) {
			e.target.classList.remove("fade");
			io.unobserve(e.target);
		} else {
			e.target.classList.add("fade");
		}
	});
});
document.querySelectorAll("main img").forEach(e => { io.observe(e); });
})();</script>
SLIDEIN;
}
}


/**
 *  Lightbox
 */
if ( !empty( $GLOBALS['frenchpress']->image_lightbox ) ) {
add_action( 'wp_footer', 'frenchpress_lightbox' );
function frenchpress_lightbox() {
print <<<LIGHTBOX
<script>(function(){
	function makeModal(e){
		var modal = document.createElement('div');
		modal.id = 'camobscur';
		modal.style = 'position:fixed;top:0;left:0;background:rgba(0,0,0,.8);z-index:999999;width:100%;height:100%;display:flex;justify-content:center;align-items:center';
		modal.innerHTML = '<img src="'+ this.href +'" style="max-width:95vw;max-height:95vh">';
		document.body.appendChild(modal);
		document.body.style.overflow = 'hidden';
		modal.addEventListener('click', destroy );
		document.addEventListener('keyup', modalEscKey );
		e.preventDefault();
	}
	function modalEscKey(e){
		if ( e.keyCode === 27 ) {
			destroy();
		}
	}
	function destroy(){
		document.body.removeChild( document.getElementById('camobscur') );
		document.removeEventListener('keyup', modalEscKey );
		document.body.style.overflow = '';
	}
	for ( var imgs = document.querySelectorAll('a[href$=jpg],a[href$=png],a[href$=jpeg]'), i=0, l=imgs.length; i<l; i++ ){
		imgs[i].addEventListener('click', makeModal, false ); 
	}
})();</script>
LIGHTBOX;
}
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
