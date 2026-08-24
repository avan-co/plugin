<?php
/**
 * Aggregated manager report data.
 *
 * @package PlatformReports
 */

namespace MPP\Reports;

defined( 'ABSPATH' ) || exit;

/**
 * Class ReportService
 */
class ReportService {

	/**
	 * Build manager overview report.
	 *
	 * @param int $manager_user_id Manager user ID.
	 * @return array<string, mixed>
	 */
	public static function get_manager_overview( $manager_user_id ) {
		$manager_user_id = (int) $manager_user_id;
		$report          = array(
			'team_members'  => 0,
			'pending_tasks' => 0,
			'task_total'    => 0,
			'task_done'     => 0,
			'task_pending'  => 0,
			'task_progress' => 0,
			'modules'       => array(),
		);

		if ( class_exists( '\MPP\Team\TeamStore' ) ) {
			$report['team_members'] = \MPP\Team\TeamStore::count_members( $manager_user_id );
			$report['modules'][]    = 'team';
		}

		if ( class_exists( '\MPP\Tasks\TaskStore' ) ) {
			$summary = \MPP\Tasks\TaskStore::summary_for_manager( $manager_user_id );
			$report['pending_tasks'] = $summary['pending'];
			$report['task_total']    = $summary['total'];
			$report['task_done']     = $summary['done'];
			$report['task_pending']  = $summary['pending'];
			$report['task_progress'] = $summary['in_progress'];
			$report['modules'][]     = 'tasks';
		}

		return $report;
	}
}
