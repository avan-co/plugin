<?php
/**
 * Front page template.
 *
 * @package PlatformTheme
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main class="mpp-home" id="mpp-main-content">
	<section class="mpp-home-hero">
		<div class="mpp-home-container">
			<h1 class="mpp-home-hero__title"><?php esc_html_e( 'Multi-panel WordPress platform foundation', 'platform-theme' ); ?></h1>
			<p class="mpp-home-hero__text"><?php esc_html_e( 'A unified infrastructure for users, roles, permissions, and user, manager, and admin panels with a modular, secure architecture.', 'platform-theme' ); ?></p>
			<div class="mpp-home-hero__actions">
				<a class="mpp-btn mpp-btn--primary" href="<?php echo esc_url( mpp_route_url( 'login' ) ); ?>"><?php esc_html_e( 'Login', 'platform-theme' ); ?></a>
				<a class="mpp-btn mpp-btn--secondary" href="<?php echo esc_url( mpp_route_url( 'register' ) ); ?>"><?php esc_html_e( 'Register', 'platform-theme' ); ?></a>
			</div>
		</div>
	</section>

	<section class="mpp-home-section">
		<div class="mpp-home-container">
			<h2><?php esc_html_e( 'Core capabilities', 'platform-theme' ); ?></h2>
			<div class="mpp-home-grid">
				<article class="mpp-home-card"><h3><?php esc_html_e( 'Modular architecture', 'platform-theme' ); ?></h3><p><?php esc_html_e( 'Add independent modules without changing the platform core.', 'platform-theme' ); ?></p></article>
				<article class="mpp-home-card"><h3><?php esc_html_e( 'Role and access management', 'platform-theme' ); ?></h3><p><?php esc_html_e( 'Dynamic ACL with roles, permissions, and scoped access.', 'platform-theme' ); ?></p></article>
				<article class="mpp-home-card"><h3><?php esc_html_e( 'Multiple panels', 'platform-theme' ); ?></h3><p><?php esc_html_e( 'Separate experiences for users, managers, and administrators.', 'platform-theme' ); ?></p></article>
				<article class="mpp-home-card"><h3><?php esc_html_e( 'WordPress foundation', 'platform-theme' ); ?></h3><p><?php esc_html_e( 'Authentication and users are powered by WordPress Core.', 'platform-theme' ); ?></p></article>
			</div>
		</div>
	</section>

	<section class="mpp-home-section mpp-home-section--muted">
		<div class="mpp-home-container">
			<h2><?php esc_html_e( 'Security & ACL', 'platform-theme' ); ?></h2>
			<p class="mpp-home-lead"><?php esc_html_e( 'Dynamic roles, scoped permissions, audit logging, and a dedicated ACL admin experience.', 'platform-theme' ); ?></p>
			<div class="mpp-home-grid">
				<article class="mpp-home-card"><h3><?php esc_html_e( 'Role-based access', 'platform-theme' ); ?></h3><p><?php esc_html_e( 'Platform roles remain separate from WordPress roles.', 'platform-theme' ); ?></p></article>
				<article class="mpp-home-card"><h3><?php esc_html_e( 'Permission matrix', 'platform-theme' ); ?></h3><p><?php esc_html_e( 'Grant and revoke permissions with scope controls.', 'platform-theme' ); ?></p></article>
				<article class="mpp-home-card"><h3><?php esc_html_e( 'Audit trail', 'platform-theme' ); ?></h3><p><?php esc_html_e( 'Administrative changes are recorded for review.', 'platform-theme' ); ?></p></article>
			</div>
		</div>
	</section>

	<section class="mpp-home-section">
		<div class="mpp-home-container">
			<h2><?php esc_html_e( 'Modular architecture', 'platform-theme' ); ?></h2>
			<p class="mpp-home-lead"><?php esc_html_e( 'Independent plugins can register routes, permissions, navigation, and dashboard widgets through Platform Core.', 'platform-theme' ); ?></p>
		</div>
	</section>

	<section class="mpp-home-section mpp-home-section--muted">
		<div class="mpp-home-container">
			<h2><?php esc_html_e( 'User / Manager / Admin panels', 'platform-theme' ); ?></h2>
			<div class="mpp-home-grid mpp-home-grid--panels">
				<article class="mpp-home-card"><h3><?php esc_html_e( 'User', 'platform-theme' ); ?></h3><p><?php esc_html_e( 'Personal dashboard, profile, and account settings.', 'platform-theme' ); ?></p></article>
				<article class="mpp-home-card"><h3><?php esc_html_e( 'Manager', 'platform-theme' ); ?></h3><p><?php esc_html_e( 'Manager workspace with placeholders for future team tools.', 'platform-theme' ); ?></p></article>
				<article class="mpp-home-card"><h3><?php esc_html_e( 'Admin', 'platform-theme' ); ?></h3><p><?php esc_html_e( 'Manage users, roles, permissions, modules, and ACL.', 'platform-theme' ); ?></p></article>
			</div>
		</div>
	</section>

	<section class="mpp-home-section">
		<div class="mpp-home-container">
			<h2><?php esc_html_e( 'How it works', 'platform-theme' ); ?></h2>
			<ol class="mpp-home-steps">
				<li><?php esc_html_e( 'Register or log in to the platform.', 'platform-theme' ); ?></li>
				<li><?php esc_html_e( 'Receive a platform role such as platform_user.', 'platform-theme' ); ?></li>
				<li><?php esc_html_e( 'Access the panel that matches your permissions.', 'platform-theme' ); ?></li>
				<li><?php esc_html_e( 'Platform administrators manage ACL from the admin panel.', 'platform-theme' ); ?></li>
			</ol>
		</div>
	</section>

	<section class="mpp-home-section mpp-home-section--muted">
		<div class="mpp-home-container">
			<h2><?php esc_html_e( 'Bilingual & RTL/LTR', 'platform-theme' ); ?></h2>
			<p class="mpp-home-lead"><?php esc_html_e( 'Native RTL/LTR support with a shared design system for Persian and English interfaces.', 'platform-theme' ); ?></p>
		</div>
	</section>

	<section class="mpp-home-section">
		<div class="mpp-home-container">
			<h2><?php esc_html_e( 'Architecture overview', 'platform-theme' ); ?></h2>
			<div class="mpp-home-arch">
				<div class="mpp-home-arch__item"><?php esc_html_e( 'WordPress Core', 'platform-theme' ); ?></div>
				<div class="mpp-home-arch__arrow" aria-hidden="true">↓</div>
				<div class="mpp-home-arch__item"><?php esc_html_e( 'Platform Core', 'platform-theme' ); ?></div>
				<div class="mpp-home-arch__arrow" aria-hidden="true">↓</div>
				<div class="mpp-home-arch__item"><?php esc_html_e( 'Platform Modules', 'platform-theme' ); ?></div>
			</div>
		</div>
	</section>

	<section class="mpp-home-cta">
		<div class="mpp-home-container">
			<h2><?php esc_html_e( 'Ready to get started?', 'platform-theme' ); ?></h2>
			<p><?php esc_html_e( 'Sign in or create an account to access your panel.', 'platform-theme' ); ?></p>
			<div class="mpp-home-hero__actions">
				<a class="mpp-btn mpp-btn--primary" href="<?php echo esc_url( mpp_route_url( 'login' ) ); ?>"><?php esc_html_e( 'Login', 'platform-theme' ); ?></a>
				<a class="mpp-btn mpp-btn--secondary" href="<?php echo esc_url( mpp_route_url( 'register' ) ); ?>"><?php esc_html_e( 'Register', 'platform-theme' ); ?></a>
			</div>
		</div>
	</section>
</main>

<?php
get_footer();
