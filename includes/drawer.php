<?php
/**
* This file handles crazy AI to decide menu ought to be in the drawer, and adds the correct JS and CSS to make it happen.
* Nav widgets placed in the "Drawer" widget area are treated differently.  It is assumed this menu should appear even on desktop,
* so the drawer defaults to open and shifts the content's left margin.
* If the menu does not have submenus, the excess code is not loaded.
*
* Cut out this entire file with add_filter( 'frenchpress_drawer', function(){ return false; });
* This just gives you a normal header menu even on mobile.  You probably won't want submenu items.
*
* This could probably be done cleaner and may have glitches, but I have to move on for now!
*
*/


// Find if there is any submenu registered
// SELECT * FROM `wp_postmeta` WHERE `meta_key` = '_menu_item_menu_item_parent' AND `meta_value` > '0' LIMIT 1


/**
* This function captures the output of the "drawer" widget
* If the widget is active, a filter is added to 'wp_nav_menu_args'
* This filter will only run if a nav menu is processed while the 'drawer' widgets are processed.
* Therefore, we know the nav has manually been placed in the drawer.
* This is taken as a desire to have the drawer always present, and open by deault on desktop.
* The filter sets up various other filters/actions for this to happen.
*/
add_action( 'wp_enqueue_scripts', 'frenchpress_process_drawer', 1 );

function frenchpress_process_drawer(){
	global $frenchpress_drawer;

	if ( empty( $frenchpress_drawer ) ) {
		$frenchpress_drawer = array( 'layout' => '', 'sidebar' => '' );
	}

	global $sidebars_widgets;
	if ( !empty( $sidebars_widgets['drawer'] ) ) {
	// if ( is_active_sidebar( 'drawer' ) ) {

		/**
		* temporarily adds relevant functions to the filter 'wp_nav_menu_args'
		* and then processes the sidebar (and stores to a global).
		* The 'wp_nav_menu_args' filter will run if a nav menu is processed within this sidebar.
		* The filter also removes itself so that it will only run once even if there are multiple navs in this sidebar.
		*/
		add_filter( 'wp_nav_menu_args', 'frenchpress_set_main_menu_in_drawer', 1 );
		ob_start();
		dynamic_sidebar( 'drawer' );
		$frenchpress_drawer['sidebar'] = ob_get_clean();
		remove_filter( 'wp_nav_menu_args', 'frenchpress_set_main_menu_in_drawer', 1 );
	}
	if ( ! $frenchpress_drawer['layout'] ) // normal case, not desk drawer
	{
		$main_menu = frenchpress_get_main_menu_details();
		if ( $main_menu['submenus'] )
			$frenchpress_drawer['layout'] = 'submenu';
		else
			$frenchpress_drawer['layout'] = 'drawer';
	}
}

function frenchpress_set_main_menu_in_drawer($args){

	// remove self so as not to run on additinal navs
	remove_filter( 'wp_nav_menu_args', 'frenchpress_set_main_menu_in_drawer', 1 );

	// if this runs, it's the main menu so we can remove the normal main menu hook
	remove_filter( 'wp_nav_menu_args', 'frenchpress_set_main_menu' );

	// we need a slightly different mobile-test script that sets drawer open by default on desktop.
	remove_action( 'wp_print_scripts', 'frenchpress_mobile_test' );
	add_action( 'wp_print_scripts', 'frenchpress_mobile_test_desk_drawer' );

	// add the drawer HTML at the top of the page header
	add_action( 'frenchpress_header_top', 'frenchpress_print_desk_drawer' );

	// for now, I'm going to check for submenus this way still for drawer widget menus
	// check for slug because that would mean this "drawer" menu was just added, and the previously cached
	// main menu was different.  This drawer menu never saves a slug because it isn't needed.
	if ( false === ( $main_menu = get_transient( 'frenchpress_main_menu' ) ) || $main_menu['slug'] )
	{
		add_filter( "wp_nav_menu_items", "frenchpress_maybe_enqueue_submenu_js" );
	}
	else
	{
		$GLOBALS['frenchpress_drawer']['layout'] = $main_menu['submenus'] ? 'sub-side' : 'side-no-sub';
	}

	$args = frenchpress_add_main_nav_args( $args );

	return $args;
}

