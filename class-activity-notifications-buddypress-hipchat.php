<?php
/*
Plugin Name: Activity Notifications for BuddyPress and HipChat
Plugin URI: https://github.com/metodiew/Activity-Notifications-for-BuddyPress-and-HipChat
Description: Send a message to a HipChat room whenever a BuddyPress Activity is published.
Version: 1.0
Author: Stanko Metodiev
Author URI: http://metodiew.com
Text Domain: anbph
Domain Path: /languages
License: GNU GPL v2
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Include HipChat core
require_once( 'lib/HipChat.php' );

// Defines
if ( ! defined( 'ANBPH_TEXTDOMAIN' ) ) {
	define( 'ANBPH_TEXTDOMAIN', 'anbph' );
}

if ( ! defined( 'ANBPH_DIR' ) ) {
	define( 'ANBPH_DIR', dirname( __FILE__ ) ); // plugin dir
}

if ( ! defined( 'ANBPH_URL' ) ) {
	define( 'ANBPH_URL', plugin_dir_url( __FILE__ ) ); // plugin URL
}

// Sorry for the long, long, long class name
if ( ! class_exists( 'Activity_Notifications_BuddyPress_HipChat' ) ) :
	
class Activity_Notifications_BuddyPress_HipChat {

	private $version = '1.0';
	
	public function __construct() {
		
		// Actions
		add_action( 'admin_init', array( $this, 'register_settings_cb' ) );
		add_action( 'admin_menu', array( $this, 'add_option_page' ) );
		
		// Filters
		add_filter( 'bp_activity_after_save', array( $this, 'hipchat_send_activity_notification' ) );
	}
	
	/**
	 * Settings Page Callback
	 */
	public function add_option_page() {
		add_options_page( 
			__( 'BuddyPress and HipChat', ANBPH_TEXTDOMAIN ),
			__( 'BuddyPress and HipChat', ANBPH_TEXTDOMAIN ),
			'manage_options', 
			'anbph-settings-page', 
			array( $this, 'include_options_page' )
		);
	}
	
	function register_settings_cb() {
		register_setting( 'anbph_hipchat', 'anbph_hipchat', array( $this, 'save_settings' ) );
	}
	
	public function include_options_page() {
		require_once( ANBPH_DIR . '/inc/settings-page.php' );
	}
	
	public function hipchat_send_activity_notification( $post ) {
		if ( empty( $post ) ) {
			return;
		}
		
		// We need to display only new posts
		if ( $post->type !== 'activity_update' ) {
			return;
		}
		
		try {
		    $auth_token = '';
			$room = '';
			$from = '';
			$r = '';
			
			// Get Plugin Options
			$anbph_options = get_option( 'anbph_hipchat' );
			$message_length = ! empty( $anbph_options['message_length'] ) ? $anbph_options['message_length'] : 10;
			
			if ( ! empty( $anbph_options['auth_token'] ) ) {
				$auth_token = $anbph_options['auth_token'];
			}
		
		    // Return if plugin is not configured propery
		    if ( empty( $auth_token ) ) {
				return;
		    }
			
			if ( ! empty( $anbph_options['room_name'] ) ) {
				$room = $anbph_options['room_name'];
			}
			
			if ( ! empty( $anbph_options['from_name'] ) ) {
				$from = $anbph_options['from_name'];
			}

			$user = '';
			
			$activity_url = bp_activity_get_permalink( $post->id );
			$user_data = get_userdata( $post->user_id );
			$activity_content = $post->content;
			
			if ( ! empty( $activity_content ) ) {
				$activity_content = wp_trim_words( $activity_content, $message_length );
			} else {
				$activity_content = __( 'No Title', ANBPH_TEXTDOMAIN );
			}
			
			if ( ! empty( $user_data ) ) {
				$user = $user_data->display_name;
			}
			
			$message = $user .' just posted <a href="'. $activity_url .'">'. $activity_content .'</a>';
			
			if ( ! empty( $room ) && ! empty( $from ) && ! empty( $message ) ) {
				$bp_hc = new Anpbp_HipChat( $auth_token );
				$r = $bp_hc->message_room( $room, $from, $message );
			}
		    
		    if ( empty( $r ) ) {
		      // Something went wrong, not sure what to do here?
		    }
		} catch ( Anpbp_HipChat_Exception $e ) {
			// Something went wrong, not sure what to do here?
		}
	
		return $post;
	}
	
	/**
	 * Validate Settings
	 * 
	 * Filter the submitted data as per your request and return the array
	 * 
	 * @param array $input
	 */
	function save_settings( $input ) {
		
		$input['auth_token'] = ! empty( $input['auth_token'] ) ? esc_attr( $input['auth_token'] ) : '';
		$input['from_name'] = ! empty( $input['from_name'] ) ? esc_attr( $input['from_name'] ) : '';
		$input['room_name'] = ! empty( $input['room_name'] ) ? esc_attr( $input['room_name'] ) : '';
		$input['message_length'] = ! empty( $input['message_length'] ) ? esc_attr( $input['message_length'] ) : '';
		
		return $input;
	}
}

// Let's roll
$anbph = new Activity_Notifications_BuddyPress_HipChat();
endif;