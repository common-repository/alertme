<?php
/**
 * Load CSS for frontend
*/
function alert_me_load_plugin_css() {
	if ( ! is_admin() ) {
		if (!wp_script_is('jquery')) {
			wp_enqueue_script('jquery');
		}
				
		wp_enqueue_style( 'alert-me-font', '//fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800&display=swap', false, time() );
		wp_enqueue_style( 'alert-me', ALERTME_ASSETS . 'css/alert_me.css', false, time() );
		wp_enqueue_script( 'alert-me-js', ALERTME_ASSETS . 'js/alertme.js', false, time());
	}
}
add_action( 'wp_enqueue_scripts', 'alert_me_load_plugin_css', 20 );
function alert_me_pre_get_posts( $query ) {
	if ( $query->is_main_query() ) {
		// Hook to change the standard buttons' priority number in content
		// Example: add_filter( 'addtoany_content_priority', 20 );
		$priority = apply_filters( 'alert_me_content_priority', 98 );
		add_filter( 'the_content', 'alert_me_add_to_content', $priority );
	}
}
add_action( 'pre_get_posts', 'alert_me_pre_get_posts' );
function alert_me_add_to_content( $content ) {
	global $wp_current_filter;
	$options = get_option( 'alertme_options', array() );
	$post_type = get_post_type( get_the_ID() );
	$alertme_enable_post_alertme = get_post_meta( get_the_ID(), 'alertme_enable_post_alertme', true );
	// Don't add to get_the_excerpt because it's too early and strips tags (adding to the_excerpt is allowed)
	if ( in_array( 'get_the_excerpt', (array) $wp_current_filter ) || is_home() || is_search() || is_archive() || !array_key_exists($post_type , $options['display_alert_me_post_type']) || get_post_status( get_the_ID() ) == 'private') {
		// Return early
		return $content;
	}
	if ($options['alert_me_post_type_visibility_setting'][$post_type] == 'manual') {
			
			if ($alertme_enable_post_alertme == 1):
				$options['alert_me_position'] = isset( $options['alert_me_position'] ) ? $options['alert_me_position'] : 'bottom';
				if ($options['alert_me_position'] == 'both' || $options['alert_me_position'] == 'top') {
					// Prepend to content
					$content = alert_me_loadAlertMeBox($options) . '<br>' . $content;
				}
				if ( $options['alert_me_position'] == 'bottom' || $options['alert_me_position'] == 'both') {
					// Append to content
					$content .= alert_me_loadAlertMeBox($options);
				}
			endif;
	} else {
		
		$options['alert_me_position'] = isset( $options['alert_me_position'] ) ? $options['alert_me_position'] : 'bottom';
		if ($options['alert_me_position'] == 'both' || $options['alert_me_position'] == 'top') {
			// Prepend to content
			$content = alert_me_loadAlertMeBox($options) . '<br>' . $content;
		}
		if ( $options['alert_me_position'] == 'bottom' || $options['alert_me_position'] == 'both') {
			// Append to content
			$content .= alert_me_loadAlertMeBox($options);
		}
	}
	return $content;	
}
function alert_me_loadAlertMeBox( $options, $print = false ) {
	global $alert_me_form_heading_text, $alert_me_form_success_message,$options, $wpdb, $alertme_table;
	$table_name = $wpdb->prefix . $alertme_table;
	if (isset($options['alert_me_my_subscription_page']) && $options['alert_me_my_subscription_page'] != 0 ) {
        $alert_me_my_subscription_page = get_the_permalink($options['alert_me_my_subscription_page']);
	} else {
		$alert_me_my_subscription_page = get_site_url(); 
	}
	ob_start();
?>
	<?php if ( is_user_logged_in() ):
    	$current_user = wp_get_current_user();
    	$count_query = "select count(*) from " .$table_name. " WHERE post_id = '". get_the_ID() ."' AND user_id = " . $current_user->ID . " AND email_confirm = 1";
	    $alreadyCount = $wpdb->get_var($count_query);
	    if ($alreadyCount > 0): ?>
			<div class="alertme_email already_subscribed_box">
				<div class="alertme_conatainer">
					<div class="alert_icon">
						<img src="<?php echo ALERTME_ASSETS . '/images/alert-bell-icon.svg'; ?>" style="max-width: 120px;">
					</div>
					<div class="already_subscribed_text">
						<p><?php echo esc_html__('You are subscribed to alerts anytime this page is updated.', ALERTME_TXT_DOMAIN); ?></p>
						<p><?php echo esc_html__('To change your subscription preferences, ', ALERTME_TXT_DOMAIN); ?><a href="<?php echo $alert_me_my_subscription_page; ?>"><?php echo esc_html__(' click here', ALERTME_TXT_DOMAIN); ?></a>.</p>
					</div>
				</div>
			</div>
	    <?php else: ?>
			<div class="alertme_email boxed">
				<div class="alertme_conatainer">
					<h2 class="alertme_heading">
						<?php echo ((isset($options['alert_me_form_heading_text']) && $options['alert_me_form_heading_text'] = '') ? $options['alert_me_form_heading_text'] :  $alert_me_form_heading_text ); ?>
					</h2>
					<form name="alert_me_form" id="alert_me_form" method="post" action="">
						<?php wp_nonce_field('alert-me-email-subscribe'); ?>
						<input type="hidden" name="alert_me_post_id" id="alert_me_post_id" value="<?php echo get_the_ID(); ?>">
						<div class="form-check">
							<input type="checkbox" class="form-check-input alertme_form_for_logge_in_users" id="exampleCheck1">
							<label class="form-check-label" for="exampleCheck1">
								<?php echo esc_html__('Subscribe to this page', ALERTME_TXT_DOMAIN); ?>
							</label>
						</div>
						<a href="<?php echo $alert_me_my_subscription_page; ?>">
							<?php echo esc_html__('Manage my subscriptions', ALERTME_TXT_DOMAIN); ?>
						</a>
					</form>
				</div>
			</div>
		<?php endif; ?>
	<?php else: ?>
		<div class="alertme_email">
			<div class="alertme_conatainer">
				<h2 class="alertme_heading">
					<?php echo ((isset($options['alert_me_form_heading_text']) && $options['alert_me_form_heading_text'] != '') ? $options['alert_me_form_heading_text'] :  $alert_me_form_heading_text ); ?>
				</h2>
				<form name="alert_me_form" id="alert_me_form" method="post" action="">
				    <div class="alertme_row">
				    	<?php wp_nonce_field('alert-me-email-subscribe'); ?>
				    	<input type="hidden" name="alert_me_post_id" id="alert_me_post_id" value="<?php echo get_the_ID(); ?>">
				      <div class="alertme_left">
				        <img src="<?php echo ALERTME_ASSETS . '/images/email.svg' ?>">
				        <input type="text" name="alert_me_email" class="alertme_input" id="alert_me_email" placeholder="<?php echo esc_html__('Email', ALERTME_TXT_DOMAIN); ?>">
				      </div>
				      <div class="alertme_right">
				        <button type="button" id="alert_me_submit" class="alertme_button">
				        	<?php echo esc_html__('Subscribe', ALERTME_TXT_DOMAIN); ?>
				        </button>
				      </div>
				    </div>
				</form>
			</div>
		</div>
	<?php endif; ?>
<?php
    $output = ob_get_contents();
	ob_end_clean();
	if ($print) {
		echo $output;
	} else {
		return $output;
	}
}
/*
*
* Add shortCode in plugin
* User this [alertme-form-show] to render form
*
*/
function alert_me_shortcode_form_show() {
	ob_start();
	if ( !is_admin() ) {
		$options = get_option( 'alertme_options', array() );
		alert_me_loadAlertMeBox($options, true);	
	}
	if (is_admin()) {
		// To load checkbox with shortcode, so admin can send notification
		$title = apply_filters( 'alertme_meta_box_title', __( 'AlertMe', 'alert-me' ) );
		add_meta_box( 'alert_me_meta', $title, 'alert_me_meta_box_content', '', 'side', 'default' );
	}
	return ob_get_clean();
}
add_shortcode( 'alertme-form-show', 'alert_me_shortcode_form_show' );