/**
 * Handles toggling for submenus with a slide effect
 * This version does not include support for a horizontal main nav (menu will be on side at all times)
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

	var i=0,
	links = nav.getElementsByTagName( 'a' ),
	parents = nav.querySelectorAll( '.menu-item-has-children' );

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
			document.removeEventListener('keyup', drawerEscKey );
		} else {
			htmlClass.add( o );
			button.setAttribute( x, 'true' );
			nav.setAttribute( x, 'true' );
			document.addEventListener('keyup', drawerEscKey );
		}
	}
	
	button.onclick = toggleDrawer;
	mask.onclick = toggleDrawer;
	
	function drawerEscKey(e){
		if( e.keyCode == 27 )
			toggleDrawer();
	}
	
})();