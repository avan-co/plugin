/**
 * Clear account flash-notice query parameters after display.
 */
(function () {
	'use strict';

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
