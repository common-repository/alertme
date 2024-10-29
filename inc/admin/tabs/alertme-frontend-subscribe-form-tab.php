<?php
$new_options = array();
if ( isset( $_POST['Submit'] ) ) {
	// Nonce verification 
	check_admin_referer( 'alert-me-fontend-subscribe-form-tab' );
	$new_options['alert_me_form_heading_text'] = ( isset( $_POST['alert_me_form_heading_text'] ) ) ? sanitize_text_field(trim($_POST['alert_me_form_heading_text'])) : $alert_me_form_heading_text;
	$new_options['alert_me_form_success_message'] = ( isset( $_POST['alert_me_form_success_message'] ) ) ? htmlentities($_POST['alert_me_form_success_message']) : $alert_me_form_success_message;
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
		<?php wp_nonce_field('alert-me-fontend-subscribe-form-tab'); ?>
		<div class="postbox">
			<div class="inside">
				<h2><?php echo esc_html__('Front-End Subscribe Form Settings', ALERTME_TXT_DOMAIN); ?></h2>
				<hr>
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row">
								<?php echo esc_html__('Subscribe Heading Text', ALERTME_TXT_DOMAIN); ?>
							</th>
							<td>
								<textarea name="alert_me_form_heading_text" style="width: 100%;"><?php echo ((isset($options['alert_me_form_heading_text'])) ? $options['alert_me_form_heading_text'] :  $alert_me_form_heading_text ); ?></textarea>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<?php echo esc_html__('Success Message', ALERTME_TXT_DOMAIN); ?>
							</th>
							<td>
								<?php 
									$success_message = ((isset($options['alert_me_form_success_message'])) ? $options['alert_me_form_success_message'] :  $alert_me_form_success_message );
									wp_editor( html_entity_decode($success_message), 'alert_me_form_success_message' ); 
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