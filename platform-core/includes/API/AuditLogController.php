<?php
/**
 * Audit log REST API controller.
 *
 * @package PlatformCore
 */

namespace MPP\API;

use MPP\ACL\AclEngine;
use MPP\Auth\AccessGuard;
use MPP\Services\AuditLogService;

defined( 'ABSPATH' ) || exit;

/**
 * Class AuditLogController
 */
class AuditLogController extends RestController {

	protected $rest_base = 'audit-log';

	/**
	 * @var AuditLogService
	 */
	private $audit;

	/**
	 * @var AccessGuard
	 */
	private $guard;

	public function __construct( AuditLogService $audit, AclEngine $acl ) {
		$this->audit = $audit;
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
	}

	public function get_items( $request ) {
		$limit  = min( 100, max( 1, (int) $request->get_param( 'per_page' ) ?: 50 ) );
		$offset = max( 0, (int) $request->get_param( 'offset' ) );

		$entries = $this->audit->query(
			array(
				'limit'       => $limit,
				'offset'      => $offset,
				'action'      => $request->get_param( 'action' ) ? sanitize_key( $request->get_param( 'action' ) ) : '',
				'object_type' => $request->get_param( 'object_type' ) ? sanitize_key( $request->get_param( 'object_type' ) ) : '',
				'user_id'     => (int) $request->get_param( 'user_id' ),
			)
		);

		return rest_ensure_response( $entries );
	}
}
