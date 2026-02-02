/**
* Sliding drawer menu with sub-menu slide effect
* Improved and cleaned-up version
*/
(function () {
	
	var menu   = document.getElementById('main-menu'),
	nav = menu.parentElement,
	button = document.getElementById('menu-open'),
	mask   = document.getElementById('mask'),
	htmlClass = document.documentElement.classList;
	if (!(menu && button && mask)) return;
	
	button.onclick = mask.onclick = () => htmlClass.toggle('dopen');
	
	/* --------------------------------------------------
	Mobile drawer submenu sliding
	-------------------------------------------------- */
	nav.querySelectorAll('.menu-item-has-children').forEach(parent => {
		// Create toggle arrow
		var tog = document.createElement('span');
		tog.className = 'menutog';
		tog.onclick = openSubmenu;
		
		// Create back button inside the .navdd wrapper
		var back = document.createElement('span');
		back.className = 'menuback';
		back.textContent = 'back';
		back.onclick = closeSubmenu;
		
		// Insert elements
		parent.insertBefore(tog, parent.lastElementChild); // tog before .navdd
		parent.querySelector('ul').prepend(back); // back inside .sub-menu
		
		// If the main link is just a placeholder (#), make it open the submenu too
		if (parent.firstElementChild.getAttribute('href') === '#') {
			parent.firstElementChild.onclick = openSubmenu;
		} else {
			parent.classList.add('seperate-tog');
		}
	});
	
	function openSubmenu(e) {
		e.preventDefault();
		
		var li = this.parentElement;
		li.classList.add('focus');
		
		// Set initial height if not already set, so CSS can animate to next height
		if (!nav.style.height) {
			nav.style.height = menu.offsetHeight + 'px';
		}
		
		// Expand to fit the open submenu (.navdd)
		nav.style.height = li.lastElementChild.scrollHeight + 'px';
	}
	
	function closeSubmenu() {
		var li = this.closest('li'); // parent <li>
		li.classList.remove('focus');
		var container = li.parentElement.parentElement; // either another .navdd or the main nav
		nav.style.height = (container.classList.contains('navdd') ? container.scrollHeight : menu.offsetHeight) + 'px';
	}
})();