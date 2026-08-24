<?php
/**
 * User/Manager panel shell helper.
 *
 * @package PlatformTheme
 */

defined( 'ABSPATH' ) || exit;

require_once get_template_directory() . '/inc/panel-layout.php';

use PlatformTheme\DesignSystem\PanelShell;
use PlatformTheme\DesignSystem\UIComponents;

/**
 * Render a panel page with shared shell metadata.
 *
 * @param string               $panel       Panel slug.
 * @param string               $title       Page title.
 * @param string               $content     Page HTML.
 * @param string               $description Optional description.
 * @param array<int, string>   $breadcrumb  Optional breadcrumb labels.
 */
function platform_render_panel_shell( $panel, $title, $content, $description = '', array $breadcrumb = array() ) {
	PanelShell::render_with_meta( $panel, $title, $content, $description, $breadcrumb );
}

/**
 * Render a placeholder section for features without backend yet.
 *
 * @param string $title       Section title.
 * @param string $description Section description.
 * @param string $cta_label   Optional CTA label.
 * @param string $cta_url     Optional CTA URL.
 */
function platform_render_placeholder_section( $title, $description, $cta_label = '', $cta_url = '' ) {
	UIComponents::placeholder_section( $title, $description, $cta_label, $cta_url );
}
