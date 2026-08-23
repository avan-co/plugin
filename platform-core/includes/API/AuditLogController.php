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
		$page     = max( 1, (int) $request->get_param( 'page' ) );
		$per_page = min( 100, max( 1, (int) $request->get_param( 'per_page' ) ?: 50 ) );

		$filters = array(
			'action'      => $request->get_param( 'action' ) ? sanitize_key( $request->get_param( 'action' ) ) : '',
			'object_type' => $request->get_param( 'object_type' ) ? sanitize_key( $request->get_param( 'object_type' ) ) : '',
			'user_id'     => (int) $request->get_param( 'user_id' ),
			'date_from'   => $request->get_param( 'date_from' ) ? sanitize_text_field( $request->get_param( 'date_from' ) ) : '',
			'date_to'     => $request->get_param( 'date_to' ) ? sanitize_text_field( $request->get_param( 'date_to' ) ) : '',
		);

		$entries = $this->audit->query(
			array_merge(
				$filters,
				array(
					'limit'  => $per_page,
					'offset' => ( $page - 1 ) * $per_page,
				)
			)
		);

		$total = $this->audit->count( $filters );

		$response = rest_ensure_response( $entries );
		$response->header( 'X-WP-Total', (string) $total );
		$response->header( 'X-WP-TotalPages', (string) (int) ceil( $total / $per_page ) );

		return $response;
	}
}
