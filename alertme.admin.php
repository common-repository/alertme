<?php
function alert_me_add_meta_box() {
	$post_types = get_post_types( array( 'public' => true ) );
	$options = get_option( 'alertme_options', array() );
	$title = apply_filters( 'alertme_meta_box_title', __( 'AlertMe', 'alert-me' ) );
	foreach( $post_types as $post_type ) {
		
		if (isset($options['display_alert_me_post_type'])):
			if (array_key_exists($post_type , $options['display_alert_me_post_type'])) {
				add_meta_box( 'alert_me_meta', $title, 'alert_me_meta_box_content', $post_type, 'side', 'default' );
			}
		endif;
	}
}
function alert_me_meta_box_content( $post ) {
	$post_type = get_post_type( $post );
	$options = get_option( 'alertme_options', array() );
	$alertme_enable_post_alertme = get_post_meta( get_the_ID(), 'alertme_enable_post_alertme', true );
	?>
	
	<?php if (array_key_exists($post_type, $options['display_alert_me_post_type']) && $options['alert_me_post_type_visibility_setting'][$post_type] == 'manual'): ?>

		<p>
			<label for="enable_post_alertme">
				<input type="checkbox" name="enable_post_alertme" id="enable_post_alertme" value="1" <?php echo ((!empty($alertme_enable_post_alertme)) ? 'checked="checked"' : ''); ?>>
				<?php _e( 'Show alert me box.' , 'alert-me'); ?>
			</label>
		</p>

	<?php endif; ?>

	<p>
		<label for="rt_send_alert_for_selected_post">
			<input type="checkbox" name="rt_send_alert_for_selected_post" id="rt_send_alert_for_selected_post" value="1" onchange="if (jQuery) jQuery('input[name=&quot;rt_send_alert_for_selected_post&quot;]').attr('checked', jQuery(this).is(':checked'))">
			<?php _e( 'Send update notification.' , 'alert-me'); ?>
		</label>
	</p>
<?php
}
function alert_me_meta_box_save( $post_id ) {
	// If this is an autosave, this form has not been submitted, so we don't want to do anything.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		return $post_id;
	// Save alertme_disabled if "Show alert me box" checkbox is unchecked
	if ( isset( $_POST['post_type'] ) ) {
		if ( current_user_can( 'edit_post', $post_id ) ) {

			if ( ! isset( $_POST['enable_post_alertme'] ) ) {
				delete_post_meta( $post_id, 'alertme_enable_post_alertme' );
			} else {
				update_post_meta( $post_id, 'alertme_enable_post_alertme', 1 );
			}

		}
	}	
}
add_action( 'admin_init', 'alert_me_add_meta_box' );
add_action( 'save_post', 'alert_me_meta_box_save' );
add_action( 'edit_attachment', 'alert_me_meta_box_save' );
/**
 * Alert Me settings page (admin)
 */
