<?php
/**
 * Sidebar template.
 *
 * @package PlatformTheme
 */

defined( 'ABSPATH' ) || exit;

use PlatformTheme\DesignSystem\PanelNavigation;

$panel = isset( $args['panel'] ) ? sanitize_key( $args['panel'] ) : 'user';
?>

<aside class="mpp-sidebar" id="mpp-sidebar" data-panel="<?php echo esc_attr( $panel ); ?>">
	<?php PanelNavigation::render( $panel ); ?>
</aside>
