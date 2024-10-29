<?php
$new_options = array();
if ( isset( $_POST['Submit'] ) ) {
	// Nonce verification 
	check_admin_referer( 'alert-me-notification-email-tab' );
	$new_options['alert_me_email_subject_line'] = ( isset( $_POST['alert_me_email_subject_line'] ) ) ? sanitize_text_field(trim($_POST['alert_me_email_subject_line'])) : $alert_me_email_subject_line;
	$new_options['alert_me_email_body'] = ( isset( $_POST['alert_me_email_body'] ) ) ? htmlentities($_POST['alert_me_email_body']) : '';	
	// Get all existing AddToAny options
	$existing_options = get_option( 'alertme_options', array() );
	
	// Merge $new_options into $existing_options to retain AddToAny options from all other screens/tabs
	if ( $existing_options ) {
		$new_options = array_merge( $existing_options, $new_options );
	}
	
	update_option( 'alertme_options', $new_options );
	
	?>
	<div class="updated"><p><?php _e( 'Settings saved.' ); ?></p></div>
	<?php
}
$options = stripslashes_deep( get_option( 'alertme_options', array() ) );
//echo "<pre>new_options";		print_r($options);		echo "</pre>";
?>
<div class="wrap">
	<form id="alertme_admin_form" method="post" action="">
		<?php wp_nonce_field('alert-me-notification-email-tab'); ?>
		<div class="postbox">
			<div class="inside">
				<h2><?php echo esc_html__('Notification Email Text', ALERTME_TXT_DOMAIN); ?></h2>
				<hr>
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row">
								<?php echo esc_html__('Email Subject Line', ALERTME_TXT_DOMAIN); ?>
							</th>
							<td>
								<textarea name="alert_me_email_subject_line" style="width: 100%;"><?php echo ((isset($options['alert_me_email_subject_line'])) ? $options['alert_me_email_subject_line'] :  $alert_me_email_subject_line ); ?></textarea>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<strong>Email body shortcodes</strong>
							</th>
							<td>
								To display <strong>Post or Page</strong> title with link, use: <strong>{alertme_post_name}</strong>
							<br>
								To display a <strong>unsubscribe link</strong>, use: <strong>{alertme_unsubscribe}</strong>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<?php echo esc_html__('Email Body Section', ALERTME_TXT_DOMAIN); ?>
							</th>
							<td>
								<?php 
									$email_body = ((isset($options['alert_me_email_body'])) ? $options['alert_me_email_body'] :  $alert_me_email_body );
									wp_editor( html_entity_decode($email_body), 'alert_me_email_body' ); 
								?>
							</td>
						</tr>
					</tbody>
				</table>					
			</div>
		</div>
		<p class="submit">
			<input class="button-primary" type="submit" name="Submit" value="<?php _e('Save Changes', 'alert-me' ) ?>" />
		</p>
	</form>
</div>