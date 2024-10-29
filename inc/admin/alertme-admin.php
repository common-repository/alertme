<?php
/**
 * Load CSS for Admin
*/
function alert_me_load_plugin_css_for_admin() {
	if ( is_admin() ) {
		wp_enqueue_style( 'alert-me-admin', ALERTME_ASSETS . 'css/alert_me_admin.css', false, '1.0' );
	}
}
add_action( 'admin_enqueue_scripts', 'alert_me_load_plugin_css_for_admin', 20 );
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
	
	$default_tab = null;
	$tab = isset($_GET['tab']) ? $_GET['tab'] : $default_tab;
	?>
	<div class="wrap">
		
		<h1><?php echo esc_html__( 'AlertMe! Settings', ALERTME_TXT_DOMAIN ); ?></h1>
		
		<nav class="nav-tab-wrapper">
			<a href="?page=alert-me-settings" class="nav-tab <?php if($tab===null):?>nav-tab-active<?php endif; ?>">
				<?php echo esc_html__( 'Display Settings', ALERTME_TXT_DOMAIN ); ?>
			</a>
			<a href="?page=alert-me-settings&tab=subscribe-form" class="nav-tab <?php if($tab==='subscribe-form'):?>nav-tab-active<?php endif; ?>">
				<?php echo esc_html__( 'Subscribe Form', ALERTME_TXT_DOMAIN ); ?>
			</a>
			<a href="?page=alert-me-settings&tab=notification-email" class="nav-tab <?php if($tab==='notification-email'):?>nav-tab-active<?php endif; ?>">
				<?php echo esc_html__( 'Notification Email', ALERTME_TXT_DOMAIN ); ?>
			</a>
			<a href="?page=alert-me-settings&tab=manage-subscriptions" class="nav-tab <?php if($tab==='manage-subscriptions'):?>nav-tab-active<?php endif; ?>">
				<?php echo esc_html__( 'Manage Subscriptions', ALERTME_TXT_DOMAIN ); ?>
			</a>
			<a href="?page=alert-me-settings&tab=confirmation-pages" class="nav-tab <?php if($tab==='confirmation-pages'):?>nav-tab-active<?php endif; ?>">
				<?php echo esc_html__( 'Confirmation Pages', ALERTME_TXT_DOMAIN ); ?>
			</a>
			<a href="?page=alert-me-settings&tab=shortcodes" class="nav-tab <?php if($tab==='shortcodes'):?>nav-tab-active<?php endif; ?>">
				<?php echo esc_html__( 'Shortcodes', ALERTME_TXT_DOMAIN ); ?>
			</a>
			<a href="?page=alert-me-settings&tab=support" class="nav-tab <?php if($tab==='support'):?>nav-tab-active<?php endif; ?>">
				<?php echo esc_html__( 'Support', ALERTME_TXT_DOMAIN ); ?>
			</a>
		</nav>
		<div class="tab-content">
		<?php switch($tab) :
			case 'subscribe-form':
				include(ALERTME_PATH.'/inc/admin/tabs/alertme-frontend-subscribe-form-tab.php');
			break;
			case 'notification-email':
				include(ALERTME_PATH.'/inc/admin/tabs/alertme-notification-email-tab.php');
			break;
			case 'manage-subscriptions':
				include(ALERTME_PATH.'/inc/admin/tabs/alertme-manage-subscriptions-tab.php');
			break;
			case 'confirmation-pages':
				include(ALERTME_PATH.'/inc/admin/tabs/alertme-confirmation-pages-tab.php');
			break;
			case 'shortcodes':
				include(ALERTME_PATH.'/inc/admin/tabs/alertme-shortcodes-tab.php');
			break;
			case 'support':
				include(ALERTME_PATH.'/inc/admin/tabs/alertme-support-tab.php');
			break;
			default:
				include(ALERTME_PATH.'/inc/admin/tabs/alertme-display-settings-tab.php');
			break;
		endswitch; ?>
		</div>
	</div>
<?php }
function alertme_position_in_content( $options, $option_box = false ) {

	$html = '';
	
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