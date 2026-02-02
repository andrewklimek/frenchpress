/**
* Handles toggling for submenus
*/
(function() {
	
	var menu   = document.getElementById('main-menu'),
	nav = menu.parentElement,
	button = document.getElementById('menu-open'),
	mask   = document.getElementById('mask'),
	htmlClass = document.documentElement.classList;
	if (!(menu && button && mask)) return;
	
	button.onclick = mask.onclick = () => htmlClass.toggle('dopen');
	
	/* --------------------------------------------------
	Desktop focus handling
	-------------------------------------------------- */
	if (htmlClass.contains('dnav')) {
		nav.addEventListener('focusin', (e) => {
			
			// Remove .focus from ALL menu items first
			nav.querySelectorAll('.menu-item.focus').forEach(item => {
				item.classList.remove('focus');
			});
			
			// Then add .focus to the focused link AND all its parent menu items
			const link = e.target.closest('a');
			if (!link) return;
			
			let item = link.closest('.menu-item');
			while (item && nav.contains(item)) {
				item.classList.add('focus');
				item = item.parentElement.closest('.menu-item');
			}
		});
	}
	
	nav.querySelectorAll('.menu-item-has-children > a').forEach(parent => {
		
		var tog = document.createElement('span');
		tog.className = 'menutog';
		tog.onclick = function(){this.parentElement.classList.toggle('focus')};
		parent.parentElement.insertBefore(tog, parent.nextSibling);
		
	});

	// fix alignment of submenus if they'll overflow the viewport
	function adjustSubmenuIfNeeded() {
		document.querySelectorAll('.navdd').forEach((submenu) => {
			if (!submenu) return;
			// Reset any previous adjustment (in case window was resized, etc.)
			submenu.style.left = '';
			submenu.style.right = '';
			if (submenu.getBoundingClientRect().right - window.innerWidth > -9) {
			submenu.style.right = submenu.classList.contains('sub') ? '100%' : '0';
			submenu.style.left = 'auto';
			}
		});
	}
	adjustSubmenuIfNeeded();
	// check on resize
	let resizeTimer;
	window.addEventListener('resize', () => {
		clearTimeout(resizeTimer);
		resizeTimer = setTimeout(adjustSubmenuIfNeeded, 120);
	});
	
})();