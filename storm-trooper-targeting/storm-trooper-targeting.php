<?php
/**
 * @file
 * Main functions and functionality for Storm Trooper Targeting in WordPress.
 *
 * @package StormTrooperTargeting
 */

/*
Plugin Name: Storm Trooper Targeting
Plugin URI: https://www.deathstarsecurity.com
Description: Never miss another sale!
Version: 1.0
Author: Lockr
Author URI: htts://www.deathstarsecurity.com/
License: GPLv2 or later
Text Domain: storm-trooper-targeting
 */

// Don't call the file directly and give up info!
if ( ! function_exists( 'add_action' ) ) {
	echo 'Lock it up!';
	exit;
}

/**
 * Create Install Post.
 *
 * @file
 */

register_activation_hook( __FILE__, 'storm_trooper_install' );

/**
 * Load plugin text domain
 */
function exhaust_port_seo_load_plugin_textdomain()
{
	load_plugin_textdomain('storm-trooper-targeting', FALSE,	dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('init', 'exhaust_port_seo_load_plugin_textdomain');

/**
 * Initial setup when the plugin is activated.
 */
function storm_trooper_install() {
	// Create the page for this exploit.
	$my_post = array(
		'post_title'   => wp_strip_all_tags( __('Storm Trooper Customer Targeting', 'storm-trooper-targeting') ),
		'post_content' => '[storm_trooper_targeting]',
		'post_status'  => 'publish',
		'post_author'  => 1,
		'post_type'    => 'page',
	);

	wp_insert_post( $my_post );

}

function storm_trooper_targeting_form() {
	echo '<h2>' . __('Are you ready for the best customer experience of your life?!', 'storm-trooper-targeting') . '</h2>';
	echo '<p>';
	echo __('We at Death Star Security are dedicated to serving you as a customer. But before we do, we need to make sure we know who we\'re dealing with. Fill out the form below and we\'ll get in touch with you right away.', 'storm-trooper-targeting');
	echo '</p>';
	echo '<form action="' . esc_url( $_SERVER['REQUEST_URI'] ) . '" method="post">';
	echo '<p>';
	echo __('What is your email?', 'storm-trooper-targeting') . '<br/>';
	// BAD! INJECTING FROM INPUT VARIABLE DIRECTLY INTO PAGE OUTPUT.
	echo '<input type="email" name="target-email" value="' . ( isset( $_POST["target-email"] ) ? $_POST["target-email"] : '' ) . '" size="40" />';
	echo '<p>';
	echo __('Who is your commanding officer', 'storm-trooper-targeting') . '<br/>';
	echo '<select name="target-commander">';
  echo '<option value="snoke">'. __('Supreme Leader Snoke', 'storm-trooper-targeting') . '</option>';
  echo '<option value="kylo">' . __('Kylo Ren', 'storm-trooper-targeting') . '</option>';
  echo '<option value="hux">' . __('General Hux', 'storm-trooper-targeting') . '</option>';
  echo '<option value="phasma">' . __('Captain Phasma', 'storm-trooper-targeting') . '</option>';
	echo '</select>';
	echo '</p>';
	echo '<p> &nbsp; </p>';
	echo '<p><input type="submit" name="target-acquired" value="' . __('Submit', 'storm-trooper-targeting') . '" /></p>';
	echo '</form>';
}

function storm_trooper_targeting_process() {

	// THIS IS BAD! DO NOT INJECT FROM POST VARIABLE WITHOUT SANITIZATION.
	$email     = isset( $_POST["target-email"] ) ? $_POST["target-email"] : '';
	$commander = isset( $_POST["target-commander"] ) ? $_POST["target-commander"] : '';

	if ( $email ) {

		// Proudly borrowed and updated from https://tommcfarlin.com/create-a-user-in-wordpress/#code .
		if ( null == username_exists( $email_address ) ) {

			// Generate the password and create the user.
			$password = 'deathstar';
			$user_id  = wp_create_user( $email, $password, $email );

			// Set the nickname.
			wp_update_user(
				array(
					'ID'       => $user_id,
					'nickname' => $email,
				)
			);

			// Set the role.
			$user = new WP_User( $user_id );
			$user->set_role( 'author' );

			// Email the user.
			wp_mail( $email_address, __('Welcome to Death Star Security!', 'storm-trooper-targeting'), __('Your Password:', 'storm-trooper-targeting') . ' ' . $password );

		}

		// THIS IS BAD! DO NOT UPDATE OPTIONS USING INPUT FROM ANYTHING POSTED TO THE SITE WITHOUT SANITIZING AND VERIFYING. EVEN THEN THINK TWICE ABOUT IT.
		$updated = update_option( $email, $commander );

		if ( $updated ) {
			echo '<div>';
			echo '<p>' . __('Input received. Thanks customer!', 'storm-trooper-targeting') . '</p>';
			echo '</div>';
		}
	}
}

function storm_trooper_targeting_shortcode() {
	ob_start();
	storm_trooper_targeting_process();
	storm_trooper_targeting_form();
	return ob_get_clean();
}

add_shortcode( 'storm_trooper_targeting', 'storm_trooper_targeting_shortcode' );
