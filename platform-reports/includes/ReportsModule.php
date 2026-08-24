<?php
/**
 * Reports module implementation.
 *
 * @package PlatformReports
 */

namespace MPP\Reports;

use MPP\Core\Router;
use MPP\Modules\AbstractModule;

defined( 'ABSPATH' ) || exit;

/**
 * Class ReportsModule
 */
class ReportsModule extends AbstractModule {

	public function get_slug() {
		return 'reports';
	}

	public function get_name() {
		return __( 'Reports', 'platform-reports' );
	}

	public function get_version() {
		return MPP_REPORTS_VERSION;
	}

	public function get_requires_core_version() {
		return '1.3.0';
	}

	public function register_permissions() {
		if ( ! function_exists( 'mpp' ) ) {
			return;
		}

		mpp()->get( \MPP\ACL\PermissionRegistry::class )->register_module(
			'reports',
			array(
				'report' => array(
					'view' => __( 'View manager reports', 'platform-reports' ),
				),
			)
		);
	}

	public function register_routes( Router $router ) {
		$router->add_route(
			'app/reports',
			array(
				'template'      => 'templates/reports.php',
				'template_file' => MPP_REPORTS_DIR . 'templates/reports.php',
				'permission'    => 'reports.report.view',
				'title'         => __( 'Reports', 'platform-reports' ),
				'description'   => __( 'Operational summaries for your manager workspace.', 'platform-reports' ),
			)
		);
	}

	public function get_navigation_items() {
		$url = function_exists( 'mpp_route_url' ) ? mpp_route_url( 'app/reports' ) : home_url( '/app/reports' );

		return array(
			array(
				'label'       => __( 'Reports', 'platform-reports' ),
				'url'         => $url,
				'route'       => 'app/reports',
				'permission'  => 'reports.report.view',
				'panel'       => 'manager',
				'section'     => 'modules',
				'description' => __( 'Task and team metrics in one place.', 'platform-reports' ),
			),
		);
	}

	public function get_dashboard_widgets() {
		$overview = ReportService::get_manager_overview( get_current_user_id() );

		return array(
			array(
				'id'         => 'reports_task_completion',
				'title'      => __( 'Tasks Completed', 'platform-reports' ),
				'panel'      => 'manager',
				'permission' => 'reports.report.view',
				'value'      => (string) $overview['task_done'],
			),
		);
	}

	public function run_migrations() {
		ModuleAccess::grant_default_roles(
			array(
				'reports.report.view',
			)
		);
	}

	public function boot() {
		// No runtime hooks required.
	}

	public function deactivate() {
		// No cleanup required.
	}
}
