/**
 * Account menu dropdown in the header.
 */
(function () {
	'use strict';

	var root = document.querySelector('[data-account-menu]');

	if (!root) {
		return;
	}

	var trigger = root.querySelector('.mpp-account-menu__trigger');
	var panel = root.querySelector('.mpp-account-menu__panel');

	if (!trigger || !panel) {
		return;
	}

	function setOpen(isOpen) {
		trigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');

		if (isOpen) {
			panel.removeAttribute('hidden');
		} else {
			panel.setAttribute('hidden', 'hidden');
		}
	}

	trigger.addEventListener('click', function () {
		setOpen(panel.hasAttribute('hidden'));
	});

	document.addEventListener('click', function (event) {
		if (!root.contains(event.target)) {
			setOpen(false);
		}
	});

	document.addEventListener('keydown', function (event) {
		if (event.key === 'Escape') {
			setOpen(false);
		}
	});
})();
