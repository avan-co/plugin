/**
 * Platform Theme — form enhancements.
 */
(function () {
	'use strict';

	var i18n = window.mppForms || {};

	var toggles = document.querySelectorAll('.mpp-password-toggle');

	toggles.forEach(function (button) {
		var showLabel = button.dataset.showLabel || i18n.show || 'Show';
		var hideLabel = button.dataset.hideLabel || i18n.hide || 'Hide';

		button.addEventListener('click', function () {
			var targetId = button.getAttribute('data-target');
			var input = document.getElementById(targetId);

			if (!input) {
				return;
			}

			var isPassword = input.type === 'password';
			input.type = isPassword ? 'text' : 'password';
			button.textContent = isPassword ? hideLabel : showLabel;
			button.setAttribute('aria-label', isPassword ? hideLabel : showLabel);
		});
	});

	var strengthInput = document.querySelector('[data-password-strength]');
	var strengthLabel = document.querySelector('[data-password-strength-label]');

	if (strengthInput && strengthLabel) {
		strengthInput.addEventListener('input', function () {
			var value = strengthInput.value || '';
			var score = 0;

			if (value.length >= 8) {
				score++;
			}
			if (/[A-Z]/.test(value) && /[a-z]/.test(value)) {
				score++;
			}
			if (/\d/.test(value)) {
				score++;
			}
			if (/[^A-Za-z0-9]/.test(value)) {
				score++;
			}

			var labels = [
				strengthLabel.dataset.weak || i18n.weak || 'Weak password',
				strengthLabel.dataset.fair || i18n.fair || 'Fair password',
				strengthLabel.dataset.good || i18n.good || 'Good password',
				strengthLabel.dataset.strong || i18n.strong || 'Strong password'
			];

			if (!value.length) {
				strengthLabel.hidden = true;
				strengthLabel.textContent = '';
				return;
			}

			strengthLabel.hidden = false;
			strengthLabel.textContent = labels[Math.max(0, Math.min(score - 1, labels.length - 1))];
		});
	}
})();
