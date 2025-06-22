/**
 * Handles toggling for submenus
 */
(function() {
	
	var menu = document.getElementById( 'main-menu' ),
	nav = menu.parentElement,
	button = document.getElementById( 'menu-open' ),
	mask = document.getElementById( 'mask' ),
	htmlClass = document.documentElement.classList,
	x = 'aria-expanded',
	o = 'dopen';
	
	if ( ! ( menu && button && mask ) ) return;

	/*
	* Begin Submenu Stuff
	*/
	var i=0,
	parentLink = menu.querySelectorAll( '.menu-item-has-children > a' );
	
	// This is all that’s needed for the mobile drawer nav
	for ( i=0; i < parentLink.length; ++i ) {
		var btn = document.createElement('span');
		btn.className = "menutog";
		btn.addEventListener('click', function(){this.parentElement.classList.toggle('focus');});
		parentLink[i].parentElement.insertBefore(btn, parentLink[i].nextSibling);
	}

	/*
	* End Submenu Stuff
	*/
	
	function toggleDrawer() {
		if ( htmlClass.contains( o ) ) {
			htmlClass.remove( o );
			button.removeAttribute( x );
			nav.removeAttribute( x );
			// document.removeEventListener('keyup', drawerEscKey );
		} else {
	// }
	// function openMenu() {
			htmlClass.add( o );
			button.setAttribute( x, 'true' );
			nav.setAttribute( x, 'true' );
			// document.addEventListener('keyup', drawerEscKey );
		}
	}
   
	button.onclick = toggleDrawer;
	// document.getElementById( 'menu-close' ).onclick = toggleDrawer;
	mask.onclick = toggleDrawer;
	// current.onclick = function(e){ htmlClass.contains('mnav') && e.preventDefault(), toggleDrawer(); };
	
	// function drawerEscKey(e){
	// 	if( e.keyCode == 27 )
	// 		toggleDrawer();
	// }

})();