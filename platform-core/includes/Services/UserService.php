<?php
/**
 * WordPress user service for platform admin.
 *
 * @package PlatformCore
 */

namespace MPP\Services;

use MPP\ACL\RoleManager;

defined( 'ABSPATH' ) || exit;

/**
 * Class UserService
 */
class UserService {

	/**
	 * Role manager.
	 *
	 * @var RoleManager
	 */
	private $roles;

	/**
	 * Constructor.
	 *
	 * @param RoleManager $roles Role manager.
	 */
	public function __construct( RoleManager $roles ) {
		$this->roles = $roles;
	}

	/**
	 * List WordPress users with platform roles.
	 *
	 * @param array<string, mixed> $args WP_User_Query args.
	 * @return array<int, array<string, mixed>>
	 */
	public function list_users( array $args = array() ) {
		$defaults = array(
			'number'  => 20,
			'offset'  => 0,
			'search'  => '',
			'orderby' => 'registered',
			'order'   => 'DESC',
			'fields'  => 'all',
		);

		$parsed     = wp_parse_args( $args, $defaults );
		$query_args = $this->prepare_query_args( $parsed );
		$filter     = $this->build_status_filter( $parsed );

		if ( $filter ) {
			add_action( 'pre_user_query', $filter );
		}

		$query = new \WP_User_Query( $query_args );

		$users = array();

		foreach ( $query->get_results() as $user ) {
			$users[] = $this->format_user( $user );
		}

		if ( $filter ) {
			remove_action( 'pre_user_query', $filter );
		}

		return $users;
	}

	/**
	 * Count users matching query.
	 *
	 * @param array<string, mixed> $args Query args.
	 * @return int
	 */
	public function count_users( array $args = array() ) {
		$query_args = $this->prepare_query_args( $args );
		$filter     = $this->build_status_filter( $args );
		$query_args['number']      = 1;
		$query_args['count_total'] = true;

		if ( $filter ) {
			add_action( 'pre_user_query', $filter );
		}

		$query = new \WP_User_Query( $query_args );

		$total = (int) $query->get_total();

		if ( $filter ) {
			remove_action( 'pre_user_query', $filter );
		}

		return $total;
	}

	/**
	 * Get a single user with platform roles.
	 *
	 * @param int $user_id User ID.
	 * @return array<string, mixed>|null
	 */
	public function get_user( $user_id ) {
		$user = get_userdata( (int) $user_id );

		if ( ! $user ) {
			return null;
		}

		return $this->format_user( $user );
	}

	/**
	 * Normalize WP_User_Query arguments.
	 *
	 * @param array<string, mixed> $args Query args.
	 * @return array<string, mixed>
	 */
	private function prepare_query_args( array $args ) {
		if ( ! empty( $args['search'] ) ) {
			$args['search'] = '*' . $args['search'] . '*';
		}

		if ( ! empty( $args['platform_role_id'] ) ) {
			$user_ids = $this->get_user_ids_for_role( (int) $args['platform_role_id'] );
			unset( $args['platform_role_id'] );

			if ( empty( $user_ids ) ) {
				$args['include'] = array( 0 );
			} else {
				$args['include'] = $user_ids;
			}
		}

		unset( $args['status'] );

		return $args;
	}

	/**
	 * Build a status filter callback for WP_User_Query.
	 *
	 * @param array<string, mixed> $args Query args.
	 * @return callable|null
	 */
	private function build_status_filter( array $args ) {
		if ( empty( $args['status'] ) || ! in_array( $args['status'], array( 'active', 'inactive' ), true ) ) {
			return null;
		}

		$status = $args['status'];

		return static function ( $query ) use ( $status ) {
			global $wpdb;

			if ( 'active' === $status ) {
				$query->query_where .= " AND {$wpdb->users}.user_status = '0'";
				return;
			}

			$query->query_where .= " AND {$wpdb->users}.user_status != '0'";
		};
	}

	/**
	 * Get user IDs assigned to a platform role.
	 *
	 * @param int $role_id Role ID.
	 * @return array<int, int>
	 */
	private function get_user_ids_for_role( $role_id ) {
		global $wpdb;

		if ( $role_id <= 0 ) {
			return array();
		}

		$ids = $wpdb->get_col(
			$wpdb->prepare(
				'SELECT user_id FROM ' . \MPP\Database\Schema::table( 'user_roles' ) . ' WHERE role_id = %d',
				$role_id
			)
		);

		return array_map( 'intval', $ids );
	}

	/**
	 * Format a WP_User for API/UI output.
	 *
	 * @param \WP_User $user WordPress user.
	 * @return array<string, mixed>
	 */
	public function format_user( $user ) {
		return array(
			'id'              => (int) $user->ID,
			'username'        => $user->user_login,
			'display_name'    => $user->display_name,
			'email'           => $user->user_email,
			'status'          => $user->user_status ? 'inactive' : 'active',
			'registered'      => $user->user_registered,
			'platform_roles'  => $this->roles->get_user_roles( (int) $user->ID ),
		);
	}
}
