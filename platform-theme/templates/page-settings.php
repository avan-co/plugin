<?php
/**
 * Settings page template.
 *
 * @package PlatformTheme
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main class="mpp-main">
	<div class="mpp-content mpp-content--narrow">
		<h1><?php esc_html_e( 'Settings', 'platform-theme' ); ?></h1>

		<div class="mpp-card">
			<p><?php esc_html_e( 'User settings will be available in a future phase. This page confirms routing and permission checks are working.', 'platform-theme' ); ?></p>

			<?php if ( function_exists( 'mpp_can' ) && mpp_can( 'core.settings.edit' ) ) : ?>
				<p class="mpp-badge mpp-badge--success"><?php esc_html_e( 'You have edit permissions.', 'platform-theme' ); ?></p>
			<?php else : ?>
				<p class="mpp-badge"><?php esc_html_e( 'View only.', 'platform-theme' ); ?></p>
			<?php endif; ?>
		</div>
	</div>
</main>

<?php
get_footer();
