<?php
/**
 * Error
 *
 * @package     aws-s3-integration
 * @subpackage  Classes/Error
 * @copyright   Copyright (c), Recuweb
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.9.12
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * as3i_Error Class
 *
 * This class handles error logging
 *
 * @since 0.9.12
 */
class as3i_Error {

	/**
	 * Wrapper for error logging a message with plugin prefix
	 *
	 * @param mixed  $message
	 * @param string $plugin_prefix
	 */
	public static function log( $message, $plugin_prefix = '' ) {
		$prefix = 'as3i';
		if ( '' !== $plugin_prefix ) {
			$prefix .= '_' . $plugin_prefix;
		}

		$prefix .= ': ';

		if ( is_array( $message ) || is_object( $message ) ) {
			error_log( $prefix . print_r( $message, true ) );
		} else {
			error_log( $prefix . $message );
		}
	}
}