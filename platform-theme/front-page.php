<?php
/**
 * Front page template.
 *
 * @package PlatformTheme
 */

defined( 'ABSPATH' ) || exit;

$platform_name         = function_exists( 'mpp_get_platform_name' ) ? mpp_get_platform_name() : get_bloginfo( 'name' );
$registration_enabled  = class_exists( '\MPP\Auth\RegistrationHandler' ) && \MPP\Auth\RegistrationHandler::is_enabled();
$login_url             = mpp_route_url( 'login' );
$register_url          = mpp_route_url( 'register' );

get_header();
?>

<main class="mpp-home" id="mpp-main-content">
	<section class="mpp-home-hero">
		<div class="mpp-home-container mpp-home-hero__grid">
			<div class="mpp-home-hero__content">
				<p class="mpp-home-eyebrow"><?php echo esc_html( $platform_name ); ?></p>
				<h1 class="mpp-home-hero__title"><?php esc_html_e( 'One workspace for every role in your organization', 'platform-theme' ); ?></h1>
				<p class="mpp-home-hero__text"><?php esc_html_e( 'Give each person the right panel, the right permissions, and a clear path to get work done — without juggling separate tools.', 'platform-theme' ); ?></p>
				<div class="mpp-home-hero__actions">
					<a class="mpp-btn mpp-btn--primary mpp-btn--lg" href="<?php echo esc_url( $login_url ); ?>"><?php esc_html_e( 'Sign in', 'platform-theme' ); ?></a>
					<?php if ( $registration_enabled ) : ?>
						<a class="mpp-btn mpp-btn--secondary mpp-btn--lg" href="<?php echo esc_url( $register_url ); ?>"><?php esc_html_e( 'Create account', 'platform-theme' ); ?></a>
					<?php endif; ?>
				</div>
			</div>
			<div class="mpp-home-preview" aria-hidden="true">
				<div class="mpp-home-preview__shell">
					<div class="mpp-home-preview__header">
						<span class="mpp-home-preview__dot"></span>
						<span class="mpp-home-preview__dot"></span>
						<span class="mpp-home-preview__dot"></span>
					</div>
					<div class="mpp-home-preview__body">
						<div class="mpp-home-preview__sidebar"></div>
						<div class="mpp-home-preview__main">
							<div class="mpp-home-preview__line mpp-home-preview__line--lg"></div>
							<div class="mpp-home-preview__cards">
								<span></span><span></span><span></span>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>

	<section class="mpp-home-section">
		<div class="mpp-home-container">
			<h2 class="mpp-home-section__title"><?php esc_html_e( 'Built for how people actually work', 'platform-theme' ); ?></h2>
			<div class="mpp-home-outcomes">
				<article class="mpp-home-outcome mpp-home-outcome--user">
					<div class="mpp-home-outcome__icon" aria-hidden="true">U</div>
					<h3><?php esc_html_e( 'For everyday users', 'platform-theme' ); ?></h3>
					<p><?php esc_html_e( 'A personal dashboard, profile, and settings — only the tools you are allowed to use.', 'platform-theme' ); ?></p>
				</article>
				<article class="mpp-home-outcome mpp-home-outcome--manager">
					<div class="mpp-home-outcome__icon" aria-hidden="true">M</div>
					<h3><?php esc_html_e( 'For team leads', 'platform-theme' ); ?></h3>
					<p><?php esc_html_e( 'Operational overview and module-driven workflows when your organization installs them.', 'platform-theme' ); ?></p>
				</article>
				<article class="mpp-home-outcome mpp-home-outcome--admin">
					<div class="mpp-home-outcome__icon" aria-hidden="true">A</div>
					<h3><?php esc_html_e( 'For administrators', 'platform-theme' ); ?></h3>
					<p><?php esc_html_e( 'Users, roles, permissions, modules, and audit — controlled from one admin panel.', 'platform-theme' ); ?></p>
				</article>
			</div>
		</div>
	</section>

	<section class="mpp-home-section mpp-home-section--muted">
		<div class="mpp-home-container mpp-home-split">
			<div>
				<h2 class="mpp-home-section__title"><?php esc_html_e( 'Secure by design', 'platform-theme' ); ?></h2>
				<p class="mpp-home-lead"><?php esc_html_e( 'Access is never accidental. Roles define what each person can see and do, with a full trail when administrators make changes.', 'platform-theme' ); ?></p>
				<ul class="mpp-home-checklist">
					<li><?php esc_html_e( 'Role-based panels instead of one cluttered interface', 'platform-theme' ); ?></li>
					<li><?php esc_html_e( 'Scoped permissions for fine-grained control', 'platform-theme' ); ?></li>
					<li><?php esc_html_e( 'Audit log for sensitive administrative actions', 'platform-theme' ); ?></li>
				</ul>
			</div>
			<div class="mpp-home-panels">
				<div class="mpp-home-panel-chip mpp-home-panel-chip--user"><?php esc_html_e( 'User Panel', 'platform-theme' ); ?></div>
				<div class="mpp-home-panel-chip mpp-home-panel-chip--manager"><?php esc_html_e( 'Manager Panel', 'platform-theme' ); ?></div>
				<div class="mpp-home-panel-chip mpp-home-panel-chip--admin"><?php esc_html_e( 'Admin Panel', 'platform-theme' ); ?></div>
			</div>
		</div>
	</section>

	<section class="mpp-home-section">
		<div class="mpp-home-container">
			<h2 class="mpp-home-section__title"><?php esc_html_e( 'Extend without rebuilding', 'platform-theme' ); ?></h2>
			<p class="mpp-home-lead"><?php esc_html_e( 'Install modules that add routes, permissions, and dashboard widgets. The core stays stable while your product grows.', 'platform-theme' ); ?></p>
			<div class="mpp-home-grid mpp-home-grid--compact">
				<article class="mpp-home-card">
					<h3><?php esc_html_e( 'Modular plugins', 'platform-theme' ); ?></h3>
					<p><?php esc_html_e( 'Register features independently without forking the platform.', 'platform-theme' ); ?></p>
				</article>
				<article class="mpp-home-card">
					<h3><?php esc_html_e( 'Bilingual interface', 'platform-theme' ); ?></h3>
					<p><?php esc_html_e( 'Persian and English with proper RTL and LTR layout support.', 'platform-theme' ); ?></p>
				</article>
				<article class="mpp-home-card">
					<h3><?php esc_html_e( 'WordPress foundation', 'platform-theme' ); ?></h3>
					<p><?php esc_html_e( 'Trusted authentication and user accounts powered by WordPress.', 'platform-theme' ); ?></p>
				</article>
			</div>
		</div>
	</section>

	<section class="mpp-home-cta">
		<div class="mpp-home-container">
			<h2><?php esc_html_e( 'Ready to enter your workspace?', 'platform-theme' ); ?></h2>
			<p><?php esc_html_e( 'Sign in with your account or ask an administrator for access.', 'platform-theme' ); ?></p>
			<div class="mpp-home-hero__actions">
				<a class="mpp-btn mpp-btn--primary mpp-btn--lg" href="<?php echo esc_url( $login_url ); ?>"><?php esc_html_e( 'Sign in', 'platform-theme' ); ?></a>
				<?php if ( $registration_enabled ) : ?>
					<a class="mpp-btn mpp-btn--secondary mpp-btn--lg" href="<?php echo esc_url( $register_url ); ?>"><?php esc_html_e( 'Create account', 'platform-theme' ); ?></a>
				<?php endif; ?>
			</div>
		</div>
	</section>
</main>

<?php
get_footer();
