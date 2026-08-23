<?php
/**
 * Admin page HTML renderer (business logic + markup for theme templates).
 *
 * @package PlatformCore
 */

namespace MPP\Admin;

use MPP\ACL\PermissionRegistry;
use MPP\ACL\RoleManager;
use MPP\Services\AuditLogService;
use MPP\Services\EffectiveAccessService;
use MPP\Services\ModuleService;
use MPP\Services\PermissionService;
use MPP\Services\ScopeService;
use MPP\Services\UserService;
use MPP\Settings\PlatformSettings;

defined( 'ABSPATH' ) || exit;

/**
 * Class AdminRenderer
 */
class AdminRenderer {

	/**
	 * @var UserService
	 */
	private $users;

	/**
	 * @var RoleManager
	 */
	private $roles;

	/**
	 * @var PermissionService
	 */
	private $permissions;

	/**
	 * @var PermissionRegistry
	 */
	private $registry;

	/**
	 * @var ModuleService
	 */
	private $modules;

	/**
	 * @var ScopeService
	 */
	private $scopes;

	/**
	 * @var AuditLogService
	 */
	private $audit;

	/**
	 * @var EffectiveAccessService
	 */
	private $access;

	/**
	 * @var PlatformSettings
	 */
	private $settings;

	/**
	 * Constructor.
	 */
	public function __construct(
		UserService $users,
		RoleManager $roles,
		PermissionService $permissions,
		PermissionRegistry $registry,
		ModuleService $modules,
		ScopeService $scopes,
		AuditLogService $audit,
		EffectiveAccessService $access,
		PlatformSettings $settings
	) {
		$this->users       = $users;
		$this->roles       = $roles;
		$this->permissions = $permissions;
		$this->registry    = $registry;
		$this->modules     = $modules;
		$this->scopes      = $scopes;
		$this->audit       = $audit;
		$this->access      = $access;
		$this->settings    = $settings;
	}

	/**
	 * Render admin page by slug.
	 *
	 * @param string $page Page slug.
	 */
	public function render( $page ) {
		$this->render_notice();

		switch ( $page ) {
			case 'dashboard':
				$this->render_dashboard();
				break;
			case 'users':
				$this->render_users();
				break;
			case 'roles':
				$this->render_roles();
				break;
			case 'permissions':
				$this->render_permissions();
				break;
			case 'modules':
				$this->render_modules();
				break;
			case 'acl':
				$this->render_acl();
				break;
			case 'settings':
				$this->render_settings();
				break;
		}
	}

	/**
	 * Render admin flash notice.
	 */
	private function render_notice() {
		if ( empty( $_GET['mpp_notice'] ) ) {
			return;
		}

		$type    = sanitize_key( wp_unslash( $_GET['mpp_notice'] ) );
		$message = isset( $_GET['mpp_message'] ) ? sanitize_text_field( wp_unslash( $_GET['mpp_message'] ) ) : '';

		if ( empty( $message ) ) {
			return;
		}

		$alert_type = 'success' === $type ? 'success' : ( 'error' === $type ? 'error' : 'info' );

		printf(
			'<div class="mpp-alert mpp-alert--%s" role="alert">%s</div>',
			esc_attr( $alert_type ),
			esc_html( $message )
		);
	}

	/**
	 * Human-readable module group label.
	 *
	 * @param string $module Module slug.
	 * @return string
	 */
	private function get_module_group_label( $module ) {
		if ( 'core' === $module ) {
			return __( 'Core', 'platform-core' );
		}

		foreach ( $this->modules->list_modules() as $listed ) {
			if ( $listed['slug'] === $module ) {
				return $listed['name'];
			}
		}

		return ucfirst( $module );
	}

