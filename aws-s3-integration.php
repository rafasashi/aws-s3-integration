<?php
/*
 * Plugin Name: AWS S3 Integration
 * Plugin URI: https://code.recuweb.com/download/aws-s3-integration/
 * Description: Extends Amazon Cloud Services to provide S3 integration into the media library
 * Version: 3.0.0
 * Author: Rafasashi
 * Author URI: https://code.recuweb.com/about-us/
 * Requires at least: 4.6
 * Tested up to: 4.9.6
 *
 * Text Domain: aws_s3_integration
 * Domain Path: /lang/
 * 
 * Copyright: © 2018 Recuweb.
 * License: GNU General Public License v3.0
 * License URI: https://code.recuweb.com/product-licenses/
 */

	if(!defined('ABSPATH')) exit; // Exit if accessed directly
 
	/**
	* Minimum version required
	*
	*/
	
	if ( get_bloginfo('version') < 3.3 ) return;	
	
	// Checks if the free plugin is installed and active.
	
	$plugins = get_option( 'active_plugins' );	
	
	if( in_array('amazon-cloud-services/amazon-cloud-services.php', apply_filters( 'active_plugins', $plugins )) ){
		
		// Load plugin class files
		
		require_once( 'includes/class-aws-s3-integration.php' );
		require_once( 'includes/class-aws-s3-integration-settings.php' );
		
		// Load plugin libraries
		
		require_once( 'includes/lib/class-aws-s3-integration-admin-api.php' );
		require_once( 'includes/lib/class-aws-s3-integration-admin-notices.php' );
		require_once( 'includes/lib/class-aws-s3-integration-post-type.php' );
		require_once( 'includes/lib/class-aws-s3-integration-taxonomy.php' );			
			
		/**
		 * Returns the main instance of AWS_S3_Integration to prevent the need to use globals.
		 *
		 * @since  1.0.0
		 * @return object AWS_S3_Integration
		 */
		 
		function AWS_S3_Integration($version) {
					
			$instance = Amazon_Cloud_Services::instance( __FILE__, $version );	
			
			if ( is_null( $instance->s3 ) ) {

				$instance->s3 = new stdClass();
				
				$instance->s3 = AWS_S3_Integration::instance( __FILE__, $instance, $version );
			}		
			
			if ( is_null( $instance->s3->notices ) ) {
				
				$instance->s3->notices = AWS_S3_Integration_Admin_Notices::instance( $instance->s3 );
			}
			
			if ( is_null( $instance->s3->settings ) ) {
				
				$instance->s3->settings = AWS_S3_Integration_Settings::instance( $instance->s3 );
			}

			return $instance;
		}	
		
		add_filter( 'plugins_loaded', function(){
		
			AWS_S3_Integration( time() );
		});
	}
	else{
		
		add_action('admin_notices', function(){
			
			global $current_screen;
			
			if( $current_screen->parent_base == 'plugins' ){
				
				echo '<div class="error"><p>Amazon S3 Integration '.__('requires <a href="https://code.recuweb.com/download/amazon-cloud-services/" target="_blank">Amazon Cloud Services</a> to be activated in order to work. Please install and activate <a href="'.admin_url('plugin-install.php?tab=search&type=term&s=amazon+cloud+services').'" target="_blank">Amazon Cloud Services</a> first.', 'aws_s3_integration').'</p></div>';
			}
		});
	}	
