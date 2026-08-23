<?php
/**
 * Profile page template.
 *
 * @package PlatformTheme
 */

defined( 'ABSPATH' ) || exit;

get_header();

$user = wp_get_current_user();
?>

<main class="mpp-main">
	<div class="mpp-content mpp-content--narrow">
		<h1><?php esc_html_e( 'Profile', 'platform-theme' ); ?></h1>

		<div class="mpp-card">
			<dl class="mpp-profile-list">
				<dt><?php esc_html_e( 'Display Name', 'platform-theme' ); ?></dt>
				<dd><?php echo esc_html( $user->display_name ); ?></dd>

				<dt><?php esc_html_e( 'Email', 'platform-theme' ); ?></dt>
				<dd><?php echo esc_html( $user->user_email ); ?></dd>

				<dt><?php esc_html_e( 'Username', 'platform-theme' ); ?></dt>
				<dd><?php echo esc_html( $user->user_login ); ?></dd>

				<?php if ( function_exists( 'mpp_get_accessible_panels' ) ) : ?>
					<dt><?php esc_html_e( 'Accessible Panels', 'platform-theme' ); ?></dt>
					<dd><?php echo esc_html( implode( ', ', mpp_get_accessible_panels() ) ); ?></dd>
				<?php endif; ?>
			</dl>
		</div>
	</div>
</main>

<?php
get_footer();
