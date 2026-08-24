<?php
/**
 * Public template functions for themes.
 *
 * @package PlatformCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Check if current user has a permission.
 *
 * @param string               $permission Permission key.
 * @param array<string, mixed> $context    Access context.
 * @return bool
 */
function mpp_can( $permission, array $context = array() ) {
	return mpp()->acl()->can( get_current_user_id(), $permission, $context );
}

/**
 * Check if current user can access a panel.
 *
 * @param string $panel Panel slug (user, manager, admin).
 * @return bool
 */
function mpp_can_access_panel( $panel ) {
	return mpp_can( 'core.panel.' . sanitize_key( $panel ) . '.access' );
}

/**
 * Get accessible panels for current user.
 *
 * @return array<int, string>
 */
function mpp_get_accessible_panels() {
	return mpp()->acl()->get_accessible_panels( get_current_user_id() );
}

/**
 * Whether WordPress pretty permalinks are enabled.
 *
 * @return bool
 */
function mpp_uses_pretty_permalinks() {
	global $wp_rewrite;

	if ( $wp_rewrite instanceof \WP_Rewrite ) {
		return (bool) $wp_rewrite->using_permalinks();
	}

	return (bool) get_option( 'permalink_structure' );
}

/**
 * Build a platform route URL.
 *
 * @param string $slug Route slug.
 * @return string
 */
function mpp_route_url( $slug ) {
	$slug = trim( (string) $slug, '/' );

	if ( '' === $slug ) {
		return home_url( '/' );
	}

	if ( mpp_uses_pretty_permalinks() ) {
		return home_url( '/' . $slug );
	}

	return home_url( 'index.php?' . \MPP\Core\Router::QUERY_VAR . '=' . rawurlencode( $slug ) );
}

/**
 * Get registered platform routes.
 *
 * @return array<string, array<string, mixed>>
 */
function mpp_get_registered_routes() {
	if ( ! function_exists( 'mpp' ) ) {
		return array();
	}

	return mpp()->get( \MPP\Core\Router::class )->get_routes();
}

/**
 * Get current route data.
 *
 * @return array<string, mixed>|null
 */
function mpp_get_current_route() {
	return isset( $GLOBALS['mpp_current_route'] ) ? $GLOBALS['mpp_current_route'] : null;
}

/**
 * Get logout URL.
 *
 * @return string
 */
function mpp_logout_url() {
	return \MPP\Auth\AuthIntegration::logout_url();
}

/**
 * Whether the current request is an authentication route.
 *
 * @return bool
 */
function mpp_is_auth_route() {
	if ( ! function_exists( 'mpp_get_current_route' ) ) {
		return false;
	}

	$route = mpp_get_current_route();

	if ( ! $route || empty( $route['slug'] ) ) {
		return false;
	}

	return in_array( $route['slug'], array( 'login', 'register', 'forgot-password' ), true );
}

/**
 * Get the themed forgot-password URL.
 *
 * @return string
 */
function mpp_forgot_password_url() {
	return mpp_route_url( 'forgot-password' );
}

/**
 * Render navigation for a panel type.
 *
 * @param string $panel Panel slug.
 */
function mpp_render_panel_nav( $panel ) {
	get_template_part( 'template-parts/navigation', sanitize_key( $panel ) );
}

/**
 * Render an admin sub-page (requires core.acl.manage — enforced by router).
 *
 * @param string $page Page slug.
 */
function mpp_render_admin_page( $page ) {
	mpp()->get( \MPP\Admin\AdminRenderer::class )->render( sanitize_key( $page ) );
}

/**
 * Check if current user can manage ACL.
 *
 * @return bool
 */
function mpp_can_manage_acl() {
	return mpp_can( 'core.acl.manage' );
}

/**
 * Get user dashboard summary data.
 *
 * @param int $user_id User ID.
 * @return array<string, mixed>
 */
function mpp_get_user_summary( $user_id = 0 ) {
	return mpp()->get( \MPP\Panels\DashboardService::class )->get_user_summary( $user_id );
}

/**
 * Get manager dashboard stats.
 *
 * @param int $user_id User ID.
 * @return array<string, mixed>
 */
function mpp_get_manager_stats( $user_id = 0 ) {
	return mpp()->get( \MPP\Panels\DashboardService::class )->get_manager_stats( $user_id );
}

/**
 * Get permission-filtered navigation items for a panel.
 *
 * @param string $panel Panel slug (user, manager, admin).
 * @return array<int, array<string, string>>
 */
