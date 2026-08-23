<?php
/**
 * Main template fallback.
 *
 * @package PlatformTheme
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main class="mpp-main">
	<div class="mpp-content">
		<?php
		if ( have_posts() ) {
			while ( have_posts() ) {
				the_post();
				the_content();
			}
		} else {
			echo '<p>' . esc_html__( 'No content found.', 'platform-theme' ) . '</p>';
		}
		?>
	</div>
</main>

<?php
get_footer();
