<?php
/**
 * Plugin Name: MW WP Form A8 Tracker
 * Plugin URI: http://plugins.2inc.org/mw-wp-form-a8-tracker/
 * Description: Add A8 tracking code to MW WP Form. "Saving inquiry data in database" is required.
 * Version: 1.0.0
 * Author: Copyright(c) FAN Communications, Inc.
 * Author URI: https://www.fancs.com/
 * Text Domain: mw-wp-form-a8-tracker
 * Domain Path: /languages/
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

define( 'MW_WP_FORM_A8_TRACKER_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'MW_WP_FORM_A8_TRACKER_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );

class MW_WP_Form_A8_Tracker {

	public function __construct() {
		register_uninstall_hook( __FILE__, array( __CLASS__, 'uninstall' ) );

		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
	}

	public static function uninstall() {
		include_once( plugin_dir_path( __FILE__ ) . '/app/common.php' );
		$forms = MW_WP_Form_A8_Tracker_Common::get_forms();
		foreach ( $forms as $form ) {
			delete_post_meta( $form->ID, 'mw_wp_form_a8_tracker' );
		}
	}

	public function plugins_loaded() {
		load_plugin_textdomain( 'mw-wp-form-a8-tracker', false, basename( dirname( __FILE__ ) ) . '/languages' );

		include_once( plugin_dir_path( __FILE__ ) . '/app/common.php' );
		include_once( plugin_dir_path( __FILE__ ) . '/app/controller/admin.php' );
		include_once( plugin_dir_path( __FILE__ ) . '/app/controller/main.php' );

		new MW_WP_Form_A8_Tracker_Admin_Controller();
		new MW_WP_Form_A8_Tracker_Main_Controller();
	}

}

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( is_plugin_active( 'mw-wp-form/mw-wp-form.php' ) ) {
	new MW_WP_Form_A8_Tracker();
}