function mpp_get_panel_navigation( $panel ) {
	$panel = sanitize_key( $panel );

	$core_items = array(
		'user' => array(
			array(
				'label'       => __( 'Dashboard', 'platform-core' ),
				'url'         => mpp_route_url( 'app/user' ),
				'route'       => 'app/user',
				'permission'  => 'core.panel.user.access',
				'section'     => 'main',
				'description' => __( 'Overview and module widgets', 'platform-core' ),
			),
			array(
				'label'       => __( 'Profile', 'platform-core' ),
				'url'         => mpp_route_url( 'profile' ),
				'route'       => 'profile',
				'permission'  => 'core.profile.view',
				'section'     => 'account',
				'description' => __( 'Personal account details', 'platform-core' ),
			),
			array(
				'label'       => __( 'Settings', 'platform-core' ),
				'url'         => mpp_route_url( 'settings' ),
				'route'       => 'settings',
				'permission'  => 'core.settings.view',
				'section'     => 'account',
				'description' => __( 'Preferences and notifications', 'platform-core' ),
			),
			array(
				'label'       => __( 'Logout', 'platform-core' ),
				'url'         => mpp_logout_url(),
				'route'       => '',
				'section'     => 'system',
				'description' => __( 'Sign out of the platform', 'platform-core' ),
			),
		),
		'manager' => array(
			array(
				'label'       => __( 'Dashboard', 'platform-core' ),
				'url'         => mpp_route_url( 'app/manager' ),
				'route'       => 'app/manager',
				'permission'  => 'core.panel.manager.access',
				'section'     => 'main',
				'description' => __( 'Team overview and widgets', 'platform-core' ),
			),
			array(
				'label'       => __( 'Profile', 'platform-core' ),
				'url'         => mpp_route_url( 'app/manager/profile' ),
				'route'       => 'app/manager/profile',
				'permission'  => 'core.profile.view',
				'section'     => 'account',
				'description' => __( 'Manager account details', 'platform-core' ),
			),
			array(
				'label'       => __( 'Settings', 'platform-core' ),
				'url'         => mpp_route_url( 'settings' ),
				'route'       => 'settings',
				'permission'  => 'core.settings.view',
				'section'     => 'account',
				'description' => __( 'Workspace preferences', 'platform-core' ),
			),
		),
	);

	$items = isset( $core_items[ $panel ] ) ? $core_items[ $panel ] : array();

	if ( 'admin' !== $panel ) {
		$module_items = mpp()->get( \MPP\Modules\ModuleManager::class )->get_navigation_items();
		foreach ( $module_items as $item ) {
			if ( empty( $item['panel'] ) || $item['panel'] !== $panel ) {
				continue;
			}
			if ( empty( $item['section'] ) ) {
				$item['section'] = 'modules';
			}
			$items[] = $item;
		}
	}

	$filtered = array();

	foreach ( $items as $item ) {
		if ( ! empty( $item['permission'] ) && ! mpp_can( $item['permission'] ) ) {
			continue;
		}

		$filtered[] = $item;
	}

	/**
	 * Filter panel navigation items after permission checks.
	 *
	 * @param array<int, array<string, string>> $filtered Items.
	 * @param string                            $panel    Panel slug.
	 */
	return apply_filters( 'mpp_panel_navigation', $filtered, $panel );
}

/**
 * Get permission-filtered admin navigation items.
 *
 * @return array<int, array<string, string>>
 */
function mpp_get_admin_navigation() {
	$items = array(
		array(
			'label'       => __( 'Dashboard', 'platform-core' ),
			'route'       => 'app/admin',
			'permission'  => 'core.panel.admin.access',
			'description' => __( 'Platform overview', 'platform-core' ),
		),
		array(
			'label'      => __( 'Users', 'platform-core' ),
			'permission' => 'core.acl.manage',
			'children'   => array(
				array(
					'label'       => __( 'Users', 'platform-core' ),
					'route'       => 'app/admin/users',
					'description' => __( 'Accounts and role assignments', 'platform-core' ),
				),
				array(
					'label'       => __( 'Roles', 'platform-core' ),
					'route'       => 'app/admin/roles',
					'description' => __( 'Role definitions and members', 'platform-core' ),
				),
			),
		),
		array(
			'label'       => __( 'Permissions', 'platform-core' ),
			'route'       => 'app/admin/permissions',
			'permission'  => 'core.acl.manage',
			'description' => __( 'Browse and inspect permissions', 'platform-core' ),
		),
		array(
			'label'       => __( 'ACL', 'platform-core' ),
			'route'       => 'app/admin/acl',
			'permission'  => 'core.acl.manage',
			'description' => __( 'Access control overview', 'platform-core' ),
		),
		array(
			'label'       => __( 'Modules', 'platform-core' ),
			'route'       => 'app/admin/modules',
			'permission'  => 'core.acl.manage',
			'description' => __( 'Installed platform modules', 'platform-core' ),
		),
		array(
			'label'       => __( 'Settings', 'platform-core' ),
			'route'       => 'app/admin/settings',
			'permission'  => 'core.acl.manage',
			'description' => __( 'Platform configuration', 'platform-core' ),
		),
		array(
			'label'       => __( 'Audit Log', 'platform-core' ),
			'route'       => 'app/admin/acl',
			'permission'  => 'core.acl.manage',
			'description' => __( 'ACL change history', 'platform-core' ),
			'query_args'  => array( 'view' => 'audit' ),
		),
	);

	return mpp_filter_admin_navigation( $items );
}

