<?php
/**
 * Main functions and functionality for DroidScanner in WordPress.
 *
 * @package DroidScanner
 */

/*
Plugin Name: Droid Scanner
Plugin URI: https://www.deathstarsecurity.com/
Description: These ARE The droids you are looking for.
Version: 1.0
Author: Lockr
Author URI: htts://www.deathstarsecurity.com/
License: GPLv2 or later
Domain Path: /languages
Text Domain: droid-scanner
Tested up to: 5.4
*/

define( 'DROIDSCANNER__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'DROIDSCANNER__PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Load plugin text domain
 */
function droidscanner_load_plugin_textdomain(){
	load_plugin_textdomain('droid-scanner', FALSE,	dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action( 'init', 'droidscanner_load_plugin_textdomain' );

/**
 * Include our admin functions.
 */
require_once( ABSPATH . '/wp-includes/pluggable.php' );

if ( current_user_can('publish_posts') ) {
	require_once DROIDSCANNER__PLUGIN_DIR . '/droid-scanner-admin.php';
}

/**
 * Create Install Post.
 *
 * @file
 */

register_activation_hook( __FILE__, 'droid_scanner_install' );

/**
 * Initial setup when the plugin is activated.
 */
function droid_scanner_install() {

	global $wpdb;
	global $droid_scanner_db_version;
	$current_droid_scanner_db_version = get_option( 'droid_scanner_db_version' );

	if ( $current_droid_scanner_db_version !== $droid_scanner_db_version ) {
		$table_name      = $wpdb->prefix . 'droid_scanner_entries';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT null AUTO_INCREMENT,
			time datetime DEFAULT '0000-00-00 00:00:00' NOT null,
			droid_scanner_name tinytext NOT null,
			droid_scanner_email text NOT null,
			droid_scanner_inspire text NOT null,
			UNIQUE KEY id (id)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		update_option( 'droid_scanner_db_version', $droid_scanner_db_version );
	}
	// Create the page for this exploit.
	$my_post = array(
		'post_title'   => wp_strip_all_tags( 'Droid Scanner' ),
		'post_content' => '[droid_scanner_targeting]',
		'post_status'  => 'publish',
		'post_author'  => 1,
		'post_type'    => 'page',
	);

	wp_insert_post( $my_post );

}

function droid_scanner_targeting_form() {
	echo '<h2>' . __('Interested in working for our dynamic and industry leading team?', 'droid-scanner') . '</h2>';
	echo '<p>';
	echo __('We at Death Star Security are always looking for the best of the best to help secure our clients. Fill out the form below to get in contact with one of our managers.', 'droid-scanner');
	echo '</p>';
	echo '<form action="' . esc_url( $_SERVER['REQUEST_URI'] ) . '" method="post">';
	echo '<p>';
	// BAD! INJECTING FROM INPUT VARIABLE DIRECTLY INTO PAGE OUTPUT.
	echo __('What is your name?', 'droid-scanner') . '<br/>';
	echo '<input type="text" name="target-name" value="' . ( isset( $_GET["target-name"] ) ? $_GET["target-name"] : '' ) . '" size="40" />';
	echo __('What is your email?', 'droid-scanner') . '<br/>';
	echo '<input type="email" name="target-email" value="' . ( isset( $_GET["target-email"] ) ? $_GET["target-email"] : '' ) . '" size="40" />';
	echo '<p>';
	echo __('What type of leader would you say you are?', 'droid-scanner') . '<br/>';
	echo '<select name="target-inspire">';
  echo '<option value="snoke">' . __('Supreme Leader Snoke', 'droid-scanner') . '</option>';
  echo '<option value="kylo">' . __('Kylo Ren', 'droid-scanner') . '</option>';
  echo '<option value="hux">' . __('General Hux', 'droid-scanner') . '</option>';
	echo '<option value="phasma">' . __('Captain Phasma', 'droid-scanner') . '</option>';
	echo '<option value="vader">' . __('Darth Vader', 'droid-scanner') . '</option>';
	echo '<option value="emperor">' . __('Emperor Palpatine', 'droid-scanner') . '</option>';
	echo '</select>';
	echo '</p>';
	echo '<p> &nbsp; </p>';
	echo '<p><input type="submit" name="target-acquired" value="' . __('Submit', 'droid-scanner') . '" /></p>';
	echo '</form>';
}

function droid_scanner_targeting_process() {

	$name    = isset( $_POST["target-name"] ) ? $_POST["target-name"] : false;
	$email   = isset( $_POST["target-email"] ) ? $_POST["target-email"] : false;
	$inspire = isset( $_POST["target-inspire"] ) ? $_POST["target-inspire"] : false;

	if ( $email ) {

		global $wpdb;
		$table_name = $wpdb->prefix . 'droid_scanner_entries';
		$entry_data = array(
			'time' => date( 'Y-m-d H:i:s'),
			'droid_scanner_name'    => $name,
			'droid_scanner_email'   => $email,
			'droid_scanner_inspire' => $inspire,
		);

		//THIS IS A SLOW FUNCTION PRONE TO DDOS
		sleep(2);

		$storage = $wpdb->insert( $table_name, $entry_data );

		if( $storage ) {
			echo '<div>';
			echo '<p>' . __('Input recieved. Thanks customer!', 'droid-scanner') . '</p>';
			echo '</div>';
		}
	}
}

function droid_scanner_targeting_shortcode() {
	ob_start();
	droid_scanner_targeting_process();
	droid_scanner_targeting_form();
	return ob_get_clean();
}

add_shortcode( 'droid_scanner_targeting', 'droid_scanner_targeting_shortcode' );
