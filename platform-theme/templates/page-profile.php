<?php
/**
 * Profile page template.
 *
 * @package PlatformTheme
 */

defined( 'ABSPATH' ) || exit;

require_once get_template_directory() . '/inc/account-layout.php';
require_once get_template_directory() . '/inc/profile-page.php';

ob_start();
?>
<div class="mpp-card mpp-card--profile">
	<?php platform_render_profile_content( mpp_route_url( 'profile' ) ); ?>
</div>
<?php
$content = ob_get_clean();

platform_render_account_page(
	'user',
	__( 'Profile', 'platform-theme' ),
	$content,
	__( 'View and update your account profile information.', 'platform-theme' )
);
