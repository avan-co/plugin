<?php
/**
 * Manager profile page template.
 *
 * @package PlatformTheme
 */

defined( 'ABSPATH' ) || exit;

require_once get_template_directory() . '/inc/account-layout.php';

$user     = wp_get_current_user();
$can_edit = function_exists( 'mpp_can' ) && mpp_can( 'core.profile.edit' );

ob_start();
?>
<div class="mpp-card">
	<?php if ( $can_edit ) : ?>
		<form method="post" class="mpp-form">
			<?php echo function_exists( 'mpp_account_nonce_field' ) ? mpp_account_nonce_field() : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<input type="hidden" name="mpp_account_action" value="update_profile">
			<input type="hidden" name="mpp_redirect" value="<?php echo esc_url( home_url( '/app/manager/profile' ) ); ?>">

			<label for="display_name"><?php esc_html_e( 'Display Name', 'platform-theme' ); ?></label>
			<input type="text" id="display_name" name="display_name" value="<?php echo esc_attr( $user->display_name ); ?>" required>

			<label for="email"><?php esc_html_e( 'Email', 'platform-theme' ); ?></label>
			<input type="email" id="email" name="email" value="<?php echo esc_attr( $user->user_email ); ?>" required>

			<label><?php esc_html_e( 'Username', 'platform-theme' ); ?></label>
			<input type="text" value="<?php echo esc_attr( $user->user_login ); ?>" disabled>

			<button type="submit" class="mpp-btn mpp-btn--primary"><?php esc_html_e( 'Save Profile', 'platform-theme' ); ?></button>
		</form>
	<?php else : ?>
		<dl class="mpp-profile-list">
			<dt><?php esc_html_e( 'Display Name', 'platform-theme' ); ?></dt>
			<dd><?php echo esc_html( $user->display_name ); ?></dd>

			<dt><?php esc_html_e( 'Email', 'platform-theme' ); ?></dt>
			<dd><?php echo esc_html( $user->user_email ); ?></dd>

			<dt><?php esc_html_e( 'Username', 'platform-theme' ); ?></dt>
			<dd><?php echo esc_html( $user->user_login ); ?></dd>
		</dl>
	<?php endif; ?>
</div>
<?php
$content = ob_get_clean();

platform_render_account_page( 'manager', __( 'Manager Profile', 'platform-theme' ), $content );
