/**
 * Handles toggling for submenus with a slide effect
 */
(function() {
	
	var menu = document.getElementById( 'main-menu' ),
	nav = menu.parentElement,
	// current = menu.querySelector('.current-menu-item'),// dunno if it's worth it to make a click on the current menu item simple close the drawer and not reload. see onload action below.
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
	parents = nav.querySelectorAll( '.menu-item-has-children' );

	
	// This is all that’s needed for the mobile drawer nav
	function slide(e){
		e.preventDefault();
		if ( ! nav.style.height ) nav.style.height = menu.offsetHeight+"px";
		this.parentElement.classList.toggle('focus');
		this.parentElement.parentElement.classList.toggle('focus');
		nav.style.height = Math.max( menu.offsetHeight, this.parentElement.lastElementChild.scrollHeight ) + "px";
	};
	for ( i=0; i < parents.length; ++i ) {
		var tog = document.createElement('span'),
		back = document.createElement('span');
		back.className = "menuback";
		back.textContent = "back";
		back.onclick = function(){
			var p=this.parentElement.parentElement;
			p.classList.toggle('focus');
			p=p.parentElement;
			p.classList.toggle('focus');
			nav.style.height = Math.max( menu.offsetHeight, p.offsetHeight ) + "px";
		};
		parents[i].lastElementChild.insertAdjacentElement('afterbegin',back);
		tog.className = "menutog";
		tog.onclick = slide;
		// parents[i].firstChild.insertAdjacentElement('afterend',tog);
		parents[i].insertBefore(tog, parents[i].lastElementChild);
		if ( parents[i].firstChild.getAttribute('href')=="#" ){
			parents[i].firstChild.onclick = slide;
		} else {
			parents[i].className += ' seperate-tog';
		}
	}
	
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