/**
* Test for mobile breakpoint and set HTML class
* The f() function here is a different version than the standard one in functions.php
* It’s for opening the drawer by default on side-nav-only sites.
* If 'i' is undefined, this is the Initial run, so set the drawer open on desktop sizes.
* During resize, 'i' will have the event object and so it won't re-open the drawer if user had closed it.
*/
function frenchpress_mobile_test_desk_drawer() {
	$breakpoint = isset( $GLOBALS['frenchpress']->menu_breakpoint ) ? $GLOBALS['frenchpress']->menu_breakpoint : 860;
	if ( ! $breakpoint ) return;
	echo "<script>(function(){var c=document.documentElement.classList;";
	echo "function f(i){if(!window.innerWidth){setTimeout(f,50)}else if(window.innerWidth>{$breakpoint}){c.remove('mnav');c.add('dnav');i||c.add('dopen')}else{c.remove('dnav');c.add('mnav')}}";
	echo "f();window.addEventListener('resize',f);})();</script>";
}


function frenchpress_print_desk_drawer(){

	global $frenchpress_drawer;
	// poo($frenchpress_drawer['sidebar']);
	echo '<div class="drawer desk-drawer">';
	echo $frenchpress_drawer['sidebar'];// contains output of dynamic_sidebar( 'drawer' );
	echo '<span id=menu-close role=button aria-controls=main-menu>×</span></div>';

}

function frenchpress_get_main_menu_details( $skip_cache = false ) {

	// check if the slug is specified. this should really be in theme mods I think.
	$main_menu = apply_filters( 'frenchpress_main_menu_slug', array() );

	if ( ! $main_menu ) {

		// check if the slug has been cached
		if ( $skip_cache || false === ( $main_menu = get_transient( 'frenchpress_main_menu' ) ) || ! $main_menu['slug'] ) {

			$menus = wp_get_nav_menus();

			// if there’s only 1 mune OR if there’s no menu slug with the word "main" in it, then just use the first menu
			if ( 1 === count( $menus ) || ! strpos( var_export( $menus, true ), "main" ) ) {

				$main_menu['slug'] = $menus[0]->slug;
				// $main_menu['id'] = $menus[0]->term_id;

			} else {

				// there are multiple menus and the word "main" is in there somewhere so find which menu it is
				foreach ( $menus as $menu ) {

					if( false !== strpos( $menu->slug, "main" ) ) {// technically "main" could be in $menu->slug and not the slug...

						$main_menu['slug'] = $menu->slug;
						// $main_menu['id'] = $menu->term_id;

						break;// found it, so quit this madness

					}
				}
			}

			if ( ! $main_menu ) {

				error_log("something’s wrong with finding the main menu slug");
				return false;
			}

			// check if there are submenus
			$main_menu['submenus'] = false;
			$menu_items = wp_get_nav_menu_items( $main_menu['slug'] );
			foreach( $menu_items as $item ) {
				if ( $item->menu_item_parent > 0 ) {
					$main_menu['submenus'] = true;
					break;
				}
			}

			// cache it
			set_transient( 'frenchpress_main_menu', $main_menu );
		}
	}
	return $main_menu;
}

// when menus are updated, flush cache so the above function runs again
add_action( 'wp_update_nav_menu', function(){ delete_transient( 'frenchpress_main_menu'); } );

function frenchpress_set_main_menu( $args ) {

	/**
	* The preliminary object & empty checks are cause depending how the menu was called this data may not be present.
	* AFAIK it will be present for the menu widget, and that's what I plan to use, so it's ok for now.
	* For normal wp_nav_menu calls, $args['menu'] might == the slug, but it could be the ID or name too.
	* See https://developer.wordpress.org/reference/functions/wp_nav_menu/
	*/
	if ( ! is_object( $args['menu'] ) || empty( $args['menu']->slug ) ) return $args;

	// attempt to get the slug of the main menu
	$menu_menu = frenchpress_get_main_menu_details();

	// if this menu matches the slug specified as the main menu, add the markup for the drawer.
	if ( $menu_menu['slug'] && $menu_menu['slug'] === $args['menu']->slug ) {

		// add div class=drawer and close button around nav.  This is only for horizontal main nav in header.
		// Only real need for this wrapper div is so additional widgets can be added inside it too via the drawer widget area.
		// These would be widgets for the mobile drawer but hidden in desktop header mode.
		add_filter( 'wp_nav_menu', 'frenchpress_add_drawer_markup_to_main_menu', 10, 2 );

		$args = frenchpress_add_main_nav_args( $args );
	}

	return $args;
}
add_filter( 'wp_nav_menu_args', 'frenchpress_set_main_menu' );


