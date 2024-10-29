<?php
/*
* Handle Ajax request to store email address in DB
*/
function alert_me_subscription() {
	global $wpdb, $alertme_table, $alert_me_form_success_message, $options;
	$result = array();
	check_admin_referer( 'alert-me-email-subscribe' );

	$table_name = $wpdb->prefix . $alertme_table;

    if ( is_user_logged_in() ):

    	$current_user = wp_get_current_user();

    	$count_query = "select count(*) from " .$table_name. " WHERE post_id = '".sanitize_text_field($_POST['alert_me_post_id'])."' AND user_id = " . $current_user->ID . " AND email_confirm = 1";
	    $alreadyCount = $wpdb->get_var($count_query);

    	if ($alreadyCount > 0) {
    		$result['error'] = esc_html__('You are already subscribed with this email address.', ALERTME_TXT_DOMAIN);
    	} else {

			$data = array(
				'post_id'    => sanitize_text_field($_POST['alert_me_post_id']),
				'email' => sanitize_email($current_user->data->user_email),
				'user_id' => $current_user->ID,
				'email_confirm' => 1
			);

			$wpdb->insert($table_name, $data );

			if (isset($options['alert_me_my_subscription_page']) && $options['alert_me_my_subscription_page'] != 0 ) {
		        $alert_me_my_subscription_page = get_the_permalink($options['alert_me_my_subscription_page']);
			} else {
				$alert_me_my_subscription_page = get_site_url(); 
			}

			sendEmailAboutNewSubscriptionToAdmin(sanitize_text_field($_POST['alert_me_post_id']));

			$success_message = '<h2>Success! </h2><p>You are subscribed to alerts anytime this page is updated.</p><p>To change your subscription preferences, <a href="'. $alert_me_my_subscription_page .'">click here</a>.</p>';

			$result['success'] = '<div class="sucess_col"><div class="sub-container">'. $success_message .'</div></div>';

    	}

    else:

		if (isset($_POST['alert_me_email']) && $_POST['alert_me_email'] != ''):
		    
	    	$count_query = "select count(*) from " .$table_name. " WHERE email= '".sanitize_email($_POST['alert_me_email'])."' AND post_id = '".sanitize_text_field($_POST['alert_me_post_id'])."' AND user_id = 0";
	    	$alreadyCount = $wpdb->get_var($count_query);

	    	if ($alreadyCount > 0) {
	    		$result['error'] = esc_html__('You are already subscribed with this email address.', ALERTME_TXT_DOMAIN);
	    	} else {

				$data = array(
					'post_id'    => sanitize_text_field($_POST['alert_me_post_id']),
					'email' => sanitize_email($_POST['alert_me_email'])
				);
				$wpdb->insert( $table_name, $data );
				$lastid = $wpdb->insert_id;

				sentOutEmailConfirmationEmail($_POST['alert_me_email'], $_POST['alert_me_post_id'], $lastid);
				sendEmailAboutNewSubscriptionToAdmin(sanitize_text_field($_POST['alert_me_post_id']));
	    	}

			$success_message = ((isset($options['alert_me_form_success_message']) && $options['alert_me_form_success_message'] != '' ) ? nl2br(html_entity_decode($options['alert_me_form_success_message'])) :  $alert_me_form_success_message );

			$result['success'] = '<div class="sucess_col"><div class="sub-container">'. $success_message .'</div></div>';

		else:

			$result['error'] = esc_html__('Email should not be empty', ALERTME_TXT_DOMAIN);

		endif;

    endif;

	echo json_encode($result);
	exit();	
}
add_action( 'wp_ajax_alert_me_subscription', 'alert_me_subscription' );
add_action( 'wp_ajax_nopriv_alert_me_subscription', 'alert_me_subscription' );


function sentOutEmailConfirmationEmail($email, $postID, $lastid) {

	global $alert_me_confirmation_subject;

	$conformationStuff = array(
		'email' => $email,
		'post' => $postID,
		'user_id' => 0,
		'last_id' => $lastid
	);

	$emailConfirmation_link = get_site_url().'/?action=alert_me_email_confirmation&data='.base64_encode(serialize($conformationStuff));

	$headers = "From: " . get_option('blogname') . " <" . get_option( 'admin_email' ) . ">" . "\r\n";
	$headers .= "Reply-To: ". get_option( 'admin_email' ). "\r\n";
	$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

	$subject = $alert_me_confirmation_subject;

	$confirmation_email_body = "<h2>Please Confirm Subscription</h2>";
	$confirmation_email_body .= '<br>';
	$confirmation_email_body .= '<a href="'.$emailConfirmation_link.'" style="background: #03a9f4;border-radius: 25px;padding: 12px 20px;font-weight: 600;font-size: 15px;color: #fff; text-decoration: none;" target="_blank"> Confirm Alert Subscription</a>';
	$confirmation_email_body .= '<br><br><br>';
	$confirmation_email_body .= 'If you received this email by mistake, simply delete it. You won\'t be subscribed
if you don\'t click the confirmation link above.';
	$confirmation_email_body .= '<br><br>';
	$confirmation_email_body .= '<p style="color: #000; font-size: 17px; font-weight: bold;">' . get_option('blogname') . '</p>';

	wp_mail( $email, $subject, nl2br($confirmation_email_body), $headers ); 
}


function alert_me_email_confirmation() {
	global $wpdb, $alertme_table, $options;

	if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'alert_me_email_confirmation' && $_REQUEST['data'] != '' ) {
		$finalData = unserialize(base64_decode(sanitize_text_field($_REQUEST['data'])));

		$table_name = $wpdb->prefix . $alertme_table;
		$wpdb->update(
			$table_name, 
			array(
				'email_confirm' => 1
			),
			array(
				'id'=> $finalData['last_id']
			)
		);

		if (isset($options['alert_me_confirmation_thank_you']) && $options['alert_me_confirmation_thank_you'] != 0 ) {
            wp_redirect( get_the_permalink($options['alert_me_confirmation_thank_you']) );
            die; 
		} else {
			wp_redirect( home_url() ); exit; 
		}
	}
}

add_action('init', 'alert_me_email_confirmation');


function sendEmailAboutNewSubscriptionToAdmin($postID) {

	$headers = "From: " . get_option('blogname') . " <" . get_option( 'admin_email' ) . ">" . "\r\n";
	$headers .= "Reply-To: ". get_option( 'admin_email' ). "\r\n";
	$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

	$subject =  esc_html__('You have a new subscriber', ALERTME_TXT_DOMAIN); ;

	$confirmation_email_body = "Hello,";
	$confirmation_email_body .= '<br>';
	$confirmation_email_body .= 'Your website has a new subscriber to the following post(s):';
	$confirmation_email_body .= '<br><br>';
	$confirmation_email_body .= '<a href="'. get_the_permalink($postID) .'" target="_blank">'. get_the_title($postID) .'</a>';
	$confirmation_email_body .= '<br><br>';
	$confirmation_email_body .= '<p style="color: #000; font-size: 17px; font-weight: bold;">' . get_option('blogname') . '</p>';

	wp_mail( $email, $subject, nl2br($confirmation_email_body), $headers ); 
}
?>