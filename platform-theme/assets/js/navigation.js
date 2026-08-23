/**
 * Platform Theme — Navigation toggle for mobile.
 */
(function () {
	'use strict';

	var toggle = document.querySelector('.mpp-nav-toggle');
	var sidebar = document.querySelector('.mpp-sidebar');

	if (!toggle || !sidebar) {
		return;
	}

	toggle.addEventListener('click', function () {
		var isOpen = sidebar.classList.toggle('is-open');
		toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
	});
})();
