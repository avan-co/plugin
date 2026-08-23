<?php
/**
 * Reusable UI component helpers.
 *
 * @package PlatformTheme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Render a button or link styled as a button.
 *
 * @param array<string, mixed> $args Arguments.
 * @return string
 */
function platform_ui_button( array $args ) {
	$args = wp_parse_args(
		$args,
		array(
			'tag'     => 'a',
			'label'   => '',
			'url'     => '#',
			'variant' => 'primary',
			'size'    => '',
			'class'   => '',
			'attrs'   => array(),
		)
	);

	$classes = array( 'mpp-btn', 'mpp-btn--' . sanitize_html_class( $args['variant'] ) );

	if ( $args['size'] ) {
		$classes[] = 'mpp-btn--' . sanitize_html_class( $args['size'] );
	}

	if ( $args['class'] ) {
		$classes[] = $args['class'];
	}

	$attr_html = '';

	foreach ( $args['attrs'] as $key => $value ) {
		$attr_html .= sprintf( ' %s="%s"', esc_attr( $key ), esc_attr( (string) $value ) );
	}

	if ( 'button' === $args['tag'] ) {
		return sprintf(
			'<button type="%s" class="%s"%s>%s</button>',
			esc_attr( $args['type'] ?? 'button' ),
			esc_attr( implode( ' ', $classes ) ),
			$attr_html,
			esc_html( $args['label'] )
		);
	}

	return sprintf(
		'<a href="%s" class="%s"%s>%s</a>',
		esc_url( $args['url'] ),
		esc_attr( implode( ' ', $classes ) ),
		$attr_html,
		esc_html( $args['label'] )
	);
}

/**
 * Render page header with optional breadcrumb.
 *
 * @param string               $title       Page title.
 * @param string               $description Optional description.
 * @param array<int, string>   $breadcrumb  Breadcrumb labels.
 * @param string               $actions_html Optional action buttons HTML.
 */
function platform_ui_page_header( $title, $description = '', array $breadcrumb = array(), $actions_html = '' ) {
	?>
	<header class="mpp-page-header">
		<?php if ( ! empty( $breadcrumb ) ) : ?>
			<ol class="mpp-page-header__breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'platform-theme' ); ?>">
				<?php foreach ( $breadcrumb as $crumb ) : ?>
					<li><?php echo esc_html( $crumb ); ?></li>
				<?php endforeach; ?>
			</ol>
		<?php endif; ?>
		<h1 class="mpp-page-header__title"><?php echo esc_html( $title ); ?></h1>
		<?php if ( $description ) : ?>
			<p class="mpp-page-header__description"><?php echo esc_html( $description ); ?></p>
		<?php endif; ?>
		<?php if ( $actions_html ) : ?>
			<div class="mpp-page-header__actions"><?php echo $actions_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
		<?php endif; ?>
	</header>
	<?php
}

/**
 * Render an alert.
 *
 * @param string $message Message.
 * @param string $type    Alert type.
 */
function platform_ui_alert( $message, $type = 'info' ) {
	printf(
		'<div class="mpp-alert mpp-alert--%s" role="alert">%s</div>',
		esc_attr( sanitize_html_class( $type ) ),
		esc_html( $message )
	);
}

/**
 * Render an empty state.
 *
 * @param string $title       Title.
 * @param string $description Description.
 */
function platform_ui_empty_state( $title, $description = '' ) {
	?>
	<div class="mpp-empty-state">
		<h3 class="mpp-empty-state__title"><?php echo esc_html( $title ); ?></h3>
		<?php if ( $description ) : ?>
			<p><?php echo esc_html( $description ); ?></p>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Render user avatar markup.
 *
 * @param int $user_id User ID.
 * @return string
 */
function platform_ui_avatar( $user_id ) {
	$user_id = (int) $user_id;
	$avatar  = get_avatar( $user_id, 32, '', '', array( 'class' => 'mpp-avatar__img' ) );

	if ( $avatar ) {
		return '<span class="mpp-avatar">' . $avatar . '</span>';
	}

	$user = get_userdata( $user_id );
	$initial = $user ? strtoupper( substr( $user->user_login, 0, 1 ) ) : '?';

	return '<span class="mpp-avatar" aria-hidden="true">' . esc_html( $initial ) . '</span>';
}

/**
 * Get admin breadcrumb labels for a page slug.
 *
 * @param string $page Admin page slug.
 * @return array<int, string>
 */
function platform_admin_breadcrumb( $page ) {
	$labels = array(
		'dashboard'   => __( 'Dashboard', 'platform-theme' ),
		'users'       => __( 'Users', 'platform-theme' ),
		'roles'       => __( 'Roles', 'platform-theme' ),
		'permissions' => __( 'Permissions', 'platform-theme' ),
		'modules'     => __( 'Modules', 'platform-theme' ),
		'acl'         => __( 'ACL', 'platform-theme' ),
		'settings'    => __( 'Settings', 'platform-theme' ),
	);

	return array(
		__( 'Admin', 'platform-theme' ),
		$labels[ $page ] ?? ucfirst( $page ),
	);
}
