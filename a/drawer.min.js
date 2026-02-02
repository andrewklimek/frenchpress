/**
* Simple mobile drawer – no submenus
* Matches coding style & structure of sub-slide-new.js
*/
(function () {
    var menu   = document.getElementById('main-menu'),
    button = document.getElementById('menu-open'),
    mask   = document.getElementById('mask'),
    htmlClass = document.documentElement.classList;
    if (!(menu && button && mask)) return;
    
    button.onclick = mask.onclick = () => htmlClass.toggle('dopen');
})();