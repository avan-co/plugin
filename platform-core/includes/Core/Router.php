<?php
/**
 * Platform route registration and dispatch.
 *
 * @package PlatformCore
 */

namespace MPP\Core;

use MPP\ACL\AclEngine;

defined( 'ABSPATH' ) || exit;

/**
 * Class Router
 */
class Router {

	/**
	 * Query var for platform routes.
	 */
	const QUERY_VAR = 'mpp_route';

	/**
	 * ACL engine.
	 *
	 * @var AclEngine
	 */
	private $acl;

	/**
	 * Registered routes.
	 *
	 * @var array<string, array<string, mixed>>
	 */
	private $routes = array();

	/**
	 * Constructor.
	 *
	 * @param AclEngine $acl ACL engine.
	 */
	public function __construct( AclEngine $acl ) {
		$this->acl = $acl;
		$this->register_default_routes();
	}

	/**
	 * Register hooks.
	 */
	public function register() {
		add_action( 'init', array( $this, 'add_rewrite_rules' ) );
		add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
		add_filter( 'redirect_canonical', array( $this, 'prevent_canonical_redirect' ), 10, 2 );
		add_filter( 'pre_handle_404', array( $this, 'pre_handle_404' ), 10, 2 );
		add_action( 'template_redirect', array( $this, 'dispatch' ) );
	}

	/**
	 * Register a route.
	 *
	 * @param string               $slug       Route slug (e.g. app/user).
	 * @param array<string, mixed> $definition Route definition.
	 */
	public function add_route( $slug, array $definition ) {
		$this->routes[ trim( $slug, '/' ) ] = wp_parse_args(
			$definition,
			array(
				'template'    => '',
				'permission'  => '',
				'auth'        => true,
				'title'       => '',
				'description' => '',
			)
		);
	}

	/**
	 * Get all registered routes.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public function get_routes() {
		return apply_filters( 'mpp_registered_routes', $this->routes );
	}

	/**
	 * Add rewrite rules on init.
	 */
	public function add_rewrite_rules() {
		add_rewrite_tag( '%' . self::QUERY_VAR . '%', '(.+)', self::QUERY_VAR . '=' );

		foreach ( array_keys( $this->get_routes() ) as $slug ) {
			add_rewrite_rule(
				'^' . preg_quote( $slug, '/' ) . '/?$',
				'index.php?' . self::QUERY_VAR . '=' . $slug,
				'top'
			);
		}
	}

	/**
	 * Add query vars.
	 *
	 * @param array<int, string> $vars Query vars.
	 * @return array<int, string>
	 */
	public function add_query_vars( $vars ) {
		$vars[] = self::QUERY_VAR;
		return $vars;
	}

	/**
	 * Dispatch the current route.
	 */
	public function dispatch() {
		$route = $this->resolve_route_slug();

		if ( empty( $route ) ) {
			return;
		}

		$routes     = $this->get_routes();
		$definition = isset( $routes[ $route ] ) ? $routes[ $route ] : null;

		if ( null === $definition ) {
			$this->render_error( '404' );
			return;
		}

		if ( ! empty( $definition['auth'] ) && ! is_user_logged_in() ) {
			wp_safe_redirect( mpp_route_url( 'login' ) );
			exit;
		}

		if ( ! empty( $definition['permission'] ) ) {
			$user_id = get_current_user_id();

			if ( ! $this->acl->can( $user_id, $definition['permission'] ) ) {
				$this->render_error( '403' );
				return;
			}
		}

		$GLOBALS['mpp_current_route'] = array(
			'slug'        => $route,
			'definition'  => $definition,
		);

		if ( ! empty( $definition['status'] ) ) {
			status_header( (int) $definition['status'] );
			nocache_headers();
		}

		$template = $definition['template'];

		if ( empty( $template ) ) {
			$this->render_error( '404' );
			return;
		}

		/**
		 * Fires before a platform route template is loaded.
		 *
		 * @param string               $route      Route slug.
		 * @param array<string, mixed> $definition Route definition.
		 */
		do_action( 'mpp_before_route_render', $route, $definition );

		$located = locate_template( $template );

		if ( $located ) {
			include $located;
		} elseif ( ! empty( $definition['template_file'] ) && is_readable( $definition['template_file'] ) ) {
			include $definition['template_file'];
		} else {
			$fallback = MPP_PLUGIN_DIR . 'templates/' . basename( $template );

			if ( is_readable( $fallback ) ) {
				include $fallback;
			} else {
				$this->render_error( '404' );
				return;
			}
		}

		exit;
	}

	/**
	 * Prevent WordPress from canonicalizing platform route URLs away from index.php.
	 *
	 * @param string $redirect_url  Canonical redirect URL.
	 * @param string $requested_url Requested URL.
	 * @return string|false
	 */
	public function prevent_canonical_redirect( $redirect_url, $requested_url ) {
		unset( $requested_url );

		if ( $this->resolve_route_slug() ) {
			return false;
		}

		return $redirect_url;
	}

