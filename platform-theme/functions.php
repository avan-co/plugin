<?php
/**
 * Theme functions.
 *
 * @package PlatformTheme
 */

defined( 'ABSPATH' ) || exit;

require_once get_template_directory() . '/inc/navigation-helpers.php';
require_once get_template_directory() . '/inc/ui-components.php';
require_once get_template_directory() . '/inc/design-system/class-bootstrap.php';

PlatformTheme\DesignSystem\Bootstrap::init();

/**
 * Theme setup.
 */
function platform_theme_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );

	load_theme_textdomain( 'platform-theme', get_template_directory() . '/languages' );

	register_nav_menus(
		array(
			'platform_primary' => __( 'Platform Primary', 'platform-theme' ),
		)
	);
}
add_action( 'after_setup_theme', 'platform_theme_setup' );

/**
 * Enqueue theme assets.
 */
function platform_theme_enqueue_assets() {
	wp_enqueue_style(
		'platform-theme-fonts',
		'https://fonts.googleapis.com/css2?family=Noto+Sans+Arabic:wght@400;500;600;700&display=swap',
		array(),
		null
	);

	wp_enqueue_style(
		'platform-theme-tokens',
		get_template_directory_uri() . '/assets/css/tokens.css',
		array( 'platform-theme-fonts' ),
		wp_get_theme()->get( 'Version' )
	);

	wp_enqueue_style(
		'platform-theme-components',
		get_template_directory_uri() . '/assets/css/components.css',
		array( 'platform-theme-tokens' ),
		wp_get_theme()->get( 'Version' )
	);

	wp_enqueue_style(
		'platform-theme',
		get_stylesheet_uri(),
		array( 'platform-theme-components' ),
		wp_get_theme()->get( 'Version' )
	);

	wp_enqueue_style(
		'platform-theme-main',
		get_template_directory_uri() . '/assets/css/main.css',
		array( 'platform-theme' ),
		wp_get_theme()->get( 'Version' )
	);

	wp_enqueue_style(
		'platform-theme-panels',
		get_template_directory_uri() . '/assets/css/panels.css',
		array( 'platform-theme-main' ),
		wp_get_theme()->get( 'Version' )
	);

	wp_enqueue_style(
		'platform-theme-responsive',
		get_template_directory_uri() . '/assets/css/responsive.css',
		array( 'platform-theme-panels' ),
		wp_get_theme()->get( 'Version' )
	);

	wp_enqueue_script(
		'platform-theme-navigation',
		get_template_directory_uri() . '/assets/js/navigation.js',
		array(),
		wp_get_theme()->get( 'Version' ),
		true
	);

	if ( is_user_logged_in() ) {
		wp_enqueue_script(
			'platform-theme-account-notice',
			get_template_directory_uri() . '/assets/js/account-notice.js',
			array(),
			wp_get_theme()->get( 'Version' ),
			true
		);
		wp_enqueue_script(
			'platform-theme-account-menu',
			get_template_directory_uri() . '/assets/js/account-menu.js',
			array(),
			wp_get_theme()->get( 'Version' ),
			true
		);
	}

	if ( is_front_page() ) {
		wp_enqueue_style(
			'platform-theme-home',
			get_template_directory_uri() . '/assets/css/home.css',
			array( 'platform-theme-main' ),
			wp_get_theme()->get( 'Version' )
		);
	}

	if ( function_exists( 'mpp_get_current_route' ) ) {
		$route = mpp_get_current_route();
		if ( $route && in_array( $route['slug'], array( 'login', 'register', 'forgot-password' ), true ) ) {
			wp_enqueue_script(
				'platform-theme-forms',
				get_template_directory_uri() . '/assets/js/forms.js',
				array(),
				wp_get_theme()->get( 'Version' ),
				true
			);
			wp_localize_script(
				'platform-theme-forms',
				'mppForms',
				array(
					'show'   => __( 'Show', 'platform-theme' ),
					'hide'   => __( 'Hide', 'platform-theme' ),
					'weak'   => __( 'Weak password', 'platform-theme' ),
					'fair'   => __( 'Fair password', 'platform-theme' ),
					'good'   => __( 'Good password', 'platform-theme' ),
					'strong' => __( 'Strong password', 'platform-theme' ),
					'passwordMismatch' => __( 'Passwords do not match.', 'platform-theme' ),
				)
			);
		}
		if ( $route && 0 === strpos( $route['slug'], 'app/admin' ) ) {
			wp_enqueue_style(
				'platform-theme-admin',
				get_template_directory_uri() . '/assets/css/admin.css',
				array( 'platform-theme-responsive' ),
				wp_get_theme()->get( 'Version' )
			);
			wp_enqueue_script(
				'platform-theme-settings-nav',
				get_template_directory_uri() . '/assets/js/settings-nav.js',
				array(),
				wp_get_theme()->get( 'Version' ),
				true
			);
		}
	}

	if ( function_exists( 'mpp_get_accent_color' ) ) {
		$accent = mpp_get_accent_color();
		if ( $accent && preg_match( '/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $accent ) ) {
			wp_add_inline_style(
				'platform-theme-tokens',
				sprintf( ':root { --mpp-primary: %s; }', esc_attr( $accent ) )
			);
		}
	}
}
add_action( 'wp_enqueue_scripts', 'platform_theme_enqueue_assets' );

/**
 * Add body classes for platform pages.
 *
 * @param array<int, string> $classes Body classes.
 * @return array<int, string>
 */
function platform_theme_body_classes( $classes ) {
	if ( is_rtl() ) {
		$classes[] = 'mpp-rtl';
	}

	$locale = get_locale();
	if ( $locale ) {
		$classes[] = 'mpp-locale-' . sanitize_html_class( strtolower( substr( $locale, 0, 2 ) ) );
	}

	if ( function_exists( 'mpp_is_auth_route' ) && mpp_is_auth_route() ) {
		$classes[] = 'mpp-auth-page';
	}

	return $classes;
}
add_filter( 'body_class', 'platform_theme_body_classes' );

/**
 * Get platform page title from current route.
 *
 * @return string
 */
function platform_get_page_title() {
	if ( function_exists( 'mpp_get_current_route' ) ) {
		$route = mpp_get_current_route();

		if ( $route && ! empty( $route['definition']['title'] ) ) {
			return $route['definition']['title'];
		}
	}

	return get_bloginfo( 'name' );
}

/**
 * Get the active panel slug from the current route.
 *
 * @return string
 */
function platform_get_current_panel() {
	if ( ! function_exists( 'mpp_get_current_route' ) ) {
		return '';
	}

	$route = mpp_get_current_route();

	if ( ! $route || empty( $route['slug'] ) ) {
		return '';
	}

	$slug = $route['slug'];

	if ( preg_match( '#^app/(user|manager|admin)(?:/|$)#', $slug, $matches ) ) {
		return $matches[1];
	}

	if ( in_array( $slug, array( 'profile', 'settings' ), true ) && function_exists( 'mpp_get_accessible_panels' ) ) {
		$panels = mpp_get_accessible_panels();

		return ! empty( $panels ) ? (string) $panels[0] : 'user';
	}

	return '';
}

/**
 * Render account menu dropdown for logged-in users.
 */
function platform_render_account_menu() {
	if ( ! is_user_logged_in() ) {
		return;
	}

	$user = wp_get_current_user();

	?>
	<div class="mpp-account-menu" data-account-menu>
		<button
			type="button"
			class="mpp-account-menu__trigger"
			aria-expanded="false"
			aria-controls="mpp-account-menu-panel"
			aria-haspopup="true"
		>
			<?php echo platform_ui_avatar( (int) $user->ID, 32 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<span class="mpp-account-menu__name"><?php echo esc_html( $user->display_name ); ?></span>
		</button>
		<div id="mpp-account-menu-panel" class="mpp-account-menu__panel" hidden>
			<div class="mpp-account-menu__header">
				<strong><?php echo esc_html( $user->display_name ); ?></strong>
				<span class="mpp-muted"><?php echo esc_html( $user->user_email ); ?></span>
			</div>
			<ul class="mpp-account-menu__list">
				<li>
					<a href="<?php echo esc_url( function_exists( 'mpp_route_url' ) ? mpp_route_url( 'profile' ) : home_url( '/profile' ) ); ?>">
						<?php esc_html_e( 'Profile', 'platform-theme' ); ?>
					</a>
				</li>
				<li>
					<a href="<?php echo esc_url( function_exists( 'mpp_route_url' ) ? mpp_route_url( 'settings' ) : home_url( '/settings' ) ); ?>">
						<?php esc_html_e( 'Settings', 'platform-theme' ); ?>
					</a>
				</li>
				<li>
					<a href="<?php echo esc_url( function_exists( 'mpp_logout_url' ) ? mpp_logout_url() : wp_logout_url() ); ?>" class="mpp-account-menu__logout">
						<?php esc_html_e( 'Logout', 'platform-theme' ); ?>
					</a>
				</li>
			</ul>
		</div>
	</div>
	<?php
}

/**
 * Render panel switcher links.
 */
function platform_render_panel_switcher() {
	if ( ! function_exists( 'mpp_get_accessible_panels' ) ) {
		return;
	}

	$panels = mpp_get_accessible_panels();
	$labels = array(
		'user'    => __( 'User Panel', 'platform-theme' ),
		'manager' => __( 'Manager Panel', 'platform-theme' ),
		'admin'   => __( 'Admin Panel', 'platform-theme' ),
	);

	if ( empty( $panels ) ) {
		return;
	}

	$current_panel = platform_get_current_panel();

	echo '<nav class="mpp-panel-switcher" aria-label="' . esc_attr__( 'Panel navigation', 'platform-theme' ) . '">';
	echo '<ul>';

	foreach ( $panels as $panel ) {
		if ( ! isset( $labels[ $panel ] ) ) {
			continue;
		}

		$is_active = $panel === $current_panel;

		printf(
			'<li%s><a href="%s"%s>%s</a></li>',
			$is_active ? ' class="is-active"' : '',
			esc_url( function_exists( 'mpp_route_url' ) ? mpp_route_url( 'app/' . $panel ) : home_url( '/app/' . $panel ) ),
			$is_active ? ' aria-current="page"' : '',
			esc_html( $labels[ $panel ] )
		);
	}

	echo '</ul>';
	echo '</nav>';
}
