<?php
/**
 * REST API base controller.
 *
 * @package PlatformCore
 */

namespace MPP\API;

defined( 'ABSPATH' ) || exit;

/**
 * Class RestController
 */
abstract class RestController extends \WP_REST_Controller {

	/**
	 * API namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'platform/v1';
}
