<?php
/**
 * Manager module plugin wiring tests.
 *
 * @package PlatformCore
 */

namespace MPP\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Class ManagerModulesTest
 */
class ManagerModulesTest extends TestCase {

	/**
	 * Tasks module registers manager routes and store.
	 */
	public function test_tasks_module_structure(): void {
		$root = dirname( __DIR__, 3 );

		$this->assertFileExists( $root . '/platform-tasks/platform-tasks.php' );
		$this->assertFileExists( $root . '/platform-tasks/includes/TaskStore.php' );
		$this->assertFileExists( $root . '/platform-tasks/includes/TasksModule.php' );
		$this->assertFileExists( $root . '/platform-tasks/templates/tasks.php' );

		$source = file_get_contents( $root . '/platform-tasks/includes/TasksModule.php' );
		$this->assertStringContainsString( "'panel'       => 'manager'", $source );
		$this->assertStringContainsString( 'mpp_manager_pending_items', $source );
	}

	/**
	 * Team module provides member store and manager navigation.
	 */
	public function test_team_module_structure(): void {
		$root = dirname( __DIR__, 3 );

		$this->assertFileExists( $root . '/platform-team/platform-team.php' );
		$this->assertFileExists( $root . '/platform-team/includes/TeamStore.php' );
		$this->assertStringContainsString(
			"'panel'       => 'manager'",
			file_get_contents( $root . '/platform-team/includes/TeamModule.php' )
		);
	}

	/**
	 * Reports module aggregates task and team metrics.
	 */
	public function test_reports_module_structure(): void {
		$root = dirname( __DIR__, 3 );

		$this->assertFileExists( $root . '/platform-reports/platform-reports.php' );
		$this->assertStringContainsString(
			'TaskStore',
			file_get_contents( $root . '/platform-reports/includes/ReportService.php' )
		);
		$this->assertStringContainsString(
			'TeamStore',
			file_get_contents( $root . '/platform-reports/includes/ReportService.php' )
		);
	}

	/**
	 * fa_IR catalogs include Persian translations for common UI strings.
	 */
	public function test_fa_ir_catalogs_complete(): void {
		$root = dirname( __DIR__, 3 );

		$theme_po = file_get_contents( $root . '/platform-theme/languages/platform-theme-fa_IR.po' );
		$core_po  = file_get_contents( $root . '/platform-core/languages/platform-core-fa_IR.po' );

		$this->assertStringContainsString( 'msgstr "داشبورد"', $theme_po );
		$this->assertStringContainsString( 'msgstr "کاربران"', $core_po );
		$this->assertFileExists( $root . '/scripts/complete-fa_IR_translations.py' );
	}
}
