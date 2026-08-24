<?php
/**
 * Team module implementation.
 *
 * @package PlatformTeam
 */

namespace MPP\Team;

use MPP\Core\Router;
use MPP\Modules\AbstractModule;

defined( 'ABSPATH' ) || exit;

/**
 * Class TeamModule
 */
class TeamModule extends AbstractModule {

	public function get_slug() {
		return 'team';
	}

	public function get_name() {
		return __( 'Team', 'platform-team' );
	}

	public function get_version() {
		return MPP_TEAM_VERSION;
	}

	public function get_requires_core_version() {
		return '1.3.0';
	}

	public function register_permissions() {
		if ( ! function_exists( 'mpp' ) ) {
			return;
		}

		mpp()->get( \MPP\ACL\PermissionRegistry::class )->register_module(
			'team',
			array(
				'member' => array(
					'view'   => __( 'View team members', 'platform-team' ),
					'manage' => __( 'Manage team members', 'platform-team' ),
				),
			)
		);
	}

	public function register_routes( Router $router ) {
		$router->add_route(
			'app/team',
			array(
				'template'      => 'templates/team.php',
				'template_file' => MPP_TEAM_DIR . 'templates/team.php',
				'permission'    => 'team.member.view',
				'title'         => __( 'Team', 'platform-team' ),
				'description'   => __( 'View members assigned to your manager scope.', 'platform-team' ),
			)
		);
	}

	public function get_navigation_items() {
		$url = function_exists( 'mpp_route_url' ) ? mpp_route_url( 'app/team' ) : home_url( '/app/team' );

		return array(
			array(
				'label'       => __( 'Team', 'platform-team' ),
				'url'         => $url,
				'route'       => 'app/team',
				'permission'  => 'team.member.view',
				'panel'       => 'manager',
				'section'     => 'modules',
				'description' => __( 'Members and groups under your oversight.', 'platform-team' ),
			),
		);
	}

	public function get_dashboard_widgets() {
		return array(
			array(
				'id'         => 'team_member_count',
				'title'      => __( 'Team Members', 'platform-team' ),
				'panel'      => 'manager',
				'permission' => 'team.member.view',
				'value'      => (string) TeamStore::count_members( get_current_user_id() ),
			),
		);
	}

	public function run_migrations() {
		TeamStore::install();
		ModuleAccess::grant_default_roles(
			array(
				'team.member.view',
				'team.member.manage',
			)
		);
	}

	public function boot() {
		add_filter( 'mpp_manager_dashboard_stats', array( $this, 'filter_manager_stats' ), 10, 2 );
	}

	public function deactivate() {
		// Tables retained.
	}

	public function filter_manager_stats( array $stats, $user_id ) {
		$stats['team_members'] = (string) TeamStore::count_members( $user_id );
		return $stats;
	}
}
