<?php
/**
 * Registers admin sub-routes.
 *
 * @package PlatformCore
 */

namespace MPP\Admin;

use MPP\Core\Router;

defined( 'ABSPATH' ) || exit;

/**
 * Class AdminRoutes
 */
class AdminRoutes {

	/**
	 * Register admin routes on the platform router.
	 *
	 * @param Router $router Router instance.
	 */
	public static function register( Router $router ) {
		$admin_routes = array(
			'app/admin/users'       => array(
				'template'   => 'templates/admin/users.php',
				'permission' => 'core.acl.manage',
				'title'      => __( 'Users', 'platform-core' ),
			),
			'app/admin/roles'       => array(
				'template'   => 'templates/admin/roles.php',
				'permission' => 'core.acl.manage',
				'title'      => __( 'Roles', 'platform-core' ),
			),
			'app/admin/permissions' => array(
				'template'   => 'templates/admin/permissions.php',
				'permission' => 'core.acl.manage',
				'title'      => __( 'Permissions', 'platform-core' ),
			),
			'app/admin/modules'     => array(
				'template'   => 'templates/admin/modules.php',
				'permission' => 'core.acl.manage',
				'title'      => __( 'Modules', 'platform-core' ),
			),
			'app/admin/acl'         => array(
				'template'   => 'templates/admin/acl.php',
				'permission' => 'core.acl.manage',
				'title'      => __( 'ACL Overview', 'platform-core' ),
			),
			'app/admin/settings'    => array(
				'template'   => 'templates/admin/settings.php',
				'permission' => 'core.acl.manage',
				'title'      => __( 'Admin Settings', 'platform-core' ),
			),
		);

		foreach ( $admin_routes as $slug => $definition ) {
			$router->add_route( $slug, $definition );
		}
	}
}
