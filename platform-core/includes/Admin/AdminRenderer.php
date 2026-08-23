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
use MPP\Services\ModuleService;
use MPP\Services\PermissionService;
use MPP\Services\ScopeService;
use MPP\Services\UserService;

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
	 * Constructor.
	 */
	public function __construct(
		UserService $users,
		RoleManager $roles,
		PermissionService $permissions,
		PermissionRegistry $registry,
		ModuleService $modules,
		ScopeService $scopes,
		AuditLogService $audit
	) {
		$this->users       = $users;
		$this->roles       = $roles;
		$this->permissions = $permissions;
		$this->registry    = $registry;
		$this->modules     = $modules;
		$this->scopes      = $scopes;
		$this->audit       = $audit;
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

		printf(
			'<div class="mpp-alert mpp-alert--%s" role="alert">%s</div>',
			esc_attr( 'success' === $type ? 'success' : 'error' ),
			esc_html( $message )
		);
	}

	/**
	 * Dashboard overview.
	 */
	private function render_dashboard() {
		$role_count = count( $this->roles->all() );
		$user_count = $this->users->count_users();
		$module_count = count( $this->modules->list_modules() );
		$audit_count = $this->audit->count();
		?>
		<div class="mpp-admin-stats">
			<div class="mpp-stat-card"><span class="mpp-stat-card__label"><?php esc_html_e( 'Users', 'platform-core' ); ?></span><span class="mpp-stat-card__value"><?php echo esc_html( (string) $user_count ); ?></span></div>
			<div class="mpp-stat-card"><span class="mpp-stat-card__label"><?php esc_html_e( 'Roles', 'platform-core' ); ?></span><span class="mpp-stat-card__value"><?php echo esc_html( (string) $role_count ); ?></span></div>
			<div class="mpp-stat-card"><span class="mpp-stat-card__label"><?php esc_html_e( 'Modules', 'platform-core' ); ?></span><span class="mpp-stat-card__value"><?php echo esc_html( (string) $module_count ); ?></span></div>
			<div class="mpp-stat-card"><span class="mpp-stat-card__label"><?php esc_html_e( 'Audit Entries', 'platform-core' ); ?></span><span class="mpp-stat-card__value"><?php echo esc_html( (string) $audit_count ); ?></span></div>
		</div>
		<div class="mpp-admin-links">
			<a class="mpp-btn mpp-btn--secondary" href="<?php echo esc_url( home_url( '/app/admin/users' ) ); ?>"><?php esc_html_e( 'Manage Users', 'platform-core' ); ?></a>
			<a class="mpp-btn mpp-btn--secondary" href="<?php echo esc_url( home_url( '/app/admin/roles' ) ); ?>"><?php esc_html_e( 'Manage Roles', 'platform-core' ); ?></a>
			<a class="mpp-btn mpp-btn--secondary" href="<?php echo esc_url( home_url( '/app/admin/acl' ) ); ?>"><?php esc_html_e( 'ACL Overview', 'platform-core' ); ?></a>
		</div>
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
		?>
		<form method="get" action="<?php echo esc_url( home_url( '/app/admin/users' ) ); ?>" class="mpp-admin-search">
			<input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Search users...', 'platform-core' ); ?>">
			<button type="submit" class="mpp-btn mpp-btn--secondary"><?php esc_html_e( 'Search', 'platform-core' ); ?></button>
		</form>
		<table class="mpp-admin-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'ID', 'platform-core' ); ?></th>
					<th><?php esc_html_e( 'Username', 'platform-core' ); ?></th>
					<th><?php esc_html_e( 'Display Name', 'platform-core' ); ?></th>
					<th><?php esc_html_e( 'Email', 'platform-core' ); ?></th>
					<th><?php esc_html_e( 'Status', 'platform-core' ); ?></th>
					<th><?php esc_html_e( 'Platform Roles', 'platform-core' ); ?></th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $users as $user ) : ?>
					<tr>
						<td><?php echo esc_html( (string) $user['id'] ); ?></td>
						<td><?php echo esc_html( $user['username'] ); ?></td>
						<td><?php echo esc_html( $user['display_name'] ); ?></td>
						<td><?php echo esc_html( $user['email'] ); ?></td>
						<td><span class="mpp-badge"><?php echo esc_html( $user['status'] ); ?></span></td>
						<td><?php echo esc_html( implode( ', ', wp_list_pluck( $user['platform_roles'], 'name' ) ) ); ?></td>
						<td><a href="<?php echo esc_url( add_query_arg( 'user_id', $user['id'], home_url( '/app/admin/users' ) ) ); ?>"><?php esc_html_e( 'View', 'platform-core' ); ?></a></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php
		Pagination::render(
			$paged,
			$total,
			$per_page,
			home_url( '/app/admin/users' ),
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
		?>
		<p><a href="<?php echo esc_url( home_url( '/app/admin/users' ) ); ?>">&larr; <?php esc_html_e( 'Back to users', 'platform-core' ); ?></a></p>
		<div class="mpp-card">
			<h2><?php echo esc_html( $user['display_name'] ); ?></h2>
			<dl class="mpp-profile-list">
				<dt><?php esc_html_e( 'ID', 'platform-core' ); ?></dt><dd><?php echo esc_html( (string) $user['id'] ); ?></dd>
				<dt><?php esc_html_e( 'Username', 'platform-core' ); ?></dt><dd><?php echo esc_html( $user['username'] ); ?></dd>
				<dt><?php esc_html_e( 'Email', 'platform-core' ); ?></dt><dd><?php echo esc_html( $user['email'] ); ?></dd>
				<dt><?php esc_html_e( 'Status', 'platform-core' ); ?></dt><dd><?php echo esc_html( $user['status'] ); ?></dd>
			</dl>
		</div>

		<h3><?php esc_html_e( 'Assigned Platform Roles', 'platform-core' ); ?></h3>
		<ul class="mpp-admin-list">
			<?php foreach ( $user['platform_roles'] as $role ) : ?>
				<li>
					<?php echo esc_html( $role['name'] ); ?>
					<form method="post" class="mpp-inline-form">
						<?php echo FormHandler::nonce_field(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<input type="hidden" name="mpp_admin_action" value="revoke_user_role">
						<input type="hidden" name="user_id" value="<?php echo esc_attr( (string) $user_id ); ?>">
						<input type="hidden" name="role_id" value="<?php echo esc_attr( (string) $role['id'] ); ?>">
						<input type="hidden" name="mpp_redirect" value="<?php echo esc_url( add_query_arg( 'user_id', $user_id, home_url( '/app/admin/users' ) ) ); ?>">
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
			<input type="hidden" name="mpp_redirect" value="<?php echo esc_url( add_query_arg( 'user_id', $user_id, home_url( '/app/admin/users' ) ) ); ?>">
			<select name="role_id" required>
				<option value=""><?php esc_html_e( 'Select role...', 'platform-core' ); ?></option>
				<?php foreach ( $all_roles as $role ) : ?>
					<?php if ( in_array( (int) $role['id'], $assigned, true ) ) { continue; } ?>
					<option value="<?php echo esc_attr( (string) $role['id'] ); ?>"><?php echo esc_html( $role['name'] ); ?></option>
				<?php endforeach; ?>
			</select>
			<button type="submit" class="mpp-btn mpp-btn--primary"><?php esc_html_e( 'Assign Role', 'platform-core' ); ?></button>
		</form>
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
		?>
		<p><a class="mpp-btn mpp-btn--primary" href="<?php echo esc_url( add_query_arg( 'action', 'create', home_url( '/app/admin/roles' ) ) ); ?>"><?php esc_html_e( 'Create Role', 'platform-core' ); ?></a></p>
		<table class="mpp-admin-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Name', 'platform-core' ); ?></th>
					<th><?php esc_html_e( 'Slug', 'platform-core' ); ?></th>
					<th><?php esc_html_e( 'Status', 'platform-core' ); ?></th>
					<th><?php esc_html_e( 'System', 'platform-core' ); ?></th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $roles as $role ) : ?>
					<tr>
						<td><?php echo esc_html( $role['name'] ); ?></td>
						<td><code><?php echo esc_html( $role['slug'] ); ?></code></td>
						<td><?php echo esc_html( $role['status'] ?? 'active' ); ?></td>
						<td><?php echo ! empty( $role['is_system'] ) ? esc_html__( 'Yes', 'platform-core' ) : esc_html__( 'No', 'platform-core' ); ?></td>
						<td>
							<a href="<?php echo esc_url( add_query_arg( 'edit', $role['id'], home_url( '/app/admin/roles' ) ) ); ?>"><?php esc_html_e( 'Edit', 'platform-core' ); ?></a>
							| <a href="<?php echo esc_url( add_query_arg( array( 'role_id' => $role['id'] ), home_url( '/app/admin/permissions' ) ) ); ?>"><?php esc_html_e( 'Permissions', 'platform-core' ); ?></a>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
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
		<p><a href="<?php echo esc_url( home_url( '/app/admin/roles' ) ); ?>">&larr; <?php esc_html_e( 'Back to roles', 'platform-core' ); ?></a></p>
		<form method="post" class="mpp-form mpp-card">
			<?php echo FormHandler::nonce_field(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<input type="hidden" name="mpp_admin_action" value="<?php echo $is_edit ? 'update_role' : 'create_role'; ?>">
			<input type="hidden" name="mpp_redirect" value="<?php echo esc_url( home_url( '/app/admin/roles' ) ); ?>">
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
				<input type="hidden" name="mpp_redirect" value="<?php echo esc_url( home_url( '/app/admin/roles' ) ); ?>">
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
		$tree    = $this->permissions->get_permission_tree();
		$roles   = $this->roles->all();
		$scope_types = $this->scopes->all();

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
		<form method="get" action="<?php echo esc_url( home_url( '/app/admin/permissions' ) ); ?>" class="mpp-form mpp-form--inline">
			<label for="role_id"><?php esc_html_e( 'Manage permissions for role:', 'platform-core' ); ?></label>
			<select name="role_id" id="role_id" onchange="this.form.submit()">
				<?php foreach ( $roles as $role ) : ?>
					<option value="<?php echo esc_attr( (string) $role['id'] ); ?>" <?php selected( $role_id, (int) $role['id'] ); ?>><?php echo esc_html( $role['name'] ); ?></option>
				<?php endforeach; ?>
			</select>
		</form>

		<?php foreach ( $tree as $module => $resources ) : ?>
			<div class="mpp-perm-module">
				<h3><?php echo esc_html( ucfirst( $module ) ); ?></h3>
				<?php foreach ( $resources as $resource => $actions ) : ?>
					<div class="mpp-perm-resource">
						<h4><?php echo esc_html( ucfirst( $resource ) ); ?></h4>
						<table class="mpp-admin-table mpp-admin-table--compact">
							<thead><tr><th><?php esc_html_e( 'Action', 'platform-core' ); ?></th><th><?php esc_html_e( 'Key', 'platform-core' ); ?></th><th><?php esc_html_e( 'Granted', 'platform-core' ); ?></th><th><?php esc_html_e( 'Scope', 'platform-core' ); ?></th><th></th></tr></thead>
							<tbody>
								<?php foreach ( $actions as $action ) : ?>
									<?php
									$pid     = (int) $action['id'];
									$is_set  = isset( $assigned[ $pid ] );
									$scope   = $is_set ? $assigned[ $pid ]['scope_type'] : 'all';
									?>
									<tr>
										<td><?php echo esc_html( $action['action'] ); ?></td>
										<td><code><?php echo esc_html( $action['key'] ); ?></code></td>
										<td><?php echo $is_set ? '&#10003;' : '&mdash;'; ?></td>
										<td><?php echo $is_set ? esc_html( $scope ) : '&mdash;'; ?></td>
										<td>
											<?php if ( $role_id ) : ?>
												<?php if ( $is_set ) : ?>
													<form method="post" class="mpp-inline-form">
														<?php echo FormHandler::nonce_field(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
														<input type="hidden" name="mpp_admin_action" value="update_permission_scope">
														<input type="hidden" name="role_id" value="<?php echo esc_attr( (string) $role_id ); ?>">
														<input type="hidden" name="permission_id" value="<?php echo esc_attr( (string) $pid ); ?>">
														<input type="hidden" name="mpp_redirect" value="<?php echo esc_url( add_query_arg( 'role_id', $role_id, home_url( '/app/admin/permissions' ) ) ); ?>">
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
														<input type="hidden" name="mpp_redirect" value="<?php echo esc_url( add_query_arg( 'role_id', $role_id, home_url( '/app/admin/permissions' ) ) ); ?>">
														<button type="submit" class="mpp-btn mpp-btn--sm mpp-btn--danger"><?php esc_html_e( 'Revoke', 'platform-core' ); ?></button>
													</form>
												<?php else : ?>
													<form method="post" class="mpp-inline-form">
														<?php echo FormHandler::nonce_field(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
														<input type="hidden" name="mpp_admin_action" value="grant_permission">
														<input type="hidden" name="role_id" value="<?php echo esc_attr( (string) $role_id ); ?>">
														<input type="hidden" name="permission_id" value="<?php echo esc_attr( (string) $pid ); ?>">
														<input type="hidden" name="scope_type" value="all">
														<input type="hidden" name="mpp_redirect" value="<?php echo esc_url( add_query_arg( 'role_id', $role_id, home_url( '/app/admin/permissions' ) ) ); ?>">
														<button type="submit" class="mpp-btn mpp-btn--sm mpp-btn--primary"><?php esc_html_e( 'Grant', 'platform-core' ); ?></button>
													</form>
												<?php endif; ?>
											<?php endif; ?>
										</td>
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
	 * Modules listing.
	 */
	private function render_modules() {
		$modules = $this->modules->list_modules();
		?>
		<table class="mpp-admin-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Module', 'platform-core' ); ?></th>
					<th><?php esc_html_e( 'Name', 'platform-core' ); ?></th>
					<th><?php esc_html_e( 'Status', 'platform-core' ); ?></th>
					<th><?php esc_html_e( 'Permissions', 'platform-core' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $modules as $module ) : ?>
					<tr>
						<td><code><?php echo esc_html( $module['slug'] ); ?></code></td>
						<td><?php echo esc_html( $module['name'] ); ?></td>
						<td><?php echo esc_html( $module['status'] ); ?></td>
						<td><?php echo esc_html( (string) $module['permission_count'] ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
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
		?>
		<h3><?php esc_html_e( 'Scope Types', 'platform-core' ); ?></h3>
		<ul class="mpp-admin-list">
			<?php foreach ( $scopes as $scope ) : ?>
				<li><strong><?php echo esc_html( $scope['slug'] ); ?></strong> — <?php echo esc_html( $scope['name'] ); ?><?php if ( ! empty( $scope['description'] ) ) : ?> <em>(<?php echo esc_html( $scope['description'] ); ?>)</em><?php endif; ?></li>
			<?php endforeach; ?>
		</ul>

		<h3><?php esc_html_e( 'Recent Audit Log', 'platform-core' ); ?></h3>
		<form method="get" action="<?php echo esc_url( home_url( '/app/admin/acl' ) ); ?>" class="mpp-form mpp-form--inline mpp-admin-filters">
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
				<a class="mpp-btn mpp-btn--secondary" href="<?php echo esc_url( home_url( '/app/admin/acl' ) ); ?>"><?php esc_html_e( 'Clear', 'platform-core' ); ?></a>
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
				<?php foreach ( $entries as $entry ) : ?>
					<tr>
						<td><?php echo esc_html( $entry['created_at'] ); ?></td>
						<td><?php echo esc_html( (string) $entry['user_id'] ); ?></td>
						<td><code><?php echo esc_html( $entry['action'] ); ?></code></td>
						<td><?php echo esc_html( $entry['object_type'] . ( $entry['object_id'] ? ':' . $entry['object_id'] : '' ) ); ?></td>
						<td><?php echo esc_html( $entry['ip_address'] ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php
		Pagination::render(
			$paged,
			$total,
			$per_page,
			home_url( '/app/admin/acl' ),
			array_filter( $filters )
		);
	}

	/**
	 * Admin settings placeholder.
	 */
	private function render_settings() {
		?>
		<div class="mpp-card">
			<p><?php esc_html_e( 'Platform administration settings will be expanded in future phases.', 'platform-core' ); ?></p>
			<p><?php esc_html_e( 'WordPress administrator sync to platform_admin is disabled by default. Enable with:', 'platform-core' ); ?></p>
			<pre><code>add_filter( 'mpp_sync_wp_admin_to_platform_admin', '__return_true' );</code></pre>
		</div>
		<?php
	}
}
