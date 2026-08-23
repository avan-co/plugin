<?php
/**
 * Footer template.
 *
 * @package PlatformTheme
 */

defined( 'ABSPATH' ) || exit;
?>

<footer class="mpp-footer">
	<div class="mpp-footer__inner">
		<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?></p>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
