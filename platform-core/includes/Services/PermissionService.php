<?php
/**
 * Permission service.
 *
 * @package PlatformCore
 */

namespace MPP\Services;

use MPP\ACL\PermissionRegistry;

defined( 'ABSPATH' ) || exit;

/**
 * Class PermissionService
 */
class PermissionService {

	/**
	 * Permission registry.
	 *
	 * @var PermissionRegistry
	 */
	private $registry;

	/**
	 * Constructor.
	 *
	 * @param PermissionRegistry $registry Permission registry.
	 */
	public function __construct( PermissionRegistry $registry ) {
		$this->registry = $registry;
	}

	/**
	 * Get permissions grouped for UI tree display.
	 *
	 * @return array<string, array<string, array<int, array<string, mixed>>>>
	 */
	public function get_permission_tree() {
		return $this->registry->get_grouped();
	}
}
