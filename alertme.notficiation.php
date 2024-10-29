<?php
function alert_me_notification_send_email($post_id) {

	if ( !wp_is_post_revision( $post_id ) ) {

		if (isset($_POST['rt_send_alert_for_selected_post'])) {

			global $wpdb, $alert_me_email_subject_line;
			$options = stripslashes_deep( get_option( 'alertme_options', array() ) );
			$table_name = $wpdb->prefix . "alert_me";
			$sql = "SELECT id, post_id, email,is_unsubscribed FROM $table_name where post_id = '".$post_id."' AND is_unsubscribed = 0";
			$results = $wpdb->get_results( $sql, 'ARRAY_A' );

			if (!empty($results)):

				foreach ($results as $key => $result) {

					$post_title = get_the_title( $post_id );
					$post_url = get_permalink( $post_id );
					$unsubscribe_link = get_site_url().'/?action=alert_me_do_unsubscribed&data='.base64_encode(serialize($result));

					$headers = "From: " . get_option('blogname') . " <" . get_option( 'admin_email' ) . ">" . "\r\n";
					$headers .= "Reply-To: ". get_option( 'admin_email' ). "\r\n";
					$headers .= "MIME-Version: 1.0\r\n";
					$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";  


					$subject = ((isset($options['alert_me_email_subject_line'])) ? $options['alert_me_email_subject_line'] :  $alert_me_email_subject_line );

					$alert_me_email_body = "Hello,";
					$alert_me_email_body .= '<br><br>';
					$alert_me_email_body .= "The page you have subscribed to, ";
					$alert_me_email_body .= "<a href='". $post_url. "'>" .$post_title. "</a> has recently been updated.";
					$alert_me_email_body .= '<br><br>';
					$alert_me_email_body .= "You are receiving this email because you had previously subscribed to update notifications. If you are no longer interested, you can <a href='". $unsubscribe_link. "' target='_blank'>unsubscribe</a> to these alerts.";
					$alert_me_email_body .= '<br><br>';
					//$alert_me_email_body .=  ((isset($options['alert_me_custom_signature'])) ? html_entity_decode($options['alert_me_custom_signature']) :  html_entity_decode($alert_me_custom_signature) );
					//$alert_me_email_body .= '<br><br>';
					$alert_me_email_body .= '<b>'.get_option('blogname').'</b>';


					$message = ((isset($options['alert_me_email_body'])) ? html_entity_decode($options['alert_me_email_body']) :  $alert_me_email_body );

					$unsubscribe_link_html = "<a href='". $unsubscribe_link. "' target='_blank'>unsubscribe</a>";
					$post_title_html = "<a href='". $post_url. "'>" .$post_title. "</a>";

					$message = str_replace( '{alertme_post_name}', $post_title_html, $message );
					$message = str_replace( '{alertme_unsubscribe}', $unsubscribe_link_html, $message );
					$message .= '<br><br><b>'.get_option('blogname').'</b>';

					wp_mail( $result['email'], $subject, nl2br($message), $headers ); 
				}

			endif;		
		}

	}
}

add_action( 'save_post', 'alert_me_notification_send_email' );

function alert_me_do_unsubscribed() {
	global $wpdb;

	if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'alert_me_do_unsubscribed' && $_REQUEST['data'] != '' ) {
		$finalData = unserialize(base64_decode(sanitize_text_field($_REQUEST['data'])));
		$table_name = $wpdb->prefix . "alert_me";
		$options = get_option( 'alertme_options', array() );

		$wpdb->delete(
			"{$wpdb->prefix}alert_me",
			[ 'id' => $finalData['id'] ],
			[ '%d' ]
		);

		if (isset($options['alert_me_opt_out_thank_you_page']) && $options['alert_me_opt_out_thank_you_page'] != 0 ) {
            wp_redirect( get_the_permalink($options['alert_me_opt_out_thank_you_page']) );
            die; 
		} else {
			wp_redirect( home_url() ); exit; 
		}
	}
}

add_action('init', 'alert_me_do_unsubscribed');
?>