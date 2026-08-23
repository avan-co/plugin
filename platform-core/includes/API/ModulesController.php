<?php
/**
 * Modules REST API controller.
 *
 * @package PlatformCore
 */

namespace MPP\API;

use MPP\ACL\AclEngine;
use MPP\Auth\AccessGuard;
use MPP\Services\ModuleService;

defined( 'ABSPATH' ) || exit;

/**
 * Class ModulesController
 */
class ModulesController extends RestController {

	protected $rest_base = 'modules';

	/**
	 * @var ModuleService
	 */
	private $modules;

	/**
	 * @var AccessGuard
	 */
	private $guard;

	public function __construct( ModuleService $modules, AclEngine $acl ) {
		$this->modules = $modules;
		$this->guard   = new AccessGuard( $acl );
	}

	public function register() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

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

	public function get_items( $request ) {
		unset( $request );

		return rest_ensure_response( $this->modules->list_modules() );
	}
}
