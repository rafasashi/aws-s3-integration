<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class AWS_S3_Integration {

	/**
	 * The single instance of AWS_S3_Integration.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;
	
	/**
	 * Settings class object
	 * @var     object
	 * @access  public
	 * @since   1.0.0
	 */
	public $filesystem = null;
	public $notices = null;
	public $settings = null;

	/**
	 * The version number.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_version;

	/**
	 * The token.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_token;

	/**
	 * The main plugin file.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $file;

	/**
	 * The main plugin directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $dir;
	public $views;

	/**
	 * The plugin assets directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_dir;

	/**
	 * The plugin assets URL.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_url;

	/**
	 * Suffix for Javascripts.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $script_suffix;

	/**
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */

	public static $plugin_prefix;
	public static $plugin_url;
	public static $plugin_path;
	public static $plugin_basefile;
	
	public $key;
	public $secret;
	public $region;
	public $bucket;
	
	public function __construct ( $file='', $parent, $version = '1.0.0'  ) {
		
		$this->parent 	= $parent;

		$this->addon_url = 'https://code.recuweb.com/download/aws-s3-integration/';
		
		$this->_version = $version;
		$this->_token 	= 'aws-s3-integration';
		$this->_base 	= 'as3i_';
		
		$this->key 		= $this->parent->key;
		$this->secret 	= $this->parent->secret;
		$this->region 	= 'eu-west-1';
		$this->bucket 	= 'test.recuweb.com';
		$this->path 	= UPLOADS;

		// Load plugin environment variables
		
		$this->file 		= $file;
		$this->dir 			= dirname( $this->file );
		$this->views   		= trailingslashit( $this->dir ) . 'views';
		$this->assets_dir 	= trailingslashit( $this->dir ) . 'assets';
		$this->assets_url 	= esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );

		$this->script_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		AWS_S3_Integration::$plugin_prefix 	= $this->_base;
		AWS_S3_Integration::$plugin_basefile 	= $this->file;
		AWS_S3_Integration::$plugin_url 		= plugin_dir_url($this->file); 
		AWS_S3_Integration::$plugin_path 		= trailingslashit($this->dir);
		
		// register plugin activation hook
		
		//register_activation_hook( $this->file, array( $this, 'install' ) );

		// Load frontend JS & CSS
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 10 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );

		// Load admin JS & CSS
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10, 1 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ), 10, 1 );

		// Load API for generic admin functions
		
		$this->admin = new AWS_S3_Integration_Admin_API($this);

		/* Localisation */
		
		$locale = apply_filters('plugin_locale', get_locale(), 'aws-s3-integration');
		load_textdomain('aws_s3_integration', WP_PLUGIN_DIR . "/".plugin_basename(dirname(__FILE__)).'/lang/aws_s3_integration-'.$locale.'.mo');
		load_plugin_textdomain('aws_s3_integration', false, dirname(plugin_basename(__FILE__)).'/lang/');
				
		add_action('init', array($this, 'init')); // must be called in init

	} // End __construct ()
	
	
	/**
	 * Init Amazon S3 Integration extension once we know WooCommerce is active
	 */
	public function init(){
		
		// set credentials
		
		$credentials = new Aws\Credentials\Credentials($this->key, $this->secret);

		// get s3 client
		
		$this->client = Aws\S3\S3Client::factory(array(
		
			'region' 		=> 	$this->region,
			'version' 		=> 	'latest',
			'credentials' 	=> 	$credentials,
		));
		
		// reister stream wrapper
		
		$this->client->registerStreamWrapper('s3');
		
		add_filter( 'upload_dir', array( $this, 's3_upload_dir' ) );
	}
	
	function bucket_url() {
		
		$proto = 'http://';
		
		return $proto . 's3-' . $this->region . '.amazonaws.com/' . $this->bucket;
	}
	
	function bucket_dir() {
		
		return 's3://' . $this->bucket;
	}

	function s3_upload_dir( $dirs ) {
		
		$original_upload_dir = $dirs;
		
		$dirs['path']    = str_replace( WP_CONTENT_DIR, 's3://' . $this->bucket, $dirs['path'] );
		$dirs['basedir'] = str_replace( WP_CONTENT_DIR, 's3://' . $this->bucket, $dirs['basedir'] );
		$dirs['url']     = str_replace( $this->bucket_dir(), $this->bucket_url(), $dirs['path'] );
		$dirs['baseurl'] = str_replace( $this->bucket_dir(), $this->bucket_url(), $dirs['basedir'] );
		
		return $dirs;
	}

	
	/**
	 * Wrapper function to register a new post type
	 * @param  string $post_type   Post type name
	 * @param  string $plural      Post type item plural name
	 * @param  string $single      Post type item single name
	 * @param  string $description Description of post type
	 * @return object              Post type class object
	 */
	public function register_post_type ( $post_type = '', $plural = '', $single = '', $description = '', $options = array() ) {

		if ( ! $post_type || ! $plural || ! $single ) return;

		$post_type = new AWS_S3_Integration_Post_Type( $post_type, $plural, $single, $description, $options );

		return $post_type;
	}

	/**
	 * Wrapper function to register a new taxonomy
	 * @param  string $taxonomy   Taxonomy name
	 * @param  string $plural     Taxonomy single name
	 * @param  string $single     Taxonomy plural name
	 * @param  array  $post_types Post types to which this taxonomy applies
	 * @return object             Taxonomy class object
	 */
	public function register_taxonomy ( $taxonomy = '', $plural = '', $single = '', $post_types = array(), $taxonomy_args = array() ) {

		if ( ! $taxonomy || ! $plural || ! $single ) return;

		$taxonomy = new AWS_S3_Integration_Taxonomy( $taxonomy, $plural, $single, $post_types, $taxonomy_args );

		return $taxonomy;
	}
	
	/**
	 * Load frontend CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return void
	 */
	public function enqueue_styles () {
		
		//wp_register_style( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'css/frontend-1.0.1.css', array(), $this->_version );
		//wp_enqueue_style( $this->_token . '-frontend' );		

	} // End enqueue_styles ()

	/**
	 * Load frontend Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function enqueue_scripts () {
		
		//wp_register_script( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'js/frontend.js', array( 'jquery' ), $this->_version );
		//wp_enqueue_script( $this->_token . '-frontend' );	
		
	} // End enqueue_scripts ()

	/**
	 * Load admin CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_enqueue_styles ( $hook = '' ) {
		
		//wp_register_style( $this->_token . '-admin', esc_url( $this->assets_url ) . 'css/admin.css', array(), $this->_version );
		//wp_enqueue_style( $this->_token . '-admin' );
		
	} // End admin_enqueue_styles ()

	/**
	 * Load admin Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_enqueue_scripts ( $hook = '' ) {
		
		//wp_register_script( $this->_token . '-admin', esc_url( $this->assets_url ) . 'js/admin.js', array( 'jquery' ), $this->_version );
		//wp_enqueue_script( $this->_token . '-admin' );	

	} // End admin_enqueue_scripts ()

	/**
	 * Load plugin localisation
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_localisation () {
		
		load_plugin_textdomain( 'aws-s3-integration', false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	
	} // End load_localisation ()

	/**
	 * Load plugin textdomain
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_plugin_textdomain () {
		
	    $domain = 'aws-s3-integration';

	    $locale = apply_filters( 'plugin_locale', get_locale(), $domain );

	    load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
	    load_plugin_textdomain( $domain, false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_plugin_textdomain ()
	
	/**
	 * Main AWS_S3_Integration Instance
	 *
	 * Ensures only one instance of AWS_S3_Integration is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see AWS_S3_Integration()
	 * @return Main AWS_S3_Integration instance
	 */
	public static function instance ( $file = '', $version = '1.0.0' ) {
		
		if ( is_null( self::$_instance ) ) {
			
			self::$_instance = new self( $file, $version );
		}
		
		return self::$_instance;
	} // End instance ()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __clone ()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __wakeup ()

	/**
	 * Installation. Runs on activation.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function install () {
		$this->_log_version_number();
	} // End install ()

	/**
	 * Log the plugin version number.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	private function _log_version_number () {
		update_option( $this->_token . '_version', $this->_version );
	} // End _log_version_number ()
}
