<?php
/**
 * Reusable UI components.
 *
 * @package PlatformTheme
 */

namespace PlatformTheme\DesignSystem;

defined( 'ABSPATH' ) || exit;

/**
 * Static helpers for consistent panel UI markup.
 */
final class UIComponents {

	/**
	 * Render a button or link styled as a button.
	 *
	 * @param array<string, mixed> $args Component arguments.
	 * @return string
	 */
	public static function button( array $args ) {
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
	 * Render page header with breadcrumb and optional actions.
	 *
	 * @param string               $title         Page title.
	 * @param string               $description   Optional description.
	 * @param array<int, string>   $breadcrumb    Breadcrumb labels.
	 * @param string               $actions_html  Optional action buttons HTML.
	 */
	public static function page_header( $title, $description = '', array $breadcrumb = array(), $actions_html = '' ) {
		?>
		<header class="mpp-page-header">
			<?php if ( ! empty( $breadcrumb ) ) : ?>
				<ol class="mpp-page-header__breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'platform-theme' ); ?>">
					<?php foreach ( $breadcrumb as $crumb ) : ?>
						<li><?php echo esc_html( $crumb ); ?></li>
					<?php endforeach; ?>
				</ol>
			<?php endif; ?>
			<div class="mpp-page-header__row">
				<div class="mpp-page-header__content">
					<h1 class="mpp-page-header__title"><?php echo esc_html( $title ); ?></h1>
					<?php if ( $description ) : ?>
						<p class="mpp-page-header__description"><?php echo esc_html( $description ); ?></p>
					<?php endif; ?>
				</div>
				<?php if ( $actions_html ) : ?>
					<div class="mpp-page-header__actions"><?php echo $actions_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
				<?php endif; ?>
			</div>
		</header>
		<?php
	}

	/**
	 * Render an alert banner.
	 *
	 * @param string $message Message text.
	 * @param string $type    Alert type.
	 */
	public static function alert( $message, $type = 'info' ) {
		printf(
			'<div class="mpp-alert mpp-alert--%s" role="alert">%s</div>',
			esc_attr( sanitize_html_class( $type ) ),
			esc_html( $message )
		);
	}

	/**
	 * Render an empty state block.
	 *
	 * @param string $title       Title.
	 * @param string $description Description.
	 */
	public static function empty_state( $title, $description = '' ) {
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
	 * @param int $size    Avatar size in pixels.
	 * @return string
	 */
	public static function avatar( $user_id, $size = 32 ) {
		$user_id = (int) $user_id;
		$size    = max( 24, (int) $size );
		$avatar  = get_avatar( $user_id, $size, '', '', array( 'class' => 'mpp-avatar__img' ) );

		if ( $avatar ) {
			return '<span class="mpp-avatar" style="width:' . esc_attr( (string) $size ) . 'px;height:' . esc_attr( (string) $size ) . 'px">' . $avatar . '</span>';
		}

		$user    = get_userdata( $user_id );
		$initial = $user ? strtoupper( substr( $user->user_login, 0, 1 ) ) : '?';

		return '<span class="mpp-avatar" aria-hidden="true" style="width:' . esc_attr( (string) $size ) . 'px;height:' . esc_attr( (string) $size ) . 'px">' . esc_html( $initial ) . '</span>';
	}

	/**
	 * Render a responsive stats grid.
	 *
	 * @param array<int, array<string, string>> $items Stat cards.
	 * @return string
	 */
	public static function stat_grid( array $items ) {
		if ( empty( $items ) ) {
			return '';
		}

		ob_start();
		?>
		<div class="mpp-stats">
			<?php foreach ( $items as $item ) : ?>
				<div class="mpp-stat-card">
					<span class="mpp-stat-card__label"><?php echo esc_html( $item['label'] ?? '' ); ?></span>
					<span class="mpp-stat-card__value"><?php echo esc_html( $item['value'] ?? '—' ); ?></span>
					<?php if ( ! empty( $item['hint'] ) ) : ?>
						<span class="mpp-stat-card__hint"><?php echo esc_html( $item['hint'] ); ?></span>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Render a titled dashboard section.
	 *
	 * @param string $title   Section title.
	 * @param string $content Inner HTML (already escaped by caller).
	 * @param string $actions Optional action buttons HTML.
	 * @return string
	 */
	public static function section( $title, $content, $actions = '' ) {
		ob_start();
		?>
		<section class="mpp-panel-section">
			<div class="mpp-panel-section__header">
				<h2 class="mpp-panel-section__title"><?php echo esc_html( $title ); ?></h2>
				<?php if ( $actions ) : ?>
					<div class="mpp-panel-section__actions"><?php echo $actions; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
				<?php endif; ?>
			</div>
			<div class="mpp-panel-section__body">
				<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		</section>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Render quick action button row.
	 *
	 * @param array<int, array<string, mixed>> $actions Button definitions.
	 * @return string
	 */
	public static function quick_actions( array $actions ) {
		if ( empty( $actions ) ) {
			return '';
		}

		ob_start();
		echo '<div class="mpp-quick-actions">';
		foreach ( $actions as $action ) {
			echo self::button( $action ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		echo '</div>';
		return (string) ob_get_clean();
	}

	/**
	 * Render a placeholder card for upcoming features.
	 *
	 * @param string $title       Section title.
	 * @param string $description Section description.
	 * @param string $cta_label   Optional CTA label.
	 * @param string $cta_url     Optional CTA URL.
	 */
	public static function placeholder_section( $title, $description, $cta_label = '', $cta_url = '' ) {
		?>
		<section class="mpp-card mpp-placeholder-section">
			<h3 class="mpp-placeholder-section__title"><?php echo esc_html( $title ); ?></h3>
			<p class="mpp-muted"><?php echo esc_html( $description ); ?></p>
			<?php if ( $cta_label && $cta_url ) : ?>
				<p><?php echo self::button( array( 'label' => $cta_label, 'url' => $cta_url, 'variant' => 'secondary' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
			<?php endif; ?>
		</section>
		<?php
	}
}
