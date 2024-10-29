<?php
$new_options = array();
if ( isset( $_POST['Submit'] ) ) {
	// Nonce verification 
	check_admin_referer( 'alert-me-fontend-subscribe-form-tab' );
	$new_options['alert_me_my_subscription_page'] = ( isset( $_POST['alert_me_my_subscription_page'] ) ) ? sanitize_text_field($_POST['alert_me_my_subscription_page']) : '0';
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
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row">
								<?php echo esc_html__('My Subscriptions page for front-end', ALERTME_TXT_DOMAIN); ?>
							</th>
							<td><?php wp_dropdown_pages(array('name' => 'alert_me_my_subscription_page', 'selected' => ((isset($options['alert_me_my_subscription_page'])) ? $options['alert_me_my_subscription_page'] : 0 ) )); ?>
							</td>
						</tr>
						
					</tbody>
				</table>
				<h3><?php echo esc_html__('How to setup the Manage Subscriptions page', ALERTME_TXT_DOMAIN); ?></h3>
				<hr>
				<p>
					<?php echo esc_html__('1) Create a new page "Manage Subscriptions"', ALERTME_TXT_DOMAIN); ?>
				</p>
				<p>
					<?php echo __('2) Add the following shortcode to the page: <b>[alertme-subscriptions-list]</b>', ALERTME_TXT_DOMAIN); ?>
				</p>
				<p>
					<?php echo __('3) Publish the page', ALERTME_TXT_DOMAIN); ?>
				</p>
			</div>
		</div>
		<p class="submit">
			<input class="button-primary" type="submit" name="Submit" value="<?php _e('Save Changes', 'alert-me' ) ?>" />
		</p>
	</form>
</div>