	/**
	 * Dashboard overview.
	 */
	private function render_dashboard() {
		$summary = mpp()->get( \MPP\Panels\DashboardService::class )->get_admin_summary();
		$user_count = $this->users->count_users();
		$audit_count = $this->audit->count();
		?>
		<div class="mpp-admin-stats">
			<div class="mpp-stat-card"><span class="mpp-stat-card__label"><?php esc_html_e( 'Platform Version', 'platform-core' ); ?></span><span class="mpp-stat-card__value"><?php echo esc_html( $summary['platform_version'] ); ?></span></div>
			<div class="mpp-stat-card"><span class="mpp-stat-card__label"><?php esc_html_e( 'WordPress Version', 'platform-core' ); ?></span><span class="mpp-stat-card__value"><?php echo esc_html( $summary['wordpress_version'] ); ?></span></div>
			<div class="mpp-stat-card"><span class="mpp-stat-card__label"><?php esc_html_e( 'Users', 'platform-core' ); ?></span><span class="mpp-stat-card__value"><?php echo esc_html( (string) $user_count ); ?></span></div>
			<div class="mpp-stat-card"><span class="mpp-stat-card__label"><?php esc_html_e( 'Platform Roles', 'platform-core' ); ?></span><span class="mpp-stat-card__value"><?php echo esc_html( (string) $summary['role_count'] ); ?></span></div>
			<div class="mpp-stat-card"><span class="mpp-stat-card__label"><?php esc_html_e( 'Modules', 'platform-core' ); ?></span><span class="mpp-stat-card__value"><?php echo esc_html( (string) $summary['module_count'] ); ?></span></div>
			<div class="mpp-stat-card"><span class="mpp-stat-card__label"><?php esc_html_e( 'Audit Entries', 'platform-core' ); ?></span><span class="mpp-stat-card__value"><?php echo esc_html( (string) $audit_count ); ?></span></div>
		</div>
		<div class="mpp-admin-links">
			<a class="mpp-btn mpp-btn--secondary" href="<?php echo esc_url( mpp_route_url( 'app/admin/users' ) ); ?>"><?php esc_html_e( 'Users', 'platform-core' ); ?></a>
			<a class="mpp-btn mpp-btn--secondary" href="<?php echo esc_url( mpp_route_url( 'app/admin/roles' ) ); ?>"><?php esc_html_e( 'Roles', 'platform-core' ); ?></a>
			<a class="mpp-btn mpp-btn--secondary" href="<?php echo esc_url( mpp_route_url( 'app/admin/permissions' ) ); ?>"><?php esc_html_e( 'Permissions', 'platform-core' ); ?></a>
			<a class="mpp-btn mpp-btn--secondary" href="<?php echo esc_url( mpp_route_url( 'app/admin/modules' ) ); ?>"><?php esc_html_e( 'Modules', 'platform-core' ); ?></a>
			<a class="mpp-btn mpp-btn--secondary" href="<?php echo esc_url( mpp_route_url( 'app/admin/acl' ) ); ?>"><?php esc_html_e( 'ACL', 'platform-core' ); ?></a>
			<a class="mpp-btn mpp-btn--secondary" href="<?php echo esc_url( mpp_route_url( 'app/admin/settings' ) ); ?>"><?php esc_html_e( 'Settings', 'platform-core' ); ?></a>
		</div>
		<?php if ( ! empty( $summary['recent_audit'] ) ) : ?>
			<h3><?php esc_html_e( 'Recent ACL Activity', 'platform-core' ); ?></h3>
			<table class="mpp-admin-table">
				<thead><tr><th><?php esc_html_e( 'Time', 'platform-core' ); ?></th><th><?php esc_html_e( 'Action', 'platform-core' ); ?></th><th><?php esc_html_e( 'Object', 'platform-core' ); ?></th></tr></thead>
				<tbody>
					<?php foreach ( $summary['recent_audit'] as $entry ) : ?>
						<tr>
							<td><?php echo esc_html( $entry['created_at'] ); ?></td>
							<td><code><?php echo esc_html( $entry['action'] ); ?></code></td>
							<td><?php echo esc_html( $entry['object_type'] . ( $entry['object_id'] ? ':' . $entry['object_id'] : '' ) ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
		<?php
	}

	/**
	 * Users list and detail.
	 */
	private function render_users() {
		$user_id = isset( $_GET['user_id'] ) ? (int) $_GET['user_id'] : 0;
		$search  = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
		$paged   = isset( $_GET['paged'] ) ? max( 1, (int) $_GET['paged'] ) : 1;
		$per_page = 20;

		if ( $user_id ) {
			$this->render_user_detail( $user_id );
			return;
		}

		$users = $this->users->list_users(
			array(
				'number' => $per_page,
				'offset' => ( $paged - 1 ) * $per_page,
				'search' => $search,
			)
		);
		$total = $this->users->count_users( array( 'search' => $search ) );

		if ( empty( $users ) ) {
			echo '<div class="mpp-empty-state"><h3 class="mpp-empty-state__title">' . esc_html__( 'No users found', 'platform-core' ) . '</h3><p>' . esc_html__( 'Try adjusting your search filters.', 'platform-core' ) . '</p></div>';
			return;
		}
		?>
		<form method="get" action="<?php echo esc_url( mpp_route_url( 'app/admin/users' ) ); ?>" class="mpp-admin-search">
			<input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Search users...', 'platform-core' ); ?>">
			<button type="submit" class="mpp-btn mpp-btn--secondary"><?php esc_html_e( 'Search', 'platform-core' ); ?></button>
		</form>
		<div class="mpp-table-wrap">
		<table class="mpp-admin-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'User', 'platform-core' ); ?></th>
					<th><?php esc_html_e( 'Username', 'platform-core' ); ?></th>
					<th><?php esc_html_e( 'Email', 'platform-core' ); ?></th>
					<th><?php esc_html_e( 'WP Role', 'platform-core' ); ?></th>
					<th><?php esc_html_e( 'Platform Roles', 'platform-core' ); ?></th>
					<th><?php esc_html_e( 'Registered', 'platform-core' ); ?></th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $users as $user ) : ?>
					<?php $wp_user = get_userdata( (int) $user['id'] ); ?>
					<tr>
						<td>
							<span class="mpp-user-cell">
								<?php echo function_exists( 'platform_ui_avatar' ) ? platform_ui_avatar( (int) $user['id'] ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								<?php echo esc_html( $user['display_name'] ); ?>
							</span>
						</td>
						<td><?php echo esc_html( $user['username'] ); ?></td>
						<td><?php echo esc_html( $user['email'] ); ?></td>
						<td><?php echo esc_html( $wp_user ? implode( ', ', (array) $wp_user->roles ) : '—' ); ?></td>
						<td><?php echo esc_html( ! empty( $user['platform_roles'] ) ? implode( ', ', wp_list_pluck( $user['platform_roles'], 'name' ) ) : '—' ); ?></td>
						<td><?php echo esc_html( $wp_user && $wp_user->user_registered ? mysql2date( get_option( 'date_format' ), $wp_user->user_registered ) : '—' ); ?></td>
						<td><a href="<?php echo esc_url( add_query_arg( 'user_id', $user['id'], mpp_route_url( 'app/admin/users' ) ) ); ?>"><?php esc_html_e( 'View', 'platform-core' ); ?></a></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		</div>
		<?php
		Pagination::render(
			$paged,
			$total,
			$per_page,
			mpp_route_url( 'app/admin/users' ),
			array( 's' => $search )
		);
	}

	/**
	 * User detail with role management.
	 *
	 * @param int $user_id User ID.
	 */
	private function render_user_detail( $user_id ) {
		$user = $this->users->get_user( $user_id );

		if ( ! $user ) {
			echo '<p>' . esc_html__( 'User not found.', 'platform-core' ) . '</p>';
			return;
		}

		$all_roles = $this->roles->all();
		$assigned  = wp_list_pluck( $user['platform_roles'], 'id' );
		$wp_user   = get_userdata( $user_id );
		$wp_roles  = $wp_user ? array_values( (array) $wp_user->roles ) : array();
		?>
		<p><a href="<?php echo esc_url( mpp_route_url( 'app/admin/users' ) ); ?>">&larr; <?php esc_html_e( 'Back to users', 'platform-core' ); ?></a></p>
		<div class="mpp-card">
			<h2><?php echo esc_html( $user['display_name'] ); ?></h2>
			<dl class="mpp-profile-list">
				<dt><?php esc_html_e( 'ID', 'platform-core' ); ?></dt><dd><?php echo esc_html( (string) $user['id'] ); ?></dd>
				<dt><?php esc_html_e( 'Username', 'platform-core' ); ?></dt><dd><?php echo esc_html( $user['username'] ); ?></dd>
				<dt><?php esc_html_e( 'Email', 'platform-core' ); ?></dt><dd><?php echo esc_html( $user['email'] ); ?></dd>
				<dt><?php esc_html_e( 'Status', 'platform-core' ); ?></dt><dd><?php echo esc_html( $user['status'] ); ?></dd>
				<dt><?php esc_html_e( 'WordPress Role', 'platform-core' ); ?></dt><dd><?php echo esc_html( ! empty( $wp_roles ) ? implode( ', ', $wp_roles ) : '—' ); ?></dd>
			</dl>
		</div>

		<h3><?php esc_html_e( 'Assigned Platform Roles', 'platform-core' ); ?></h3>
		<?php if ( empty( $user['platform_roles'] ) ) : ?>
			<p><?php esc_html_e( 'No platform roles assigned yet.', 'platform-core' ); ?></p>
		<?php endif; ?>
		<ul class="mpp-admin-list">
			<?php foreach ( $user['platform_roles'] as $role ) : ?>
				<li>
					<?php echo esc_html( $role['name'] ); ?>
					<form method="post" class="mpp-inline-form">
						<?php echo FormHandler::nonce_field(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<input type="hidden" name="mpp_admin_action" value="revoke_user_role">
						<input type="hidden" name="user_id" value="<?php echo esc_attr( (string) $user_id ); ?>">
						<input type="hidden" name="role_id" value="<?php echo esc_attr( (string) $role['id'] ); ?>">
						<input type="hidden" name="mpp_redirect" value="<?php echo esc_url( add_query_arg( 'user_id', $user_id, mpp_route_url( 'app/admin/users' ) ) ); ?>">
						<button type="submit" class="mpp-btn mpp-btn--danger mpp-btn--sm"><?php esc_html_e( 'Remove', 'platform-core' ); ?></button>
					</form>
				</li>
			<?php endforeach; ?>
		</ul>

		<h3><?php esc_html_e( 'Add Role', 'platform-core' ); ?></h3>
		<form method="post" class="mpp-form mpp-form--inline">
			<?php echo FormHandler::nonce_field(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<input type="hidden" name="mpp_admin_action" value="assign_user_role">
			<input type="hidden" name="user_id" value="<?php echo esc_attr( (string) $user_id ); ?>">
			<input type="hidden" name="mpp_redirect" value="<?php echo esc_url( add_query_arg( 'user_id', $user_id, mpp_route_url( 'app/admin/users' ) ) ); ?>">
			<select name="role_id" required>
				<option value=""><?php esc_html_e( 'Select role...', 'platform-core' ); ?></option>
				<?php foreach ( $all_roles as $role ) : ?>
					<?php if ( in_array( (int) $role['id'], $assigned, true ) ) { continue; } ?>
					<option value="<?php echo esc_attr( (string) $role['id'] ); ?>"><?php echo esc_html( $role['name'] ); ?></option>
				<?php endforeach; ?>
			</select>
			<button type="submit" class="mpp-btn mpp-btn--primary"><?php esc_html_e( 'Assign Role', 'platform-core' ); ?></button>
		</form>

		<h3><?php esc_html_e( 'Effective Access', 'platform-core' ); ?></h3>
		<p class="mpp-muted"><?php esc_html_e( 'A permission alone does not create access. Effective access is calculated from roles, permissions, and scope.', 'platform-core' ); ?></p>
		<?php
		$module_filter = isset( $_GET['access_module'] ) ? sanitize_key( wp_unslash( $_GET['access_module'] ) ) : '';
		$access_rows   = $this->access->explain_user_access( $user_id );
		$granted_count = 0;
		foreach ( $access_rows as $row ) {
			if ( ! empty( $row['granted'] ) ) {
				$granted_count++;
			}
		}
		?>
		<div class="mpp-admin-stats">
			<div class="mpp-stat-card"><span class="mpp-stat-card__label"><?php esc_html_e( 'Granted', 'platform-core' ); ?></span><span class="mpp-stat-card__value"><?php echo esc_html( (string) $granted_count ); ?></span></div>
			<div class="mpp-stat-card"><span class="mpp-stat-card__label"><?php esc_html_e( 'Total Permissions', 'platform-core' ); ?></span><span class="mpp-stat-card__value"><?php echo esc_html( (string) count( $access_rows ) ); ?></span></div>
		</div>
		<div class="mpp-table-wrap">
		<table class="mpp-admin-table mpp-admin-table--compact">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Permission', 'platform-core' ); ?></th>
					<th><?php esc_html_e( 'Module', 'platform-core' ); ?></th>
					<th><?php esc_html_e( 'Status', 'platform-core' ); ?></th>
					<th><?php esc_html_e( 'Source', 'platform-core' ); ?></th>
					<th><?php esc_html_e( 'Scope', 'platform-core' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $access_rows as $row ) : ?>
					<?php
					if ( $module_filter && $module_filter !== ( $row['module'] ?? '' ) ) {
						continue;
					}
					$source_label = '—';
					if ( ! empty( $row['sources'][0]['role_name'] ) ) {
						$source_label = $row['sources'][0]['role_name'];
					}
					$scope_label = ! empty( $row['sources'][0]['scope_label'] ) ? $row['sources'][0]['scope_label'] : '—';
					?>
					<tr>
						<td><code><?php echo esc_html( $row['permission_key'] ); ?></code></td>
						<td><?php echo esc_html( $this->get_module_group_label( $row['module'] ?? '' ) ); ?></td>
						<td><span class="mpp-badge <?php echo ! empty( $row['granted'] ) ? 'mpp-badge--success' : ''; ?>"><?php echo ! empty( $row['granted'] ) ? esc_html__( 'Granted', 'platform-core' ) : esc_html__( 'Denied', 'platform-core' ); ?></span></td>
						<td><?php echo esc_html( $source_label ); ?></td>
						<td><?php echo esc_html( $scope_label ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		</div>
		<?php
	}

	/**
	 * Roles CRUD.
	 */
	private function render_roles() {
		$edit_id = isset( $_GET['edit'] ) ? (int) $_GET['edit'] : 0;
		$create  = isset( $_GET['action'] ) && 'create' === $_GET['action'];

		if ( $create || $edit_id ) {
			$this->render_role_form( $edit_id );
			return;
		}

		$roles = $this->roles->all();

		if ( empty( $roles ) ) {
			echo '<div class="mpp-empty-state"><h3 class="mpp-empty-state__title">' . esc_html__( 'No roles found', 'platform-core' ) . '</h3><p>' . esc_html__( 'Default platform roles are created during installation.', 'platform-core' ) . '</p></div>';
			return;
		}
		?>
		<p><a class="mpp-btn mpp-btn--primary" href="<?php echo esc_url( add_query_arg( 'action', 'create', mpp_route_url( 'app/admin/roles' ) ) ); ?>"><?php esc_html_e( 'Create Role', 'platform-core' ); ?></a></p>
		<div class="mpp-table-wrap">
		<table class="mpp-admin-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Name', 'platform-core' ); ?></th>
					<th><?php esc_html_e( 'Slug', 'platform-core' ); ?></th>
					<th><?php esc_html_e( 'Permissions', 'platform-core' ); ?></th>
					<th><?php esc_html_e( 'Users', 'platform-core' ); ?></th>
					<th><?php esc_html_e( 'Status', 'platform-core' ); ?></th>
					<th><?php esc_html_e( 'System', 'platform-core' ); ?></th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $roles as $role ) : ?>
					<tr>
						<td>
							<strong><?php echo esc_html( $role['name'] ); ?></strong>
							<?php if ( ! empty( $role['description'] ) ) : ?>
								<p class="mpp-muted"><?php echo esc_html( $role['description'] ); ?></p>
							<?php endif; ?>
						</td>
						<td><code><?php echo esc_html( $role['slug'] ); ?></code></td>
						<td><?php echo esc_html( (string) count( $this->roles->get_permissions( (int) $role['id'] ) ) ); ?></td>
						<td><?php echo esc_html( (string) $this->access->count_users_with_role( (int) $role['id'] ) ); ?></td>
						<td><?php echo esc_html( $role['status'] ?? 'active' ); ?></td>
						<td><?php echo ! empty( $role['is_system'] ) ? esc_html__( 'Yes', 'platform-core' ) : esc_html__( 'No', 'platform-core' ); ?></td>
						<td>
							<a href="<?php echo esc_url( add_query_arg( 'edit', $role['id'], mpp_route_url( 'app/admin/roles' ) ) ); ?>"><?php esc_html_e( 'Edit', 'platform-core' ); ?></a>
							| <a href="<?php echo esc_url( add_query_arg( array( 'role_id' => $role['id'] ), mpp_route_url( 'app/admin/permissions' ) ) ); ?>"><?php esc_html_e( 'Permissions', 'platform-core' ); ?></a>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		</div>
		<?php
	}

	/**
	 * Role create/edit form.
	 *
	 * @param int $role_id Role ID for edit, 0 for create.
	 */
	private function render_role_form( $role_id ) {
		$role = $role_id ? $this->roles->find( $role_id ) : null;
		$is_edit = (bool) $role;
		?>
		<p><a href="<?php echo esc_url( mpp_route_url( 'app/admin/roles' ) ); ?>">&larr; <?php esc_html_e( 'Back to roles', 'platform-core' ); ?></a></p>
		<form method="post" class="mpp-form mpp-card">
			<?php echo FormHandler::nonce_field(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<input type="hidden" name="mpp_admin_action" value="<?php echo $is_edit ? 'update_role' : 'create_role'; ?>">
			<input type="hidden" name="mpp_redirect" value="<?php echo esc_url( mpp_route_url( 'app/admin/roles' ) ); ?>">
			<?php if ( $is_edit ) : ?>
				<input type="hidden" name="role_id" value="<?php echo esc_attr( (string) $role_id ); ?>">
			<?php endif; ?>

			<?php if ( ! $is_edit ) : ?>
				<label><?php esc_html_e( 'Slug', 'platform-core' ); ?></label>
				<input type="text" name="slug" required pattern="[a-z0-9_-]+">
			<?php endif; ?>

			<label><?php esc_html_e( 'Name', 'platform-core' ); ?></label>
			<input type="text" name="name" required value="<?php echo $is_edit ? esc_attr( $role['name'] ) : ''; ?>">

			<label><?php esc_html_e( 'Description', 'platform-core' ); ?></label>
			<textarea name="description" rows="3"><?php echo $is_edit ? esc_textarea( $role['description'] ) : ''; ?></textarea>

			<label><?php esc_html_e( 'Status', 'platform-core' ); ?></label>
			<select name="status">
				<option value="active" <?php selected( $is_edit ? ( $role['status'] ?? 'active' ) : 'active', 'active' ); ?>><?php esc_html_e( 'Active', 'platform-core' ); ?></option>
				<option value="inactive" <?php selected( $is_edit ? ( $role['status'] ?? 'active' ) : '', 'inactive' ); ?>><?php esc_html_e( 'Inactive', 'platform-core' ); ?></option>
			</select>

			<button type="submit" class="mpp-btn mpp-btn--primary"><?php echo $is_edit ? esc_html__( 'Update Role', 'platform-core' ) : esc_html__( 'Create Role', 'platform-core' ); ?></button>
		</form>

		<?php if ( $is_edit && empty( $role['is_system'] ) ) : ?>
			<form method="post" class="mpp-form mpp-card" style="margin-top:1rem" onsubmit="return confirm('<?php echo esc_js( __( 'Delete this role?', 'platform-core' ) ); ?>');">
				<?php echo FormHandler::nonce_field(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<input type="hidden" name="mpp_admin_action" value="delete_role">
				<input type="hidden" name="role_id" value="<?php echo esc_attr( (string) $role_id ); ?>">
				<input type="hidden" name="mpp_redirect" value="<?php echo esc_url( mpp_route_url( 'app/admin/roles' ) ); ?>">
				<button type="submit" class="mpp-btn mpp-btn--danger"><?php esc_html_e( 'Delete Role', 'platform-core' ); ?></button>
			</form>
		<?php endif; ?>
		<?php
	}

	/**
	 * Permission browser and role permission matrix.
	 */
	private function render_permissions() {
		$role_id = isset( $_GET['role_id'] ) ? (int) $_GET['role_id'] : 0;
		$query   = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';
		$module_filter = isset( $_GET['module'] ) ? sanitize_key( wp_unslash( $_GET['module'] ) ) : '';
		$tree    = $this->permissions->get_permission_tree();
		$roles   = $this->roles->all();
		$scope_types = $this->scopes->assignable();
		$stats   = $this->access->get_permission_stats();

		if ( ! $role_id && ! empty( $roles ) ) {
			$role_id = (int) $roles[0]['id'];
		}

		$assigned = array();
		if ( $role_id ) {
			foreach ( $this->roles->get_permissions( $role_id ) as $perm ) {
				$assigned[ (int) $perm['permission_id'] ] = $perm;
			}
		}
		?>
		<div class="mpp-admin-stats">
			<div class="mpp-stat-card"><span class="mpp-stat-card__label"><?php esc_html_e( 'Total Permissions', 'platform-core' ); ?></span><span class="mpp-stat-card__value"><?php echo esc_html( (string) $stats['total'] ); ?></span></div>
			<div class="mpp-stat-card"><span class="mpp-stat-card__label"><?php esc_html_e( 'Core Permissions', 'platform-core' ); ?></span><span class="mpp-stat-card__value"><?php echo esc_html( (string) $stats['core'] ); ?></span></div>
			<div class="mpp-stat-card"><span class="mpp-stat-card__label"><?php esc_html_e( 'Module Permissions', 'platform-core' ); ?></span><span class="mpp-stat-card__value"><?php echo esc_html( (string) $stats['module'] ); ?></span></div>
			<div class="mpp-stat-card"><span class="mpp-stat-card__label"><?php esc_html_e( 'Active Modules', 'platform-core' ); ?></span><span class="mpp-stat-card__value"><?php echo esc_html( (string) $stats['active_modules'] ); ?></span></div>
		</div>
		<form method="get" action="<?php echo esc_url( mpp_route_url( 'app/admin/permissions' ) ); ?>" class="mpp-admin-search">
			<label class="screen-reader-text" for="role_id"><?php esc_html_e( 'Role', 'platform-core' ); ?></label>
			<select name="role_id" id="role_id">
				<?php foreach ( $roles as $role ) : ?>
					<option value="<?php echo esc_attr( (string) $role['id'] ); ?>" <?php selected( $role_id, (int) $role['id'] ); ?>><?php echo esc_html( $role['name'] ); ?></option>
				<?php endforeach; ?>
			</select>
			<label class="screen-reader-text" for="q"><?php esc_html_e( 'Search permissions', 'platform-core' ); ?></label>
			<input type="search" name="q" id="q" value="<?php echo esc_attr( $query ); ?>" placeholder="<?php esc_attr_e( 'Search permissions...', 'platform-core' ); ?>">
			<select name="module">
				<option value=""><?php esc_html_e( 'All modules', 'platform-core' ); ?></option>
				<?php foreach ( array_keys( $tree ) as $module_slug ) : ?>
					<option value="<?php echo esc_attr( $module_slug ); ?>" <?php selected( $module_filter, $module_slug ); ?>><?php echo esc_html( $this->get_module_group_label( $module_slug ) ); ?></option>
				<?php endforeach; ?>
			</select>
			<button type="submit" class="mpp-btn mpp-btn--secondary"><?php esc_html_e( 'Filter', 'platform-core' ); ?></button>
		</form>

		<?php foreach ( $tree as $module => $resources ) : ?>
			<?php if ( $module_filter && $module_filter !== $module ) { continue; } ?>
			<div class="mpp-perm-module">
				<h3><?php echo esc_html( $this->get_module_group_label( $module ) ); ?></h3>
				<?php foreach ( $resources as $resource => $actions ) : ?>
					<div class="mpp-perm-resource">
						<h4><?php echo esc_html( ucfirst( $resource ) ); ?></h4>
						<div class="mpp-perm-cards">
							<?php foreach ( $actions as $action ) : ?>
								<?php
								if ( $query && false === stripos( $action['key'] . ' ' . $action['action'] . ' ' . ( $action['description'] ?? '' ), $query ) ) {
									continue;
								}
								$pid    = (int) $action['id'];
								$is_set = isset( $assigned[ $pid ] );
								$scope  = $is_set ? $assigned[ $pid ]['scope_type'] : 'all';
								?>
								<article class="mpp-perm-card">
									<strong><?php echo esc_html( $action['action'] ); ?></strong>
									<p class="mpp-muted"><code><?php echo esc_html( $action['key'] ); ?></code></p>
									<?php if ( ! empty( $action['description'] ) ) : ?>
										<p><?php echo esc_html( $action['description'] ); ?></p>
									<?php endif; ?>
									<?php
									$role_usage = $this->access->get_roles_using_permission( $pid );
									if ( ! empty( $role_usage ) ) :
										?>
										<p class="mpp-muted"><?php esc_html_e( 'Used by roles:', 'platform-core' ); ?> <?php echo esc_html( implode( ', ', wp_list_pluck( $role_usage, 'name' ) ) ); ?></p>
									<?php endif; ?>
									<div class="mpp-perm-card__meta">
										<span class="mpp-badge <?php echo $is_set ? 'mpp-badge--success' : ''; ?>"><?php echo $is_set ? esc_html__( 'Granted', 'platform-core' ) : esc_html__( 'Not granted', 'platform-core' ); ?></span>
										<?php $this->render_permission_actions( $role_id, $pid, $is_set, $scope, $scope_types, $assigned ); ?>
									</div>
								</article>
							<?php endforeach; ?>
						</div>
						<table class="mpp-admin-table mpp-admin-table--compact mpp-admin-table--matrix">
							<thead><tr><th><?php esc_html_e( 'Action', 'platform-core' ); ?></th><th><?php esc_html_e( 'Key', 'platform-core' ); ?></th><th><?php esc_html_e( 'Granted', 'platform-core' ); ?></th><th><?php esc_html_e( 'Scope', 'platform-core' ); ?></th><th></th></tr></thead>
							<tbody>
								<?php foreach ( $actions as $action ) : ?>
									<?php
									if ( $query && false === stripos( $action['key'] . ' ' . $action['action'] . ' ' . ( $action['description'] ?? '' ), $query ) ) {
										continue;
									}
									$pid     = (int) $action['id'];
									$is_set  = isset( $assigned[ $pid ] );
									$scope   = $is_set ? $assigned[ $pid ]['scope_type'] : 'all';
									?>
									<tr>
										<td><?php echo esc_html( $action['action'] ); ?></td>
										<td><code><?php echo esc_html( $action['key'] ); ?></code></td>
										<td><?php echo $is_set ? '&#10003;' : '&mdash;'; ?></td>
										<td><?php echo $is_set ? esc_html( $scope ) : '&mdash;'; ?></td>
										<td><?php $this->render_permission_actions( $role_id, $pid, $is_set, $scope, $scope_types, $assigned ); ?></td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endforeach; ?>
		<?php
	}

	/**
	 * Render grant/revoke actions for a permission row.
	 *
	 * @param int   $role_id      Role ID.
	 * @param int   $pid          Permission ID.
	 * @param bool  $is_set       Whether permission is granted.
	 * @param string $scope       Current scope.
	 * @param array $scope_types  Scope types.
	 * @param array $assigned      Assigned permissions.
	 */
	private function render_permission_actions( $role_id, $pid, $is_set, $scope, $scope_types, $assigned ) {
		if ( ! $role_id ) {
			return;
		}

		if ( $is_set ) {
			?>
			<form method="post" class="mpp-inline-form">
				<?php echo FormHandler::nonce_field(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<input type="hidden" name="mpp_admin_action" value="update_permission_scope">
				<input type="hidden" name="role_id" value="<?php echo esc_attr( (string) $role_id ); ?>">
				<input type="hidden" name="permission_id" value="<?php echo esc_attr( (string) $pid ); ?>">
				<input type="hidden" name="mpp_redirect" value="<?php echo esc_url( add_query_arg( 'role_id', $role_id, mpp_route_url( 'app/admin/permissions' ) ) ); ?>">
				<select name="scope_type">
					<?php foreach ( $scope_types as $st ) : ?>
						<option value="<?php echo esc_attr( $st['slug'] ); ?>" <?php selected( $scope, $st['slug'] ); ?>><?php echo esc_html( $st['name'] ); ?></option>
					<?php endforeach; ?>
				</select>
				<button type="submit" class="mpp-btn mpp-btn--sm mpp-btn--secondary"><?php esc_html_e( 'Set Scope', 'platform-core' ); ?></button>
			</form>
			<form method="post" class="mpp-inline-form">
				<?php echo FormHandler::nonce_field(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<input type="hidden" name="mpp_admin_action" value="revoke_permission">
				<input type="hidden" name="role_id" value="<?php echo esc_attr( (string) $role_id ); ?>">
				<input type="hidden" name="permission_id" value="<?php echo esc_attr( (string) $pid ); ?>">
				<input type="hidden" name="mpp_redirect" value="<?php echo esc_url( add_query_arg( 'role_id', $role_id, mpp_route_url( 'app/admin/permissions' ) ) ); ?>">
				<button type="submit" class="mpp-btn mpp-btn--sm mpp-btn--danger"><?php esc_html_e( 'Revoke', 'platform-core' ); ?></button>
			</form>
			<?php
			return;
		}
		?>
		<form method="post" class="mpp-inline-form">
			<?php echo FormHandler::nonce_field(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<input type="hidden" name="mpp_admin_action" value="grant_permission">
			<input type="hidden" name="role_id" value="<?php echo esc_attr( (string) $role_id ); ?>">
			<input type="hidden" name="permission_id" value="<?php echo esc_attr( (string) $pid ); ?>">
			<input type="hidden" name="scope_type" value="all">
			<input type="hidden" name="mpp_redirect" value="<?php echo esc_url( add_query_arg( 'role_id', $role_id, mpp_route_url( 'app/admin/permissions' ) ) ); ?>">
			<button type="submit" class="mpp-btn mpp-btn--sm mpp-btn--primary"><?php esc_html_e( 'Grant', 'platform-core' ); ?></button>
		</form>
		<?php
	}

	/**
	 * Modules listing.
	 */
	private function render_modules() {
		$modules = $this->modules->list_modules();

		if ( empty( $modules ) ) {
			echo '<div class="mpp-empty-state"><h3 class="mpp-empty-state__title">' . esc_html__( 'No modules registered', 'platform-core' ) . '</h3><p>' . esc_html__( 'Install and activate platform module plugins in WordPress to extend the platform.', 'platform-core' ) . '</p></div>';
			return;
		}
		?>
		<p class="mpp-muted"><?php esc_html_e( 'Module availability is controlled by WordPress plugin activation. Deactivating a plugin removes its runtime routes and widgets.', 'platform-core' ); ?></p>
		<div class="mpp-module-grid">
			<?php foreach ( $modules as $module ) : ?>
				<article class="mpp-card mpp-module-card">
					<h3><?php echo esc_html( $module['name'] ); ?></h3>
					<p class="mpp-muted"><code><?php echo esc_html( $module['slug'] ); ?></code> · <?php echo esc_html( $module['version'] ?? '—' ); ?></p>
					<p><?php echo esc_html( $module['description'] ?? __( 'No description provided.', 'platform-core' ) ); ?></p>
					<dl class="mpp-profile-list">
						<dt><?php esc_html_e( 'Permissions', 'platform-core' ); ?></dt><dd><?php echo esc_html( (string) ( $module['permission_count'] ?? 0 ) ); ?></dd>
						<dt><?php esc_html_e( 'Routes', 'platform-core' ); ?></dt><dd><?php echo esc_html( (string) ( $module['route_count'] ?? '—' ) ); ?></dd>
						<dt><?php esc_html_e( 'Status', 'platform-core' ); ?></dt><dd><?php echo esc_html( $module['status'] ); ?></dd>
					</dl>
					<div class="mpp-quick-actions">
						<a class="mpp-btn mpp-btn--secondary" href="<?php echo esc_url( add_query_arg( 'module', $module['slug'], mpp_route_url( 'app/admin/permissions' ) ) ); ?>"><?php esc_html_e( 'Permissions', 'platform-core' ); ?></a>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * ACL overview with audit log.
	 */
	private function render_acl() {
		$filters = array(
			'user_id'     => isset( $_GET['user_id'] ) ? (int) $_GET['user_id'] : 0,
			'action'      => isset( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : '',
			'object_type' => isset( $_GET['object_type'] ) ? sanitize_key( wp_unslash( $_GET['object_type'] ) ) : '',
			'date_from'   => isset( $_GET['date_from'] ) ? sanitize_text_field( wp_unslash( $_GET['date_from'] ) ) : '',
			'date_to'     => isset( $_GET['date_to'] ) ? sanitize_text_field( wp_unslash( $_GET['date_to'] ) ) : '',
		);
		$paged    = isset( $_GET['paged'] ) ? max( 1, (int) $_GET['paged'] ) : 1;
		$per_page = 25;
		$query    = array_merge(
			$filters,
			array(
				'limit'  => $per_page,
				'offset' => ( $paged - 1 ) * $per_page,
			)
		);
		$entries = $this->audit->query( $query );
		$total   = $this->audit->count( $filters );
		$scopes  = $this->scopes->all();
		$effective = function_exists( 'mpp' ) ? count( mpp()->acl()->get_user_permissions( get_current_user_id() ) ) : 0;
		?>
		<div class="mpp-stats mpp-admin-stats">
			<div class="mpp-stat-card"><span class="mpp-stat-card__label"><?php esc_html_e( 'Your Effective Permissions', 'platform-core' ); ?></span><span class="mpp-stat-card__value"><?php echo esc_html( (string) $effective ); ?></span></div>
			<div class="mpp-stat-card"><span class="mpp-stat-card__label"><?php esc_html_e( 'Scope Types', 'platform-core' ); ?></span><span class="mpp-stat-card__value"><?php echo esc_html( (string) count( $scopes ) ); ?></span></div>
		</div>

		<h3><?php esc_html_e( 'Scope Types', 'platform-core' ); ?></h3>
		<ul class="mpp-admin-list">
			<?php foreach ( $scopes as $scope ) : ?>
				<li><strong><?php echo esc_html( $scope['slug'] ); ?></strong> — <?php echo esc_html( $scope['name'] ); ?><?php if ( ! empty( $scope['description'] ) ) : ?> <em>(<?php echo esc_html( $scope['description'] ); ?>)</em><?php endif; ?></li>
			<?php endforeach; ?>
		</ul>

		<h3><?php esc_html_e( 'Audit Log', 'platform-core' ); ?></h3>
		<form method="get" action="<?php echo esc_url( mpp_route_url( 'app/admin/acl' ) ); ?>" class="mpp-form mpp-form--inline mpp-admin-filters">
			<label>
				<?php esc_html_e( 'User ID', 'platform-core' ); ?>
				<input type="number" name="user_id" min="0" value="<?php echo esc_attr( $filters['user_id'] ? (string) $filters['user_id'] : '' ); ?>">
			</label>
			<label>
				<?php esc_html_e( 'Action', 'platform-core' ); ?>
				<input type="text" name="action" value="<?php echo esc_attr( $filters['action'] ); ?>" placeholder="<?php esc_attr_e( 'e.g. role.created', 'platform-core' ); ?>">
			</label>
			<label>
				<?php esc_html_e( 'Object Type', 'platform-core' ); ?>
				<input type="text" name="object_type" value="<?php echo esc_attr( $filters['object_type'] ); ?>" placeholder="<?php esc_attr_e( 'e.g. role', 'platform-core' ); ?>">
			</label>
			<label>
				<?php esc_html_e( 'From', 'platform-core' ); ?>
				<input type="date" name="date_from" value="<?php echo esc_attr( $filters['date_from'] ); ?>">
			</label>
			<label>
				<?php esc_html_e( 'To', 'platform-core' ); ?>
				<input type="date" name="date_to" value="<?php echo esc_attr( $filters['date_to'] ); ?>">
			</label>
			<button type="submit" class="mpp-btn mpp-btn--secondary"><?php esc_html_e( 'Filter', 'platform-core' ); ?></button>
			<?php if ( array_filter( $filters ) ) : ?>
				<a class="mpp-btn mpp-btn--secondary" href="<?php echo esc_url( mpp_route_url( 'app/admin/acl' ) ); ?>"><?php esc_html_e( 'Clear', 'platform-core' ); ?></a>
			<?php endif; ?>
		</form>
		<table class="mpp-admin-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Time', 'platform-core' ); ?></th>
					<th><?php esc_html_e( 'User', 'platform-core' ); ?></th>
					<th><?php esc_html_e( 'Action', 'platform-core' ); ?></th>
					<th><?php esc_html_e( 'Object', 'platform-core' ); ?></th>
					<th><?php esc_html_e( 'IP', 'platform-core' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $entries ) ) : ?>
					<tr><td colspan="5"><?php esc_html_e( 'No audit entries matched your filters.', 'platform-core' ); ?></td></tr>
				<?php else : ?>
				<?php foreach ( $entries as $entry ) : ?>
					<tr>
						<td><?php echo esc_html( $entry['created_at'] ); ?></td>
						<td><?php echo esc_html( (string) $entry['user_id'] ); ?></td>
						<td><code><?php echo esc_html( $entry['action'] ); ?></code></td>
						<td><?php echo esc_html( $entry['object_type'] . ( $entry['object_id'] ? ':' . $entry['object_id'] : '' ) ); ?></td>
						<td><?php echo esc_html( $entry['ip_address'] ); ?></td>
					</tr>
				<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
		<?php
		Pagination::render(
			$paged,
			$total,
			$per_page,
			mpp_route_url( 'app/admin/acl' ),
			array_filter( $filters )
		);
	}

	/**
	 * Admin settings placeholder.
	 */
	private function render_settings() {
		$summary  = mpp()->get( \MPP\Panels\DashboardService::class )->get_admin_summary();
		$settings = $this->settings->all();
		$roles    = $this->roles->all();
		?>
		<form method="post" class="mpp-form mpp-settings-sections">
			<?php echo FormHandler::nonce_field(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<input type="hidden" name="mpp_admin_action" value="save_settings">
			<input type="hidden" name="mpp_redirect" value="<?php echo esc_url( mpp_route_url( 'app/admin/settings' ) ); ?>">

			<section class="mpp-card">
				<h2><?php esc_html_e( 'General', 'platform-core' ); ?></h2>
				<label for="platform_name"><?php esc_html_e( 'Platform Name', 'platform-core' ); ?></label>
				<input type="text" id="platform_name" name="platform_name" value="<?php echo esc_attr( $settings['general']['platform_name'] ); ?>">
				<label for="default_dashboard"><?php esc_html_e( 'Default Dashboard Route', 'platform-core' ); ?></label>
				<select id="default_dashboard" name="default_dashboard">
					<option value="app/user" <?php selected( $settings['general']['default_dashboard'], 'app/user' ); ?>><?php esc_html_e( 'User Panel', 'platform-core' ); ?></option>
					<option value="app/manager" <?php selected( $settings['general']['default_dashboard'], 'app/manager' ); ?>><?php esc_html_e( 'Manager Panel', 'platform-core' ); ?></option>
					<option value="app/admin" <?php selected( $settings['general']['default_dashboard'], 'app/admin' ); ?>><?php esc_html_e( 'Admin Panel', 'platform-core' ); ?></option>
				</select>
			</section>

			<section class="mpp-card">
				<h2><?php esc_html_e( 'Registration', 'platform-core' ); ?></h2>
				<label class="mpp-checkbox">
					<input type="checkbox" name="registration_enabled" value="1" <?php checked( $settings['registration']['enabled'] ); ?>>
					<?php esc_html_e( 'Enable public registration', 'platform-core' ); ?>
				</label>
				<label for="default_platform_role"><?php esc_html_e( 'Default Platform Role', 'platform-core' ); ?></label>
				<select id="default_platform_role" name="default_platform_role">
					<?php foreach ( $roles as $role ) : ?>
						<option value="<?php echo esc_attr( $role['slug'] ); ?>" <?php selected( $settings['registration']['default_platform_role'], $role['slug'] ); ?>><?php echo esc_html( $role['name'] ); ?></option>
					<?php endforeach; ?>
				</select>
			</section>

			<section class="mpp-card">
				<h2><?php esc_html_e( 'Security', 'platform-core' ); ?></h2>
				<label for="session_remember_days"><?php esc_html_e( 'Remember Me Duration (days)', 'platform-core' ); ?></label>
				<input type="number" id="session_remember_days" name="session_remember_days" min="1" max="365" value="<?php echo esc_attr( (string) $settings['security']['session_remember_days'] ); ?>">
				<p class="mpp-muted"><?php esc_html_e( 'WordPress administrators with manage_options receive effective platform_admin access for core permissions.', 'platform-core' ); ?></p>
			</section>

			<section class="mpp-card">
				<h2><?php esc_html_e( 'Localization', 'platform-core' ); ?></h2>
				<label for="date_format"><?php esc_html_e( 'Date Format', 'platform-core' ); ?></label>
				<input type="text" id="date_format" name="date_format" value="<?php echo esc_attr( $settings['localization']['date_format'] ); ?>">
				<dl class="mpp-profile-list">
					<dt><?php esc_html_e( 'Text Direction', 'platform-core' ); ?></dt>
					<dd><?php echo is_rtl() ? esc_html__( 'Right-to-left (RTL)', 'platform-core' ) : esc_html__( 'Left-to-right (LTR)', 'platform-core' ); ?></dd>
					<dt><?php esc_html_e( 'Locale', 'platform-core' ); ?></dt>
					<dd><?php echo esc_html( get_locale() ); ?></dd>
					<dt><?php esc_html_e( 'Routing Mode', 'platform-core' ); ?></dt>
					<dd><?php echo esc_html( $summary['permalink_mode'] ); ?></dd>
				</dl>
			</section>

			<section class="mpp-card">
				<h2><?php esc_html_e( 'System Information', 'platform-core' ); ?></h2>
				<dl class="mpp-profile-list">
					<dt><?php esc_html_e( 'Platform Core Version', 'platform-core' ); ?></dt>
					<dd><?php echo esc_html( $summary['platform_version'] ); ?></dd>
					<dt><?php esc_html_e( 'WordPress Version', 'platform-core' ); ?></dt>
					<dd><?php echo esc_html( $summary['wordpress_version'] ); ?></dd>
					<dt><?php esc_html_e( 'Database Schema', 'platform-core' ); ?></dt>
					<dd><?php echo esc_html( $summary['database_version'] ?: __( 'Not installed', 'platform-core' ) ); ?></dd>
				</dl>
			</section>

			<button type="submit" class="mpp-btn mpp-btn--primary"><?php esc_html_e( 'Save Settings', 'platform-core' ); ?></button>
		</form>
		<?php
	}
}
