<?php
/**
 * Roles REST API controller.
 *
 * @package PlatformCore
 */

namespace MPP\API;

use MPP\ACL\AclEngine;
use MPP\ACL\RoleManager;
use MPP\Auth\AccessGuard;
use MPP\Security\Sanitizer;
use MPP\Services\AuditLogService;

defined( 'ABSPATH' ) || exit;

/**
 * Class RolesController
 */
class RolesController extends RestController {

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'roles';

	/**
	 * Role manager.
	 *
	 * @var RoleManager
	 */
	private $roles;

	/**
	 * ACL engine.
	 *
	 * @var AclEngine
	 */
	private $acl;

	/**
	 * Access guard.
	 *
	 * @var AccessGuard
	 */
	private $guard;

	/**
	 * Audit log service.
	 *
	 * @var AuditLogService
	 */
	private $audit;

	/**
	 * Constructor.
	 *
	 * @param RoleManager     $roles Role manager.
	 * @param AclEngine       $acl   ACL engine.
	 * @param AuditLogService $audit Audit log service.
	 */
	public function __construct( RoleManager $roles, AclEngine $acl, AuditLogService $audit ) {
		$this->roles = $roles;
		$this->acl   = $acl;
		$this->audit = $audit;
		$this->guard = new AccessGuard( $acl );
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
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => $this->guard->rest_permission( 'core.acl.manage' ),
				),
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_item' ),
					'permission_callback' => $this->guard->rest_permission( 'core.acl.manage' ),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => $this->guard->rest_permission( 'core.acl.manage' ),
				),
				array(
					'methods'             => \WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_item' ),
					'permission_callback' => $this->guard->rest_permission( 'core.acl.manage' ),
				),
				array(
					'methods'             => \WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_item' ),
					'permission_callback' => $this->guard->rest_permission( 'core.acl.manage' ),
				),
			)
		);
	}

	/**
	 * List roles.
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response
	 */
	public function get_items( $request ) {
		unset( $request );

		return rest_ensure_response( $this->roles->all() );
	}

	/**
	 * Get a single role.
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_item( $request ) {
		$role = $this->roles->find( (int) $request['id'] );

		if ( ! $role ) {
			return new \WP_Error( 'not_found', __( 'Role not found.', 'platform-core' ), array( 'status' => 404 ) );
		}

		$role['permissions'] = $this->roles->get_permissions( (int) $role['id'] );

		return rest_ensure_response( $role );
	}

	/**
	 * Create a role.
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function create_item( $request ) {
		$data = Sanitizer::role( $request->get_json_params() );

		if ( empty( $data['slug'] ) || empty( $data['name'] ) ) {
			return new \WP_Error( 'invalid_data', __( 'Slug and name are required.', 'platform-core' ), array( 'status' => 400 ) );
		}

		if ( $this->roles->find_by_slug( $data['slug'] ) ) {
			return new \WP_Error( 'duplicate', __( 'Role slug already exists.', 'platform-core' ), array( 'status' => 409 ) );
		}

		$id = $this->roles->create( $data['slug'], $data['name'], $data['description'] );

		if ( ! $id ) {
			return new \WP_Error( 'create_failed', __( 'Could not create role.', 'platform-core' ), array( 'status' => 500 ) );
		}

		if ( 'inactive' === $data['status'] ) {
			$this->roles->update( $id, array( 'status' => 'inactive' ) );
		}

		$role = $this->roles->find( $id );
		$this->audit->log( 'role.created', 'role', $id, array(), $role );

		return rest_ensure_response( $role );
	}

	/**
	 * Update a role.
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function update_item( $request ) {
		$id   = (int) $request['id'];
		$role = $this->roles->find( $id );

		if ( ! $role ) {
			return new \WP_Error( 'not_found', __( 'Role not found.', 'platform-core' ), array( 'status' => 404 ) );
		}

		$data = Sanitizer::role( $request->get_json_params() );
		unset( $data['slug'] );

		$this->roles->update( $id, $data );
		$after = $this->roles->find( $id );
		$this->audit->log( 'role.updated', 'role', $id, $role, $after );

		return rest_ensure_response( $after );
	}

	/**
	 * Delete a role.
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function delete_item( $request ) {
		$id = (int) $request['id'];
		$role = $this->roles->find( $id );

		if ( ! $role ) {
			return new \WP_Error( 'not_found', __( 'Role not found.', 'platform-core' ), array( 'status' => 404 ) );
		}

		if ( ! $this->roles->delete( $id ) ) {
			return new \WP_Error( 'delete_failed', __( 'Could not delete role. System roles cannot be deleted.', 'platform-core' ), array( 'status' => 400 ) );
		}

		$this->audit->log( 'role.deleted', 'role', $id, $role, array() );

		return rest_ensure_response( array( 'deleted' => true ) );
	}
}
