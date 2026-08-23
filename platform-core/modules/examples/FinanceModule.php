<?php
/**
 * Example module demonstrating permission registration.
 *
 * @package PlatformCore
 */

namespace MPP\Modules\Examples;

use MPP\ACL\PermissionRegistry;
use MPP\Modules\AbstractModule;

defined( 'ABSPATH' ) || exit;

/**
 * Class FinanceModule
 *
 * Example module — register via mpp_register_modules hook in production.
 */
class FinanceModule extends AbstractModule {

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
	 * @inheritDoc
	 */
	public function get_slug() {
		return 'finance';
	}

	/**
	 * @inheritDoc
	 */
	public function get_name() {
		return __( 'Finance', 'platform-core' );
	}

	/**
	 * @inheritDoc
	 */
	public function register_permissions() {
		$this->registry->register_module(
			'finance',
			array(
				'invoice' => array(
					'view'    => __( 'View invoices', 'platform-core' ),
					'create'  => __( 'Create invoices', 'platform-core' ),
					'edit'    => __( 'Edit invoices', 'platform-core' ),
					'delete'  => __( 'Delete invoices', 'platform-core' ),
					'approve' => __( 'Approve invoices', 'platform-core' ),
				),
				'payment' => array(
					'view'   => __( 'View payments', 'platform-core' ),
					'create' => __( 'Create payments', 'platform-core' ),
					'delete' => __( 'Delete payments', 'platform-core' ),
				),
			)
		);
	}

	/**
	 * @inheritDoc
	 */
	public function boot() {
		// Future: register routes, REST endpoints, etc.
	}
}
