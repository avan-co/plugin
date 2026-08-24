<?php
/**
 * Manager tasks page template.
 *
 * @package PlatformTasks
 */

defined( 'ABSPATH' ) || exit;

require_once get_template_directory() . '/inc/panel-shell.php';

$user_id = get_current_user_id();
$tasks   = \MPP\Tasks\TaskStore::list_for_manager( $user_id );
$pending = \MPP\Tasks\TaskStore::count_pending_for_manager( $user_id );

ob_start();
?>
<div class="mpp-admin-stats">
	<div class="mpp-stat-card">
		<span class="mpp-stat-card__label"><?php esc_html_e( 'Pending Tasks', 'platform-tasks' ); ?></span>
		<span class="mpp-stat-card__value"><?php echo esc_html( (string) $pending ); ?></span>
	</div>
	<div class="mpp-stat-card">
		<span class="mpp-stat-card__label"><?php esc_html_e( 'Total Tasks', 'platform-tasks' ); ?></span>
		<span class="mpp-stat-card__value"><?php echo esc_html( (string) count( $tasks ) ); ?></span>
	</div>
</div>

<?php if ( empty( $tasks ) ) : ?>
	<div class="mpp-card">
		<p><?php esc_html_e( 'No tasks yet. Tasks created for your scope will appear here.', 'platform-tasks' ); ?></p>
	</div>
<?php else : ?>
	<div class="mpp-table-wrap">
		<table class="mpp-admin-table mpp-admin-table--stack">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Task', 'platform-tasks' ); ?></th>
					<th><?php esc_html_e( 'Status', 'platform-tasks' ); ?></th>
					<th><?php esc_html_e( 'Updated', 'platform-tasks' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $tasks as $task ) : ?>
					<tr>
						<td data-label="<?php esc_attr_e( 'Task', 'platform-tasks' ); ?>">
							<strong><?php echo esc_html( $task['title'] ); ?></strong>
							<?php if ( ! empty( $task['description'] ) ) : ?>
								<p class="mpp-muted"><?php echo esc_html( wp_strip_all_tags( $task['description'] ) ); ?></p>
							<?php endif; ?>
						</td>
						<td data-label="<?php esc_attr_e( 'Status', 'platform-tasks' ); ?>">
							<span class="mpp-badge"><?php echo esc_html( ucfirst( str_replace( '_', ' ', $task['status'] ) ) ); ?></span>
						</td>
						<td data-label="<?php esc_attr_e( 'Updated', 'platform-tasks' ); ?>">
							<?php
							echo esc_html(
								function_exists( 'mpp_format_date' )
									? mpp_format_date( $task['updated_at'] )
									: $task['updated_at']
							);
							?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
<?php endif; ?>
<?php
$content = ob_get_clean();

platform_render_panel_shell(
	'manager',
	__( 'Tasks', 'platform-tasks' ),
	$content,
	__( 'Track pending approvals and team workload.', 'platform-tasks' )
);
