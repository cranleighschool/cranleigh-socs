<?php
namespace FredBradley\SOCS;

use WeDevs_Settings_API;

class SettingsApi {

	private $settings_api;

	function __construct() {
		$this->settings_api = new WeDevs_Settings_API();

		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
	}

	function admin_init() {

		// set the settings
		$this->settings_api->set_sections( $this->get_settings_sections() );
		$this->settings_api->set_fields( $this->get_settings_fields() );

		// initialize settings
		$this->settings_api->admin_init();
	}

	function admin_menu() {
		add_options_page( 'SOCS Sports', 'SOCS Sports', 'manage_options', 'cranleigh-socs-settings', array( $this, 'plugin_page' ) );
	}

	function get_settings_sections() {
		$sections = array(
			array(
				'id'    => 'socs-sports',
				'title' => __( 'SOCS Sports Settings', 'wedevs' ),
			),

		);
		return $sections;
	}

	/**
	 * Returns all the settings fields
	 *
	 * @return array settings fields
	 */
	function get_settings_fields() {
		$settings_fields = array(
			'socs-sports' => array(
				array(
					'name'    => 'schoolID',
					'label'   => __( 'School ID', 'wedevs' ),
					'desc'    => __( 'Your School\'s SOCS ID.', 'wedevs' ),
					'type'    => 'text',
					'default' => '',
				),
				array(
					'name'    => 'apiKey',
					'label'   => __( 'API KEY', 'wedevs' ),
					'desc'    => __( 'The API Key that SOCS would have given you....', 'wedevs' ),
					'type'    => 'text',
					'default' => '',
				),
				array(
					'name'    => 'intoFuture',
					'label'   => __( 'Into the Future', 'wedevs' ),
					'desc'    => __( 'How many weeks into the future to you want the fixtures list to look to?', 'wedevs' ),
					'type'    => 'number',
					'default' => '',
				),
			),
		);

		return $settings_fields;
	}

	private function get_plugin_list() {
		$all_plugins = get_plugins();
		$output      = array();
		foreach ( $all_plugins as $plugin => $value ) :
			$output[ $plugin ] = $value['Name'];
		endforeach;

		return $output;
	}

	function plugin_page() {
		echo '<div class="wrap">';

		if ( count( $this->get_settings_sections() ) > 1 ) :
			$this->settings_api->show_navigation();
		endif;

		$this->settings_api->show_forms();

		echo '</div>';
	}



	/**
	 * Get all the pages
	 *
	 * @return array page names with key value pairs
	 */
	function get_pages() {
		$pages         = get_pages();
		$pages_options = array();
		if ( $pages ) {
			foreach ( $pages as $page ) {
				$pages_options[ $page->ID ] = $page->post_title;
			}
		}

		return $pages_options;
	}

}
