<?php
/**
 * Shared profile page content.
 *
 * @package PlatformTheme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Render profile page body content.
 *
 * @param string $redirect_url Redirect URL after save.
 */
function platform_render_profile_content( $redirect_url ) {
	$user     = wp_get_current_user();
	$can_edit = function_exists( 'mpp_can' ) && mpp_can( 'core.profile.edit' );
	$summary  = function_exists( 'mpp_get_user_summary' ) ? mpp_get_user_summary() : array();
	$panels   = ! empty( $summary['panels'] ) ? $summary['panels'] : array();
	$roles    = ! empty( $summary['role_names'] ) ? $summary['role_names'] : array();
	?>
	<div class="mpp-profile-page">
		<header class="mpp-profile-header mpp-profile-page__header">
			<?php if ( function_exists( 'platform_ui_avatar' ) ) : ?>
				<?php echo platform_ui_avatar( (int) $user->ID, 72 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php endif; ?>
			<div class="mpp-profile-header__meta">
				<h2 class="mpp-profile-header__name"><?php echo esc_html( $user->display_name ); ?></h2>
				<p class="mpp-muted"><?php echo esc_html( $user->user_email ); ?></p>
				<p class="mpp-profile-header__since mpp-muted">
					<?php
					echo esc_html(
						sprintf(
							/* translators: %s: registration date */
							__( 'Member since %s', 'platform-theme' ),
							mysql2date( get_option( 'date_format' ), $user->user_registered )
						)
					);
					?>
				</p>
			</div>
		</header>

		<?php if ( $can_edit ) : ?>
			<section class="mpp-profile-section">
				<h3 class="mpp-profile-section__title"><?php esc_html_e( 'Edit Profile', 'platform-theme' ); ?></h3>
				<form method="post" class="mpp-form mpp-profile-form">
					<?php echo function_exists( 'mpp_account_nonce_field' ) ? mpp_account_nonce_field() : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<input type="hidden" name="mpp_account_action" value="update_profile">
					<input type="hidden" name="mpp_redirect" value="<?php echo esc_url( $redirect_url ); ?>">

					<?php
					if ( function_exists( 'platform_ui_form_field' ) ) {
						echo platform_ui_form_field( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							array(
								'name'        => 'display_name',
								'label'       => __( 'Display Name', 'platform-theme' ),
								'value'       => $user->display_name,
								'description' => __( 'Shown in the header and across the platform.', 'platform-theme' ),
								'attributes'  => array(
									'required' => 'required',
								),
							)
						);
						echo platform_ui_form_field( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							array(
								'type'        => 'email',
								'name'        => 'email',
								'label'       => __( 'Email', 'platform-theme' ),
								'value'       => $user->user_email,
								'description' => __( 'Used for account notifications and password recovery.', 'platform-theme' ),
								'attributes'  => array(
									'required' => 'required',
									'autocomplete' => 'email',
								),
							)
						);
						echo platform_ui_form_field( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							array(
								'id'          => 'mpp-profile-username',
								'label'       => __( 'Username', 'platform-theme' ),
								'value'       => $user->user_login,
								'description' => __( 'Usernames cannot be changed.', 'platform-theme' ),
								'attributes'  => array(
									'disabled' => 'disabled',
									'readonly' => 'readonly',
								),
							)
						);
					} else {
						?>
						<label for="display_name"><?php esc_html_e( 'Display Name', 'platform-theme' ); ?></label>
						<input type="text" id="display_name" name="display_name" value="<?php echo esc_attr( $user->display_name ); ?>" required>

						<label for="email"><?php esc_html_e( 'Email', 'platform-theme' ); ?></label>
						<input type="email" id="email" name="email" value="<?php echo esc_attr( $user->user_email ); ?>" required>

						<label><?php esc_html_e( 'Username', 'platform-theme' ); ?></label>
						<input type="text" value="<?php echo esc_attr( $user->user_login ); ?>" disabled>
						<?php
					}
					?>

					<div class="mpp-profile-form__actions mpp-form-actions">
						<button type="submit" class="mpp-btn mpp-btn--primary"><?php esc_html_e( 'Save Profile', 'platform-theme' ); ?></button>
					</div>
				</form>
			</section>
		<?php endif; ?>

		<section class="mpp-profile-section">
			<h3 class="mpp-profile-section__title"><?php esc_html_e( 'Account Details', 'platform-theme' ); ?></h3>
			<dl class="mpp-profile-list mpp-profile-list--compact">
				<dt><?php esc_html_e( 'Username', 'platform-theme' ); ?></dt>
				<dd><?php echo esc_html( $user->user_login ); ?></dd>

				<dt><?php esc_html_e( 'Display Name', 'platform-theme' ); ?></dt>
				<dd><?php echo esc_html( $user->display_name ); ?></dd>

				<dt><?php esc_html_e( 'Email', 'platform-theme' ); ?></dt>
				<dd><?php echo esc_html( $user->user_email ); ?></dd>

				<dt><?php esc_html_e( 'Registered', 'platform-theme' ); ?></dt>
				<dd><?php echo esc_html( mysql2date( get_option( 'date_format' ), $user->user_registered ) ); ?></dd>

				<dt><?php esc_html_e( 'Platform Roles', 'platform-theme' ); ?></dt>
				<dd><?php echo esc_html( ! empty( $roles ) ? implode( ', ', $roles ) : '—' ); ?></dd>

				<dt><?php esc_html_e( 'Permissions', 'platform-theme' ); ?></dt>
				<dd><?php echo esc_html( isset( $summary['permission_count'] ) ? (string) $summary['permission_count'] : '0' ); ?></dd>

				<dt><?php esc_html_e( 'Accessible Panels', 'platform-theme' ); ?></dt>
				<dd><?php echo esc_html( ! empty( $panels ) ? implode( ', ', $panels ) : '—' ); ?></dd>
			</dl>
		</section>

		<section class="mpp-profile-section">
			<h3 class="mpp-profile-section__title"><?php esc_html_e( 'Security', 'platform-theme' ); ?></h3>
			<p class="mpp-muted"><?php esc_html_e( 'Password changes are managed through your WordPress account profile.', 'platform-theme' ); ?></p>
		</section>
	</div>
	<?php
}
