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
		);

		foreach ( $actions as $action ) {
			$this->assertMatchesRegularExpression( '/^[a-z0-9_.]+$/', $action );
		}
	}
}
