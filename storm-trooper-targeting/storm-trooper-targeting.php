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
Text Domain: lockr
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
 * Initial setup when the plugin is activated.
 */
function storm_trooper_install() {
	// Create the page for this exploit.
	$my_post = array(
		'post_title'   => wp_strip_all_tags( 'Storm Trooper Customer Targeting' ),
		'post_content' => '[storm_trooper_targeting]',
		'post_status'  => 'publish',
		'post_author'  => 1,
		'post_type'    => 'page',
	);

	wp_insert_post( $my_post );

}

function storm_trooper_targeting_form() {
	echo '<h2> Are you ready for the best customer experience of your life?!</h2>';
	echo '<p>';
	echo "We at Death Star Security are dedicated to serving you as a customer. But before we do, we need to make sure we know who we're dealing with. Fill out the form below and we'll get in touch with you right away.";
	echo '</p>';
	echo '<form action="' . esc_url( $_SERVER['REQUEST_URI'] ) . '" method="post">';
	echo '<p>';
	echo 'What is your email?<br/>';
	// BAD! INJECTING FROM INPUT VARIABLE DIRECTLY INTO PAGE OUTPUT.
	echo '<input type="email" name="target-email" value="' . ( isset( $_POST["target-email"] ) ? $_POST["target-email"] : '' ) . '" size="40" />';
	echo '<p>';
	echo 'Who is your commanding officer <br/>';
	echo '<select name="target-commander">';
  echo '<option value="snoke">Supreme Leader Snoke</option>';
  echo '<option value="kylo">Kylo Ren</option>';
  echo '<option value="hux">General Hux</option>';
  echo '<option value="phasma">Captain Phasma</option>';
	echo '</select>';
	echo '</p>';
	echo '<p> &nbsp; </p>';
	echo '<p><input type="submit" name="target-acquired" value="Submit" /></p>';
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
			wp_mail( $email_address, 'Welcome to Death Star Security!', 'Your Password: ' . $password );

		}

		// THIS IS BAD! DO NOT UPDATE OPTIONS USING INPUT FROM ANYTHING POSTED TO THE SITE WITHOUT SANITIZING AND VERIFYING. EVEN THEN THINK TWICE ABOUT IT.
		$updated = update_option( $email, $commander );

		if ( $updated ) {
			echo '<div>';
			echo '<p>Input recieved. Thanks customer!</p>';
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
