<?php
/**
 * Settings page template.
 *
 * @package PlatformTheme
 */

defined( 'ABSPATH' ) || exit;

require_once get_template_directory() . '/inc/account-layout.php';

$user_id        = get_current_user_id();
$can_edit       = function_exists( 'mpp_can' ) && mpp_can( 'core.settings.edit' );
$notifications  = (bool) get_user_meta( $user_id, 'mpp_notifications', true );

ob_start();
?>
<div class="mpp-card">
	<?php if ( $can_edit ) : ?>
		<form method="post" class="mpp-form">
			<?php echo function_exists( 'mpp_account_nonce_field' ) ? mpp_account_nonce_field() : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<input type="hidden" name="mpp_account_action" value="update_settings">
			<input type="hidden" name="mpp_redirect" value="<?php echo esc_url( home_url( '/settings' ) ); ?>">

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
</div>
<?php
$content = ob_get_clean();

platform_render_account_page( 'user', __( 'Settings', 'platform-theme' ), $content );
