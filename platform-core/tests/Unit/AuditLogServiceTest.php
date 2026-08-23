<?php

namespace MPP\Tests\Unit;

use MPP\Services\AuditLogService;
use PHPUnit\Framework\TestCase;

class AuditLogServiceTest extends TestCase {

	public function test_log_action_constants_are_generic(): void {
		$actions = array(
			'role.created',
			'role.updated',
			'role.deleted',
			'user.role.assigned',
			'user.role.revoked',
			'permission.granted',
			'permission.revoked',
			'scope.changed',
			'profile.updated',
			'settings.updated',
		);

		foreach ( $actions as $action ) {
			$this->assertMatchesRegularExpression( '/^[a-z0-9_.]+$/', $action );
		}
	}

	public function test_normalize_args_includes_date_filters(): void {
		$service = new AuditLogService();
		$method  = new \ReflectionMethod( AuditLogService::class, 'normalize_args' );
		$method->setAccessible( true );

		$args = $method->invoke( $service, array( 'date_from' => '2026-01-01', 'date_to' => '2026-01-31' ) );

		$this->assertSame( '2026-01-01', $args['date_from'] );
		$this->assertSame( '2026-01-31', $args['date_to'] );
	}
}
