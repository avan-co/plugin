<?php
/**
 * Manager reports page template.
 *
 * @package PlatformReports
 */

defined( 'ABSPATH' ) || exit;

require_once get_template_directory() . '/inc/panel-shell.php';

$user_id  = get_current_user_id();
$overview = \MPP\Reports\ReportService::get_manager_overview( $user_id );

ob_start();
?>
<div class="mpp-admin-stats">
	<div class="mpp-stat-card">
		<span class="mpp-stat-card__label"><?php esc_html_e( 'Team Members', 'platform-reports' ); ?></span>
		<span class="mpp-stat-card__value"><?php echo esc_html( (string) $overview['team_members'] ); ?></span>
	</div>
	<div class="mpp-stat-card">
		<span class="mpp-stat-card__label"><?php esc_html_e( 'Pending Tasks', 'platform-reports' ); ?></span>
		<span class="mpp-stat-card__value"><?php echo esc_html( (string) $overview['task_pending'] ); ?></span>
	</div>
	<div class="mpp-stat-card">
		<span class="mpp-stat-card__label"><?php esc_html_e( 'Tasks In Progress', 'platform-reports' ); ?></span>
		<span class="mpp-stat-card__value"><?php echo esc_html( (string) $overview['task_progress'] ); ?></span>
	</div>
	<div class="mpp-stat-card">
		<span class="mpp-stat-card__label"><?php esc_html_e( 'Tasks Completed', 'platform-reports' ); ?></span>
		<span class="mpp-stat-card__value"><?php echo esc_html( (string) $overview['task_done'] ); ?></span>
	</div>
</div>

<div class="mpp-card">
	<h2><?php esc_html_e( 'Operational summary', 'platform-reports' ); ?></h2>
	<p class="mpp-muted"><?php esc_html_e( 'This report aggregates live data from installed manager modules.', 'platform-reports' ); ?></p>
	<dl class="mpp-profile-list">
		<dt><?php esc_html_e( 'Connected modules', 'platform-reports' ); ?></dt>
		<dd>
			<?php if ( empty( $overview['modules'] ) ) : ?>
				<?php esc_html_e( 'Install Tasks and Team modules to populate this report.', 'platform-reports' ); ?>
			<?php else : ?>
				<code><?php echo esc_html( implode( ', ', $overview['modules'] ) ); ?></code>
			<?php endif; ?>
		</dd>
		<dt><?php esc_html_e( 'Total tracked tasks', 'platform-reports' ); ?></dt>
		<dd><?php echo esc_html( (string) $overview['task_total'] ); ?></dd>
	</dl>
	<div class="mpp-quick-actions">
		<?php if ( class_exists( '\MPP\Tasks\TaskStore' ) ) : ?>
			<a class="mpp-btn mpp-btn--secondary" href="<?php echo esc_url( mpp_route_url( 'app/tasks' ) ); ?>"><?php esc_html_e( 'Open Tasks', 'platform-reports' ); ?></a>
		<?php endif; ?>
		<?php if ( class_exists( '\MPP\Team\TeamStore' ) ) : ?>
			<a class="mpp-btn mpp-btn--secondary" href="<?php echo esc_url( mpp_route_url( 'app/team' ) ); ?>"><?php esc_html_e( 'Open Team', 'platform-reports' ); ?></a>
		<?php endif; ?>
	</div>
</div>
<?php
$content = ob_get_clean();

platform_render_panel_shell(
	'manager',
	__( 'Reports', 'platform-reports' ),
	$content,
	__( 'Cross-module metrics for manager oversight.', 'platform-reports' )
);
