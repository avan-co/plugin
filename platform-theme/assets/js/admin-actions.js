/**
 * Admin destructive-action confirmations and UX helpers.
 */
(function () {
	'use strict';

	var STORAGE_SCROLL = 'mppAdminScrollY';
	var STORAGE_HASH = 'mppAdminHash';
	var i18n = window.mppAdminActions || {};

	function t(key, fallback) {
		return i18n[key] || fallback;
	}

	function parseInitialGranted(form) {
		var raw = form.getAttribute('data-initial-granted');

		if (!raw) {
			return [];
		}

		try {
			var parsed = JSON.parse(raw);
			return Array.isArray(parsed) ? parsed.map(function (id) {
				return parseInt(id, 10);
			}) : [];
		} catch (error) {
			return [];
		}
	}

	function getPermissionImpact(form) {
		var initial = parseInitialGranted(form);
		var checked = [];
		var checkboxes = form.querySelectorAll('input[name="permission_ids[]"]');

		checkboxes.forEach(function (input) {
			if (input.checked) {
				checked.push(parseInt(input.value, 10));
			}
		});

		var granted = 0;
		var revoked = 0;

		checked.forEach(function (id) {
			if (initial.indexOf(id) === -1) {
				granted += 1;
			}
		});

		initial.forEach(function (id) {
			if (checked.indexOf(id) === -1) {
				revoked += 1;
			}
		});

		return { granted: granted, revoked: revoked };
	}

	function getConfirmMessage(form) {
		var message = form.getAttribute('data-mpp-confirm');

		if (message) {
			return message;
		}

		if (form.classList.contains('mpp-role-permissions')) {
			var base = form.getAttribute('data-mpp-confirm-save') || t('savePermissions', 'Save permission changes for this role?');
			var impact = getPermissionImpact(form);

			if (impact.granted || impact.revoked) {
				return base + ' ' + t('impactPrefix', 'This will grant') + ' ' + impact.granted + ' ' + t('impactGranted', 'and revoke') + ' ' + impact.revoked + ' ' + t('impactSuffix', 'permissions.');
			}

			return base;
		}

		return '';
	}

	document.querySelectorAll('form[data-mpp-confirm], form.mpp-role-permissions').forEach(function (form) {
		form.addEventListener('submit', function (event) {
			var message = getConfirmMessage(form);

			if (!message) {
				return;
			}

			if (!window.confirm(message)) {
				event.preventDefault();
				return;
			}

			try {
				sessionStorage.setItem(STORAGE_SCROLL, String(window.scrollY || 0));
				sessionStorage.setItem(STORAGE_HASH, window.location.hash || '');
			} catch (error) {
				// Ignore storage failures.
			}
		});
	});

	try {
		var savedScroll = sessionStorage.getItem(STORAGE_SCROLL);
		var savedHash = sessionStorage.getItem(STORAGE_HASH);

		if (savedScroll !== null) {
			window.scrollTo(0, parseInt(savedScroll, 10) || 0);
			sessionStorage.removeItem(STORAGE_SCROLL);
		}

		if (savedHash) {
			window.location.hash = savedHash;
			sessionStorage.removeItem(STORAGE_HASH);
		}
	} catch (error) {
		// Ignore storage failures.
	}

	var notice = document.querySelector('.mpp-admin-page .mpp-alert, .mpp-page-body--admin .mpp-alert');

	if (notice) {
		notice.setAttribute('tabindex', '-1');
		notice.focus({ preventScroll: true });
	}

	var params = new URLSearchParams(window.location.search);

	if (params.has('mpp_notice')) {
		params.delete('mpp_notice');
		params.delete('mpp_message');

		var next = window.location.pathname;

		if (params.toString()) {
			next += '?' + params.toString();
		}

		if (window.location.hash) {
			next += window.location.hash;
		}

		window.history.replaceState({}, document.title, next);
	}
})();
