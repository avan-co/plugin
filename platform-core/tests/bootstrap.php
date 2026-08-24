<?php
/**
 * PHPUnit bootstrap.
 */

define( 'ABSPATH', __DIR__ . '/../' );
define( 'MPP_PLUGIN_DIR', dirname( __DIR__ ) . '/' );
define( 'MPP_VERSION', '1.5.0' );

if ( ! defined( 'ARRAY_A' ) ) {
	define( 'ARRAY_A', 'ARRAY_A' );
}

require_once dirname( __DIR__ ) . '/includes/Core/Autoloader.php';
MPP\Core\Autoloader::register( dirname( __DIR__ ) . '/includes/' );

require_once dirname( __DIR__ ) . '/vendor/autoload.php';

Brain\Monkey\setUp();

if ( ! function_exists( '__' ) ) {
	function __( $text, $domain = 'default' ) {
		return $text;
	}
}

if ( ! function_exists( 'sanitize_text_field' ) ) {
	function sanitize_text_field( $str ) {
		return is_string( $str ) ? trim( strip_tags( $str ) ) : '';
	}
}

if ( ! function_exists( 'wp_json_encode' ) ) {
	function wp_json_encode( $data ) {
		return json_encode( $data );
	}
}

if ( ! function_exists( 'apply_filters' ) ) {
	function apply_filters( $tag, $value ) {
		return $value;
	}
}

if ( ! function_exists( 'get_option' ) ) {
	function get_option( $option, $default = false ) {
		return $default;
	}
}

if ( ! function_exists( 'update_option' ) ) {
	function update_option( $option, $value, $autoload = true ) {
		return true;
	}
}

if ( ! function_exists( 'delete_option' ) ) {
	function delete_option( $option ) {
		return true;
	}
}

if ( ! function_exists( 'sanitize_key' ) ) {
	function sanitize_key( $key ) {
		return strtolower( preg_replace( '/[^a-z0-9_\-]/', '', (string) $key ) );
	}
}

if ( ! function_exists( 'wp_parse_args' ) ) {
	function wp_parse_args( $args, $defaults = array() ) {
		if ( is_object( $args ) ) {
			$args = get_object_vars( $args );
		}

		return array_merge( $defaults, (array) $args );
	}
}

if ( ! function_exists( 'esc_html' ) ) {
	function esc_html( $text ) {
		return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'esc_html__' ) ) {
	function esc_html__( $text, $domain = 'default' ) {
		unset( $domain );
		return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'esc_attr__' ) ) {
	function esc_attr__( $text, $domain = 'default' ) {
		unset( $domain );
		return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'esc_url' ) ) {
	function esc_url( $url ) {
		return $url;
	}
}

if ( ! function_exists( 'add_query_arg' ) ) {
	function add_query_arg( $key, $value = false, $url = false ) {
		if ( is_array( $key ) ) {
			$query = $key;
			$url   = $value;
		} else {
			$query = array( $key => $value );
			$url   = $url ?: '';
		}

		$separator = strpos( $url, '?' ) === false ? '?' : '&';

		return $url . $separator . http_build_query( $query );
	}
}

if ( ! function_exists( 'wp_unslash' ) ) {
	function wp_unslash( $value ) {
		return is_string( $value ) ? stripslashes( $value ) : $value;
	}
}

if ( ! function_exists( 'wp_verify_nonce' ) ) {
	function wp_verify_nonce( $nonce, $action ) {
		unset( $nonce, $action );
		return isset( $GLOBALS['mpp_test_nonce_valid'] ) ? (bool) $GLOBALS['mpp_test_nonce_valid'] : true;
	}
}

if ( ! function_exists( 'wp_nonce_field' ) ) {
	function wp_nonce_field( $action, $name, $referer = true, $echo = true ) {
		unset( $action, $referer );
		$field = '<input type="hidden" name="' . esc_attr( $name ) . '" value="test-nonce" />';
		if ( $echo ) {
			echo $field; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		return $field;
	}
}

if ( ! function_exists( 'is_email' ) ) {
	function is_email( $email ) {
		return false !== filter_var( $email, FILTER_VALIDATE_EMAIL );
	}
}

if ( ! function_exists( 'sanitize_email' ) ) {
	function sanitize_email( $email ) {
		$filtered = filter_var( $email, FILTER_VALIDATE_EMAIL );
		return $filtered ? $filtered : '';
	}
}

if ( ! function_exists( 'home_url' ) ) {
	function home_url( $path = '' ) {
		return 'https://example.test' . $path;
	}
}
