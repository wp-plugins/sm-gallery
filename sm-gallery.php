<?php
/*
Plugin Name: SM Gallery
Plugin URI: http://wordpress.org/extend/plugins/sm-gallery/
Description: Gallery plugin thats simple because it leans on existing WordPress gallery features provided by http://sethmatics.com/.
Author: sethcarstens, sethmatics
Version: 1.1.8
Author URI: http://sethmatics.com/
*/

//avoid direct calls to this file, because now WP core and framework has been used
if ( ! function_exists( 'add_filter' ) ) {
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit();
}

if ( ! class_exists('sm_gallery') ) {

	add_action( 'plugins_loaded', array ( 'sm_gallery_plugin', 'get_instance' ) );

	class sm_gallery_plugin {
		//singleton instance
		protected static $instance = NULL;

		//class init
		function __construct(){
			define( 'SM_GALLERY_BASENAME',   plugin_basename(__FILE__) );
			define( 'SM_GALLERY_TEXTDOMAIN',   'sm_gallery_plugin' );
			add_action( 'init', array( $this, 'on_init') );
			add_action( 'admin_init', array( $this, 'on_admin_init' ) );
			//maybe remove gallery shortcode, and setup sm_gallery shortcode
			self::setup_shortcodes();
			//will be for use with plugin option to disable gallery shortcode override
			//add_action( 'wp_ajax_wm_config-update', array( $this, 'save_config' ) );
			//add_action( 'wp_ajax_wm_config-active', array( $this, 'save_active' ) );
		}

		//plugins working instance
		public static function get_instance() {
			self::load_includes();
			NULL === self::$instance and self::$instance = new self;
			return self::$instance;
		}

		//additional files included for front end or admin
		public static function load_includes(){
			if(!is_admin()) {
				include_once(__DIR__.'/sm-gallery-builder.php');
			}
			else {
				include_once(__DIR__.'/sm-gallery-admin.php');
				sm_gallery_admin::get_instance();
			}
		}

		//prepare and or modify shortcodes for use
		function setup_shortcodes(){
			//add our own gallery shortcode
			//ex: [gallery modal="" post_id="" box_height="" box_width="" title="" thumbnail="" thumb_class=""]
			$sm_gallery_shortcode_noconflict = get_option('sm_gallery_shortcode_noconflict');
			if(empty($sm_gallery_shortcode_noconflict)) {
				remove_shortcode('gallery');
				add_shortcode('gallery', 'sm_gallery');
			}
			add_shortcode('sm_gallery', 'sm_gallery');
		}

		//WordPress public init
		function on_init(){
			add_action('wp_enqueue_scripts', array($this, 'gallery_scripts_and_styles'));
			add_action('get_footer', array($this, 'conditional_gallery_script_enqueue', 1));
			// filter featured image content and add gallery when applicable
			add_filter( 'post_thumbnail_html', 'sm_gallery_featured_filter', 10, 3 );
		}

		//front end javascript for new gallery uix
		function gallery_scripts_and_styles() {
			wp_enqueue_script('jquery-ad-gallery-scripts', plugins_url('js/jquery.ad-gallery.js', __FILE__), array('jquery'), '', true );
			//older version of UI scripts (core, resizeable, dragable, dialog) fixes IE Jump issue
			wp_enqueue_script('jquery-ui', false, array('jquery'), '', true );
			wp_enqueue_script('jquery-ui-dialog', false, array('jquery'), '', true );
		}

		// remove scripts from footer if Gallery is not being used on the page
		function conditional_gallery_script_enqueue() {
			global $wp_scripts;
			if(!did_action('before_gallery')) {
				$wpFooterScripts = $wp_scripts->in_footer;
				$remove = array_search ('jquery-ad-gallery-scripts', $wpFooterScripts);
				unset($wpFooterScripts[$remove]);
				$wp_scripts->in_footer = $wpFooterScripts;
			}
		}

		//WordPress admin init
		function on_admin_init(){
			add_filter('plugin_action_links', array($this, 'add_settings_link'), 10, 2);
		}

		//add settings link to plugins page -> actions link
		function add_settings_link( $links, $file ) {
			if ( plugin_basename( SM_GALLERY_BASENAME ) == $file  ) {
				array_unshift( $links, sprintf('<a id="sm_add_settings_link" href="javascript:void(0)" onclick="alert(\'Ability to allow [gallery] to give default WordPress gallery while [sm_gallery] gives new gallery slider will be an option in the next version.\');" title="Configure this plugin">%s</a>', __('Settings')) );
			}
			return $links;
		}
	} //end class
} //end if class exists