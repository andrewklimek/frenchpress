<?php

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
	$options = $request->get_param('opts') ?: [];
	error_log( print_r( $options, true ) );
	foreach ($options as $option => $value) {
	    if ('' === $value) delete_option($option);
        else update_option($option, $value, true);
	}
	return "Saved";
}


add_action( 'admin_menu', 'frenchpress_admin_menu' );
function frenchpress_admin_menu() {
	add_submenu_page( 'themes.php', 'Frenchpress Theme Options', 'Theme Options', 'import', 'frenchpress', 'frenchpress_options_page', 1 );
}

function frenchpress_options_define() {

	$fields = array_fill_keys([
		// 'no_drawer',
		'inline_css',
		'site_width','content_width','sidebar_width','menu_breakpoint',
		'post_layout','page_layout','index_layout',
		'sidebar_position_desktop','sidebar_position_mobile','sidebar_centered_content',
		'dark_theme',
		'slide_in_images',
		'image_lightbox',
		'no_blog_thumbnails',
		'blog_layout_desktop','blog_layout_mobile_breakpoint','blog_layout_mobile','column_minimum_width',
		'blog_excerpt',
		'entry_meta','entry_meta_time','entry_meta_byline',
		'entry_footer',
		'page_titles','hide_archive_prefix',
		'feat_image_bg', 'feat_image_bg_location', 'feat_image_bg_color_overlay', 'title_in_header',
		'disable_comments','avatar_size','comment_form_unstyle','comment_form_website_field','comment_dates',
		'mini_toolbar',
		'boring_404',
		'style_wp_login',
		'dont_redirect_wplogin',
		'header_footer_on_login',
		'mobile_nav',
		'nav_position', 'nav_align','branding_align',
		'desktop_drawer',
		'logo',
		'use_simple_menu', 'simple_menu',
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

	$fields['blog_layout_desktop'] = ['options' => ['list','grid'], 'show' => ['no_blog_thumbnails' => 'empty'] ];
	$fields['blog_layout_mobile'] = $fields['blog_layout_desktop'];
	$fields['blog_layout_mobile_breakpoint'] = ['type' => 'number', 'placeholder' => '768', 'show' => ['no_blog_thumbnails' => 'empty'] ];
	$fields['column_minimum_width'] = ['type' => 'number', 'desc' => 'set in rem units. 13 - 18 is a good range', 'placeholder' => '15', 'show' => ['blog_layout_desktop' => 'grid', 'blog_layout_mobile' => 'grid'] ];

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
	$fields['nav_align']['options'] = ["left","right","center","justified"];
	$fields['branding_align']['options'] = ["left","right","center"];

	$fields['logo']['type'] = 'text';
	$fields['logo']['show'] = ['use_custom_code_for_branding' => 'empty'];

	$fields['simple_menu']['type'] = 'code';
	$fields['simple_menu']['show'] = 'use_simple_menu';
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
	$fields['site_width'] = $fields['content_width'] = $fields['sidebar_width'] = [ 'type' => 'number', 'required' => 1 ];
	$fields['mobile_nav']['options'] = ["fullscreen","slide","tree","none"];// tree not implemented yet.
	$fields['avatar_size']['type'] = 'number';
	$fields['avatar_size']['desc'] = 'in pixels. 0 disables avatars.';

	$fields['logo']['desc'] = 'URL to logo. SVGs will be inlined so the fill color can be manipulated.';

	$fields['custom_css']['type'] = 'code';

	// Defaults
	$fields['site_width']['default'] = 1050;
	$fields['content_width']['default'] = 700;
	$fields['sidebar_width']['default'] = 350;
	$fields['menu_breakpoint']['default'] = 782;// same as wp admin bar
	$fields['sidebar_position_desktop']['default'] = 'right';
	$fields['sidebar_position_mobile']['default'] = 'bottom';
	$fields['nav_position']['default'] = 'right';
	$fields['nav_align']['default'] = 'right';
	$fields['post_layout']['default'] = 'content-width';
	$fields['page_layout']['default'] = 'content-width';
	$fields['index_layout']['default'] = 'site-width';

	return [ 'frenchpress_' => $fields ];
}

function frenchpress_option( $key, $fallback = null ) {

	$return = get_option( $key, null );
	if ( $return !== null ) {
		return $return;
	} else {
		$schema = frenchpress_options_define();
	}


    static $schema = null;
    if ( $schema === null ) $schema = frenchpress_options_define();

    foreach ( $schema as $group => $fields ) {
        if ( !isset($fields[$key]) && !array_key_exists($key, $fields) ) continue;

        $def = $fields[$key]['default'] ?? $fallback ?? '';

        if ( substr($group, -1) === '_' ) {
            // serialized group
            $data = get_option( $group, [] );
            return $data[$key] ?? $def;
        } else {
            // flat prefixed
            return get_option( $group . $key, $def );
        }
    }
    return $fallback;
}

function frenchpress_options_page() {
	$options = frenchpress_options_define();
	$endpoint = rest_url('frenchpress/v1/settings');
	$title = "Frenchpress Theme Options";
	require( __DIR__.'/settings-framework.php' );// needs $options, $endpoint, $title
}

function frenchpress_settings( $option = null ) {
	static $values = [];
	if ( ! $values ) {
		$options = frenchpress_options_define();
		foreach ( $options as $group => $fields ) {
			$serial = substr($group, -1) !== '_';
			if ( $serial ) {
				$saved = get_option( $group, [] );
                foreach ( $fields as $k => $f ) {
                    $values[ $group ][ $k ] = $saved[ $k ] ?? ( $f['default'] ?? '' );
                }
			} else {
				foreach ( $fields as $k => $f ) {
					$values[$k] = get_option( "{$group}{$k}", $f['default'] ?? '' );
				}
			}
		}
	}
	if ( $option === null ) return $values;
	return $values[$option] ?? null;
}