<?php
/**
 * Users REST API controller.
 *
 * @package PlatformCore
 */

namespace MPP\API;

use MPP\ACL\AclEngine;
use MPP\Auth\AccessGuard;
use MPP\Services\UserService;

defined( 'ABSPATH' ) || exit;

/**
 * Class UsersController
 */
class UsersController extends RestController {

	protected $rest_base = 'users';

	/**
	 * @var UserService
	 */
	private $users;

	/**
	 * @var AccessGuard
	 */
	private $guard;

	public function __construct( UserService $users, AclEngine $acl ) {
		$this->users = $users;
		$this->guard = new AccessGuard( $acl );
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

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_item' ),
				'permission_callback' => $this->guard->rest_permission( 'core.acl.manage' ),
			)
		);
	}

	public function get_items( $request ) {
		$search   = $request->get_param( 'search' );
		$page     = max( 1, (int) $request->get_param( 'page' ) );
		$per_page = min( 100, max( 1, (int) $request->get_param( 'per_page' ) ?: 20 ) );
		$search   = $search ? sanitize_text_field( $search ) : '';

		$users = $this->users->list_users(
			array(
				'number' => $per_page,
				'offset' => ( $page - 1 ) * $per_page,
				'search' => $search,
			)
		);

		$total = $this->users->count_users( array( 'search' => $search ) );

		$response = rest_ensure_response( $users );
		$response->header( 'X-WP-Total', (string) $total );
		$response->header( 'X-WP-TotalPages', (string) (int) ceil( $total / $per_page ) );

		return $response;
	}

	public function get_item( $request ) {
		$user = $this->users->get_user( (int) $request['id'] );

		if ( ! $user ) {
			return new \WP_Error( 'not_found', __( 'User not found.', 'platform-core' ), array( 'status' => 404 ) );
		}

		return rest_ensure_response( $user );
	}
}
