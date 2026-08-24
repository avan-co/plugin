<?php
/**
 * Design system bootstrap.
 *
 * @package PlatformTheme
 */

namespace PlatformTheme\DesignSystem;

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/class-ui-components.php';
require_once __DIR__ . '/class-language-switcher.php';
require_once __DIR__ . '/class-panel-navigation.php';
require_once __DIR__ . '/class-panel-shell.php';

/**
 * Loads design system classes and registers hooks.
 */
final class Bootstrap {

	/**
	 * Initialize the design system.
	 */
	public static function init() {
		LanguageSwitcher::init();
	}
}
