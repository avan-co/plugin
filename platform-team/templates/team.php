<?php
/**
 * Manager team page template.
 *
 * @package PlatformTeam
 */

defined( 'ABSPATH' ) || exit;

require_once get_template_directory() . '/inc/panel-shell.php';

$user_id = get_current_user_id();
$members = \MPP\Team\TeamStore::list_members( $user_id );
$count   = count( $members );

ob_start();
?>
<div class="mpp-admin-stats">
	<div class="mpp-stat-card">
		<span class="mpp-stat-card__label"><?php esc_html_e( 'Team Members', 'platform-team' ); ?></span>
		<span class="mpp-stat-card__value"><?php echo esc_html( (string) $count ); ?></span>
	</div>
</div>

<?php if ( empty( $members ) ) : ?>
	<div class="mpp-card">
		<p><?php esc_html_e( 'No team members are assigned to your scope yet.', 'platform-team' ); ?></p>
	</div>
<?php else : ?>
	<div class="mpp-table-wrap">
		<table class="mpp-admin-table mpp-admin-table--stack">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Member', 'platform-team' ); ?></th>
					<th><?php esc_html_e( 'Team', 'platform-team' ); ?></th>
					<th><?php esc_html_e( 'Email', 'platform-team' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $members as $member ) : ?>
					<tr>
						<td data-label="<?php esc_attr_e( 'Member', 'platform-team' ); ?>"><?php echo esc_html( $member['display_name'] ); ?></td>
						<td data-label="<?php esc_attr_e( 'Team', 'platform-team' ); ?>"><?php echo esc_html( $member['team_name'] ?: __( 'Default', 'platform-team' ) ); ?></td>
						<td data-label="<?php esc_attr_e( 'Email', 'platform-team' ); ?>"><?php echo esc_html( $member['user_email'] ); ?></td>
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
	__( 'Team', 'platform-team' ),
	$content,
	__( 'People and groups assigned to your manager scope.', 'platform-team' )
);
