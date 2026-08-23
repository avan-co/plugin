<?php
/**
 * App dashboard — redirects to first accessible panel.
 *
 * @package PlatformTheme
 */

defined( 'ABSPATH' ) || exit;

get_header();

$panels = function_exists( 'mpp_get_accessible_panels' ) ? mpp_get_accessible_panels() : array();
?>

<main class="mpp-main mpp-main--dashboard">
	<div class="mpp-content">
		<h1><?php esc_html_e( 'Dashboard', 'platform-theme' ); ?></h1>

		<?php if ( ! empty( $panels ) ) : ?>
			<p><?php esc_html_e( 'Select a panel to continue:', 'platform-theme' ); ?></p>
			<div class="mpp-panel-cards">
				<?php
				$labels = array(
					'user'    => __( 'User Panel', 'platform-theme' ),
					'manager' => __( 'Manager Panel', 'platform-theme' ),
					'admin'   => __( 'Admin Panel', 'platform-theme' ),
				);

				foreach ( $panels as $panel ) :
					if ( ! isset( $labels[ $panel ] ) ) {
						continue;
					}
					?>
					<a href="<?php echo esc_url( home_url( '/app/' . $panel ) ); ?>" class="mpp-panel-card">
						<h2><?php echo esc_html( $labels[ $panel ] ); ?></h2>
						<span class="mpp-panel-card__arrow">&rarr;</span>
					</a>
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<div class="mpp-alert mpp-alert--warning">
				<?php esc_html_e( 'No panels are available for your account. Contact an administrator.', 'platform-theme' ); ?>
			</div>
		<?php endif; ?>
	</div>
</main>

<?php
get_footer();
