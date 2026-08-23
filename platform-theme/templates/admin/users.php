<?php
/**
 * Admin users template.
 *
 * @package PlatformTheme
 */

defined( 'ABSPATH' ) || exit;

require_once get_template_directory() . '/inc/admin-shell.php';

platform_render_admin_shell( 'users' );