function alert_me_settings() {
	global $alert_me_form_heading_text, $alert_me_form_success_message, $alert_me_email_subject_line;
	// Require admin privs
	if ( ! current_user_can( 'manage_options' ) )
		return false;
	
	$new_options = array();
	if ( isset( $_POST['Submit'] ) ) {
		// Nonce verification 
		check_admin_referer( 'alert-me-update-options' );

		$new_options['alert_me_position'] = ( isset( $_POST['alert_me_position'] ) ) ? sanitize_text_field($_POST['alert_me_position']) : 'bottom';
		$new_options['display_alert_me_post_type'] = ( isset( $_POST['display_alert_me_post_type'] ) ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['display_alert_me_post_type'] ) ) : array();
		$new_options['alert_me_post_type_visibility_setting'] = ( isset( $_POST['alert_me_post_type_visibility_setting'] ) ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['alert_me_post_type_visibility_setting'] ) ) : array();
		$new_options['alert_me_form_heading_text'] = ( isset( $_POST['alert_me_form_heading_text'] ) ) ? sanitize_text_field(trim($_POST['alert_me_form_heading_text'])) : $alert_me_form_heading_text;
		$new_options['alert_me_form_success_message'] = ( isset( $_POST['alert_me_form_success_message'] ) ) ? sanitize_text_field(trim($_POST['alert_me_form_success_message'])) : $alert_me_form_success_message;
		$new_options['alert_me_opt_out_thank_you_page'] = ( isset( $_POST['alert_me_opt_out_thank_you_page'] ) ) ? sanitize_text_field($_POST['alert_me_opt_out_thank_you_page']) : '0';
		$new_options['alert_me_email_body'] = ( isset( $_POST['alert_me_email_body'] ) ) ? htmlentities($_POST['alert_me_email_body']) : '';

		$new_options['alert_me_email_subject_line'] = ( isset( $_POST['alert_me_email_subject_line'] ) ) ? sanitize_text_field(trim($_POST['alert_me_email_subject_line'])) : $alert_me_email_subject_line;


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
			<?php wp_nonce_field('alert-me-update-options'); ?>

			<div class="postbox">
				<div class="inside">
					<h2><?php _e( 'Alert Me Settings', 'alert-me' ); ?></h2>
					<hr>
					<table class="form-table">
						<tbody>
							<tr>
								<th scope="row">Placement of Alert Me Box</th>
								<td><?php printf( __( '%s','add-to-any' ), alertme_position_in_content( $options, true ) ); ?>
									<p class="description" id="tagline-description">Select the position where you want to display the Alert Me subscription form.</p>
								</td>
							</tr>
							<tr>
								<th scope="row">Post Types</th>
								<td>
									<?php  
									$getPostTypes = alertme_getPostTypes();
									if (!empty($getPostTypes)):
										foreach ( $getPostTypes as $custom_post_type_obj ) {
										$placement_name = $custom_post_type_obj->name;
										?>
											<fieldset>
												<legend class="screen-reader-text"><span><?php echo ucwords($placement_name); ?></span></legend>
												<label for="alert_me_post_type_<?php echo $custom_post_type_obj->name; ?>">
													<input name="display_alert_me_post_type[<?php echo $custom_post_type_obj->name; ?>]" type="checkbox" id="alert_me_post_type_<?php echo $custom_post_type_obj->name; ?>" value="1" <?php echo ((isset($options['display_alert_me_post_type'][$custom_post_type_obj->name])) ? "checked='checked'" : '' ); ?>>
														<?php echo ucwords($placement_name); ?>
												</label>
											</fieldset>								
										<?php } ?>
									<?php endif; ?>
									<?php  
									$custom_post_types = alertme_getCustomPostTypes();
									if (!empty($custom_post_types)):
										foreach ( $custom_post_types as $custom_post_type_obj ) {
										$placement_name = $custom_post_type_obj->name;
										?>
											<fieldset>
												<legend class="screen-reader-text"><span><?php echo ucwords($placement_name); ?></span></legend>
												<label for="alert_me_post_type_<?php echo $custom_post_type_obj->name; ?>">
													<input name="display_alert_me_post_type[<?php echo $custom_post_type_obj->name; ?>]" type="checkbox" id="alert_me_post_type_<?php echo $custom_post_type_obj->name; ?>" value="1" <?php echo ((isset($options['display_alert_me_post_type'][$custom_post_type_obj->name])) ? "checked='checked'" : '' ); ?>>
														<?php echo ucwords($placement_name); ?>
												</label>
											</fieldset>
										<?php } ?>
									<?php endif; ?>
								</td>
							</tr>
							<tr>
								<th scope="row">Post Types Visibility Setting</th>
								<td>
									<?php
									if (!empty($getPostTypes)):
										foreach ( $getPostTypes as $custom_post_type_obj ) {
										$placement_name = $custom_post_type_obj->name;
										?>
											<fieldset>
												<legend class="screen-reader-text"><span><?php echo ucwords($placement_name); ?></span></legend>
												<label for="alert_me_post_type_visibility_setting<?php echo $custom_post_type_obj->name; ?>"> <?php echo ucwords($custom_post_type_obj->name); ?>
												<select name="alert_me_post_type_visibility_setting[<?php echo $custom_post_type_obj->name; ?>]" id="alert_me_post_type_visibility_setting<?php echo $custom_post_type_obj->name; ?>">
													<option value="auto" <?php echo (($options['alert_me_post_type_visibility_setting'][$custom_post_type_obj->name] == 'auto') ? "selected='selected'" : '' ); ?>><?php _e( 'Automatic' , 'alert-me'); ?></option>
													<option value="manual" <?php echo (($options['alert_me_post_type_visibility_setting'][$custom_post_type_obj->name] == 'manual' ) ? "selected='selected'" : '' ); ?>><?php _e( 'Manual' , 'alert-me'); ?></option>
												</select>
													
												</label>
											</fieldset>
										<?php } ?>
									<?php endif; ?>
									<?php
									if (!empty($custom_post_types)):
										foreach ( $custom_post_types as $custom_post_type_obj ) {
										$placement_name = $custom_post_type_obj->name;
										?>
											<fieldset>
												<legend class="screen-reader-text"><span><?php echo ucwords($placement_name); ?></span></legend>
												<label for="alert_me_post_type_visibility_setting<?php echo $custom_post_type_obj->name; ?>"> <?php echo ucwords($custom_post_type_obj->name); ?>

												<select name="alert_me_post_type_visibility_setting[<?php echo $custom_post_type_obj->name; ?>]" id="alert_me_post_type_visibility_setting<?php echo $custom_post_type_obj->name; ?>">
													<option value="auto" <?php echo (($options['alert_me_post_type_visibility_setting'][$custom_post_type_obj->name] == 'auto') ? "selected='selected'" : '' ); ?>><?php _e( 'Automatic' , 'alert-me'); ?></option>
													<option value="manual" <?php echo (($options['alert_me_post_type_visibility_setting'][$custom_post_type_obj->name] == 'manual' ) ? "selected='selected'" : '' ); ?>><?php _e( 'Manual' , 'alert-me'); ?></option>
												</select>
												</label>
											</fieldset>
										<?php } ?>
									<?php endif; ?>
								</td>
							</tr>
							<tr>
								<td colspan="2"><hr></td>
							</tr>
							
							<tr>
								<td colspan="2"><h2>Front-End Subscribe Form Settings</h2></td>
							</tr>
							<tr>
								<th scope="row">Subscribe Heading Text</th>
								<td>
									<textarea name="alert_me_form_heading_text" style="width: 100%;"><?php echo ((isset($options['alert_me_form_heading_text'])) ? $options['alert_me_form_heading_text'] :  $alert_me_form_heading_text ); ?></textarea>
								</td>
							</tr>
							<tr>
								<th scope="row">Success Message</th>
								<td>
									<textarea name="alert_me_form_success_message" style="width: 100%;"><?php echo ((isset($options['alert_me_form_success_message'])) ? $options['alert_me_form_success_message'] :  $alert_me_form_success_message ); ?></textarea>
								</td>
							</tr>
							<tr>
								<th scope="row">Unsubscribe notification page</th>
								<td><?php wp_dropdown_pages(array('name' => 'alert_me_opt_out_thank_you_page', 'selected' => $options['alert_me_opt_out_thank_you_page'] )); ?>
								</td>
							</tr>
							<tr>
								<td colspan="2"><hr></td>
							</tr>							
							<tr>
								<td colspan="2"><h2>Notification Email Text</h2></td>
							</tr>
							<tr>
								<th scope="row">Email Subject Line</th>
								<td>
									<textarea name="alert_me_email_subject_line" style="width: 100%;"><?php echo ((isset($options['alert_me_email_subject_line'])) ? $options['alert_me_email_subject_line'] :  $alert_me_email_subject_line ); ?></textarea>
								</td>
							</tr>							
							<tr>
								<th scope="row"><strong>Email body shortcodes</strong></th>
								<td>
									To display <strong>Post or Page</strong> title with link, use: <strong>{alertme_post_name}</strong>
								<br>
									To display a <strong>unsubscribe link</strong>, use: <strong>{alertme_unsubscribe}</strong>
								</td>
							</tr>
							<tr>
								<th scope="row">Email Body Section</th>
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
<?php }
function alertme_position_in_content( $options, $option_box = false ) {
	
	if ( ! isset( $options['alert_me_position'] ) ) {
		$options['alert_me_position'] = 'bottom';
	}
	
	$positions = array(
		'bottom' => array(
			'selected' => ( 'bottom' == $options['alert_me_position'] ) ? ' selected="selected"' : '',
			'string' => __( 'bottom', 'alert-me' )
		),
		'top' => array(
			'selected' => ( 'top' == $options['alert_me_position'] ) ? ' selected="selected"' : '',
			'string' => __( 'top', 'alert-me' )
		),
		'both' => array(
			'selected' => ( 'both' == $options['alert_me_position'] ) ? ' selected="selected"' : '',
			'string' => __( 'top &amp; bottom', 'alert-me' )
		)
	);
	
	if ( $option_box ) {
		$html .= '<select name="alert_me_position">';
		$html .= '<option value="bottom"' . $positions['bottom']['selected'] . '>' . $positions['bottom']['string'] . '</option>';
		$html .= '<option value="top"' . $positions['top']['selected'] . '>' . $positions['top']['string'] . '</option>';
		$html .= '<option value="both"' . $positions['both']['selected'] . '>' . $positions['both']['string'] . '</option>';
		$html .= '</select>';
		
		return $html;
	} else {
		$html = '<span class="alert_me_position">';
		$html .= $positions[$options['position']]['string'];
		$html .= '</span>';
		
		return esc_html($html);
	}
}
?>