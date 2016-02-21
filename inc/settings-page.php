<?php
// Get some plugin details
$anbph_options = get_option( 'anbph_hipchat' );

$auth_token = ! empty( $anbph_options['auth_token'] ) ? $anbph_options['auth_token'] : '';
$from_name = ! empty( $anbph_options['from_name'] ) ? $anbph_options['from_name'] : '';
$room_name = ! empty( $anbph_options['room_name'] ) ? $anbph_options['room_name'] : '';
$message_length = ! empty( $anbph_options['message_length'] ) ? $anbph_options['message_length'] : '';

$updated = null;
$error = null;

if ( ! isset( $_REQUEST['settings-updated'] ) ) {
	$_REQUEST['settings-updated'] = false;
}

// Not the best way to handle this, but still
if ( isset( $_POST['anbph_check_integration'] ) ) {
	$successful = false;
	
	// make sure token is valid and room exists
	$hc = new Anpbp_HipChat( $auth_token );
	try {
		$r = $hc->message_room( $room_name, $from_name, "Plugin enabled successfully." );
		if ( ! empty( $r ) ) {
			$successful = true;
		}
	} catch ( Anpbp_HipChat_Exception $e ) {
		// token must have failed
	}

	if ( $successful !== true ) {
		$error = __( 'Bad auth token or room name.', ANBPH_TEXTDOMAINN );
	} else if ( empty( $from_name ) ) {
		$error = __( 'Please enter a "From Name"', ANBPH_TEXTDOMAIN );
	} else if (strlen( $from_name ) > 15) {
		$error = __( 'From Name" must be less than 15 characters.', ANBPH_TEXTDOMAIN );
	} else if ( empty( $room_name ) ) {
		$error = __( 'Please enter a "Room Name"', ANBPH_TEXTDOMAIN );
	} else {;
		$updated =  __( 'Settings saved! Auth token is valid and room exists.', ANBPH_TEXTDOMAIN );
	}
}
?>

<div class="wrap">
	<h1><?php _e( 'BuddyPress and HipChat Integration', ANBPH_TEXTDOMAIN ); ?></h1>
	
	<form method="post" action="options.php">
		<?php settings_fields( 'anbph_hipchat' ); ?>
	    <table class="form-table">
	        <tr valign="top">
		        <th scope="row"><?php _e( 'Auth Token', ANBPH_TEXTDOMAIN ); ?></th>
		        <td>
		        	<input type="text" id="auth_token" name="anbph_hipchat[auth_token]" value="<?php echo esc_attr( $auth_token ); ?>" />
		        	<label for="auth_token">A HipChat <a href="http://www.hipchat.com/group_admin/api" target="_blank"> API token</a>.</label>
		        </td>
	        </tr>
	         
	        <tr valign="top">
		        <th scope="row"><?php _e( 'From Name', ANBPH_TEXTDOMAIN ); ?></th>
		        <td>
		        	<input type="text" id="from_name" name="anbph_hipchat[from_name]" value="<?php echo esc_attr( $from_name ); ?>" />
		        	<label for="from_name"><?php _e( 'Name the messages will come from.', ANBPH_TEXTDOMAIN ); ?></label>
	        	</td>
	        </tr>
	        
	        <tr valign="top">
		        <th scope="row"><?php _e( 'Room Name', ANBPH_TEXTDOMAIN ); ?></th>
		        <td>
		        	<input type="text" id="room_name" name="anbph_hipchat[room_name]" value="<?php echo esc_attr( $room_name ); ?>" />
		        	<label for="room_name"><?php _e( 'Name of the room to send messages to.', ANBPH_TEXTDOMAIN ); ?></label>
		        </td>
	        </tr>
	        
	        <tr valign="top">
		        <th scope="row"><?php _e( 'Message length', ANBPH_TEXTDOMAIN ); ?></th>
		        <td>
		        	<input type="text" id="message_length" name="anbph_hipchat[message_length]" value="<?php echo esc_attr( $message_length ); ?>" />
		        	<label for="message_length"><?php _e( 'The number of characters that will be displayed. By default they will be 10.', ANBPH_TEXTDOMAIN ); ?></label>
		        </td>
	        </tr>
	    </table>
	    <?php submit_button(); ?>
	</form>
	
	<form method="post" action="">
		<input type="hidden" id="auth_token" name="anbph_hipchat[auth_token]" value="<?php echo esc_attr( $auth_token ); ?>" />
		<input type="hidden" id="from_name" name="anbph_hipchat[from_name]" value="<?php echo esc_attr( $from_name ); ?>" />
		<input type="hidden" id="room_name" name="anbph_hipchat[room_name]" value="<?php echo esc_attr( $room_name ); ?>" />
		<div>
			<?php
			if ( ! empty( $updated ) ) {
		  		echo '<div class="updated">'. $updated .'</div>';
			}
			
			if ( ! empty( $error ) ) {
		  		echo '<div class="error">'. $error .'</div>';
			}
			
			_e( 'Click the button to check if the integration is okay. This will send a message ot the room.', ANBPH_TEXTDOMAIN );
			submit_button( 'Check Integration', 'primary', 'anbph_check_integration' );
			?>
		</div>
	</form>
</div>