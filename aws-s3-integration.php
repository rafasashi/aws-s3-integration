<?php
/*
Plugin Name: AWS S3 Integration
Plugin URI: https://code.recuweb.com/
Description: Automatically copies media uploads to Amazon S3 for storage and delivery.
Author: Rafasashi
Version: 1.0.0
Author URI: https://code.recuweb.com/
Text Domain: aws-s3-integration
Domain Path: /lang/
*/

$GLOBALS['aws_meta']['aws-s3-integration']['version'] = '1.0.0';

require_once dirname( __FILE__ ) . '/classes/as3i-compatibility-check.php';

add_action( 'activated_plugin', array( 'as3i_Compatibility_Check', 'deactivate_other_instances' ) );

global $as3i_compat_check;
$as3i_compat_check = new as3i_Compatibility_Check(
	'AWS S3 Integration',
	'aws-s3-integration',
	__FILE__
);

/**
 * @throws Exception
 */
function as3i_init() {
	if ( class_exists( 'AWS_s3_Integration' ) ) {
		return;
	}

	global $as3i_compat_check;

	if ( method_exists( 'as3i_Compatibility_Check', 'is_plugin_active' ) && $as3i_compat_check->is_plugin_active( 'aws-s3-integration-pro/aws-s3-integration-pro.php' ) ) {
		// Don't load if pro plugin installed
		return;
	}

	if ( ! $as3i_compat_check->is_compatible() ) {
		return;
	}

	global $as3i;
	$abspath = dirname( __FILE__ );
	
	// Autoloader.
	require_once $abspath . '/aws-s3-integration-autoloader.php';

	require_once $abspath . '/include/functions.php';
	require_once $abspath . '/classes/as3i-utils.php';
	require_once $abspath . '/classes/as3i-error.php';
	require_once $abspath . '/classes/as3i-filter.php';
	require_once $abspath . '/classes/filters/as3i-local-to-s3.php';
	require_once $abspath . '/classes/filters/as3i-s3-to-local.php';
	require_once $abspath . '/classes/as3i-notices.php';
	require_once $abspath . '/classes/as3i-plugin-base.php';
	require_once $abspath . '/classes/as3i-plugin-compatibility.php';
	require_once $abspath . '/classes/aws-s3-integration.php';
	
	new AWS_S3_Integration_Autoloader( 'AWS_S3_Integration', $abspath );
	
	$as3i = new AWS_s3_Integration( __FILE__ );

	do_action( 'as3i_init', $as3i );
}

add_action( 'init', 'as3i_init' );

// If AWS still active need to be around to satisfy addon version checks until upgraded.
add_action( 'aws_init', 'as3i_init', 11 );
