<?php

/**
 * @file
 * Main functions and functionality for Exhaust Port SEO in WordPress.
 *
 * @package ExhaustPort
 */

/*
Plugin Name: Exhaust Port SEO
Plugin URI: https://www.deathstarsecurity.com
Description: Make your site explode with traffic
Version: 1.0
Author: Lockr
Author URI: htts://www.deathstarsecurity.com/
License: GPLv2 or later
Text Domain: lockr
 */

require_once( ABSPATH . '/wp-includes/pluggable.php' );

/**
 * Create Install Post.
 *
 * @file
 */

register_activation_hook( __FILE__, 'exhaust_port_install' );

/**
 * Initial setup when the plugin is activated.
 */
function exhaust_port_install() {
	// Create the page for this exploit.
	$my_post = array(
		'post_title'   => wp_strip_all_tags( 'Exhaust Port SEO' ),
		'post_content' => '[exhaust_port_targeting]',
		'post_status'  => 'publish',
		'post_author'  => 1,
		'post_type'    => 'page',
	);

	wp_insert_post( $my_post );

}

function exhaust_port_targeting_form() {
	echo '<h2>Want to get the top ranking on every search?</h2>';
	echo '<p>';
	echo "We at Death Star Security are dedicated to making our customers content the best it can be. Use this tool to test your post before you create it to get an SEO scoe and make sure your site explodes with traffic.";
	echo '</p>';
	echo '<p>';
	echo "This form is only for registered customers though! If you're not a customer contact us and we'll connect you with our sales team.";
	echo '</p>';

	if ( current_user_can('publish_posts') ) {
		echo '<form action="' . esc_url( $_SERVER['REQUEST_URI'] ) . '" method="post">';
		echo '<p>';
		echo 'What is the post title?<br/>';
		// BAD! INJECTING FROM INPUT VARIABLE DIRECTLY INTO PAGE OUTPUT.
		echo '<input type="text" name="target-title" value="' . ( isset( $_POST["target-title"] ) ? $_POST["target-title"] : '' ) . '" size="40" />';
		echo '<p>';
		echo 'What is the content of your post? <br/>';
		// BAD! INJECTING FROM INPUT VARIABLE DIRECTLY INTO PAGE OUTPUT.
		echo '<textarea name="target-content" rows="10"  >' . ( isset( $_POST["target-content"] ) ? $_POST["target-content"] : '' ) . '</textarea>';
		echo '</p>';
		echo '<p> &nbsp; </p>';
		echo '<p><input type="submit" name="target-acquired" value="Submit" /></p>';
		echo '</form>';
	}

}

function exhaust_port_targeting_process() {
	if ( current_user_can('publish_posts') ) {
		$title   = isset( $_POST["target-title"] ) ? $_POST["target-title"] : false;
		$content = isset( $_POST["target-content"] ) ? $_POST["target-content"] : false;

		// No nonce verification leads to CSRF
		if ( $content && $title ) {

			$seo_post = array(
				'post_title'   => wp_strip_all_tags( $title ),
				//EXTRA XSS vulnerability as we print out unsanitized body content.
				'post_content' => $content,
				'post_status'  => 'publish',
				'post_author'  => 1,
				'post_type'    => 'post',
			);
			wp_insert_post( $seo_post );

			echo '<div>';
			echo '<p>Great post! You are definitely going to the top of the rankings! </p>';
			echo '</div>';
		}
	}
}

function exhaust_port_targeting_shortcode() {
	ob_start();
	exhaust_port_targeting_process();
	exhaust_port_targeting_form();
	return ob_get_clean();
}
add_shortcode( 'exhaust_port_targeting', 'exhaust_port_targeting_shortcode' );

function exhaust_port_cors_http_header(){
    header("Access-Control-Allow-Origin: https://rebel.deathstarsecurity.com");
}
add_action('init','exhaust_port_cors_http_header');