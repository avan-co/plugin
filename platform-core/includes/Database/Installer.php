<?php
/**
 * Database installation and upgrades.
 *
 * @package PlatformCore
 */

namespace MPP\Database;

use MPP\ACL\PermissionRegistry;
use MPP\ACL\RoleManager;
use MPP\Core\Router;
use MPP\Modules\ModuleManager;

defined( 'ABSPATH' ) || exit;

/**
 * Class Installer
 */
class Installer {

	/**
	 * Run on plugin activation.
	 */
	public static function activate() {
		self::create_tables();
		update_option( Schema::VERSION_OPTION, Schema::DB_VERSION );

		self::seed_scopes();
		self::seed_permissions_and_roles();

		flush_rewrite_rules();
	}

	/**
	 * Run on plugin deactivation.
	 */
	public static function deactivate() {
		flush_rewrite_rules();
	}

	/**
	 * Run database upgrades if needed.
	 */
	public static function maybe_upgrade() {
		$installed = get_option( Schema::VERSION_OPTION, '0' );

		if ( version_compare( $installed, Schema::DB_VERSION, '<' ) ) {
			self::create_tables();
			self::seed_scopes();
			self::seed_permissions_and_roles();
			delete_option( 'mpp_permissions_hash' );
			update_option( Schema::VERSION_OPTION, Schema::DB_VERSION );
		}
	}

	/**
	 * Create database tables.
	 */
	public static function create_tables() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		foreach ( Schema::get_tables_sql() as $sql ) {
			dbDelta( $sql );
		}
	}

	/**
	 * Seed default scope types.
	 */
	private static function seed_scopes() {
		global $wpdb;

		$table = Schema::table( 'scopes' );
		$scopes = array(
			array( 'all', __( 'All', 'platform-core' ), __( 'Access to all resources.', 'platform-core' ) ),
			array( 'own', __( 'Own', 'platform-core' ), __( 'Access to own resources only.', 'platform-core' ) ),
			array( 'department', __( 'Department', 'platform-core' ), __( 'Access within user department.', 'platform-core' ) ),
			array( 'team', __( 'Team', 'platform-core' ), __( 'Access within user team.', 'platform-core' ) ),
			array( 'project', __( 'Project', 'platform-core' ), __( 'Access within assigned projects.', 'platform-core' ) ),
			array( 'organization', __( 'Organization', 'platform-core' ), __( 'Access within organization.', 'platform-core' ) ),
			array( 'custom', __( 'Custom', 'platform-core' ), __( 'Custom scope with handler.', 'platform-core' ) ),
		);

		foreach ( $scopes as $scope ) {
			$exists = $wpdb->get_var(
				$wpdb->prepare( "SELECT id FROM {$table} WHERE slug = %s", $scope[0] )
			);

			if ( $exists ) {
				continue;
			}

			$wpdb->insert(
				$table,
				array(
					'slug'        => $scope[0],
					'name'        => $scope[1],
					'description' => $scope[2],
				),
				array( '%s', '%s', '%s' )
			);
		}
	}

	/**
	 * Seed core permissions and default roles.
	 */
	private static function seed_permissions_and_roles() {
		$registry = new PermissionRegistry();
		$manager  = new ModuleManager( $registry );
		$manager->register_core_module();
		$registry->sync_to_database();
		update_option( 'mpp_permissions_hash', $registry->get_registry_hash(), false );

		$roles = new RoleManager();

		$default_roles = array(
			'platform_user'    => array(
				'name'        => __( 'Platform User', 'platform-core' ),
				'description' => __( 'Standard platform user with access to the user panel.', 'platform-core' ),
				'permissions' => array(
					'core.panel.access',
					'core.panel.user.access',
					'core.profile.view',
					'core.profile.edit',
					'core.settings.view',
				),
			),
			'platform_manager' => array(
				'name'        => __( 'Platform Manager', 'platform-core' ),
				'description' => __( 'Manager with access to the manager panel.', 'platform-core' ),
				'permissions' => array(
					'core.panel.access',
					'core.panel.user.access',
					'core.panel.manager.access',
					'core.profile.view',
					'core.profile.edit',
					'core.settings.view',
					'core.settings.edit',
				),
			),
			'platform_admin'   => array(
				'name'        => __( 'Platform Admin', 'platform-core' ),
				'description' => __( 'Administrator with full panel access.', 'platform-core' ),
				'permissions' => array(
					'core.panel.access',
					'core.panel.user.access',
					'core.panel.manager.access',
					'core.panel.admin.access',
					'core.profile.view',
					'core.profile.edit',
					'core.settings.view',
					'core.settings.edit',
					'core.acl.manage',
				),
			),
		);

		foreach ( $default_roles as $slug => $config ) {
			$role = $roles->find_by_slug( $slug );

			if ( ! $role ) {
				$role_id = $roles->create( $slug, $config['name'], $config['description'], true );
			} else {
				$role_id = (int) $role['id'];
			}

			if ( ! $role_id ) {
				continue;
			}

			foreach ( $config['permissions'] as $permission_key ) {
				$permission_id = $registry->get_id_by_key( $permission_key );

				if ( $permission_id ) {
					$roles->assign_permission( $role_id, $permission_id, 'all' );
				}
			}
		}
	}
}
