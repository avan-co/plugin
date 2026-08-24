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
		$user_id  = isset( $_GET['user_id'] ) ? (int) $_GET['user_id'] : 0;
		$search   = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
		$role_id  = isset( $_GET['role_id'] ) ? (int) $_GET['role_id'] : 0;
		$status   = isset( $_GET['status'] ) ? sanitize_key( wp_unslash( $_GET['status'] ) ) : '';
		$paged    = isset( $_GET['paged'] ) ? max( 1, (int) $_GET['paged'] ) : 1;
		$per_page = 20;

		if ( $user_id ) {
			$this->render_user_detail( $user_id );
			return;
		}

		$query_args = array(
			'number' => $per_page,
			'offset' => ( $paged - 1 ) * $per_page,
			'search' => $search,
		);

		if ( $role_id > 0 ) {
			$query_args['platform_role_id'] = $role_id;
		}

		if ( in_array( $status, array( 'active', 'inactive' ), true ) ) {
			$query_args['status'] = $status;
		}

		$users = $this->users->list_users( $query_args );
		$total = $this->users->count_users( $query_args );

		$role_options = array( '' => __( 'All roles', 'platform-core' ) );
		foreach ( $this->roles->all() as $role ) {
			$role_options[ (string) $role['id'] ] = $role['name'];
		}

		$status_options = array(
			''         => __( 'All statuses', 'platform-core' ),
			'active'   => __( 'Active', 'platform-core' ),
			'inactive' => __( 'Inactive', 'platform-core' ),
		);

		if ( function_exists( 'platform_ui_filter_bar' ) ) {
			echo platform_ui_filter_bar( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				mpp_route_url( 'app/admin/users' ),
				array(
					array(
						'type'        => 'search',
						'name'        => 's',
						'label'       => __( 'Search users', 'platform-core' ),
						'value'       => $search,
						'placeholder' => __( 'Search users...', 'platform-core' ),
					),
					array(
						'type'    => 'select',
						'name'    => 'role_id',
						'label'   => __( 'Platform role', 'platform-core' ),
						'value'   => $role_id > 0 ? (string) $role_id : '',
						'options' => $role_options,
					),
					array(
						'type'    => 'select',
						'name'    => 'status',
						'label'   => __( 'Status', 'platform-core' ),
						'value'   => $status,
						'options' => $status_options,
					),
				)
			);
		} else {
			?>
			<form method="get" action="<?php echo esc_url( mpp_route_url( 'app/admin/users' ) ); ?>" class="mpp-admin-search">
				<label class="screen-reader-text" for="mpp-admin-user-search"><?php esc_html_e( 'Search users', 'platform-core' ); ?></label>
				<input type="search" id="mpp-admin-user-search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Search users...', 'platform-core' ); ?>">
				<label class="screen-reader-text" for="mpp-admin-user-role"><?php esc_html_e( 'Platform role', 'platform-core' ); ?></label>
				<select id="mpp-admin-user-role" name="role_id" class="mpp-select" aria-label="<?php esc_attr_e( 'Platform role', 'platform-core' ); ?>">
					<?php foreach ( $role_options as $value => $label ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( (string) $role_id, (string) $value ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
				<label class="screen-reader-text" for="mpp-admin-user-status"><?php esc_html_e( 'Status', 'platform-core' ); ?></label>
				<select id="mpp-admin-user-status" name="status" class="mpp-select" aria-label="<?php esc_attr_e( 'Status', 'platform-core' ); ?>">
					<?php foreach ( $status_options as $value => $label ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $status, $value ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
				<button type="submit" class="mpp-btn mpp-btn--secondary"><?php esc_html_e( 'Filter', 'platform-core' ); ?></button>
			</form>
			<?php
		}

		if ( empty( $users ) ) {
			echo '<div class="mpp-empty-state"><h3 class="mpp-empty-state__title">' . esc_html__( 'No users found', 'platform-core' ) . '</h3><p>' . esc_html__( 'Try adjusting your search filters.', 'platform-core' ) . '</p></div>';
			return;
		}
		?>
		<div class="mpp-table-wrap">
		<table class="mpp-admin-table mpp-admin-table--stack">
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
						<td data-label="<?php esc_attr_e( 'User', 'platform-core' ); ?>">
							<span class="mpp-user-cell">
								<?php echo function_exists( 'platform_ui_avatar' ) ? platform_ui_avatar( (int) $user['id'] ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								<?php echo esc_html( $user['display_name'] ); ?>
							</span>
						</td>
						<td data-label="<?php esc_attr_e( 'Username', 'platform-core' ); ?>"><?php echo esc_html( $user['username'] ); ?></td>
						<td data-label="<?php esc_attr_e( 'Email', 'platform-core' ); ?>"><?php echo esc_html( $user['email'] ); ?></td>
						<td data-label="<?php esc_attr_e( 'WP Role', 'platform-core' ); ?>"><?php echo esc_html( $wp_user ? implode( ', ', (array) $wp_user->roles ) : '—' ); ?></td>
						<td data-label="<?php esc_attr_e( 'Platform Roles', 'platform-core' ); ?>"><?php $this->render_platform_role_chips( $user['platform_roles'] ); ?></td>
						<td data-label="<?php esc_attr_e( 'Registered', 'platform-core' ); ?>"><?php echo esc_html( $wp_user && $wp_user->user_registered ? mysql2date( get_option( 'date_format' ), $wp_user->user_registered ) : '—' ); ?></td>
						<td data-label="<?php esc_attr_e( 'Actions', 'platform-core' ); ?>"><a href="<?php echo esc_url( add_query_arg( 'user_id', $user['id'], mpp_route_url( 'app/admin/users' ) ) ); ?>"><?php esc_html_e( 'View', 'platform-core' ); ?></a></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		</div>
		<?php
		$pagination_args = array(
			's' => $search,
		);

		if ( $role_id > 0 ) {
			$pagination_args['role_id'] = $role_id;
		}

		if ( in_array( $status, array( 'active', 'inactive' ), true ) ) {
			$pagination_args['status'] = $status;
		}

		Pagination::render(
			$paged,
			$total,
			$per_page,
			mpp_route_url( 'app/admin/users' ),
			$pagination_args
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

		$wp_user  = get_userdata( $user_id );
		$wp_roles = $wp_user ? array_values( (array) $wp_user->roles ) : array();
		$tab      = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'overview';
		$base_url = add_query_arg( 'user_id', $user_id, mpp_route_url( 'app/admin/users' ) );
		$tabs     = array(
			'overview'         => __( 'Overview', 'platform-core' ),
			'roles'            => __( 'Roles', 'platform-core' ),
			'permissions'      => __( 'Permissions', 'platform-core' ),
			'effective-access' => __( 'Effective Access', 'platform-core' ),
			'security'         => __( 'Security', 'platform-core' ),
			'activity'         => __( 'Activity', 'platform-core' ),
		);

		if ( ! isset( $tabs[ $tab ] ) ) {
			$tab = 'overview';
		}

		$this->set_page_meta(
			array(
				'title'       => $user['display_name'],
				'description' => $user['email'],
			)
		);

		$avatar = function_exists( 'platform_ui_avatar' ) ? platform_ui_avatar( (int) $user_id, 48 ) : '';
		if ( function_exists( 'platform_ui_detail_header' ) ) {
			echo platform_ui_detail_header( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				$user['display_name'],
				$user['email'],
				array(
					array(
						'label' => __( 'Platform Roles', 'platform-core' ),
						'value' => ! empty( $user['platform_roles'] ) ? (string) count( $user['platform_roles'] ) : '0',
					),
					array(
						'label' => __( 'Status', 'platform-core' ),
						'value' => $user['status'],
					),
				),
				$avatar
			);
		}

		$this->echo_back_link( mpp_route_url( 'app/admin/users' ), __( 'Back to users', 'platform-core' ) );
		$this->render_admin_tabs( $tabs, $tab, $base_url );

		if ( 'roles' === $tab ) {
			$this->render_user_roles_tab( $user_id, $user );
			return;
		}

		if ( 'permissions' === $tab ) {
			$this->render_user_permissions_tab( $user_id );
			return;
		}

		if ( 'effective-access' === $tab ) {
			$this->render_user_effective_access_tab( $user_id );
			return;
		}

		if ( 'security' === $tab ) {
			$this->render_user_security_tab( $user, $wp_roles );
			return;
		}

		if ( 'activity' === $tab ) {
			$this->render_user_activity_tab( $user_id );
			return;
		}

		?>
		<dl class="mpp-profile-list">
			<dt><?php esc_html_e( 'ID', 'platform-core' ); ?></dt><dd><?php echo esc_html( (string) $user['id'] ); ?></dd>
			<dt><?php esc_html_e( 'Username', 'platform-core' ); ?></dt><dd><?php echo esc_html( $user['username'] ); ?></dd>
			<dt><?php esc_html_e( 'Email', 'platform-core' ); ?></dt><dd><?php echo esc_html( $user['email'] ); ?></dd>
			<dt><?php esc_html_e( 'Status', 'platform-core' ); ?></dt><dd><?php echo esc_html( $user['status'] ); ?></dd>
			<dt><?php esc_html_e( 'WordPress Role', 'platform-core' ); ?></dt><dd><?php echo esc_html( ! empty( $wp_roles ) ? implode( ', ', $wp_roles ) : '—' ); ?></dd>
			<dt><?php esc_html_e( 'Platform Roles', 'platform-core' ); ?></dt><dd><?php $this->render_platform_role_chips( $user['platform_roles'] ); ?></dd>
			<dt><?php esc_html_e( 'Registered', 'platform-core' ); ?></dt>
			<dd><?php echo esc_html( $wp_user && $wp_user->user_registered ? mysql2date( get_option( 'date_format' ), $wp_user->user_registered ) : '—' ); ?></dd>
		</dl>
		<?php
	}

	/**
	 * Roles CRUD.
	 */
	private function render_roles() {
		$view_id = isset( $_GET['view'] ) ? (int) $_GET['view'] : 0;
		$edit_id = isset( $_GET['edit'] ) ? (int) $_GET['edit'] : 0;
		$create  = isset( $_GET['action'] ) && 'create' === $_GET['action'];

		if ( $view_id ) {
			$this->render_role_detail( $view_id );
			return;
		}

		if ( $create || $edit_id ) {
			$this->render_role_form( $edit_id );
			return;
		}

		$roles = $this->roles->all();

		if ( empty( $roles ) ) {
			echo '<div class="mpp-empty-state"><h3 class="mpp-empty-state__title">' . esc_html__( 'No roles found', 'platform-core' ) . '</h3><p>' . esc_html__( 'Default platform roles are created during installation.', 'platform-core' ) . '</p></div>';
			return;
		}

		$this->set_page_actions(
			sprintf(
				'<a class="mpp-btn mpp-btn--primary" href="%s">%s</a>',
				esc_url( add_query_arg( 'action', 'create', mpp_route_url( 'app/admin/roles' ) ) ),
				esc_html__( 'Create Role', 'platform-core' )
			)
		);
		?>
		<div class="mpp-table-wrap">
		<table class="mpp-admin-table mpp-admin-table--stack">
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
						<td data-label="<?php esc_attr_e( 'Name', 'platform-core' ); ?>">
							<strong><?php echo esc_html( $role['name'] ); ?></strong>
							<?php if ( ! empty( $role['description'] ) ) : ?>
								<p class="mpp-muted"><?php echo esc_html( $role['description'] ); ?></p>
							<?php endif; ?>
						</td>
						<td data-label="<?php esc_attr_e( 'Slug', 'platform-core' ); ?>"><code><?php echo esc_html( $role['slug'] ); ?></code></td>
						<td data-label="<?php esc_attr_e( 'Permissions', 'platform-core' ); ?>"><?php echo esc_html( (string) count( $this->roles->get_permissions( (int) $role['id'] ) ) ); ?></td>
						<td data-label="<?php esc_attr_e( 'Users', 'platform-core' ); ?>"><?php echo esc_html( (string) $this->access->count_users_with_role( (int) $role['id'] ) ); ?></td>
						<td data-label="<?php esc_attr_e( 'Status', 'platform-core' ); ?>"><?php echo esc_html( $role['status'] ?? 'active' ); ?></td>
						<td data-label="<?php esc_attr_e( 'System', 'platform-core' ); ?>"><?php echo ! empty( $role['is_system'] ) ? esc_html__( 'Yes', 'platform-core' ) : esc_html__( 'No', 'platform-core' ); ?></td>
						<td data-label="<?php esc_attr_e( 'Actions', 'platform-core' ); ?>">
							<a href="<?php echo esc_url( add_query_arg( 'view', $role['id'], mpp_route_url( 'app/admin/roles' ) ) ); ?>"><?php esc_html_e( 'View', 'platform-core' ); ?></a>
							| <a href="<?php echo esc_url( add_query_arg( 'edit', $role['id'], mpp_route_url( 'app/admin/roles' ) ) ); ?>"><?php esc_html_e( 'Edit', 'platform-core' ); ?></a>
							| <a href="<?php echo esc_url( add_query_arg( array( 'view' => $role['id'], 'tab' => 'permissions' ), mpp_route_url( 'app/admin/roles' ) ) ); ?>"><?php esc_html_e( 'Permissions', 'platform-core' ); ?></a>
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
				<label for="mpp-role-slug"><?php esc_html_e( 'Slug', 'platform-core' ); ?></label>
				<input type="text" id="mpp-role-slug" name="slug" required pattern="[a-z0-9_-]+">
			<?php endif; ?>

			<label for="mpp-role-name"><?php esc_html_e( 'Name', 'platform-core' ); ?></label>
			<input type="text" id="mpp-role-name" name="name" required value="<?php echo $is_edit ? esc_attr( $role['name'] ) : ''; ?>">

			<label for="mpp-role-description"><?php esc_html_e( 'Description', 'platform-core' ); ?></label>
			<textarea id="mpp-role-description" name="description" rows="3"><?php echo $is_edit ? esc_textarea( $role['description'] ) : ''; ?></textarea>

			<label for="mpp-role-status"><?php esc_html_e( 'Status', 'platform-core' ); ?></label>
			<select id="mpp-role-status" name="status" aria-label="<?php esc_attr_e( 'Role status', 'platform-core' ); ?>">
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
		$permission_id = isset( $_GET['permission_id'] ) ? (int) $_GET['permission_id'] : 0;

		if ( $permission_id ) {
			$this->render_permission_detail( $permission_id );
			return;
		}

		$query         = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';
		$module_filter = isset( $_GET['module'] ) ? sanitize_key( wp_unslash( $_GET['module'] ) ) : '';
		$tree          = $this->permissions->get_permission_tree();
		$stats         = $this->access->get_permission_stats();
		?>
		<div class="mpp-admin-stats">
			<div class="mpp-stat-card"><span class="mpp-stat-card__label"><?php esc_html_e( 'Total Permissions', 'platform-core' ); ?></span><span class="mpp-stat-card__value"><?php echo esc_html( (string) $stats['total'] ); ?></span></div>
			<div class="mpp-stat-card"><span class="mpp-stat-card__label"><?php esc_html_e( 'Core Permissions', 'platform-core' ); ?></span><span class="mpp-stat-card__value"><?php echo esc_html( (string) $stats['core'] ); ?></span></div>
			<div class="mpp-stat-card"><span class="mpp-stat-card__label"><?php esc_html_e( 'Module Permissions', 'platform-core' ); ?></span><span class="mpp-stat-card__value"><?php echo esc_html( (string) $stats['module'] ); ?></span></div>
			<div class="mpp-stat-card"><span class="mpp-stat-card__label"><?php esc_html_e( 'Active Modules', 'platform-core' ); ?></span><span class="mpp-stat-card__value"><?php echo esc_html( (string) $stats['active_modules'] ); ?></span></div>
		</div>
		<?php
		if ( function_exists( 'platform_ui_filter_bar' ) ) {
			$module_options = array( '' => __( 'All modules', 'platform-core' ) );
			foreach ( array_keys( $tree ) as $module_slug ) {
				$module_options[ $module_slug ] = $this->get_module_group_label( $module_slug );
			}
			echo platform_ui_filter_bar( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				mpp_route_url( 'app/admin/permissions' ),
				array(
					array(
						'type'        => 'search',
						'name'        => 'q',
						'label'       => __( 'Search permissions', 'platform-core' ),
						'value'       => $query,
						'placeholder' => __( 'Search permissions...', 'platform-core' ),
					),
					array(
						'type'    => 'select',
						'name'    => 'module',
						'label'   => __( 'Module', 'platform-core' ),
						'value'   => $module_filter,
						'options' => $module_options,
					),
				)
			);
		}
		?>
		<div class="mpp-perm-tree">
			<?php foreach ( $tree as $module => $resources ) : ?>
				<?php if ( $module_filter && $module_filter !== $module ) { continue; } ?>
				<section class="mpp-perm-tree__module">
					<h3 class="mpp-perm-tree__module-title"><?php echo esc_html( $this->get_module_group_label( $module ) ); ?></h3>
					<?php foreach ( $resources as $resource => $actions ) : ?>
						<div class="mpp-perm-tree__resource">
							<h4 class="mpp-perm-tree__resource-title"><?php echo esc_html( ucfirst( $resource ) ); ?></h4>
							<ul class="mpp-perm-tree__list">
								<?php foreach ( $actions as $action ) : ?>
									<?php
									if ( $query && false === stripos( $action['key'] . ' ' . $action['action'] . ' ' . ( $action['description'] ?? '' ), $query ) ) {
										continue;
									}
									$pid        = (int) $action['id'];
									$role_usage = $this->access->get_roles_using_permission( $pid );
									$title      = $action['description'] ?: ucfirst( $action['action'] );
									?>
									<li class="mpp-perm-tree__item">
										<div class="mpp-perm-tree__item-main">
											<p class="mpp-perm-tree__item-title"><?php echo esc_html( $title ); ?></p>
											<?php if ( ! empty( $action['description'] ) ) : ?>
												<p class="mpp-muted"><?php echo esc_html( $action['description'] ); ?></p>
											<?php endif; ?>
											<div class="mpp-perm-tree__item-meta">
												<code><?php echo esc_html( $action['key'] ); ?></code>
												<span class="mpp-muted"><?php esc_html_e( 'Module:', 'platform-core' ); ?> <?php echo esc_html( $this->get_module_group_label( $module ) ); ?></span>
												<?php if ( ! empty( $role_usage ) ) : ?>
													<span class="mpp-muted">
														<?php
														printf(
															/* translators: %d: role count */
															esc_html( _n( 'Used by %d role', 'Used by %d roles', count( $role_usage ), 'platform-core' ) ),
															count( $role_usage )
														);
														?>
													</span>
												<?php endif; ?>
											</div>
										</div>
										<a href="<?php echo esc_url( add_query_arg( 'permission_id', $pid, mpp_route_url( 'app/admin/permissions' ) ) ); ?>"><?php esc_html_e( 'Details', 'platform-core' ); ?> &rarr;</a>
									</li>
								<?php endforeach; ?>
							</ul>
						</div>
					<?php endforeach; ?>
				</section>
			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * Render grouped permission editor for a role.
	 *
	 * @param int $role_id Role ID.
	 */
	private function render_role_permissions_editor( $role_id ) {
		$query         = isset( $_GET['perm_q'] ) ? sanitize_text_field( wp_unslash( $_GET['perm_q'] ) ) : '';
		$tree          = $this->permissions->get_permission_tree();
		$scope_types   = $this->scopes->assignable();
		$assigned      = array();
		$granted_count = 0;

		foreach ( $this->roles->get_permissions( $role_id ) as $perm ) {
			$assigned[ (int) $perm['permission_id'] ] = $perm;
			$granted_count++;
		}

		$redirect = add_query_arg(
			array(
				'view'   => $role_id,
				'tab'    => 'permissions',
				'perm_q' => $query,
			),
			mpp_route_url( 'app/admin/roles' )
		);
		?>
		<form method="get" action="<?php echo esc_url( mpp_route_url( 'app/admin/roles' ) ); ?>" class="mpp-filter-bar">
			<input type="hidden" name="view" value="<?php echo esc_attr( (string) $role_id ); ?>">
			<input type="hidden" name="tab" value="permissions">
			<label class="mpp-filter-bar__field mpp-filter-bar__field--grow">
				<span class="screen-reader-text"><?php esc_html_e( 'Search permissions', 'platform-core' ); ?></span>
				<input type="search" name="perm_q" value="<?php echo esc_attr( $query ); ?>" placeholder="<?php esc_attr_e( 'Search permissions...', 'platform-core' ); ?>" class="mpp-input">
			</label>
			<button type="submit" class="mpp-btn mpp-btn--secondary"><?php esc_html_e( 'Search', 'platform-core' ); ?></button>
		</form>

		<form method="post" class="mpp-role-permissions">
			<?php echo FormHandler::nonce_field(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<input type="hidden" name="mpp_admin_action" value="save_role_permissions">
			<input type="hidden" name="role_id" value="<?php echo esc_attr( (string) $role_id ); ?>">
			<input type="hidden" name="mpp_redirect" value="<?php echo esc_url( $redirect ); ?>">

			<?php foreach ( $tree as $module => $resources ) : ?>
				<?php
				$module_visible = false;
				foreach ( $resources as $resource => $actions ) {
					foreach ( $actions as $action ) {
						if ( ! $query || false !== stripos( $action['key'] . ' ' . $action['action'] . ' ' . ( $action['description'] ?? '' ), $query ) ) {
							$module_visible = true;
							break 2;
						}
					}
				}
				if ( ! $module_visible ) {
					continue;
				}
				?>
				<section class="mpp-role-permissions__module">
					<h3 class="mpp-role-permissions__module-title"><?php echo esc_html( $this->get_module_group_label( $module ) ); ?></h3>
					<?php foreach ( $resources as $resource => $actions ) : ?>
						<?php
						$resource_visible = false;
						foreach ( $actions as $action ) {
							if ( ! $query || false !== stripos( $action['key'] . ' ' . $action['action'] . ' ' . ( $action['description'] ?? '' ), $query ) ) {
								$resource_visible = true;
								break;
							}
						}
						if ( ! $resource_visible ) {
							continue;
						}
						?>
						<div class="mpp-role-permissions__resource">
							<h4 class="mpp-role-permissions__resource-title"><?php echo esc_html( ucfirst( $resource ) ); ?></h4>
							<ul class="mpp-role-permissions__list">
								<?php foreach ( $actions as $action ) : ?>
									<?php
									if ( $query && false === stripos( $action['key'] . ' ' . $action['action'] . ' ' . ( $action['description'] ?? '' ), $query ) ) {
										continue;
									}
									$pid     = (int) $action['id'];
									$granted = isset( $assigned[ $pid ] );
									$scope   = $granted ? $assigned[ $pid ]['scope_type'] : 'all';
									$label   = $action['description'] ?: ucfirst( $action['action'] );
									?>
									<li class="mpp-role-permissions__item">
										<label class="mpp-role-permissions__checkbox">
											<input type="checkbox" name="permission_ids[]" value="<?php echo esc_attr( (string) $pid ); ?>" <?php checked( $granted ); ?>>
											<span><?php echo esc_html( $label ); ?></span>
										</label>
										<select name="permission_scopes[<?php echo esc_attr( (string) $pid ); ?>]" class="mpp-select mpp-role-permissions__scope" aria-label="<?php echo esc_attr( sprintf( __( 'Scope for %s', 'platform-core' ), $label ) ); ?>">
											<?php foreach ( $scope_types as $st ) : ?>
												<option value="<?php echo esc_attr( $st['slug'] ); ?>" <?php selected( $scope, $st['slug'] ); ?>><?php echo esc_html( $st['name'] ); ?></option>
											<?php endforeach; ?>
										</select>
									</li>
								<?php endforeach; ?>
							</ul>
						</div>
					<?php endforeach; ?>
				</section>
			<?php endforeach; ?>

			<div class="mpp-role-permissions__footer">
				<p class="mpp-muted">
					<?php
					printf(
						/* translators: %d: permission count */
						esc_html( _n( '%d permission granted', '%d permissions granted', $granted_count, 'platform-core' ) ),
						$granted_count
					);
					?>
				</p>
				<div class="mpp-role-permissions__actions">
					<a class="mpp-btn mpp-btn--secondary" href="<?php echo esc_url( add_query_arg( array( 'view' => $role_id, 'tab' => 'permissions' ), mpp_route_url( 'app/admin/roles' ) ) ); ?>"><?php esc_html_e( 'Cancel', 'platform-core' ); ?></a>
					<button type="submit" class="mpp-btn mpp-btn--primary"><?php esc_html_e( 'Save Changes', 'platform-core' ); ?></button>
				</div>
			</div>
		</form>
		<?php
	}

	/**
	 * Render read-only effective access for a role.
	 *
	 * @param int $role_id Role ID.
	 */
	private function render_role_effective_access_tab( $role_id ) {
		$permissions = $this->roles->get_permissions( $role_id );

		if ( empty( $permissions ) ) {
			echo '<p>' . esc_html__( 'This role does not grant any permissions yet.', 'platform-core' ) . '</p>';
			return;
		}
		?>
		<p class="mpp-muted"><?php esc_html_e( 'Permissions granted directly by this role.', 'platform-core' ); ?></p>
		<div class="mpp-table-wrap">
		<table class="mpp-admin-table mpp-admin-table--stack">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Permission', 'platform-core' ); ?></th>
					<th><?php esc_html_e( 'Module', 'platform-core' ); ?></th>
					<th><?php esc_html_e( 'Scope', 'platform-core' ); ?></th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $permissions as $perm ) : ?>
					<tr>
						<td data-label="<?php esc_attr_e( 'Permission', 'platform-core' ); ?>"><code><?php echo esc_html( $perm['permission_key'] ); ?></code></td>
						<td data-label="<?php esc_attr_e( 'Module', 'platform-core' ); ?>"><?php echo esc_html( $this->get_module_group_label( $perm['module'] ) ); ?></td>
						<td data-label="<?php esc_attr_e( 'Scope', 'platform-core' ); ?>"><?php echo esc_html( $this->access->get_scope_label( $perm['scope_type'] ) ); ?></td>
						<td data-label="<?php esc_attr_e( 'Actions', 'platform-core' ); ?>"><a href="<?php echo esc_url( add_query_arg( 'permission_id', $perm['permission_id'], mpp_route_url( 'app/admin/permissions' ) ) ); ?>"><?php esc_html_e( 'Details', 'platform-core' ); ?></a></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		</div>
		<?php
	}

	/**
	 * Modules listing.
	 */
	private function render_modules() {
		$module_slug = isset( $_GET['module'] ) ? sanitize_key( wp_unslash( $_GET['module'] ) ) : '';

		if ( $module_slug ) {
			$this->render_module_detail( $module_slug );
			return;
		}

		$query   = isset( $_GET['mod_q'] ) ? sanitize_text_field( wp_unslash( $_GET['mod_q'] ) ) : '';
		$modules = $this->modules->list_modules();

		if ( $query ) {
			$modules = array_values(
				array_filter(
					$modules,
					function ( $module ) use ( $query ) {
						$haystack = strtolower( $module['slug'] . ' ' . $module['name'] . ' ' . ( $module['description'] ?? '' ) );
						return false !== strpos( $haystack, strtolower( $query ) );
					}
				)
			);
		}

		if ( empty( $modules ) ) {
			echo '<div class="mpp-empty-state"><h3 class="mpp-empty-state__title">' . esc_html__( 'No modules found', 'platform-core' ) . '</h3><p>' . esc_html__( 'Try adjusting your search or install a platform module plugin.', 'platform-core' ) . '</p></div>';
			return;
		}

		if ( function_exists( 'platform_ui_filter_bar' ) ) {
			echo platform_ui_filter_bar( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				mpp_route_url( 'app/admin/modules' ),
				array(
					array(
						'type'        => 'search',
						'name'        => 'mod_q',
						'label'       => __( 'Search modules', 'platform-core' ),
						'value'       => $query,
						'placeholder' => __( 'Search modules...', 'platform-core' ),
					),
				)
			);
		}
		?>
		<p class="mpp-muted"><?php esc_html_e( 'Module availability is controlled by WordPress plugin activation. Deactivating a plugin removes its runtime routes and widgets.', 'platform-core' ); ?></p>
		<div class="mpp-module-grid">
			<?php foreach ( $modules as $module ) : ?>
				<?php
				$card = array(
					'name'             => $module['name'],
					'description'      => $module['description'] ?? __( 'No description provided.', 'platform-core' ),
					'version'          => $module['version'] ?? '—',
					'status'           => $module['status'] ?? 'active',
					'permission_count' => $module['permission_count'] ?? 0,
					'route_count'      => $module['route_count'] ?? 0,
					'url'              => add_query_arg( 'module', $module['slug'], mpp_route_url( 'app/admin/modules' ) ),
				);
				if ( function_exists( 'platform_ui_module_card' ) ) {
					echo platform_ui_module_card( $card ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				} else {
					?>
					<article class="mpp-card mpp-module-card">
						<h3><?php echo esc_html( $card['name'] ); ?></h3>
						<p class="mpp-muted"><?php echo esc_html( $card['description'] ); ?></p>
					</article>
					<?php
				}
				?>
			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * Module detail page with tabs.
	 *
	 * @param string $module_slug Module slug.
	 */
	private function render_module_detail( $module_slug ) {
		$module = $this->modules->find_module( $module_slug );

		if ( ! $module ) {
			echo '<p>' . esc_html__( 'Module not found.', 'platform-core' ) . '</p>';
			return;
		}

		$tab      = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'overview';
		$base_url = add_query_arg( 'module', $module_slug, mpp_route_url( 'app/admin/modules' ) );
		$tabs     = array(
			'overview'    => __( 'Overview', 'platform-core' ),
			'permissions' => __( 'Permissions', 'platform-core' ),
			'routes'      => __( 'Routes', 'platform-core' ),
			'settings'    => __( 'Settings', 'platform-core' ),
		);

		if ( ! isset( $tabs[ $tab ] ) ) {
			$tab = 'overview';
		}

		$permissions = $this->modules->get_module_permissions( $module_slug );
		$routes      = $this->modules->get_module_routes( $module_slug );

		$this->set_page_meta(
			array(
				'title'       => $module['name'],
				'description' => $module['description'] ?? '',
			)
		);

		if ( function_exists( 'platform_ui_detail_header' ) ) {
			echo platform_ui_detail_header( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				$module['name'],
				$module['description'] ?? '',
				array(
					array(
						'label' => __( 'Version', 'platform-core' ),
						'value' => $module['version'] ?? '—',
					),
					array(
						'label' => __( 'Permissions', 'platform-core' ),
						'value' => (string) count( $permissions ),
					),
					array(
						'label' => __( 'Routes', 'platform-core' ),
						'value' => (string) count( $routes ),
					),
				)
			);
		}

		$this->echo_back_link( mpp_route_url( 'app/admin/modules' ), __( 'Back to modules', 'platform-core' ) );
		$this->render_admin_tabs( $tabs, $tab, $base_url );

		if ( 'permissions' === $tab ) {
			if ( empty( $permissions ) ) {
				echo '<p>' . esc_html__( 'This module has not registered any permissions.', 'platform-core' ) . '</p>';
				return;
			}
			?>
			<ul class="mpp-module-permissions__list">
				<?php foreach ( $permissions as $permission ) : ?>
					<li class="mpp-module-permissions__item">
						<div>
							<strong><?php echo esc_html( $permission['description'] ?: ucfirst( $permission['action'] ) ); ?></strong>
							<p class="mpp-muted"><code><?php echo esc_html( $permission['key'] ); ?></code></p>
						</div>
						<a href="<?php echo esc_url( add_query_arg( 'permission_id', $permission['id'], mpp_route_url( 'app/admin/permissions' ) ) ); ?>"><?php esc_html_e( 'Details', 'platform-core' ); ?> &rarr;</a>
					</li>
				<?php endforeach; ?>
			</ul>
			<?php
			return;
		}

		if ( 'routes' === $tab ) {
			if ( empty( $routes ) ) {
				echo '<p>' . esc_html__( 'This module has not registered any routes.', 'platform-core' ) . '</p>';
				return;
			}
			?>
			<div class="mpp-table-wrap">
			<table class="mpp-admin-table mpp-admin-table--stack">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Route', 'platform-core' ); ?></th>
						<th><?php esc_html_e( 'Title', 'platform-core' ); ?></th>
						<th><?php esc_html_e( 'Permission', 'platform-core' ); ?></th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $routes as $route ) : ?>
						<tr>
							<td data-label="<?php esc_attr_e( 'Route', 'platform-core' ); ?>"><code><?php echo esc_html( $route['slug'] ); ?></code></td>
							<td data-label="<?php esc_attr_e( 'Title', 'platform-core' ); ?>"><?php echo esc_html( $route['title'] ?: '—' ); ?></td>
							<td data-label="<?php esc_attr_e( 'Permission', 'platform-core' ); ?>"><?php echo $route['permission'] ? '<code>' . esc_html( $route['permission'] ) . '</code>' : '&mdash;'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
							<td data-label="<?php esc_attr_e( 'Actions', 'platform-core' ); ?>"><a href="<?php echo esc_url( $route['url'] ); ?>"><?php esc_html_e( 'Open', 'platform-core' ); ?></a></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			</div>
			<?php
			return;
		}

		if ( 'settings' === $tab ) {
			$settings_html = apply_filters( 'mpp_module_admin_settings_html', '', $module_slug, $module );

			if ( $settings_html ) {
				echo $settings_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				return;
			}
			?>
			<div class="mpp-empty-state">
				<h3 class="mpp-empty-state__title"><?php esc_html_e( 'No module settings', 'platform-core' ); ?></h3>
				<p><?php esc_html_e( 'This module does not expose admin settings yet.', 'platform-core' ); ?></p>
			</div>
			<?php
			return;
		}

		?>
		<dl class="mpp-profile-list">
			<dt><?php esc_html_e( 'Slug', 'platform-core' ); ?></dt><dd><code><?php echo esc_html( $module['slug'] ); ?></code></dd>
			<dt><?php esc_html_e( 'Version', 'platform-core' ); ?></dt><dd><?php echo esc_html( $module['version'] ?? '—' ); ?></dd>
			<dt><?php esc_html_e( 'Status', 'platform-core' ); ?></dt><dd><?php echo esc_html( $module['status'] ?? 'active' ); ?></dd>
			<dt><?php esc_html_e( 'Requires Core', 'platform-core' ); ?></dt><dd><?php echo esc_html( $module['requires_core_version'] ?? '—' ); ?></dd>
			<dt><?php esc_html_e( 'Permissions', 'platform-core' ); ?></dt><dd><?php echo esc_html( (string) count( $permissions ) ); ?></dd>
			<dt><?php esc_html_e( 'Routes', 'platform-core' ); ?></dt><dd><?php echo esc_html( (string) count( $routes ) ); ?></dd>
		</dl>
		<div class="mpp-quick-actions">
			<a class="mpp-btn mpp-btn--secondary" href="<?php echo esc_url( add_query_arg( array( 'module' => $module_slug, 'tab' => 'permissions' ), mpp_route_url( 'app/admin/modules' ) ) ); ?>"><?php esc_html_e( 'View Permissions', 'platform-core' ); ?></a>
			<a class="mpp-btn mpp-btn--secondary" href="<?php echo esc_url( add_query_arg( array( 'module' => $module_slug, 'tab' => 'routes' ), mpp_route_url( 'app/admin/modules' ) ) ); ?>"><?php esc_html_e( 'View Routes', 'platform-core' ); ?></a>
			<a class="mpp-btn mpp-btn--secondary" href="<?php echo esc_url( add_query_arg( 'module', $module_slug, mpp_route_url( 'app/admin/permissions' ) ) ); ?>"><?php esc_html_e( 'Browse in Permissions', 'platform-core' ); ?></a>
		</div>
		<?php
	}

	/**
	 * ACL overview with audit log.
	 */
	private function render_acl() {
		$view = isset( $_GET['view'] ) ? sanitize_key( wp_unslash( $_GET['view'] ) ) : 'overview';

		if ( 'audit' === $view ) {
			$this->render_audit_log();
			return;
		}

		$scopes    = $this->scopes->all();
		$effective = function_exists( 'mpp' ) ? count( mpp()->acl()->get_user_permissions( get_current_user_id() ) ) : 0;
		$roles     = $this->roles->all();
		?>
		<div class="mpp-stats mpp-admin-stats">
			<div class="mpp-stat-card"><span class="mpp-stat-card__label"><?php esc_html_e( 'Your Effective Permissions', 'platform-core' ); ?></span><span class="mpp-stat-card__value"><?php echo esc_html( (string) $effective ); ?></span></div>
			<div class="mpp-stat-card"><span class="mpp-stat-card__label"><?php esc_html_e( 'Scope Types', 'platform-core' ); ?></span><span class="mpp-stat-card__value"><?php echo esc_html( (string) count( $scopes ) ); ?></span></div>
			<div class="mpp-stat-card"><span class="mpp-stat-card__label"><?php esc_html_e( 'Platform Roles', 'platform-core' ); ?></span><span class="mpp-stat-card__value"><?php echo esc_html( (string) count( $roles ) ); ?></span></div>
		</div>

		<h3><?php esc_html_e( 'Scope Types', 'platform-core' ); ?></h3>
		<ul class="mpp-admin-list">
			<?php foreach ( $scopes as $scope ) : ?>
				<li><strong><?php echo esc_html( $scope['slug'] ); ?></strong> — <?php echo esc_html( $scope['name'] ); ?><?php if ( ! empty( $scope['description'] ) ) : ?> <em>(<?php echo esc_html( $scope['description'] ); ?>)</em><?php endif; ?></li>
			<?php endforeach; ?>
		</ul>

		<div class="mpp-quick-actions">
			<a class="mpp-btn mpp-btn--secondary" href="<?php echo esc_url( mpp_route_url( 'app/admin/users' ) ); ?>"><?php esc_html_e( 'Users', 'platform-core' ); ?></a>
			<a class="mpp-btn mpp-btn--secondary" href="<?php echo esc_url( mpp_route_url( 'app/admin/roles' ) ); ?>"><?php esc_html_e( 'Roles', 'platform-core' ); ?></a>
			<a class="mpp-btn mpp-btn--secondary" href="<?php echo esc_url( mpp_route_url( 'app/admin/permissions' ) ); ?>"><?php esc_html_e( 'Permissions', 'platform-core' ); ?></a>
			<a class="mpp-btn mpp-btn--secondary" href="<?php echo esc_url( add_query_arg( 'view', 'audit', mpp_route_url( 'app/admin/acl' ) ) ); ?>"><?php esc_html_e( 'Audit Log', 'platform-core' ); ?></a>
		</div>
		<?php
	}

	/**
	 * Audit log listing.
	 */
	private function render_audit_log() {
		$filters = array(
			'user_id'     => isset( $_GET['user_id'] ) ? (int) $_GET['user_id'] : 0,
			'action'      => isset( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : '',
			'object_type' => isset( $_GET['object_type'] ) ? sanitize_key( wp_unslash( $_GET['object_type'] ) ) : '',
			'date_from'   => isset( $_GET['date_from'] ) ? sanitize_text_field( wp_unslash( $_GET['date_from'] ) ) : '',
			'date_to'     => isset( $_GET['date_to'] ) ? sanitize_text_field( wp_unslash( $_GET['date_to'] ) ) : '',
		);
		$paged     = isset( $_GET['paged'] ) ? max( 1, (int) $_GET['paged'] ) : 1;
		$per_page  = 25;
		$base_url  = add_query_arg( 'view', 'audit', mpp_route_url( 'app/admin/acl' ) );
		$query     = array_merge(
			$filters,
			array(
				'limit'  => $per_page,
				'offset' => ( $paged - 1 ) * $per_page,
			)
		);
		$entries = $this->audit->query( $query );
		$total   = $this->audit->count( $filters );

		$this->echo_back_link( mpp_route_url( 'app/admin/acl' ), __( 'Back to ACL overview', 'platform-core' ) );
		?>
		<form method="get" action="<?php echo esc_url( mpp_route_url( 'app/admin/acl' ) ); ?>" class="mpp-form mpp-form--inline mpp-admin-filters">
			<input type="hidden" name="view" value="audit">
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
				<a class="mpp-btn mpp-btn--secondary" href="<?php echo esc_url( $base_url ); ?>"><?php esc_html_e( 'Clear', 'platform-core' ); ?></a>
			<?php endif; ?>
		</form>
		<div class="mpp-table-wrap">
		<table class="mpp-admin-table mpp-admin-table--stack">
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
						<td data-label="<?php esc_attr_e( 'Time', 'platform-core' ); ?>"><?php echo esc_html( $entry['created_at'] ); ?></td>
						<td data-label="<?php esc_attr_e( 'User', 'platform-core' ); ?>"><?php echo esc_html( (string) $entry['user_id'] ); ?></td>
						<td data-label="<?php esc_attr_e( 'Action', 'platform-core' ); ?>"><code><?php echo esc_html( $entry['action'] ); ?></code></td>
						<td data-label="<?php esc_attr_e( 'Object', 'platform-core' ); ?>"><?php echo esc_html( $entry['object_type'] . ( $entry['object_id'] ? ':' . $entry['object_id'] : '' ) ); ?></td>
						<td data-label="<?php esc_attr_e( 'IP', 'platform-core' ); ?>"><?php echo esc_html( $entry['ip_address'] ); ?></td>
					</tr>
				<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
		</div>
		<?php
		Pagination::render(
			$paged,
			$total,
			$per_page,
			$base_url,
			array_filter( $filters )
		);
	}

	/**
	 * Render admin tab navigation.
	 *
	 * @param array<string, string> $tabs    Tab slug => label.
	 * @param string                $current Active tab.
	 * @param string                $base_url Base URL for tabs.
	 */
	private function render_admin_tabs( array $tabs, $current, $base_url ) {
		if ( function_exists( 'platform_ui_tabs' ) ) {
			echo platform_ui_tabs( $tabs, $current, $base_url ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			return;
		}

		echo '<nav class="mpp-tabs" aria-label="' . esc_attr__( 'Section navigation', 'platform-core' ) . '"><ul class="mpp-tabs__list">';
		foreach ( $tabs as $slug => $label ) {
			$url       = add_query_arg( 'tab', $slug, $base_url );
			$is_active = $current === $slug;
			echo '<li class="mpp-tabs__item' . ( $is_active ? ' is-active' : '' ) . '">';
			echo '<a href="' . esc_url( $url ) . '"' . ( $is_active ? ' aria-current="page"' : '' ) . '>' . esc_html( $label ) . '</a>';
			echo '</li>';
		}
		echo '</ul></nav>';
	}

	/**
	 * Permission detail page.
	 *
	 * @param int $permission_id Permission ID.
	 */
	private function render_permission_detail( $permission_id ) {
		$permission = $this->registry->find_by_id( $permission_id );

		if ( ! $permission ) {
			echo '<p>' . esc_html__( 'Permission not found.', 'platform-core' ) . '</p>';
			return;
		}

		$role_usage = $this->access->get_roles_using_permission( $permission_id );
		$user_count = $this->access->count_users_with_permission( $permission_id );
		$audit      = $this->audit->query(
			array(
				'object_type' => 'role_permission',
				'limit'       => 10,
			)
		);
		$back_url = mpp_route_url( 'app/admin/permissions' );
		?>
		<p><a href="<?php echo esc_url( $back_url ); ?>">&larr; <?php esc_html_e( 'Back to permissions', 'platform-core' ); ?></a></p>
		<div class="mpp-card">
			<h2><?php echo esc_html( $permission['description'] ?: $permission['permission_key'] ); ?></h2>
			<dl class="mpp-profile-list">
				<dt><?php esc_html_e( 'Permission', 'platform-core' ); ?></dt><dd><code><?php echo esc_html( $permission['permission_key'] ); ?></code></dd>
				<dt><?php esc_html_e( 'Module', 'platform-core' ); ?></dt><dd><a href="<?php echo esc_url( add_query_arg( 'module', $permission['module'], mpp_route_url( 'app/admin/modules' ) ) ); ?>"><?php echo esc_html( $this->get_module_group_label( $permission['module'] ) ); ?></a></dd>
				<dt><?php esc_html_e( 'Category', 'platform-core' ); ?></dt><dd><?php echo esc_html( ucfirst( $permission['resource'] ) ); ?></dd>
				<dt><?php esc_html_e( 'Action', 'platform-core' ); ?></dt><dd><?php echo esc_html( $permission['action'] ); ?></dd>
				<dt><?php esc_html_e( 'Users with access', 'platform-core' ); ?></dt><dd><?php echo esc_html( (string) $user_count ); ?></dd>
			</dl>
			<p class="mpp-muted"><?php esc_html_e( 'A permission alone does not create access. Effective access is calculated from roles, permissions, and scope.', 'platform-core' ); ?></p>
		</div>

		<h3><?php esc_html_e( 'Used by roles', 'platform-core' ); ?></h3>
		<?php if ( empty( $role_usage ) ) : ?>
			<p><?php esc_html_e( 'No roles currently grant this permission.', 'platform-core' ); ?></p>
		<?php else : ?>
			<table class="mpp-admin-table">
				<thead><tr><th><?php esc_html_e( 'Role', 'platform-core' ); ?></th><th><?php esc_html_e( 'Scope', 'platform-core' ); ?></th><th></th></tr></thead>
				<tbody>
					<?php foreach ( $role_usage as $role ) : ?>
						<tr>
							<td><a href="<?php echo esc_url( add_query_arg( 'view', $role['id'], mpp_route_url( 'app/admin/roles' ) ) ); ?>"><?php echo esc_html( $role['name'] ); ?></a></td>
							<td><?php echo esc_html( $this->access->get_scope_label( $role['scope_type'] ) ); ?><?php echo $this->scopes->requires_resource_context( $role['scope_type'] ) ? ' <span class="mpp-muted">(' . esc_html__( 'resource context required', 'platform-core' ) . ')</span>' : ''; ?></td>
							<td><a href="<?php echo esc_url( add_query_arg( array( 'view' => $role['id'], 'tab' => 'permissions' ), mpp_route_url( 'app/admin/roles' ) ) ); ?>"><?php esc_html_e( 'Manage', 'platform-core' ); ?></a></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>

		<h3><?php esc_html_e( 'Recent audit activity', 'platform-core' ); ?></h3>
		<?php if ( empty( $audit ) ) : ?>
			<p><?php esc_html_e( 'No related audit entries yet.', 'platform-core' ); ?></p>
		<?php else : ?>
			<ul class="mpp-activity-list">
				<?php foreach ( $audit as $entry ) : ?>
					<li><code><?php echo esc_html( $entry['action'] ); ?></code> — <?php echo esc_html( $entry['created_at'] ); ?></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
		<?php
	}

	/**
	 * Role detail page with tabs.
	 *
	 * @param int $role_id Role ID.
	 */
	private function render_role_detail( $role_id ) {
		$role = $this->roles->find( $role_id );

		if ( ! $role ) {
			echo '<p>' . esc_html__( 'Role not found.', 'platform-core' ) . '</p>';
			return;
		}

		$tab      = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'overview';
		$base_url = add_query_arg( 'view', $role_id, mpp_route_url( 'app/admin/roles' ) );
		$tabs     = array(
			'overview'         => __( 'Overview', 'platform-core' ),
			'permissions'      => __( 'Permissions', 'platform-core' ),
			'users'            => __( 'Users', 'platform-core' ),
			'effective-access' => __( 'Effective Access', 'platform-core' ),
		);

		if ( ! isset( $tabs[ $tab ] ) ) {
			$tab = 'overview';
		}

		$perm_count = count( $this->roles->get_permissions( $role_id ) );
		$user_count = $this->access->count_users_with_role( $role_id );

		$this->set_page_meta(
			array(
				'title'       => $role['name'],
				'description' => $role['description'] ?: $role['slug'],
			)
		);

		if ( function_exists( 'platform_ui_detail_header' ) ) {
			echo platform_ui_detail_header( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				$role['name'],
				$role['description'] ?: $role['slug'],
				array(
					array(
						'label' => __( 'Users', 'platform-core' ),
						'value' => (string) $user_count,
					),
					array(
						'label' => __( 'Permissions', 'platform-core' ),
						'value' => (string) $perm_count,
					),
					array(
						'label' => __( 'Status', 'platform-core' ),
						'value' => $role['status'] ?? 'active',
					),
				)
			);
		}

		$this->echo_back_link( mpp_route_url( 'app/admin/roles' ), __( 'Back to roles', 'platform-core' ) );
		$this->render_admin_tabs( $tabs, $tab, $base_url );

		if ( 'permissions' === $tab ) {
			$this->render_role_permissions_editor( $role_id );
			return;
		}

		if ( 'users' === $tab ) {
			$users = $this->roles->get_users_with_role( $role_id );
			if ( empty( $users ) ) {
				echo '<p>' . esc_html__( 'No users are assigned to this role.', 'platform-core' ) . '</p>';
				return;
			}
			echo '<table class="mpp-admin-table"><thead><tr><th>' . esc_html__( 'User', 'platform-core' ) . '</th><th>' . esc_html__( 'Email', 'platform-core' ) . '</th><th></th></tr></thead><tbody>';
			foreach ( $users as $user ) {
				echo '<tr><td>' . esc_html( $user['display_name'] ) . '</td><td>' . esc_html( $user['email'] ) . '</td>';
				echo '<td><a href="' . esc_url( add_query_arg( 'user_id', $user['id'], mpp_route_url( 'app/admin/users' ) ) ) . '">' . esc_html__( 'View', 'platform-core' ) . '</a></td></tr>';
			}
			echo '</tbody></table>';
			return;
		}

		if ( 'effective-access' === $tab ) {
			$this->render_role_effective_access_tab( $role_id );
			return;
		}

		$perm_count = count( $this->roles->get_permissions( $role_id ) );
		$user_count = $this->access->count_users_with_role( $role_id );
		?>
		<dl class="mpp-profile-list">
			<dt><?php esc_html_e( 'Description', 'platform-core' ); ?></dt><dd><?php echo esc_html( $role['description'] ?: '—' ); ?></dd>
			<dt><?php esc_html_e( 'Status', 'platform-core' ); ?></dt><dd><?php echo esc_html( $role['status'] ?? 'active' ); ?></dd>
			<dt><?php esc_html_e( 'System role', 'platform-core' ); ?></dt><dd><?php echo ! empty( $role['is_system'] ) ? esc_html__( 'Yes', 'platform-core' ) : esc_html__( 'No', 'platform-core' ); ?></dd>
			<dt><?php esc_html_e( 'Permissions', 'platform-core' ); ?></dt><dd><?php echo esc_html( (string) $perm_count ); ?></dd>
			<dt><?php esc_html_e( 'Users', 'platform-core' ); ?></dt><dd><?php echo esc_html( (string) $user_count ); ?></dd>
		</dl>
		<p><a class="mpp-btn mpp-btn--secondary" href="<?php echo esc_url( add_query_arg( 'edit', $role_id, mpp_route_url( 'app/admin/roles' ) ) ); ?>"><?php esc_html_e( 'Edit role', 'platform-core' ); ?></a></p>
		<?php
	}

	/**
	 * Admin settings placeholder.
	 */
	private function render_settings() {
		$summary  = mpp()->get( \MPP\Panels\DashboardService::class )->get_admin_summary();
		$settings = $this->settings->all();
		$roles    = $this->roles->all();
		$section  = isset( $_GET['section'] ) ? sanitize_key( wp_unslash( $_GET['section'] ) ) : 'general';
		$sections = array(
			'general'      => __( 'General', 'platform-core' ),
			'appearance'   => __( 'Appearance', 'platform-core' ),
			'localization' => __( 'Localization', 'platform-core' ),
			'registration' => __( 'Registration', 'platform-core' ),
			'security'     => __( 'Security', 'platform-core' ),
			'system'       => __( 'System', 'platform-core' ),
		);

		if ( ! isset( $sections[ $section ] ) ) {
			$section = 'general';
		}

		$base_url = mpp_route_url( 'app/admin/settings' );
		ob_start();

		if ( 'system' === $section ) {
			$this->render_settings_section_header(
				__( 'System Information', 'platform-core' ),
				__( 'Read-only platform and environment details.', 'platform-core' )
			);
			?>
			<dl class="mpp-profile-list">
				<dt><?php esc_html_e( 'Platform Core Version', 'platform-core' ); ?></dt>
				<dd><?php echo esc_html( $summary['platform_version'] ); ?></dd>
				<dt><?php esc_html_e( 'WordPress Version', 'platform-core' ); ?></dt>
				<dd><?php echo esc_html( $summary['wordpress_version'] ); ?></dd>
				<dt><?php esc_html_e( 'Database Schema', 'platform-core' ); ?></dt>
				<dd><?php echo esc_html( $summary['database_version'] ?: __( 'Not installed', 'platform-core' ) ); ?></dd>
				<dt><?php esc_html_e( 'Routing Mode', 'platform-core' ); ?></dt>
				<dd><?php echo esc_html( $summary['permalink_mode'] ); ?></dd>
			</dl>
			<?php
		} else {
			?>
			<form method="post" class="mpp-form mpp-settings-form">
				<?php echo FormHandler::nonce_field(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<input type="hidden" name="mpp_admin_action" value="save_settings">
				<input type="hidden" name="mpp_redirect" value="<?php echo esc_url( add_query_arg( 'section', $section, $base_url ) ); ?>">

				<?php if ( 'general' === $section ) : ?>
					<?php
					$this->render_settings_section_header(
						__( 'General Settings', 'platform-core' ),
						__( 'Default landing experience after login.', 'platform-core' )
					);
					echo $this->settings_field( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						array(
							'type'        => 'select',
							'name'        => 'default_dashboard',
							'label'       => __( 'Default Dashboard Route', 'platform-core' ),
							'value'       => $settings['general']['default_dashboard'],
							'description' => __( 'Where users are sent after signing in when no specific panel is requested.', 'platform-core' ),
							'options'     => array(
								'app/user'    => __( 'User Panel', 'platform-core' ),
								'app/manager' => __( 'Manager Panel', 'platform-core' ),
								'app/admin'   => __( 'Admin Panel', 'platform-core' ),
							),
						)
					);
					?>
				<?php elseif ( 'appearance' === $section ) : ?>
					<?php
					$this->render_settings_section_header(
						__( 'Appearance', 'platform-core' ),
						__( 'Branding shown in the platform header and navigation.', 'platform-core' )
					);
					echo $this->settings_field( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						array(
							'name'        => 'platform_name',
							'label'       => __( 'Platform Name', 'platform-core' ),
							'value'       => $settings['appearance']['platform_name'],
							'description' => __( 'Displayed in the header logo and page titles.', 'platform-core' ),
						)
					);
					echo $this->settings_field( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						array(
							'name'        => 'logo_mark',
							'label'       => __( 'Logo Mark', 'platform-core' ),
							'value'       => $settings['appearance']['logo_mark'],
							'description' => __( 'Single character shown inside the logo badge.', 'platform-core' ),
							'attributes'  => array(
								'maxlength' => '1',
							),
						)
					);
					echo $this->settings_field( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						array(
							'name'        => 'accent_color',
							'label'       => __( 'Accent Color', 'platform-core' ),
							'value'       => $settings['appearance']['accent_color'],
							'description' => __( 'Optional hex color (e.g. #0f172a). Leave empty to use the theme default.', 'platform-core' ),
							'attributes'  => array(
								'placeholder' => '#0f172a',
							),
						)
					);
					?>
				<?php elseif ( 'registration' === $section ) : ?>
					<?php
					$this->render_settings_section_header(
						__( 'Registration', 'platform-core' ),
						__( 'Control public sign-up and default role assignment.', 'platform-core' )
					);
					echo $this->settings_field( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						array(
							'type'        => 'checkbox',
							'name'        => 'registration_enabled',
							'label'       => __( 'Enable public registration', 'platform-core' ),
							'value'       => $settings['registration']['enabled'],
							'description' => __( 'Allow new users to create accounts from the registration page.', 'platform-core' ),
						)
					);

					$role_options = array();
					foreach ( $roles as $role ) {
						$role_options[ $role['slug'] ] = $role['name'];
					}

					echo $this->settings_field( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						array(
							'type'        => 'select',
							'name'        => 'default_platform_role',
							'label'       => __( 'Default Platform Role', 'platform-core' ),
							'value'       => $settings['registration']['default_platform_role'],
							'description' => __( 'Assigned automatically to newly registered users.', 'platform-core' ),
							'options'     => $role_options,
						)
					);
					?>
				<?php elseif ( 'security' === $section ) : ?>
					<?php
					$this->render_settings_section_header(
						__( 'Security', 'platform-core' ),
						__( 'Session and access-related platform defaults.', 'platform-core' )
					);
					echo $this->settings_field( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						array(
							'type'        => 'number',
							'name'        => 'session_remember_days',
							'label'       => __( 'Remember Me Duration (days)', 'platform-core' ),
							'value'       => (string) $settings['security']['session_remember_days'],
							'description' => __( 'WordPress administrators with manage_options receive effective platform_admin access for core permissions.', 'platform-core' ),
							'attributes'  => array(
								'min' => '1',
								'max' => '365',
							),
						)
					);
					?>
				<?php else : ?>
					<?php
					$this->render_settings_section_header(
						__( 'Localization', 'platform-core' ),
						__( 'Date, time, and locale preferences for the platform UI.', 'platform-core' )
					);
					echo $this->settings_field( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						array(
							'name'        => 'date_format',
							'label'       => __( 'Date Format', 'platform-core' ),
							'value'       => $settings['localization']['date_format'],
							'description' => __( 'PHP date format string used in platform views.', 'platform-core' ),
						)
					);
					echo $this->settings_field( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						array(
							'type'        => 'select',
							'name'        => 'timezone',
							'label'       => __( 'Timezone', 'platform-core' ),
							'value'       => $settings['localization']['timezone'],
							'description' => __( 'Used for platform timestamps. Overrides the WordPress site timezone when set.', 'platform-core' ),
							'options'     => $this->get_timezone_options( $settings['localization']['timezone'] ),
						)
					);
					?>
					<dl class="mpp-profile-list mpp-profile-list--compact">
						<dt><?php esc_html_e( 'Text Direction', 'platform-core' ); ?></dt>
						<dd><?php echo is_rtl() ? esc_html__( 'Right-to-left (RTL)', 'platform-core' ) : esc_html__( 'Left-to-right (LTR)', 'platform-core' ); ?></dd>
						<dt><?php esc_html_e( 'Locale', 'platform-core' ); ?></dt>
						<dd><?php echo esc_html( get_locale() ); ?></dd>
					</dl>
				<?php endif; ?>

				<div class="mpp-form-actions">
					<button type="submit" class="mpp-btn mpp-btn--primary"><?php esc_html_e( 'Save Changes', 'platform-core' ); ?></button>
				</div>
			</form>
			<?php
		}

		$content = ob_get_clean();

		if ( function_exists( 'platform_ui_settings_layout' ) ) {
			echo platform_ui_settings_layout( $sections, $section, $base_url, $content ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			return;
		}

		echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Render a settings section heading.
	 *
	 * @param string $title       Section title.
	 * @param string $description Section description.
	 */
	private function render_settings_section_header( $title, $description = '' ) {
		?>
		<header class="mpp-settings-section__header">
			<h2 class="mpp-settings-section__title"><?php echo esc_html( $title ); ?></h2>
			<?php if ( $description ) : ?>
				<p class="mpp-settings-section__description mpp-muted"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>
		</header>
		<?php
	}

	/**
	 * Render a settings form field via the theme design system.
	 *
	 * @param array<string, mixed> $args Field arguments.
	 * @return string
	 */
	private function settings_field( array $args ) {
		if ( function_exists( 'platform_ui_form_field' ) ) {
			return platform_ui_form_field( $args );
		}

		$name  = $args['name'] ?? '';
		$label = $args['label'] ?? '';
		$value = $args['value'] ?? '';

		return sprintf(
			'<label for="%1$s">%2$s</label><input type="text" id="%1$s" name="%1$s" value="%3$s">',
			esc_attr( $name ),
			esc_html( $label ),
			esc_attr( (string) $value )
		);
	}

	/**
	 * Build timezone select options.
	 *
	 * @param string $selected Selected timezone.
	 * @return array<string, string>
	 */
	private function get_timezone_options( $selected ) {
		$options = array(
			'' => __( 'Use WordPress default', 'platform-core' ),
		);

		foreach ( timezone_identifiers_list() as $timezone ) {
			$options[ $timezone ] = str_replace( '_', ' ', $timezone );
		}

		if ( $selected && ! isset( $options[ $selected ] ) ) {
			$options[ $selected ] = $selected;
		}

		return $options;
	}

	/**
	 * Render user roles management tab.
	 *
	 * @param int                  $user_id User ID.
	 * @param array<string, mixed> $user    User record.
	 */
	private function render_user_roles_tab( $user_id, array $user ) {
		$all_roles = $this->roles->all();
		$assigned  = wp_list_pluck( $user['platform_roles'], 'id' );
		$redirect  = add_query_arg(
			array(
				'user_id' => $user_id,
				'tab'     => 'roles',
			),
			mpp_route_url( 'app/admin/users' )
		);
		?>
		<?php if ( empty( $user['platform_roles'] ) ) : ?>
			<p><?php esc_html_e( 'No platform roles assigned yet.', 'platform-core' ); ?></p>
		<?php endif; ?>
		<ul class="mpp-admin-list">
			<?php foreach ( $user['platform_roles'] as $role ) : ?>
				<li class="mpp-admin-list__item--chip">
					<?php $this->render_platform_role_chips( array( $role ) ); ?>
					<form method="post" class="mpp-inline-form">
						<?php echo FormHandler::nonce_field(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<input type="hidden" name="mpp_admin_action" value="revoke_user_role">
						<input type="hidden" name="user_id" value="<?php echo esc_attr( (string) $user_id ); ?>">
						<input type="hidden" name="role_id" value="<?php echo esc_attr( (string) $role['id'] ); ?>">
						<input type="hidden" name="mpp_redirect" value="<?php echo esc_url( $redirect ); ?>">
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
			<input type="hidden" name="mpp_redirect" value="<?php echo esc_url( $redirect ); ?>">
			<label for="mpp-assign-role-select"><?php esc_html_e( 'Platform role', 'platform-core' ); ?></label>
			<select id="mpp-assign-role-select" name="role_id" required aria-label="<?php esc_attr_e( 'Select role to assign', 'platform-core' ); ?>">
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
	 * Render granted permissions for a user.
	 *
	 * @param int $user_id User ID.
	 */
	private function render_user_permissions_tab( $user_id ) {
		$access_rows = $this->access->explain_user_access( $user_id );
		?>
		<p class="mpp-muted"><?php esc_html_e( 'Permissions granted to this user through assigned roles.', 'platform-core' ); ?></p>
		<div class="mpp-table-wrap">
		<table class="mpp-admin-table mpp-admin-table--stack">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Permission', 'platform-core' ); ?></th>
					<th><?php esc_html_e( 'Source', 'platform-core' ); ?></th>
					<th><?php esc_html_e( 'Scope', 'platform-core' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $access_rows as $row ) : ?>
					<?php if ( empty( $row['granted'] ) ) { continue; } ?>
					<tr>
						<td data-label="<?php esc_attr_e( 'Permission', 'platform-core' ); ?>"><code><?php echo esc_html( $row['permission_key'] ); ?></code></td>
						<td data-label="<?php esc_attr_e( 'Source', 'platform-core' ); ?>"><?php echo esc_html( $this->format_access_source( $row['sources'][0] ?? array() ) ); ?></td>
						<td data-label="<?php esc_attr_e( 'Scope', 'platform-core' ); ?>"><?php echo esc_html( $row['sources'][0]['scope_label'] ?? '—' ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		</div>
		<?php
	}

	/**
	 * Render effective access explanation for a user.
	 *
	 * @param int $user_id User ID.
	 */
	private function render_user_effective_access_tab( $user_id ) {
		$access_rows   = $this->access->explain_user_access( $user_id );
		$granted_count = 0;
		foreach ( $access_rows as $row ) {
			if ( ! empty( $row['granted'] ) ) {
				$granted_count++;
			}
		}
		?>
		<p class="mpp-muted"><?php esc_html_e( 'A permission alone does not create access. Effective access is calculated from roles, permissions, and scope.', 'platform-core' ); ?></p>
		<div class="mpp-admin-stats">
			<div class="mpp-stat-card"><span class="mpp-stat-card__label"><?php esc_html_e( 'Granted', 'platform-core' ); ?></span><span class="mpp-stat-card__value"><?php echo esc_html( (string) $granted_count ); ?></span></div>
			<div class="mpp-stat-card"><span class="mpp-stat-card__label"><?php esc_html_e( 'Total Permissions', 'platform-core' ); ?></span><span class="mpp-stat-card__value"><?php echo esc_html( (string) count( $access_rows ) ); ?></span></div>
		</div>
		<div class="mpp-table-wrap">
		<table class="mpp-admin-table mpp-admin-table--stack">
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
					<tr>
						<td data-label="<?php esc_attr_e( 'Permission', 'platform-core' ); ?>"><code><?php echo esc_html( $row['permission_key'] ); ?></code></td>
						<td data-label="<?php esc_attr_e( 'Module', 'platform-core' ); ?>"><?php echo esc_html( $this->get_module_group_label( $row['module'] ?? '' ) ); ?></td>
						<td data-label="<?php esc_attr_e( 'Status', 'platform-core' ); ?>"><span class="mpp-badge <?php echo ! empty( $row['granted'] ) ? 'mpp-badge--success' : ''; ?>"><?php echo ! empty( $row['granted'] ) ? esc_html__( 'Granted', 'platform-core' ) : esc_html__( 'Denied', 'platform-core' ); ?></span></td>
						<td data-label="<?php esc_attr_e( 'Source', 'platform-core' ); ?>"><?php echo esc_html( $this->format_access_source( $row['sources'][0] ?? array() ) ); ?></td>
						<td data-label="<?php esc_attr_e( 'Scope', 'platform-core' ); ?>"><?php echo esc_html( $row['sources'][0]['scope_label'] ?? '—' ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		</div>
		<?php
	}

	/**
	 * Render user security tab.
	 *
	 * @param array<string, mixed> $user     User record.
	 * @param array<int, string>     $wp_roles WordPress roles.
	 */
	private function render_user_security_tab( array $user, array $wp_roles ) {
		?>
		<dl class="mpp-profile-list">
			<dt><?php esc_html_e( 'Username', 'platform-core' ); ?></dt><dd><?php echo esc_html( $user['username'] ); ?></dd>
			<dt><?php esc_html_e( 'Status', 'platform-core' ); ?></dt><dd><?php echo esc_html( $user['status'] ); ?></dd>
			<dt><?php esc_html_e( 'WordPress Role', 'platform-core' ); ?></dt><dd><?php echo esc_html( ! empty( $wp_roles ) ? implode( ', ', $wp_roles ) : '—' ); ?></dd>
		</dl>
		<p class="mpp-muted"><?php esc_html_e( 'Password changes are managed through the WordPress user profile.', 'platform-core' ); ?></p>
		<?php
	}

	/**
	 * Render user activity tab.
	 *
	 * @param int $user_id User ID.
	 */
	private function render_user_activity_tab( $user_id ) {
		$entries = $this->audit->query(
			array(
				'user_id' => $user_id,
				'limit'   => 25,
			)
		);

		if ( empty( $entries ) ) {
			echo '<p>' . esc_html__( 'No activity recorded for this user yet.', 'platform-core' ) . '</p>';
			return;
		}
		?>
		<ul class="mpp-activity-list">
			<?php foreach ( $entries as $entry ) : ?>
				<li>
					<code><?php echo esc_html( $entry['action'] ); ?></code>
					<span class="mpp-muted"><?php echo esc_html( $entry['created_at'] ); ?></span>
					<?php if ( ! empty( $entry['object_type'] ) ) : ?>
						<span><?php echo esc_html( $entry['object_type'] . ( $entry['object_id'] ? ':' . $entry['object_id'] : '' ) ); ?></span>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php
	}

	/**
	 * Set admin shell header actions.
	 *
	 * @param string $html Actions HTML.
	 */
	private function set_page_actions( $html ) {
		if ( function_exists( 'mpp_set_admin_page_actions' ) ) {
			mpp_set_admin_page_actions( $html );
		}
	}

	/**
	 * Override admin shell page meta.
	 *
	 * @param array<string, string> $meta Meta overrides.
	 */
	private function set_page_meta( array $meta ) {
		if ( function_exists( 'mpp_set_admin_page_meta' ) ) {
			mpp_set_admin_page_meta( $meta );
		}
	}

	/**
	 * Render a back navigation link.
	 *
	 * @param string $url   URL.
	 * @param string $label Label.
	 */
	private function echo_back_link( $url, $label ) {
		if ( function_exists( 'platform_ui_back_link' ) ) {
			echo platform_ui_back_link( $url, $label ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			return;
		}

		echo '<p><a href="' . esc_url( $url ) . '">&larr; ' . esc_html( $label ) . '</a></p>';
	}

	/**
	 * Render linked platform role chips.
	 *
	 * @param array<int, array<string, mixed>> $roles Platform roles.
	 */
	private function render_platform_role_chips( array $roles ) {
		if ( empty( $roles ) ) {
			echo '<span class="mpp-muted">—</span>';
			return;
		}

		echo '<span class="mpp-chip-list">';
		foreach ( $roles as $role ) {
			$url = add_query_arg( 'view', $role['id'], mpp_route_url( 'app/admin/roles' ) );
			if ( function_exists( 'platform_ui_chip' ) ) {
				echo platform_ui_chip( $role['name'], $url ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			} else {
				echo '<a class="mpp-chip" href="' . esc_url( $url ) . '">' . esc_html( $role['name'] ) . '</a>';
			}
		}
		echo '</span>';
	}

	/**
	 * Format an access source row for display.
	 *
	 * @param array<string, mixed> $source Source metadata.
	 * @return string
	 */
	private function format_access_source( array $source ) {
		if ( empty( $source ) ) {
			return '—';
		}

		$type = $source['type'] ?? 'role';
		$name = $source['role_name'] ?? '—';

		if ( 'effective_admin' === $type ) {
			return sprintf(
				/* translators: %s: role name */
				__( '%s (effective)', 'platform-core' ),
				$name
			);
		}

		return sprintf(
			/* translators: 1: source type label, 2: role name */
			__( '%1$s: %2$s', 'platform-core' ),
			__( 'Role', 'platform-core' ),
			$name
		);
	}
}
