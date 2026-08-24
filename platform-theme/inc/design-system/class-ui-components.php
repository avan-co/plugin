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
		$type = sanitize_html_class( $type );
		$role = in_array( $type, array( 'error', 'warning' ), true ) ? 'alert' : 'status';

		printf(
			'<div class="mpp-alert mpp-alert--%s" role="%s"%s>%s</div>',
			esc_attr( $type ),
			esc_attr( $role ),
			'status' === $role ? ' aria-live="polite"' : '',
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
	 * @param string                            $class Optional extra class names.
	 * @return string
	 */
	public static function stat_grid( array $items, $class = '' ) {
		if ( empty( $items ) ) {
			return '';
		}

		$classes = trim( 'mpp-stats ' . $class );

		ob_start();
		?>
		<div class="<?php echo esc_attr( $classes ); ?>">
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
	 * Render a dashboard welcome header.
	 *
	 * @param string $name        User display name.
	 * @param string $description Optional subtitle.
	 * @return string
	 */
	public static function dashboard_welcome( $name, $description = '' ) {
		ob_start();
		?>
		<header class="mpp-dashboard-welcome">
			<h1 class="mpp-dashboard-welcome__title"><?php echo esc_html( sprintf( __( 'Welcome back, %s', 'platform-theme' ), $name ) ); ?></h1>
			<?php if ( $description ) : ?>
				<p class="mpp-dashboard-welcome__description mpp-muted"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>
		</header>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Render a grid of module shortcut links.
	 *
	 * @param array<int, array<string, string>> $shortcuts Shortcut definitions.
	 * @return string
	 */
	public static function module_shortcut_grid( array $shortcuts ) {
		if ( empty( $shortcuts ) ) {
			ob_start();
			self::empty_state(
				__( 'No modules available', 'platform-theme' ),
				__( 'Installed modules with routes you can access will appear here.', 'platform-theme' )
			);
			return (string) ob_get_clean();
		}

		ob_start();
		?>
		<div class="mpp-module-shortcut-grid">
			<?php foreach ( $shortcuts as $shortcut ) : ?>
				<a class="mpp-module-shortcut" href="<?php echo esc_url( $shortcut['url'] ); ?>">
					<span class="mpp-module-shortcut__icon" aria-hidden="true"><?php echo esc_html( $shortcut['icon'] ?? mb_strtoupper( mb_substr( $shortcut['label'], 0, 1 ) ) ); ?></span>
					<span class="mpp-module-shortcut__label"><?php echo esc_html( $shortcut['label'] ); ?></span>
					<?php if ( ! empty( $shortcut['description'] ) ) : ?>
						<span class="mpp-module-shortcut__description mpp-muted"><?php echo esc_html( $shortcut['description'] ); ?></span>
					<?php endif; ?>
				</a>
			<?php endforeach; ?>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Render a pending items list for manager dashboards.
	 *
	 * @param array<int, array<string, string>> $items Pending items.
	 * @return string
	 */
	public static function pending_list( array $items ) {
		if ( empty( $items ) ) {
			ob_start();
			self::empty_state(
				__( 'No pending items', 'platform-theme' ),
				__( 'Open tasks from installed modules will appear here.', 'platform-theme' )
			);
			return (string) ob_get_clean();
		}

		ob_start();
		?>
		<ul class="mpp-pending-list">
			<?php foreach ( $items as $item ) : ?>
				<li class="mpp-pending-list__item">
					<div class="mpp-pending-list__content">
						<strong class="mpp-pending-list__title"><?php echo esc_html( $item['title'] ?? '' ); ?></strong>
						<?php if ( ! empty( $item['description'] ) ) : ?>
							<p class="mpp-pending-list__description mpp-muted"><?php echo esc_html( $item['description'] ); ?></p>
						<?php endif; ?>
					</div>
					<?php if ( ! empty( $item['url'] ) ) : ?>
						<?php echo self::button( array( 'label' => $item['action_label'] ?? __( 'Review', 'platform-theme' ), 'url' => $item['url'], 'variant' => 'secondary', 'size' => 'sm' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Render a recent activity list.
	 *
	 * @param array<int, array<string, mixed>> $entries Activity entries.
	 * @return string
	 */
	public static function activity_list( array $entries ) {
		if ( empty( $entries ) ) {
			ob_start();
			self::empty_state(
				__( 'No activity yet', 'platform-theme' ),
				__( 'Your recent platform actions will appear here.', 'platform-theme' )
			);
			return (string) ob_get_clean();
		}

		ob_start();
		?>
		<ul class="mpp-activity-list">
			<?php foreach ( $entries as $entry ) : ?>
				<li class="mpp-activity-list__item">
					<div class="mpp-activity-list__main">
						<code class="mpp-activity-list__action"><?php echo esc_html( $entry['action'] ?? '' ); ?></code>
						<?php if ( ! empty( $entry['object_type'] ) ) : ?>
							<span class="mpp-activity-list__object"><?php echo esc_html( $entry['object_type'] . ( ! empty( $entry['object_id'] ) ? ':' . $entry['object_id'] : '' ) ); ?></span>
						<?php endif; ?>
					</div>
					<time class="mpp-activity-list__time mpp-muted" datetime="<?php echo esc_attr( $entry['created_at'] ?? '' ); ?>">
						<?php echo esc_html( $entry['created_at'] ?? '' ); ?>
					</time>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php
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

	/**
	 * Render tab navigation.
	 *
	 * @param array<string, string> $tabs       Tab slug => label.
	 * @param string                $current    Active tab slug.
	 * @param string                $base_url   Base URL for tab links.
	 * @param string                $query_arg  Query argument name.
	 * @return string
	 */
	public static function tabs( array $tabs, $current, $base_url, $query_arg = 'tab' ) {
		if ( empty( $tabs ) ) {
			return '';
		}

		ob_start();
		?>
		<nav class="mpp-tabs" aria-label="<?php esc_attr_e( 'Section navigation', 'platform-theme' ); ?>">
			<ul class="mpp-tabs__list" role="tablist">
				<?php foreach ( $tabs as $slug => $label ) : ?>
					<?php
					$url       = add_query_arg( $query_arg, $slug, $base_url );
					$is_active = $current === $slug;
					?>
					<li class="mpp-tabs__item<?php echo $is_active ? ' is-active' : ''; ?>" role="presentation">
						<a href="<?php echo esc_url( $url ); ?>" role="tab"<?php echo $is_active ? ' aria-current="page"' : ''; ?>>
							<?php echo esc_html( $label ); ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</nav>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Render a detail page header with optional meta stats.
	 *
	 * @param string                              $title    Primary title.
	 * @param string                              $subtitle Optional subtitle.
	 * @param array<int, array<string, string>>   $meta     Stat items with label/value keys.
	 * @param string                              $leading  Optional leading HTML (e.g. avatar).
	 * @return string
	 */
	public static function detail_header( $title, $subtitle = '', array $meta = array(), $leading = '' ) {
		ob_start();
		?>
		<div class="mpp-detail-header mpp-card">
			<div class="mpp-detail-header__main">
				<?php if ( $leading ) : ?>
					<div class="mpp-detail-header__leading"><?php echo $leading; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
				<?php endif; ?>
				<div class="mpp-detail-header__content">
					<h2 class="mpp-detail-header__title"><?php echo esc_html( $title ); ?></h2>
					<?php if ( $subtitle ) : ?>
						<p class="mpp-detail-header__subtitle mpp-muted"><?php echo esc_html( $subtitle ); ?></p>
					<?php endif; ?>
				</div>
			</div>
			<?php if ( ! empty( $meta ) ) : ?>
				<dl class="mpp-detail-header__meta">
					<?php foreach ( $meta as $item ) : ?>
						<div class="mpp-detail-header__meta-item">
							<dt><?php echo esc_html( $item['label'] ?? '' ); ?></dt>
							<dd><?php echo esc_html( $item['value'] ?? '—' ); ?></dd>
						</div>
					<?php endforeach; ?>
				</dl>
			<?php endif; ?>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Render a back navigation link.
	 *
	 * @param string $url   Destination URL.
	 * @param string $label Link label.
	 * @return string
	 */
	public static function back_link( $url, $label ) {
		return sprintf(
			'<p class="mpp-back-link"><a href="%s">&larr; %s</a></p>',
			esc_url( $url ),
			esc_html( $label )
		);
	}

	/**
	 * Render a filter/search toolbar.
	 *
	 * @param string                            $action Form action URL.
	 * @param array<int, array<string, mixed>>  $fields Field definitions.
	 * @return string
	 */
	public static function filter_bar( $action, array $fields ) {
		if ( empty( $fields ) ) {
			return '';
		}

		ob_start();
		?>
		<form method="get" action="<?php echo esc_url( $action ); ?>" class="mpp-filter-bar">
			<?php foreach ( $fields as $field ) : ?>
				<?php
				$type  = $field['type'] ?? 'search';
				$name  = $field['name'] ?? '';
				$label = $field['label'] ?? '';
				$value = $field['value'] ?? '';
				?>
				<?php if ( 'select' === $type ) : ?>
					<label class="mpp-filter-bar__field">
						<?php if ( $label ) : ?>
							<span class="screen-reader-text"><?php echo esc_html( $label ); ?></span>
						<?php endif; ?>
						<select name="<?php echo esc_attr( $name ); ?>" class="mpp-select">
							<?php foreach ( (array) ( $field['options'] ?? array() ) as $option_value => $option_label ) : ?>
								<option value="<?php echo esc_attr( (string) $option_value ); ?>" <?php selected( (string) $value, (string) $option_value ); ?>>
									<?php echo esc_html( $option_label ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</label>
				<?php else : ?>
					<label class="mpp-filter-bar__field mpp-filter-bar__field--grow">
						<?php if ( $label ) : ?>
							<span class="screen-reader-text"><?php echo esc_html( $label ); ?></span>
						<?php endif; ?>
						<input
							type="<?php echo esc_attr( $type ); ?>"
							name="<?php echo esc_attr( $name ); ?>"
							value="<?php echo esc_attr( (string) $value ); ?>"
							placeholder="<?php echo esc_attr( $field['placeholder'] ?? '' ); ?>"
							class="mpp-input"
						>
					</label>
				<?php endif; ?>
			<?php endforeach; ?>
			<button type="submit" class="mpp-btn mpp-btn--secondary"><?php esc_html_e( 'Filter', 'platform-theme' ); ?></button>
		</form>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Render a settings layout with section sidebar.
	 *
	 * @param array<string, string> $sections Section slug => label.
	 * @param string                $current  Active section slug.
	 * @param string                $base_url Base URL for section links.
	 * @param string                $content  Section content HTML.
	 * @param string                $query_arg Query argument name.
	 * @return string
	 */
	public static function settings_layout( array $sections, $current, $base_url, $content, $query_arg = 'section' ) {
		ob_start();
		?>
		<div class="mpp-settings-layout">
			<nav class="mpp-settings-layout__nav" aria-label="<?php esc_attr_e( 'Settings sections', 'platform-theme' ); ?>">
				<label class="mpp-settings-layout__mobile-label" for="mpp-settings-section-select"><?php esc_html_e( 'Settings section', 'platform-theme' ); ?></label>
				<select id="mpp-settings-section-select" class="mpp-settings-layout__select mpp-select" data-settings-nav>
					<?php foreach ( $sections as $slug => $label ) : ?>
						<option value="<?php echo esc_url( add_query_arg( $query_arg, $slug, $base_url ) ); ?>" <?php selected( $current, $slug ); ?>>
							<?php echo esc_html( $label ); ?>
						</option>
					<?php endforeach; ?>
				</select>
				<ul class="mpp-settings-layout__list">
					<?php foreach ( $sections as $slug => $label ) : ?>
						<?php $is_active = $current === $slug; ?>
						<li class="mpp-settings-layout__item<?php echo $is_active ? ' is-active' : ''; ?>">
							<a href="<?php echo esc_url( add_query_arg( $query_arg, $slug, $base_url ) ); ?>"<?php echo $is_active ? ' aria-current="page"' : ''; ?>>
								<?php echo esc_html( $label ); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</nav>
			<div class="mpp-settings-layout__content">
				<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Render a labeled form field.
	 *
	 * @param array<string, mixed> $args Field arguments.
	 * @return string
	 */
	public static function form_field( array $args ) {
		$type        = $args['type'] ?? 'text';
		$id          = $args['id'] ?? ( $args['name'] ?? '' );
		$name        = $args['name'] ?? '';
		$label       = $args['label'] ?? '';
		$value       = $args['value'] ?? '';
		$description = $args['description'] ?? '';
		$options     = $args['options'] ?? array();
		$attributes  = $args['attributes'] ?? array();
		$classes     = array( 'mpp-form-field' );

		if ( ! empty( $args['class'] ) ) {
			$classes[] = sanitize_html_class( $args['class'] );
		}

		$attr_html = '';
		foreach ( $attributes as $attr_key => $attr_value ) {
			$attr_html .= sprintf( ' %s="%s"', esc_attr( $attr_key ), esc_attr( (string) $attr_value ) );
		}

		ob_start();
		?>
		<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
			<?php if ( $label ) : ?>
				<label class="mpp-form-field__label" for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
			<?php endif; ?>

			<?php if ( 'checkbox' === $type ) : ?>
				<label class="mpp-checkbox">
					<input
						type="checkbox"
						id="<?php echo esc_attr( $id ); ?>"
						name="<?php echo esc_attr( $name ); ?>"
						value="1"
						<?php checked( ! empty( $value ) ); ?>
						<?php echo $attr_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					>
					<?php if ( $description ) : ?>
						<span><?php echo esc_html( $description ); ?></span>
					<?php endif; ?>
				</label>
			<?php elseif ( 'select' === $type ) : ?>
				<select id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" class="mpp-select"<?php echo $attr_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
					<?php foreach ( $options as $option_value => $option_label ) : ?>
						<option value="<?php echo esc_attr( (string) $option_value ); ?>" <?php selected( (string) $value, (string) $option_value ); ?>>
							<?php echo esc_html( $option_label ); ?>
						</option>
					<?php endforeach; ?>
				</select>
				<?php if ( $description ) : ?>
					<p class="mpp-form-field__help mpp-muted"><?php echo esc_html( $description ); ?></p>
				<?php endif; ?>
			<?php elseif ( 'color' === $type ) : ?>
				<input
					type="color"
					id="<?php echo esc_attr( $id ); ?>"
					name="<?php echo esc_attr( $name ); ?>"
					value="<?php echo esc_attr( (string) $value ); ?>"
					class="mpp-input mpp-input--color"
					<?php echo $attr_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				>
				<?php if ( $description ) : ?>
					<p class="mpp-form-field__help mpp-muted"><?php echo esc_html( $description ); ?></p>
				<?php endif; ?>
			<?php else : ?>
				<input
					type="<?php echo esc_attr( $type ); ?>"
					id="<?php echo esc_attr( $id ); ?>"
					name="<?php echo esc_attr( $name ); ?>"
					value="<?php echo esc_attr( (string) $value ); ?>"
					class="mpp-input"
					<?php echo $attr_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				>
				<?php if ( $description ) : ?>
					<p class="mpp-form-field__help mpp-muted"><?php echo esc_html( $description ); ?></p>
				<?php endif; ?>
			<?php endif; ?>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Render a chip/badge link.
	 *
	 * @param string $label   Chip label.
	 * @param string $url     Optional URL.
	 * @param string $variant Optional variant class suffix.
	 * @return string
	 */
	public static function chip( $label, $url = '', $variant = '' ) {
		$classes = array( 'mpp-chip' );

		if ( $variant ) {
			$classes[] = 'mpp-chip--' . sanitize_html_class( $variant );
		}

		$class_attr = esc_attr( implode( ' ', $classes ) );

		if ( $url ) {
			return sprintf(
				'<a href="%s" class="%s">%s</a>',
				esc_url( $url ),
				$class_attr,
				esc_html( $label )
			);
		}

		return sprintf( '<span class="%s">%s</span>', $class_attr, esc_html( $label ) );
	}

	/**
	 * Render a module summary card.
	 *
	 * @param array<string, mixed> $module Module data.
	 * @return string
	 */
	public static function module_card( array $module ) {
		$title       = $module['name'] ?? '';
		$description = $module['description'] ?? '';
		$version     = $module['version'] ?? '';
		$status      = $module['status'] ?? 'active';
		$url         = $module['url'] ?? '#';
		$perms       = isset( $module['permission_count'] ) ? (int) $module['permission_count'] : 0;
		$routes      = isset( $module['route_count'] ) ? (int) $module['route_count'] : 0;
		$is_active   = 'active' === $status;

		ob_start();
		?>
		<article class="mpp-module-card mpp-card">
			<h3 class="mpp-module-card__title"><?php echo esc_html( $title ); ?></h3>
			<?php if ( $description ) : ?>
				<p class="mpp-module-card__desc mpp-muted"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>
			<div class="mpp-module-card__meta">
				<?php if ( $version ) : ?>
					<span><?php echo esc_html( $version ); ?></span>
				<?php endif; ?>
				<span class="mpp-module-card__status<?php echo $is_active ? ' is-active' : ''; ?>">
					<?php echo $is_active ? esc_html__( 'Active', 'platform-theme' ) : esc_html__( 'Inactive', 'platform-theme' ); ?>
				</span>
			</div>
			<p class="mpp-module-card__stats mpp-muted">
				<?php
				printf(
					/* translators: %d: permission count */
					esc_html( _n( '%d Permission', '%d Permissions', $perms, 'platform-theme' ) ),
					$perms
				);
				echo ' · ';
				printf(
					/* translators: %d: route count */
					esc_html( _n( '%d Route', '%d Routes', $routes, 'platform-theme' ) ),
					$routes
				);
				?>
			</p>
			<?php echo self::button( array( 'label' => __( 'Open Module', 'platform-theme' ), 'url' => $url, 'variant' => 'secondary', 'size' => 'sm' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</article>
		<?php
		return (string) ob_get_clean();
	}
}