/**
 * Filter and normalize admin navigation items.
 *
 * @param array<int, array<string, mixed>> $items Raw navigation items.
 * @return array<int, array<string, mixed>>
 */
function mpp_filter_admin_navigation( array $items ) {
	$filtered = array();

	foreach ( $items as $item ) {
		if ( ! empty( $item['permission'] ) && ! mpp_can( $item['permission'] ) ) {
			continue;
		}

		if ( ! empty( $item['children'] ) ) {
			$children = array();

			foreach ( $item['children'] as $child ) {
				if ( ! empty( $child['permission'] ) && ! mpp_can( $child['permission'] ) ) {
					continue;
				}

				if ( empty( $child['permission'] ) && ! empty( $item['permission'] ) && ! mpp_can( $item['permission'] ) ) {
					continue;
				}

				$child['url'] = ! empty( $child['query_args'] )
					? add_query_arg( $child['query_args'], mpp_route_url( $child['route'] ) )
					: mpp_route_url( $child['route'] );
				$children[]   = $child;
			}

			if ( empty( $children ) ) {
				continue;
			}

			$item['children'] = $children;
			unset( $item['route'], $item['url'] );
			$filtered[] = $item;
			continue;
		}

		if ( empty( $item['route'] ) ) {
			continue;
		}

		$item['url'] = ! empty( $item['query_args'] )
			? add_query_arg( $item['query_args'], mpp_route_url( $item['route'] ) )
			: mpp_route_url( $item['route'] );
		$filtered[]  = $item;
	}

	return apply_filters( 'mpp_admin_navigation', $filtered );
}

/**
 * Reset admin page shell context before rendering content.
 */
function mpp_reset_admin_page_context() {
	unset( $GLOBALS['mpp_admin_page_actions'], $GLOBALS['mpp_admin_page_meta'] );
}

/**
 * Set header action buttons HTML for the current admin page.
 *
 * @param string $html Actions HTML.
 */
function mpp_set_admin_page_actions( $html ) {
	$GLOBALS['mpp_admin_page_actions'] = $html;
}

/**
 * Get header action buttons HTML for the current admin page.
 *
 * @return string
 */
function mpp_get_admin_page_actions() {
	return isset( $GLOBALS['mpp_admin_page_actions'] ) ? (string) $GLOBALS['mpp_admin_page_actions'] : '';
}

/**
 * Override admin shell title/description for the current page.
 *
 * @param array<string, string> $meta Meta overrides (title, description).
 */
function mpp_set_admin_page_meta( array $meta ) {
	$GLOBALS['mpp_admin_page_meta'] = $meta;
}

/**
 * Get admin shell meta overrides for the current page.
 *
 * @return array<string, string>
 */
function mpp_get_admin_page_meta() {
	return isset( $GLOBALS['mpp_admin_page_meta'] ) ? (array) $GLOBALS['mpp_admin_page_meta'] : array();
}

/**
 * Get recent activity for the current user.
 *
 * @param int $user_id User ID.
 * @return array<int, array<string, mixed>>
 */
function mpp_get_user_recent_activity( $user_id = 0 ) {
	return mpp()->get( \MPP\Panels\DashboardService::class )->get_user_recent_activity( $user_id );
}

/**
 * Get admin dashboard summary.
 *
 * @return array<string, mixed>
 */
function mpp_get_admin_summary() {
	return mpp()->get( \MPP\Panels\DashboardService::class )->get_admin_summary();
}

/**
 * Render account flash notice from query string.
 */
function mpp_render_account_notice() {
	if ( empty( $_GET['mpp_notice'] ) ) {
		return;
	}

	$type    = sanitize_key( wp_unslash( $_GET['mpp_notice'] ) );
	$message = isset( $_GET['mpp_message'] ) ? sanitize_text_field( wp_unslash( $_GET['mpp_message'] ) ) : '';

	if ( empty( $message ) ) {
		return;
	}

	printf(
		'<div class="mpp-alert mpp-alert--%s" role="alert">%s</div>',
		esc_attr( 'success' === $type ? 'success' : 'error' ),
		esc_html( $message )
	);
}