	/**
	 * Keep platform routes out of WordPress core 404 handling.
	 *
	 * @param bool      $preempt Whether to short-circuit 404 handling.
	 * @param \WP_Query $query   Main query.
	 * @return bool
	 */
	public function pre_handle_404( $preempt, $query ) {
		unset( $query );

		if ( $this->resolve_route_slug() ) {
			return true;
		}

		return $preempt;
	}

	/**
	 * Resolve the current route slug from query vars or request URI.
	 *
	 * @return string
	 */
	private function resolve_route_slug() {
		$route = get_query_var( self::QUERY_VAR );

		if ( ! empty( $route ) ) {
			return trim( (string) $route, '/' );
		}

		if ( empty( $_SERVER['REQUEST_URI'] ) ) {
			return '';
		}

		$path = wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH );

		if ( ! is_string( $path ) ) {
			return '';
		}

		$path      = trim( $path, '/' );
		$home_path = trim( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ), '/' );

		if ( $home_path && 0 === strpos( $path, $home_path ) ) {
			$path = trim( substr( $path, strlen( $home_path ) ), '/' );
		}

		$routes = $this->get_routes();

		return isset( $routes[ $path ] ) ? $path : '';
	}

	/**
	 * Render an error route.
	 *
	 * @param string $code Error code (403 or 404).
	 */
	private function render_error( $code ) {
		$routes     = $this->get_routes();
		$error_slug = $code;
		$definition = isset( $routes[ $error_slug ] ) ? $routes[ $error_slug ] : null;

		if ( null === $definition ) {
			status_header( '404' === $code ? 404 : 403 );
			nocache_headers();
			wp_die( esc_html__( 'Page not found.', 'platform-core' ), '', array( 'response' => '404' === $code ? 404 : 403 ) );
		}

		$GLOBALS['mpp_current_route'] = array(
			'slug'       => $error_slug,
			'definition' => $definition,
		);

		status_header( '404' === $code ? 404 : 403 );

		$located = locate_template( $definition['template'] );

		if ( $located ) {
			include $located;
		} else {
			$fallback = MPP_PLUGIN_DIR . 'templates/' . basename( $definition['template'] );
			if ( is_readable( $fallback ) ) {
				include $fallback;
			}
		}

		exit;
	}

	/**
	 * Register default platform routes.
	 */
	private function register_default_routes() {
		$this->add_route(
			'login',
			array(
				'template' => 'templates/page-login.php',
				'auth'     => false,
				'title'    => __( 'Login', 'platform-core' ),
			)
		);

		$this->add_route(
			'register',
			array(
				'template' => 'templates/page-register.php',
				'auth'     => false,
				'title'    => __( 'Register', 'platform-core' ),
			)
		);

		$this->add_route(
			'forgot-password',
			array(
				'template' => 'templates/page-forgot-password.php',
				'auth'     => false,
				'title'    => __( 'Forgot Password', 'platform-core' ),
			)
		);

		$this->add_route(
			'app',
			array(
				'template'   => 'templates/page-app.php',
				'permission' => 'core.panel.access',
				'title'      => __( 'Dashboard', 'platform-core' ),
			)
		);

		$this->add_route(
			'app/user',
			array(
				'template'   => 'templates/panel-user.php',
				'permission' => 'core.panel.user.access',
				'title'      => __( 'User Panel', 'platform-core' ),
			)
		);

		$this->add_route(
			'app/manager',
			array(
				'template'   => 'templates/panel-manager.php',
				'permission' => 'core.panel.manager.access',
				'title'      => __( 'Manager Panel', 'platform-core' ),
			)
		);

		$this->add_route(
			'app/admin',
			array(
				'template'   => 'templates/panel-admin.php',
				'permission' => 'core.panel.admin.access',
				'title'      => __( 'Admin Panel', 'platform-core' ),
			)
		);

		$this->add_route(
			'app/manager/profile',
			array(
				'template'   => 'templates/page-manager-profile.php',
				'permission' => 'core.profile.view',
				'title'      => __( 'Manager Profile', 'platform-core' ),
			)
		);

		$this->add_route(
			'profile',
			array(
				'template'   => 'templates/page-profile.php',
				'permission' => 'core.profile.view',
				'title'      => __( 'Profile', 'platform-core' ),
			)
		);

		$this->add_route(
			'settings',
			array(
				'template'   => 'templates/page-settings.php',
				'permission' => 'core.settings.view',
				'title'      => __( 'Settings', 'platform-core' ),
			)
		);

		$this->add_route(
			'403',
			array(
				'template' => 'templates/page-403.php',
				'auth'     => false,
				'status'   => 403,
				'title'    => __( 'Access Denied', 'platform-core' ),
			)
		);

		$this->add_route(
			'404',
			array(
				'template' => 'templates/page-404.php',
				'auth'     => false,
				'status'   => 404,
				'title'    => __( 'Not Found', 'platform-core' ),
			)
		);
	}
}
