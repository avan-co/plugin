<?php
/**
 * Permissions REST API controller.
 *
 * @package PlatformCore
 */

namespace MPP\API;

use MPP\ACL\AclEngine;
use MPP\ACL\PermissionRegistry;
use MPP\Auth\AccessGuard;
use MPP\Services\PermissionService;

defined( 'ABSPATH' ) || exit;

/**
 * Class PermissionsController
 */
class PermissionsController extends RestController {

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'permissions';

	/**
	 * Permission registry.
	 *
	 * @var PermissionRegistry
	 */
	private $registry;

	/**
	 * Permission service.
	 *
	 * @var PermissionService
	 */
	private $service;

	/**
	 * Access guard.
	 *
	 * @var AccessGuard
	 */
	private $guard;

	/**
	 * Constructor.
	 *
	 * @param PermissionRegistry $registry Permission registry.
	 * @param AclEngine          $acl      ACL engine.
	 */
	public function __construct( PermissionRegistry $registry, AclEngine $acl ) {
		$this->registry = $registry;
		$this->service  = new PermissionService( $registry );
		$this->guard    = new AccessGuard( $acl );
	}

	/**
	 * Register routes.
	 */
	public function register() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register REST routes.
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_items' ),
				'permission_callback' => $this->guard->rest_permission( 'core.acl.manage' ),
			)
		);
	}

	/**
	 * Get permission tree.
	 *
	 * @return \WP_REST_Response
	 */
	public function get_items() {
		return rest_ensure_response( $this->service->get_permission_tree() );
	}
}
