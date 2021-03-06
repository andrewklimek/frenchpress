/**
 * navigation.js
 *
 * Handles toggling the navigation menu for small screens and enables tab
 * support for dropdown menus.
 */

// an idea for fixing sub-menus on the far right.
//if ( document.body.clientWidth - document.querySelector('#menu-item-142 ul').getBoundingClientRect().right <= 0 ) { document.querySelector('#menu-item-142 ul').style.right=0; }

( function() {
	var container, button, menu, links, subMenus, height;

	container = document.getElementById( 'site-navigation' );
	if ( ! container ) {
		return;
	}
	
	
	function checkMobileMenu(){
		if ( !~document.body.className.indexOf('mobile-nav-open') ){
			container.className = container.className.replace(' mobile','');
			if ( !~container.className.indexOf('desktop') ){
				container.className += ' desktop';
			}
			if ( container.offsetWidth / container.offsetHeight < 2.4 ){
				//appears too tall but lets test removing it
				// container.style.display = 'none';
				// refHeight = document.getElementById( 'site-header-main' ).offsetHeight;
				// container.style.display = '';
				// if ( document.getElementById( 'site-header-main' ).offsetHeight > refHeight ){
				container.className = container.className.replace(' desktop',' mobile');
				// container.className += ' mobile';
				// }
			}
		}
	}
	// checkMobileMenu();
	// window.addEventListener( 'resize', checkMobileMenu );
	// window.addEventListener( 'loaded', checkMobileMenu );

	function checkForDesktop(){
		if ( window.innerWidth > breakpoint ) {
			if ( ~document.body.className.indexOf('mobile') ) {
				document.body.className = document.body.className.replace(' mobile',' desktop');
			}
		} else if ( ~document.body.className.indexOf('desktop') ) {
			document.body.className = document.body.className.replace(' desktop',' mobile');
		}
	}
	// document.body.className += ' mobile';// added to header.php

	var breakpoint = ( typeof(BREAKPOINTS) !== "undefined" ) ? BREAKPOINTS.main : 860;
	// Set custom breakpoint in functions.php:
	// add_action( 'wp_enqueue_scripts', function() {
	// wp_localize_script( 'frenchpress-navigation', 'BREAKPOINTS', array( 'main' => 800 ) );
	// }, 11 );// 11 if used in a child theme so it's called after the parent theme enqueues this JS file
	checkForDesktop();
	window.addEventListener( 'resize', checkForDesktop );



	button = container.querySelector( '.menu-toggle' );
	if ( 'undefined' === typeof button ) {
		return;
	}

	menu = container.getElementsByTagName( 'ul' )[0];

	// Hide menu toggle button if menu is empty and return early.
	if ( 'undefined' === typeof menu ) {
		button.style.display = 'none';
		return;
	}

	menu.setAttribute( 'aria-expanded', 'false' );
	if ( -1 === menu.className.indexOf( 'nav-menu' ) ) {
		menu.className += ' nav-menu';
	}

	button.onclick = function() {
		if ( ~ document.body.className.indexOf( 'mobile-nav-open' ) ) {
			document.body.className = document.body.className.replace( ' mobile-nav-open', '' );
			button.setAttribute( 'aria-expanded', 'false' );
			menu.setAttribute( 'aria-expanded', 'false' );
		} else {
			document.body.className += ' mobile-nav-open';
			button.setAttribute( 'aria-expanded', 'true' );
			menu.setAttribute( 'aria-expanded', 'true' );
		}
	};

	// Get all the link elements within the menu.
	links    = menu.getElementsByTagName( 'a' );
	subMenus = menu.getElementsByTagName( 'ul' );

	// Set menu items with submenus to aria-haspopup="true".
	for ( var i = 0, len = subMenus.length; i < len; i++ ) {
		subMenus[i].parentNode.setAttribute( 'aria-haspopup', 'true' );
	}

	// Each time a menu link is focused or blurred, toggle focus.
	for ( i = 0, len = links.length; i < len; i++ ) {
		links[i].addEventListener( 'focus', toggleFocus, true );
		links[i].addEventListener( 'blur', toggleFocus, true );
		links[i].addEventListener( 'click', toggleClick, true );
	}

	/**
	 * Sets or removes .focus class on an element.
	 */
	function toggleFocus() {
		var self = this;

		// Move up through the ancestors of the current link until we hit .nav-menu.
		while ( -1 === self.className.indexOf( 'nav-menu' ) ) {

			// On li elements toggle the class .focus.
			if ( 'li' === self.tagName.toLowerCase() ) {
				if ( -1 !== self.className.indexOf( 'focus' ) ) {
					self.className = self.className.replace( ' focus', '' );
				} else {
					self.className += ' focus';
				}
			}

			self = self.parentElement;
		}
	}
	

	/**
	 * Sets or removes .clicked class on an element.
	 */
	function toggleClick() {
		if ( ~ this.parentElement.className.indexOf( 'clicked' ) ) {
			this.parentElement.className = this.parentElement.className.replace( ' clicked', '' );
		} else {
			this.parentElement.className += ' clicked';
		}
	}
} )();
