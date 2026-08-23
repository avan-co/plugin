<?php
/**
 * Settings page template.
 *
 * @package PlatformTheme
 */

defined( 'ABSPATH' ) || exit;

require_once get_template_directory() . '/inc/account-layout.php';

$user_id       = get_current_user_id();
$user          = wp_get_current_user();
$can_edit      = function_exists( 'mpp_can' ) && mpp_can( 'core.settings.edit' );
$notifications = (bool) get_user_meta( $user_id, 'mpp_notifications', true );
$panels        = function_exists( 'mpp_get_accessible_panels' ) ? mpp_get_accessible_panels() : array();

ob_start();
?>
<div class="mpp-settings-sections">
	<section class="mpp-card">
		<h2><?php esc_html_e( 'Account', 'platform-theme' ); ?></h2>
		<?php if ( $can_edit ) : ?>
			<form method="post" class="mpp-form">
				<?php echo function_exists( 'mpp_account_nonce_field' ) ? mpp_account_nonce_field() : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<input type="hidden" name="mpp_account_action" value="update_settings">
				<input type="hidden" name="mpp_redirect" value="<?php echo esc_url( function_exists( 'mpp_route_url' ) ? mpp_route_url( 'settings' ) : home_url( '/settings' ) ); ?>">

				<label class="mpp-checkbox">
					<input type="checkbox" name="mpp_notifications" value="1" <?php checked( $notifications ); ?>>
					<?php esc_html_e( 'Enable email notifications', 'platform-theme' ); ?>
				</label>

				<button type="submit" class="mpp-btn mpp-btn--primary"><?php esc_html_e( 'Save Settings', 'platform-theme' ); ?></button>
			</form>
		<?php else : ?>
			<p><?php esc_html_e( 'You have view-only access to settings.', 'platform-theme' ); ?></p>
			<p class="mpp-badge"><?php esc_html_e( 'Notifications:', 'platform-theme' ); ?> <?php echo $notifications ? esc_html__( 'Enabled', 'platform-theme' ) : esc_html__( 'Disabled', 'platform-theme' ); ?></p>
		<?php endif; ?>
	</section>

	<section class="mpp-card">
		<h2><?php esc_html_e( 'Security', 'platform-theme' ); ?></h2>
		<p><?php esc_html_e( 'Password changes are managed through your WordPress account profile.', 'platform-theme' ); ?></p>
		<p><strong><?php esc_html_e( 'Username:', 'platform-theme' ); ?></strong> <?php echo esc_html( $user->user_login ); ?></p>
	</section>

	<section class="mpp-card">
		<h2><?php esc_html_e( 'Platform', 'platform-theme' ); ?></h2>
		<dl class="mpp-profile-list">
			<dt><?php esc_html_e( 'Accessible Panels', 'platform-theme' ); ?></dt>
			<dd><?php echo esc_html( ! empty( $panels ) ? implode( ', ', $panels ) : '—' ); ?></dd>
			<dt><?php esc_html_e( 'Platform Roles', 'platform-theme' ); ?></dt>
			<dd>
				<?php
				$summary = function_exists( 'mpp_get_user_summary' ) ? mpp_get_user_summary() : array();
				echo esc_html( ! empty( $summary['role_names'] ) ? implode( ', ', $summary['role_names'] ) : '—' );
				?>
			</dd>
		</dl>
	</section>
</div>
<?php
$content = ob_get_clean();

platform_render_account_page(
	'user',
	__( 'Settings', 'platform-theme' ),
	$content,
	__( 'Manage your account preferences and platform access summary.', 'platform-theme' )
);
