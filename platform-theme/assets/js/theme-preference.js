/**
 * Theme preference (light / dark) with local persistence.
 */
(function () {
	'use strict';

	var STORAGE_KEY = 'mppTheme';
	var i18n = window.mppThemePreference || {};

	function t(key, fallback) {
		return i18n[key] || fallback;
	}

	function getStoredTheme() {
		try {
			return localStorage.getItem(STORAGE_KEY);
		} catch (error) {
			return null;
		}
	}

	function setStoredTheme(theme) {
		try {
			localStorage.setItem(STORAGE_KEY, theme);
		} catch (error) {
			// Ignore storage failures.
		}
	}

	function resolveTheme() {
		var stored = getStoredTheme();

		if (stored === 'light' || stored === 'dark') {
			return stored;
		}

		if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
			return 'dark';
		}

		return 'light';
	}

	function applyTheme(theme) {
		document.documentElement.setAttribute('data-theme', theme);
		document.body.classList.toggle('mpp-theme-dark', theme === 'dark');
	}

	function updateToggleLabels(theme) {
		document.querySelectorAll('[data-mpp-theme-toggle]').forEach(function (button) {
			var next = theme === 'dark' ? 'light' : 'dark';
			var label = next === 'dark' ? t('dark', 'Dark mode') : t('light', 'Light mode');
			button.setAttribute('aria-label', label);
			button.setAttribute('title', label);
			button.textContent = next === 'dark' ? t('darkShort', 'Dark') : t('lightShort', 'Light');
		});
	}

	function init() {
		var theme = resolveTheme();
		applyTheme(theme);
		updateToggleLabels(theme);

		document.querySelectorAll('[data-mpp-theme-toggle]').forEach(function (button) {
			button.addEventListener('click', function () {
				var current = document.documentElement.getAttribute('data-theme') || 'light';
				var next = current === 'dark' ? 'light' : 'dark';
				applyTheme(next);
				setStoredTheme(next);
				updateToggleLabels(next);
			});
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