/**
 * Output account form nonce field.
 *
 * @return string
 */
function mpp_account_nonce_field() {
	return \MPP\Account\AccountFormHandler::nonce_field();
}

/**
 * Register an external platform module.
 *
 * Call from a business plugin on plugins_loaded (priority < 10) or via mpp_register_modules.
 *
 * @param \MPP\Modules\ModuleInterface $module Module instance.
 * @return bool
 */
function mpp_register_module( $module ) {
	if ( ! $module instanceof \MPP\Modules\ModuleInterface ) {
		return false;
	}

	if ( function_exists( 'mpp' ) ) {
		$manager = mpp()->get( \MPP\Modules\ModuleManager::class );

		if ( $manager->is_booted() ) {
			return $manager->register( $module );
		}
	}

	return \MPP\Modules\ModuleManager::enqueue( $module );
}

/**
 * Deactivate a registered module (plugin deactivation hook).
 *
 * @param string $slug Module slug.
 * @return bool
 */
function mpp_deactivate_module( $slug ) {
	if ( ! function_exists( 'mpp' ) ) {
		return false;
	}

	return mpp()->get( \MPP\Modules\ModuleManager::class )->deactivate_module( $slug );
}

/**
 * Get registered module instances.
 *
 * @return array<string, \MPP\Modules\ModuleInterface>
 */
function mpp_get_registered_modules() {
	if ( ! function_exists( 'mpp' ) ) {
		return array();
	}

	return mpp()->get( \MPP\Modules\ModuleManager::class )->all();
}

/**
 * Get permission-filtered dashboard widgets for a panel.
 *
 * @param string $panel Panel slug.
 * @return array<int, array<string, string>>
 */
function mpp_get_panel_widgets( $panel ) {
	$panel = sanitize_key( $panel );

	if ( ! function_exists( 'mpp' ) ) {
		return array();
	}

	$widgets  = mpp()->get( \MPP\Modules\ModuleManager::class )->get_dashboard_widgets();
	$filtered = array();

	foreach ( $widgets as $widget ) {
		if ( ! empty( $widget['panel'] ) && $widget['panel'] !== $panel ) {
			continue;
		}

		if ( ! empty( $widget['permission'] ) && ! mpp_can( $widget['permission'] ) ) {
			continue;
		}

		$filtered[] = $widget;
	}

	/**
	 * Filter panel dashboard widgets after permission checks.
	 *
	 * @param array<int, array<string, string>> $filtered Widgets.
	 * @param string                            $panel    Panel slug.
	 */
	return apply_filters( 'mpp_panel_widgets', $filtered, $panel );
}

/**
 * Get the platform settings service.
 *
 * @return \MPP\Settings\PlatformSettings|null
 */
function mpp_platform_settings() {
	if ( ! function_exists( 'mpp' ) ) {
		return null;
	}

	return mpp()->get( \MPP\Settings\PlatformSettings::class );
}

/**
 * Get the configured platform display name.
 *
 * @return string
 */
function mpp_get_platform_name() {
	$settings = mpp_platform_settings();

	if ( ! $settings ) {
		return get_bloginfo( 'name' );
	}

	return (string) $settings->get( 'platform_name', get_bloginfo( 'name' ) );
}

/**
 * Get the single-character logo mark for the platform brand.
 *
 * @return string
 */
function mpp_get_logo_mark() {
	$settings = mpp_platform_settings();

	if ( ! $settings ) {
		return 'P';
	}

	$mark = (string) $settings->get( 'logo_mark', 'P' );

	return '' !== $mark ? mb_substr( $mark, 0, 1 ) : 'P';
}

/**
 * Get the configured accent color override, if any.
 *
 * @return string
 */
function mpp_get_accent_color() {
	$settings = mpp_platform_settings();

	if ( ! $settings ) {
		return '';
	}

	return (string) $settings->get( 'accent_color', '' );
}

/**
 * Get module shortcut links for a panel dashboard.
 *
 * @param string $panel Panel slug.
 * @return array<int, array<string, string>>
 */
function mpp_get_panel_module_shortcuts( $panel ) {
	if ( ! function_exists( 'mpp' ) ) {
		return array();
	}

	return mpp()->get( \MPP\Panels\DashboardService::class )->get_module_shortcuts( $panel );
}

/**
 * Get pending items for the manager dashboard.
 *
 * @return array<int, array<string, string>>
 */
function mpp_get_manager_pending_items() {
	if ( ! function_exists( 'mpp' ) ) {
		return array();
	}

	return mpp()->get( \MPP\Panels\DashboardService::class )->get_pending_items();
}
