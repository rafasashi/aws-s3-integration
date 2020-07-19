<?php
/*
Plugin Name: AWS S3 Integration
Plugin URI: https://code.recuweb.com/
Description: Automatically copies media uploads to Amazon S3 for storage and delivery.
Author: Rafasashi
Version: 1.0.0
Author URI: https://code.recuweb.com/
Text Domain: aws-s3-integration
Domain Path: /languages/
*/

$GLOBALS['aws_meta']['aws-s3-integration']['version'] = '1.0.0';

require_once dirname( __FILE__ ) . '/classes/as3cf-compatibility-check.php';

add_action( 'activated_plugin', array( 'AS3CF_Compatibility_Check', 'deactivate_other_instances' ) );

global $as3cf_compat_check;
$as3cf_compat_check = new AS3CF_Compatibility_Check(
	'WP Offload Media Lite',
	'aws-s3-integration',
	__FILE__
);

/**
 * @throws Exception
 */
function as3cf_init() {
	if ( class_exists( 'Amazon_S3_And_CloudFront' ) ) {
		return;
	}

	global $as3cf_compat_check;

	if ( method_exists( 'AS3CF_Compatibility_Check', 'is_plugin_active' ) && $as3cf_compat_check->is_plugin_active( 'aws-s3-integration-pro/aws-s3-integration-pro.php' ) ) {
		// Don't load if pro plugin installed
		return;
	}

	if ( ! $as3cf_compat_check->is_compatible() ) {
		return;
	}

	global $as3cf;
	$abspath = dirname( __FILE__ );

	// Autoloader.
	require_once $abspath . '/wp-offload-media-autoloader.php';

	require_once $abspath . '/include/functions.php';
	require_once $abspath . '/classes/as3cf-utils.php';
	require_once $abspath . '/classes/as3cf-error.php';
	require_once $abspath . '/classes/as3cf-filter.php';
	require_once $abspath . '/classes/filters/as3cf-local-to-s3.php';
	require_once $abspath . '/classes/filters/as3cf-s3-to-local.php';
	require_once $abspath . '/classes/as3cf-notices.php';
	require_once $abspath . '/classes/as3cf-plugin-base.php';
	require_once $abspath . '/classes/as3cf-plugin-compatibility.php';
	require_once $abspath . '/classes/aws-s3-integration.php';

	new WP_Offload_Media_Autoloader( 'WP_Offload_Media', $abspath );

	$as3cf = new Amazon_S3_And_CloudFront( __FILE__ );

	do_action( 'as3cf_init', $as3cf );
}

add_action( 'init', 'as3cf_init' );

// If AWS still active need to be around to satisfy addon version checks until upgraded.
add_action( 'aws_init', 'as3cf_init', 11 );
