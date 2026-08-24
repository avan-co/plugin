<?php
/**
 * Panel layout helper.
 *
 * @package PlatformTheme
 */

defined( 'ABSPATH' ) || exit;

use PlatformTheme\DesignSystem\PanelShell;

/**
 * Render a panel page with application shell.
 *
 * @param string               $panel        Panel slug.
 * @param string               $title        Page title.
 * @param string               $content      HTML content.
 * @param string               $description  Optional page description.
 * @param array<int, string>   $breadcrumb   Optional breadcrumb.
 */
function platform_render_panel( $panel, $title, $content, $description = '', array $breadcrumb = array() ) {
	PanelShell::render( $panel, $title, $content, $description, $breadcrumb );
}
