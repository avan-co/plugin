<?php
/**
 * Account page layout helper.
 *
 * @package PlatformTheme
 */

defined( 'ABSPATH' ) || exit;

require_once get_template_directory() . '/inc/panel-layout.php';

/**
 * Render an account page inside a panel layout.
 *
 * @param string $panel   Panel slug.
 * @param string $title   Page title.
 * @param string $content HTML content.
 */
function platform_render_account_page( $panel, $title, $content, $description = '', array $breadcrumb = array() ) {
	ob_start();

	if ( function_exists( 'mpp_render_account_notice' ) ) {
		mpp_render_account_notice();
	}

	echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by caller

	$body = ob_get_clean();

	if ( empty( $breadcrumb ) ) {
		$breadcrumb = array(
			__( 'Account', 'platform-theme' ),
			$title,
		);
	}

	platform_render_panel( $panel, $title, $body, $description, $breadcrumb );
}
