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

	var backdrop = document.querySelector('.mpp-nav-backdrop');

	if (!backdrop) {
		backdrop = document.createElement('div');
		backdrop.className = 'mpp-nav-backdrop';
		backdrop.setAttribute('hidden', 'hidden');
		document.body.appendChild(backdrop);
	}

	function setOpen(isOpen) {
		sidebar.classList.toggle('is-open', isOpen);
		toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');

		if (isOpen) {
			backdrop.classList.add('is-visible');
			backdrop.removeAttribute('hidden');
			document.body.classList.add('mpp-nav-open');
		} else {
			backdrop.classList.remove('is-visible');
			backdrop.setAttribute('hidden', 'hidden');
			document.body.classList.remove('mpp-nav-open');
		}
	}

	toggle.addEventListener('click', function () {
		setOpen(!sidebar.classList.contains('is-open'));
	});

	backdrop.addEventListener('click', function () {
		setOpen(false);
	});

	document.addEventListener('keydown', function (event) {
		if (event.key === 'Escape') {
			setOpen(false);
		}
	});

	sidebar.querySelectorAll('a').forEach(function (link) {
		link.addEventListener('click', function () {
			if (window.matchMedia('(max-width: 900px)').matches) {
				setOpen(false);
			}
		});
	});

	document.addEventListener('click', function (event) {
		if (!sidebar.classList.contains('is-open')) {
			return;
		}

		if (sidebar.contains(event.target) || toggle.contains(event.target)) {
			return;
		}

		setOpen(false);
	});
})();
