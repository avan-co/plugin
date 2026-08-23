<?php
/**
 * App dashboard — panel launcher.
 *
 * @package PlatformTheme
 */

defined( 'ABSPATH' ) || exit;

get_header();

$panels = function_exists( 'mpp_get_accessible_panels' ) ? mpp_get_accessible_panels() : array();
$labels = array(
	'user'    => __( 'User Panel', 'platform-theme' ),
	'manager' => __( 'Manager Panel', 'platform-theme' ),
	'admin'   => __( 'Admin Panel', 'platform-theme' ),
);
?>

<main class="mpp-main mpp-main--dashboard" id="mpp-main-content">
	<div class="mpp-content mpp-content--narrow">
		<?php platform_ui_page_header( __( 'Dashboard', 'platform-theme' ), __( 'Select a panel to continue.', 'platform-theme' ) ); ?>

		<?php if ( ! empty( $panels ) ) : ?>
			<div class="mpp-panel-cards">
				<?php foreach ( $panels as $panel ) : ?>
					<?php if ( ! isset( $labels[ $panel ] ) ) { continue; } ?>
					<a href="<?php echo esc_url( mpp_route_url( 'app/' . $panel ) ); ?>" class="mpp-panel-card">
						<h2><?php echo esc_html( $labels[ $panel ] ); ?></h2>
						<span class="mpp-panel-card__arrow" aria-hidden="true">→</span>
					</a>
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<?php platform_ui_alert( __( 'No panels are available for your account. Contact an administrator.', 'platform-theme' ), 'warning' ); ?>
		<?php endif; ?>
	</div>
</main>

<?php
get_footer();
