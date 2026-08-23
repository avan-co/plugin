<?php
/**
 * Reusable pagination renderer.
 *
 * @package PlatformCore
 */

namespace MPP\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Class Pagination
 */
class Pagination {

	/**
	 * Render pagination links.
	 *
	 * @param int                  $current_page Current page (1-based).
	 * @param int                  $total_items  Total item count.
	 * @param int                  $per_page     Items per page.
	 * @param string               $base_url     Base URL without paged arg.
	 * @param array<string, mixed> $query_args   Extra query args to preserve.
	 */
	public static function render( $current_page, $total_items, $per_page, $base_url, array $query_args = array() ) {
		$total_pages = (int) ceil( $total_items / max( 1, $per_page ) );

		if ( $total_pages <= 1 ) {
			return;
		}

		unset( $query_args['paged'] );
		$base_url = add_query_arg( $query_args, $base_url );

		echo '<nav class="mpp-pagination" aria-label="' . esc_attr__( 'Pagination', 'platform-core' ) . '">';
		echo '<ul class="mpp-pagination__list">';

		if ( $current_page > 1 ) {
			printf(
				'<li><a href="%s">&laquo; %s</a></li>',
				esc_url( add_query_arg( 'paged', $current_page - 1, $base_url ) ),
				esc_html__( 'Previous', 'platform-core' )
			);
		}

		printf(
			'<li class="mpp-pagination__info">%s</li>',
			esc_html(
				sprintf(
					/* translators: 1: current page, 2: total pages */
					__( 'Page %1$d of %2$d', 'platform-core' ),
					$current_page,
					$total_pages
				)
			)
		);

		if ( $current_page < $total_pages ) {
			printf(
				'<li><a href="%s">%s &raquo;</a></li>',
				esc_url( add_query_arg( 'paged', $current_page + 1, $base_url ) ),
				esc_html__( 'Next', 'platform-core' )
			);
		}

		echo '</ul></nav>';
	}
}
