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
	
	nav.querySelectorAll('.menu-item-has-children > a').forEach(parent => {
		
		var tog = document.createElement('span');
		tog.className = 'menutog';
		tog.onclick = function(){this.parentElement.classList.toggle('focus')};
		parent.parentElement.insertBefore(tog, parent.nextSibling);
		
	});
	
})();