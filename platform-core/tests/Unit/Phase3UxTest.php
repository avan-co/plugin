<?php
/**
 * Phase 3 admin ACL UX tests.
 *
 * @package PlatformCore
 */

namespace MPP\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Class Phase3UxTest
 */
class Phase3UxTest extends TestCase {

	/**
	 * Audit label service maps internal actions to readable labels.
	 */
	public function test_audit_label_service_exists(): void {
		$path = dirname( __DIR__, 3 ) . '/platform-core/includes/Services/AuditLabelService.php';

		$this->assertFileExists( $path );
		$source = file_get_contents( $path );
		$this->assertStringContainsString( 'role.permissions.saved', $source );
		$this->assertStringContainsString( 'get_action_label', $source );
	}

	/**
	 * Admin actions script handles confirmations and notice cleanup.
	 */
	public function test_admin_actions_script(): void {
		$path = dirname( __DIR__, 3 ) . '/platform-theme/assets/js/admin-actions.js';

		$this->assertFileExists( $path );
		$source = file_get_contents( $path );
		$this->assertStringContainsString( 'data-mpp-confirm', $source );
		$this->assertStringContainsString( 'mpp_notice', $source );
		$this->assertStringContainsString( 'data-initial-granted', $source );
	}

	/**
	 * Scope service limits assignable scopes to implemented types.
	 */
	public function test_scope_availability_defaults(): void {
		$path = dirname( __DIR__, 3 ) . '/platform-core/includes/Services/ScopeService.php';
		$source = file_get_contents( $path );

		$this->assertStringContainsString( 'get_scope_availability', $source );
		$this->assertStringContainsString( "'department'   => false", $source );
		$this->assertStringContainsString( "'own'          => true", $source );
	}

	/**
	 * Form handler preserves redirect URL and logs permission saves.
	 */
	public function test_form_handler_redirect_and_audit(): void {
		$path = dirname( __DIR__, 3 ) . '/platform-core/includes/Admin/FormHandler.php';
		$source = file_get_contents( $path );

		$this->assertStringContainsString( 'redirect_field', $source );
		$this->assertStringContainsString( 'role.permissions.saved', $source );
	}

	/**
	 * Admin renderer wires audit labels and confirm attributes.
	 */
	public function test_admin_renderer_phase3_wiring(): void {
		$path = dirname( __DIR__, 3 ) . '/platform-core/includes/Admin/AdminRenderer.php';
		$source = file_get_contents( $path );

		$this->assertStringContainsString( 'AuditLabelService', $source );
		$this->assertStringContainsString( 'data-mpp-confirm', $source );
		$this->assertStringContainsString( 'format_audit_display', $source );
	}

	/**
	 * Theme enqueues admin-actions on admin routes.
	 */
	public function test_admin_actions_enqueued(): void {
		$functions = file_get_contents( dirname( __DIR__, 3 ) . '/platform-theme/functions.php' );

		$this->assertStringContainsString( 'admin-actions.js', $functions );
		$this->assertStringContainsString( 'mppAdminActions', $functions );
	}
}