function frenchpress_add_main_nav_args( $args ) {

	$args['container_class'] = trim( $args['container_class'] . ' main-nav' );
	$args['menu_id'] = 'main-menu';
	$args['menu_class'] .= ' fff fff-middle fff-' . apply_filters( 'frenchpress_main_menu_align', 'right' );// removed fff-pad for now
   // $args['items_wrap'] .= '<span id=menu-close role=button aria-controls=main-menu>×</span>';
	$args['item_spacing'] = 'discard';

	// add_filter( "wp_nav_menu_{$args['menu']->slug}_items", "frenchpress_maybe_enqueue_submenu_js" );

	return $args;
}


function frenchpress_maybe_enqueue_submenu_js( $items ) {

	global $frenchpress_drawer;

	remove_filter( "wp_nav_menu_items", "frenchpress_maybe_enqueue_submenu_js" );

	$main_menu = array( 'submenus' => false, 'slug' => false );

	if ( false !== strpos($items, "menu-item-has-children" ) )
	{
		$frenchpress_drawer['layout'] = 'sub-side';
		$main_menu['submenus'] = true;
	}
	else
	{
		$frenchpress_drawer['layout'] = 'side-no-sub';
		$main_menu['submenus'] = false;
	}

	set_transient( 'frenchpress_main_menu', $main_menu );

	return $items;
}


add_action('wp_before_admin_bar_render',function(){echo '<style>.mnav .drawer,.desk-drawer{padding-top:32px!important} @media(max-width:782px){.mnav .drawer{padding-top:46px!important}}</style>';});


function frenchpress_add_drawer_markup_to_main_menu( $nav_menu, $args ) {

	remove_filter( 'wp_nav_menu', 'frenchpress_add_drawer_markup_to_main_menu', 10, 2 );

	global $frenchpress_drawer;

	// $frenchpress_drawer['sidebar'] contains output of dynamic_sidebar( 'drawer' );

	return "<div class=drawer>{$nav_menu}{$frenchpress_drawer['sidebar']}</div>";

	/**
	* below was for adding buttons, disabled for now...
	* I think this should always be in the header branding section no matter hwere the actual menu is.
	*
	return '<div id=obfuscator></div>
		' . $nav_menu . '
		<div id=menu-toggle role=button aria-controls=main-menu aria-expanded=false class=fffi>
			<div class=menu-tog></div><div class=menu-tog></div><div class=menu-tog></div>
			<span id=menu-toggle-label>Menu</span>
		</div>';
	*/
	
	/**
	*  old SVG buttons:
	*
	<svg id=menu-toggle-svg xmlns=http://www.w3.org/2000/svg width=24 height=24 viewBox="0 0 24 24">
		<path id=menu-toggle-close d="M13.4 12l9.3-9.3c0.4-0.4 0.4-1 0-1.4 -0.4-0.4-1-0.4-1.4 0L12 10.6 2.7 1.3c-0.4-0.4-1-0.4-1.4 0 -0.4 0.4-0.4 1 0 1.4L10.6 12l-9.3 9.3c-0.4 0.4-0.4 1 0 1.4 0.4 0.4 1 0.4 1.4 0L12 13.4l9.3 9.3c0.4 0.4 1 0.4 1.4 0 0.4-0.4 0.4-1 0-1.4L13.4 12z"/>
		<path id=menu-toggle-open  d="M23 20H1c-0.6 0-1 0.4-1 1s0.4 1 1 1h22c0.6 0 1-0.4 1-1S23.6 20 23 20zM23 11H1c-0.6 0-1 0.4-1 1 0 0.6 0.4 1 1 1h22c0.6 0 1-0.4 1-1C24 11.4 23.6 11 23 11zM1 4h22c0.6 0 1-0.4 1-1 0-0.6-0.4-1-1-1H1C0.4 2 0 2.4 0 3 0 3.6 0.4 4 1 4z"/>
	</svg>
	*/
}

/**
* DRAWER WIDGET REGISTRATION
* If any widgets are placed in this Drawer area,
* they will appear in the drawer, below the navigation.
* The drawer is of course usually only active on mobile.
* Usage example: contact info which is in the header on desktop but hidden on mobile
*/
function frenchpress_drawer_widgets_init() {
	register_sidebar( array(
		'name'		  => 'Drawer',
		'id'			=> 'drawer',
		'description'   => 'Widgets will appear in the drawer (typically only used on mobile) below the navigation.',
		'before_widget' => '<aside id="%1$s" class="widget drawer-widget %2$s">',
		'after_widget'  => "</aside>\n",
		'before_title'  => '<h3 class=widgettitle>',
		'after_title'   => "</h3>\n",
	) );
}
add_action( 'widgets_init', 'frenchpress_drawer_widgets_init' );
