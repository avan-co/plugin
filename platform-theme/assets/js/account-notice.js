/**
 * Clear account flash-notice query parameters and move focus to the message.
 */
(function () {
	'use strict';

	var notice = document.querySelector('.mpp-alert');

	if (notice) {
		notice.setAttribute('tabindex', '-1');
		notice.focus({ preventScroll: true });
	}

	var params = new URLSearchParams(window.location.search);

	if (!params.has('mpp_notice')) {
		return;
	}

	params.delete('mpp_notice');
	params.delete('mpp_message');

	var next = window.location.pathname;

	if (params.toString()) {
		next += '?' + params.toString();
	}

	window.history.replaceState({}, document.title, next);
})();
