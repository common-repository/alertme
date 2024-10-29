<?php
/*
Plugin Name: AlertMe!
Plugin URI: https://bloomwp.com/plugins/alertme/
Description: Easily add a signup box in a post or page that when updated will send your subscribers an email alert.
Version: 2.0.3
Author: Bloom WP
Author URI: https://www.bloomwp.com/
*/
global $alertme_table, $options, $alert_me_form_heading_text, $alert_me_form_success_message, $alert_me_confirmation_subject;
/**
* Define global variables values
*/

$alertme_table = 'alert_me';

define('ALERTME_PATH', plugin_dir_path(__FILE__));
define('ALERTME_ASSETS', plugins_url('/assets/', __FILE__));
define('ALERTME_TXT_DOMAIN', 'alert-me');

$alert_me_form_heading_text = esc_html__('Send me an email when this page has been updated', ALERTME_TXT_DOMAIN);
$alert_me_form_success_message = '<h2>Success! </h2><p>Please check your email for a <strong>confirmation link...</strong></p><p>We hate spam as much as you do, so we need to confirm your email address.</p>';

$alert_me_email_subject_line = esc_html__('A post has been updated', ALERTME_TXT_DOMAIN);
$alert_me_confirmation_subject = esc_html__('Confirm your subscription', ALERTME_TXT_DOMAIN);

$options = get_option( 'alertme_options', array() );
/**
 * Load Current User stuff
 */

add_action( 'plugins_loaded', 'alertme_loadCurrentUserClass' );
function alertme_loadCurrentUserClass() {
	if(!function_exists('wp_get_current_user')) {
	    include(ABSPATH . "wp-includes/pluggable.php"); 
	}
}

/**
 * Get all custom post types
*/
function alertme_getCustomPostTypes() {
	$custom_post_types = array_values( get_post_types( array( 'public' => true, '_builtin' => false ), 'objects' ) );
	return $custom_post_types;
}
/**
 * Get all custom post types
*/
function alertme_getPostTypes() {
	$getPostTypes = array_values( get_post_types( array( 'public' => true, '_builtin' => true ), 'objects' ) );
	return $getPostTypes;
}
/**
 * Admin Options
 */
if ( is_admin() ) {
	include_once ALERTME_PATH . '/inc/admin/alertme-admin.php';
	include_once ALERTME_PATH . '/inc/admin/alertme-subscribers.php';
	include_once ALERTME_PATH . '/inc/admin/alertme-statistics.php';
	add_action( 'plugins_loaded', function () {
		AlertMe_load_Subscriber_page::get_instance();
	} );

	add_action( 'plugins_loaded', function () {
		AlertMe_load_Statistics_page::get_instance();
	} );	
}
include_once ALERTME_PATH . '/inc/front-end/alertme-frontend.php';
include_once ALERTME_PATH . '/inc/front-end/alertme-ajaxrequest.php';
include_once ALERTME_PATH . '/inc/front-end/alertme-notficiation.php';
include_once ALERTME_PATH . '/inc/front-end/alertme-subscriptions-list.php';



/**
 * Add setting page hook
*/
function alertme_add_menu_link() {

	add_menu_page(
		esc_html__('AlertMe!', ALERTME_TXT_DOMAIN),
		esc_html__('AlertMe!', ALERTME_TXT_DOMAIN),
		'',
		'alert_me_settings',
		'',
		'dashicons-bell',
		100
	);

	add_submenu_page(
		'alert_me_settings',
		esc_html__('Settings', ALERTME_TXT_DOMAIN),
		esc_html__('Settings', ALERTME_TXT_DOMAIN),
		'manage_options',
		'alert-me-settings',
		'alert_me_settings'
	);
}
add_filter( 'admin_menu', 'alertme_add_menu_link' );


// Place in Option List on Settings > Plugins page 
function alert_me_action_links( $links, $file ) {
	// Static so we don't call plugin_basename on every plugin row.
	static $this_plugin;
	
	if ( ! $this_plugin ) {
		$this_plugin = plugin_basename( __FILE__ );
	}
	
	if ( $file == $this_plugin ) {
		$settings_link = '<a href="admin.php?page=alert-me-settings">' . __( 'Settings' ) . '</a>';
		array_unshift( $links, $settings_link );
	}
	
	return $links;
}
add_filter( 'plugin_action_links', 'alert_me_action_links', 10, 2 );

/**
 * Add ajax URl in head for Ajax call
*/
function alert_me_ajaxurl() {
   	echo '<script type="text/javascript">
           var ajaxurl = "' . admin_url('admin-ajax.php') . '";
         </script>';
}
add_action('wp_head', 'alert_me_ajaxurl');
/**
 * Activation hook
*/
function alertme_activation() {
	// Get all existing Aletme options
	$options = get_option( 'alertme_options', array() );
	// Create a table in DB to hold the singup info.
	global $wpdb, $alertme_table;
	$table_name = $wpdb->prefix . $alertme_table;
	$charset_collate = $wpdb->get_charset_collate();
    if($wpdb->get_var( "show tables like '$table_name'" ) != $table_name) { 
		$sql = "CREATE TABLE $table_name (
		  `id` bigint(20) NOT NULL auto_increment,
		  `post_id` bigint(44) COLLATE utf8mb4_unicode_ci NOT NULL,
		  `email` varchar(44) COLLATE utf8mb4_unicode_ci NOT NULL,
		  `is_unsubscribed` int(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 0,
		  `email_confirm` int(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 0,
		  `user_id` int(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 0,
		  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		  PRIMARY KEY  (id)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
    }	
}
register_activation_hook( __FILE__, 'alertme_activation' );
/**
 * Deactivation hook
 */
function alertme_unistall() {
	global $wpdb, $alertme_table;
	update_option( 'alertme_options', array() );
	$table_name = $wpdb->prefix . $alertme_table;
	$sql = "DROP TABLE IF EXISTS $table_name;";
	$wpdb->query($sql);
	delete_option("my_plugin_db_version");	
}
//register_deactivation_hook( __FILE__, 'alertme_unistall' );
register_uninstall_hook( __FILE__, 'alertme_unistall' );


/**
* Alter Table for Version 2.0 for user ID and email confirmation
*/
function alertme_alter_table() {
  global $wpdb, $alertme_table;

  $table_name = $wpdb->prefix . $alertme_table;

  $result = $wpdb->get_results("SHOW COLUMNS FROM " . $table_name ." LIKE 'user_id'");
  $exists = ( ($wpdb->num_rows > 0 ) ? TRUE : FALSE);

  if (!$exists) {
    $sql = $wpdb->query("ALTER TABLE ". $table_name ." ADD user_id int(10) DEFAULT 0 after is_unsubscribed");
  }

  // Alter table excel for Bulky stuff
  $result = $wpdb->get_results("SHOW COLUMNS FROM " . $table_name ." LIKE 'email_confirm'");
  $exists = ( ($wpdb->num_rows > 0 ) ? TRUE : FALSE);

  if (!$exists) {
    $sql = $wpdb->query("ALTER TABLE " . $table_name ." ADD email_confirm int(1) DEFAULT 1 after is_unsubscribed");
  }

}
add_action( 'admin_init', 'alertme_alter_table');
?>