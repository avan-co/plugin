<?php
/**
 * Sidebar template.
 *
 * @package PlatformTheme
 */

defined( 'ABSPATH' ) || exit;

$panel = isset( $args['panel'] ) ? sanitize_key( $args['panel'] ) : 'user';
?>

<aside class="mpp-sidebar" data-panel="<?php echo esc_attr( $panel ); ?>">
	<?php get_template_part( 'template-parts/navigation', $panel ); ?>
</aside>
