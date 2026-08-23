<?php

namespace MPP\Tests\Unit;

use MPP\Admin\Pagination;
use PHPUnit\Framework\TestCase;

class PaginationTest extends TestCase {

	public function test_renders_nothing_for_single_page(): void {
		ob_start();
		Pagination::render( 1, 10, 20, 'https://example.test/users' );
		$output = ob_get_clean();

		$this->assertSame( '', $output );
	}

	public function test_renders_next_link_when_more_pages_exist(): void {
		ob_start();
		Pagination::render( 1, 50, 20, 'https://example.test/users', array( 's' => 'test' ) );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'mpp-pagination', $output );
		$this->assertStringContainsString( 'Page 1 of 3', $output );
		$this->assertStringContainsString( 'paged=2', $output );
		$this->assertStringContainsString( 's=test', $output );
	}
}
