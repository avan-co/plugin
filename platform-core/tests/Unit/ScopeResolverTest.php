<?php

namespace MPP\Tests\Unit;

use MPP\ACL\ScopeResolver;
use PHPUnit\Framework\TestCase;

class ScopeResolverTest extends TestCase {

	private ScopeResolver $resolver;

	protected function setUp(): void {
		parent::setUp();
		$this->resolver = new ScopeResolver();
	}

	public function test_all_scope_always_allows(): void {
		$this->assertTrue( $this->resolver->allows( 'all', null, 1 ) );
	}

	public function test_own_scope_requires_owner_match(): void {
		$this->assertFalse( $this->resolver->allows( 'own', null, 5, array() ) );
		$this->assertTrue( $this->resolver->allows( 'own', null, 5, array( 'owner_id' => 5 ) ) );
		$this->assertFalse( $this->resolver->allows( 'own', null, 5, array( 'owner_id' => 9 ) ) );
	}

	public function test_department_scope_requires_matching_department(): void {
		$context = array(
			'department_id'       => 3,
			'user_department_id'  => 3,
		);

		$this->assertTrue( $this->resolver->allows( 'department', null, 1, $context ) );
		$this->assertFalse( $this->resolver->allows( 'department', null, 1, array( 'department_id' => 3, 'user_department_id' => 4 ) ) );
	}

	public function test_unknown_scope_defaults_to_deny(): void {
		$this->assertFalse( $this->resolver->allows( 'nonexistent', null, 1 ) );
	}
}
