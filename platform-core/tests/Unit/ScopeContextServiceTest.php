<?php

namespace MPP\Tests\Unit;

use Brain\Monkey\Functions;
use MPP\ACL\ScopeContextService;
use PHPUnit\Framework\TestCase;

class ScopeContextServiceTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		\Brain\Monkey\setUp();
	}

	protected function tearDown(): void {
		\Brain\Monkey\tearDown();
		parent::tearDown();
	}

	public function test_for_user_returns_default_context_keys(): void {
		Functions\when( 'get_user_meta' )->alias(
			function ( $user_id, $key, $single ) {
				unset( $user_id, $key, $single );
				return '';
			}
		);

		$service = new ScopeContextService();
		$context = $service->for_user( 42 );

		$this->assertSame( 42, $context['owner_id'] );
		$this->assertSame( 0, $context['user_department_id'] );
		$this->assertSame( array(), $context['user_team_ids'] );
		$this->assertSame( array(), $context['user_project_ids'] );
	}

	public function test_for_user_parses_team_ids_from_csv_meta(): void {
		Functions\when( 'get_user_meta' )->alias(
			function ( $user_id, $key, $single ) {
				unset( $user_id, $single );

				if ( 'mpp_team_ids' === $key ) {
					return '1, 2, 3';
				}

				return '';
			}
		);

		$service = new ScopeContextService();
		$context = $service->for_user( 1 );

		$this->assertSame( array( 1, 2, 3 ), $context['user_team_ids'] );
	}
}
