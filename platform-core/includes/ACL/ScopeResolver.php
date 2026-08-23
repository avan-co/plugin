<?php
/**
 * Access scope resolution.
 *
 * @package PlatformCore
 */

namespace MPP\ACL;

defined( 'ABSPATH' ) || exit;

/**
 * Class ScopeResolver
 */
class ScopeResolver {

	/**
	 * Built-in scope types.
	 *
	 * @var array<string, string>
	 */
	private $builtin_scopes = array(
		'all'          => 'All resources',
		'own'          => 'Own resources only',
		'department'   => 'Department scope',
		'team'         => 'Team scope',
		'project'      => 'Project scope',
		'organization' => 'Organization scope',
		'custom'       => 'Custom scope',
	);

	/**
	 * Custom scope handlers.
	 *
	 * @var array<string, callable>
	 */
	private $handlers = array();

	/**
	 * Constructor.
	 */
	public function __construct() {
		foreach ( array_keys( $this->builtin_scopes ) as $scope ) {
			$this->handlers[ $scope ] = array( $this, 'resolve_' . $scope );
		}
	}

	/**
	 * Register a custom scope handler.
	 *
	 * @param string   $scope_type Scope type slug.
	 * @param callable $handler    Handler callback.
	 */
	public function register_handler( $scope_type, callable $handler ) {
		$this->handlers[ sanitize_key( $scope_type ) ] = $handler;
	}

	/**
	 * Get available scope types.
	 *
	 * @return array<string, string>
	 */
	public function get_scope_types() {
		return apply_filters( 'mpp_scope_types', $this->builtin_scopes );
	}

	/**
	 * Check if a scope allows access.
	 *
	 * @param string               $scope_type  Scope type.
	 * @param mixed                $scope_value Scope configuration value.
	 * @param int                  $user_id     User ID.
	 * @param array<string, mixed> $context     Access context.
	 * @return bool
	 */
	public function allows( $scope_type, $scope_value, $user_id, array $context = array() ) {
		$scope_type = sanitize_key( $scope_type );

		if ( isset( $this->handlers[ $scope_type ] ) ) {
			return (bool) call_user_func( $this->handlers[ $scope_type ], $scope_value, $user_id, $context );
		}

		/**
		 * Filter scope resolution for unknown scope types.
		 *
		 * @param bool                 $allowed     Whether access is allowed.
		 * @param string               $scope_type  Scope type.
		 * @param mixed                $scope_value Scope value.
		 * @param int                  $user_id     User ID.
		 * @param array<string, mixed> $context     Context.
		 */
		return (bool) apply_filters( 'mpp_resolve_scope', false, $scope_type, $scope_value, $user_id, $context );
	}

	/**
	 * Resolve "all" scope — always allows.
	 *
	 * @param mixed                $scope_value Scope value.
	 * @param int                  $user_id     User ID.
	 * @param array<string, mixed> $context     Context.
	 * @return bool
	 */
	public function resolve_all( $scope_value, $user_id, array $context = array() ) {
		return true;
	}

	/**
	 * Resolve "own" scope — resource must belong to the user.
	 *
	 * @param mixed                $scope_value Scope value.
	 * @param int                  $user_id     User ID.
	 * @param array<string, mixed> $context     Context.
	 * @return bool
	 */
	public function resolve_own( $scope_value, $user_id, array $context = array() ) {
		if ( empty( $context['owner_id'] ) ) {
			return false;
		}

		return (int) $context['owner_id'] === (int) $user_id;
	}

	/**
	 * Resolve "department" scope — placeholder for future implementation.
	 *
	 * @param mixed                $scope_value Scope value.
	 * @param int                  $user_id     User ID.
	 * @param array<string, mixed> $context     Context.
	 * @return bool
	 */
	public function resolve_department( $scope_value, $user_id, array $context = array() ) {
		if ( empty( $context['department_id'] ) || empty( $context['user_department_id'] ) ) {
			return false;
		}

		return (int) $context['department_id'] === (int) $context['user_department_id'];
	}

	/**
	 * Resolve "team" scope — placeholder for future implementation.
	 *
	 * @param mixed                $scope_value Scope value.
	 * @param int                  $user_id     User ID.
	 * @param array<string, mixed> $context     Context.
	 * @return bool
	 */
	public function resolve_team( $scope_value, $user_id, array $context = array() ) {
		if ( empty( $context['team_id'] ) || empty( $context['user_team_ids'] ) ) {
			return false;
		}

		$team_ids = (array) $context['user_team_ids'];
		return in_array( (int) $context['team_id'], array_map( 'intval', $team_ids ), true );
	}

	/**
	 * Resolve "project" scope — placeholder for future implementation.
	 *
	 * @param mixed                $scope_value Scope value.
	 * @param int                  $user_id     User ID.
	 * @param array<string, mixed> $context     Context.
	 * @return bool
	 */
	public function resolve_project( $scope_value, $user_id, array $context = array() ) {
		if ( empty( $context['project_id'] ) || empty( $context['user_project_ids'] ) ) {
			return false;
		}

		$project_ids = (array) $context['user_project_ids'];
		return in_array( (int) $context['project_id'], array_map( 'intval', $project_ids ), true );
	}

	/**
	 * Resolve "organization" scope — placeholder for future implementation.
	 *
	 * @param mixed                $scope_value Scope value.
	 * @param int                  $user_id     User ID.
	 * @param array<string, mixed> $context     Context.
	 * @return bool
	 */
	public function resolve_organization( $scope_value, $user_id, array $context = array() ) {
		if ( empty( $context['organization_id'] ) || empty( $context['user_organization_id'] ) ) {
			return false;
		}

		return (int) $context['organization_id'] === (int) $context['user_organization_id'];
	}

	/**
	 * Resolve "custom" scope — delegates to registered handler or filter.
	 *
	 * @param mixed                $scope_value Scope value.
	 * @param int                  $user_id     User ID.
	 * @param array<string, mixed> $context     Context.
	 * @return bool
	 */
	public function resolve_custom( $scope_value, $user_id, array $context = array() ) {
		/**
		 * Filter custom scope resolution.
		 *
		 * @param bool                 $allowed     Whether access is allowed.
		 * @param mixed                $scope_value Scope configuration.
		 * @param int                  $user_id     User ID.
		 * @param array<string, mixed> $context     Context.
		 */
		return (bool) apply_filters( 'mpp_resolve_custom_scope', false, $scope_value, $user_id, $context );
	}
}
