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

		$query_args = $this->prepare_query_args( wp_parse_args( $args, $defaults ) );
		$query = new \WP_User_Query( $query_args );
		$users = array();

		foreach ( $query->get_results() as $user ) {
			$users[] = $this->format_user( $user );
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
		$query_args['number']      = 1;
		$query_args['count_total'] = true;
		$query = new \WP_User_Query( $query_args );
		return (int) $query->get_total();
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

		return $args;
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
