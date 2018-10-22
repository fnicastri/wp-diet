<?php defined( 'ABSPATH' ) or die( 'No script kiddies please!' ); ?>

<?php

/*
Plugin Name:  wp-diet
Plugin URI:
Description: load uploads assets from test server
Version:     0.1
Author:
Author URI:
License:     MIT
License URI:
Text Domain: wpdiet
Domain Path: /languages
*/



function wpdiet_install()
{
	$local_host = $_SERVER['HTTP_HOST'];
	$name = strtolower(get_bloginfo('name', 'raw' ));
	$test_host = "";
	add_option('wp_diet',['local_host'=>$local_host,'site'=>$name,'test_host'=>$test_host]);
}
register_activation_hook( __FILE__, 'wpdiet_install' );


function test_upload_url($args) {
	$diet_options = get_option("wp_diet" );
	if ($diet_options['active'] != 'on')
		return;

	if (!$diet_options['local_host'] || !($diet_options['site'] || $diet_options['test_host']))
		return;
	if ($diet_options) {
		$host = $_SERVER['HTTP_HOST'];
		$local = $diet_options['local_host']?$diet_options['local_host']:'';
		if(strstr($host, $diet_options['local_host']?$diet_options['local_host']:'')) {
			$name = strtolower($diet_options['local_host']);
			$args['baseurl'] =  "http://{$diet_options['site']}.{$diet_options['test_host']}/wp-content/uploads";
			return $args;
		}
	}
}
add_filter('upload_dir', 'test_upload_url');
// add_filter( 'pre_option_upload_url_path', 'test_upload_url' );



class MySettingsPage
{
		/**
		 * Holds the values to be used in the fields callbacks
		 */
		private $options;

		/**
		 * Start up
		 */
		public function __construct()
		{
			add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
			add_action( 'admin_init', array( $this, 'page_init' ) );
		}

		/**
		 * Add options page
		 */
		public function add_plugin_page()
		{
				// This page will be under "Settings"
			add_options_page(
				'cl Diet Admin',
				'cl Diet',
				'manage_options',
				'cl-diet-setting-admin',
				array( $this, 'create_wpdiet_admin_page' )
				);
		}

		/**
		 * Options page callback
		 */
		public function create_wpdiet_admin_page()
		{
				// Set class property
			$this->options = get_option( 'wp_diet' );
			?>
			<div class="wrap">
				<h1>My Settings</h1>
				<form method="post" action="options.php">
					<?php
								// This prints out all hidden setting fields
					settings_fields( 'wp_diet' );
					do_settings_sections( 'my-setting-admin' );
					submit_button();
					?>
				</form>
			</div>
			<?php
		}

		/**
		 * Register and add settings
		 */
		public function page_init()
		{
			register_setting(
				'wp_diet', // Option group
				'wp_diet', // Option name
				array( $this, 'sanitize' ) // Sanitize
				);

			add_settings_section(
				'setting_section_id', // ID
				'Cicolabs Diet Settings', // Title
				array( $this, 'print_section_info' ), // Callback
				'my-setting-admin' // Page
				);
			add_settings_field(
				'active',
				'active',
				array( $this, 'active_callback' ),
				'my-setting-admin',
				'setting_section_id'
				);
			add_settings_field(
				'local_host',
				'Local Host',
				array( $this, 'local_host_callback' ),
				'my-setting-admin',
				'setting_section_id'
				);

			add_settings_field(
						'site', // ID
						'Site Name', // Title
						array( $this, 'site_callback' ), // Callback
						'my-setting-admin', // Page
						'setting_section_id' // Section
						);

			add_settings_field(
				'test_host',
				'Test Host',
				array( $this, 'test_host_callback' ),
				'my-setting-admin',
				'setting_section_id'
				);
		}

		/**
		 * Sanitize each setting field as needed
		 *
		 * @param array $input Contains all settings fields as array keys
		 */
		public function sanitize( $input )
		{
			$new_input = array();

			$new_input['active'] = $input['active'];

			if( isset( $input['site'] ) )
				$new_input['site'] = sanitize_text_field( $input['site'] );

			if( isset( $input['test_host'] ) )
				$new_input['test_host'] = sanitize_text_field( $input['test_host'] );

			if( isset( $input['local_host'] ) )
				$new_input['local_host'] = sanitize_text_field( $input['local_host'] );

			return $new_input;
		}

		/**
		 * Print the Section text
		 */
		public function print_section_info()
		{
			print 'Enter your settings below:';
		}

		/**
		 * Get the settings option array and print one of its values
		 */
		public function active_callback()
		{
			printf(
				'<input type="checkbox" id="active" name="wp_diet[active]" %s/>',
				isset( $this->options['active'] ) ? "checked" : ''
				);
		}
		/**
		 * Get the settings option array and print one of its values
		 */
		public function site_callback()
		{
			printf(
				'<input type="text" id="site" name="wp_diet[site]" value="%s" />',
				isset( $this->options['site'] ) ? esc_attr( $this->options['site']) : ''
				);
		}

		/**
		 * Get the settings option array and print one of its values
		 */
		public function test_host_callback()
		{
			printf(
				'<input type="text" id="test_host" name="wp_diet[test_host]" value="%s" />',
				isset( $this->options['test_host'] ) ? esc_attr( $this->options['test_host']) : ''
				);
		}

		public function local_host_callback()
		{
			printf(
				'<input type="text" id="local_host" name="wp_diet[local_host]" value="%s" />',
				isset( $this->options['local_host'] ) ? esc_attr( $this->options['local_host']) : ''
				);
		}
	}

	if( is_admin() )
		$my_settings_page = new MySettingsPage();
