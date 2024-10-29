<?php
$new_options = array();
if ( isset( $_POST['Submit'] ) ) {
	// Nonce verification 
	check_admin_referer( 'alert-me-display-setting-tab' );
	$new_options['alert_me_position'] = ( isset( $_POST['alert_me_position'] ) ) ? sanitize_text_field($_POST['alert_me_position']) : 'bottom';
	$new_options['display_alert_me_post_type'] = ( isset( $_POST['display_alert_me_post_type'] ) ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['display_alert_me_post_type'] ) ) : array();
	$new_options['alert_me_post_type_visibility_setting'] = ( isset( $_POST['alert_me_post_type_visibility_setting'] ) ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['alert_me_post_type_visibility_setting'] ) ) : array();
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
		<?php wp_nonce_field('alert-me-display-setting-tab'); ?>
		<div class="postbox">
			<div class="inside">
				<h2><?php echo esc_html__( 'Subscribe Box Settings', ALERTME_TXT_DOMAIN ); ?></h2>
				<hr>
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row">
								<?php echo esc_html__('Placement of Alert Me Box', ALERTME_TXT_DOMAIN); ?>
							</th>
							<td><?php printf( __( '%s','add-to-any' ), alertme_position_in_content( $options, true ) ); ?>
								<p class="description" id="tagline-description">
									<?php echo esc_html__( 'Select the position where you want to display the AlertMe! subscription form.', ALERTME_TXT_DOMAIN ); ?>
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<?php echo esc_html__('Post Types', ALERTME_TXT_DOMAIN); ?>
							</th>
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
							<th scope="row">
								<?php echo esc_html__('Post Types Visibility Setting', ALERTME_TXT_DOMAIN); ?>
							</th>
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
					</tbody>
				</table>					
			</div>
		</div>
		<p class="submit">
			<input class="button-primary" type="submit" name="Submit" value="<?php _e('Save Changes', 'alert-me' ) ?>" />
		</p>
	</form>
</div>