<?php
if (isset($_POST['hm_sbp_download']) && $_POST['download_file'] == 1) {
	include(ABSPATH . "wp-includes/pluggable.php");
	// Require admin privs
	if ( ! current_user_can( 'administrator' ) )
		return false;
			
	check_admin_referer( 'alert-me-download-file' );
	$filename =  'AlertMe_list- ';
	$filename .= date('Y-m-d', current_time('timestamp')).'.csv';
	header('Content-Type: text/csv');
	header('Content-Disposition: attachment; filename="'.$filename.'"');
	
	// Output the report header row (if applicable) and body
	$stdout = fopen('php://output', 'w');
	alert_me_export_header($stdout);
	alert_me_export_body($stdout);
	
	exit;
}
function alert_me_export_header($dest, $return=false) {
	$header = array();
	$header[] = "Email";
	$header[] = "Post/Page";
	$header[] = 'SubscribedAt';
	if ($return)
		return $header;
	fputcsv($dest, $header);
}
function alert_me_export_body($dest, $return=false) { 
	global $wpdb, $alertme_table;
	$table_name = $wpdb->prefix . $alertme_table; 
	if ($return)
		$rows = array();
	$sql = "SELECT * FROM $table_name WHERE email_confirm = 1 ORDER BY created_at DESC";
	$results = $wpdb->get_results( $sql, 'ARRAY_A' );
	$rows = array();
	foreach ($results as $key => $value) {
		$row = array();
		$row[] = $value['email'];
		$row[] = get_the_title($value['post_id']);
		$row[] = date('m/d/Y', strtotime($value['created_at']));
		if ($return)
			$rows[] = $row;
		else
			fputcsv($dest, $row);		
	}
	if ($return)
		return $rows;	
}
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
class Alert_Me_Subscribers_List extends WP_List_Table {
	/** Class constructor */
	public function __construct() {
		parent::__construct( [
			'singular' => __( 'Singature', ALERTME_TXT_DOMAIN ), //singular name of the listed records
			'plural'   => __( 'Singatures', ALERTME_TXT_DOMAIN ), //plural name of the listed records
			'ajax'     => false //does this table support ajax?
		] );
	}
	/**
	 * Retrieve customers data from the database
	 *
	 * @param int $per_page
	 * @param int $page_number
	 *
	 * @return mixed
	 */
	public static function get_customers( $per_page = 5, $page_number = 1 ) {
		global $wpdb, $alertme_table;
		$table_name = $wpdb->prefix . $alertme_table; 
		$search = ( isset( $_REQUEST['s'] ) ) ? sanitize_text_field($_REQUEST['s']) : false;
		$do_search = ( $search ) ? $wpdb->prepare(" email LIKE '%%%s%%'", $search) : '';
		$sql = "SELECT * FROM $table_name where id != '' AND email_confirm = 1 ";
		$sql .= $do_search;
		if ( ! empty( $_REQUEST['orderby'] ) ) {
			$sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
			$sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
		} else {
			$sql .= " ORDER BY created_at DESC";
		}
		$sql .= " LIMIT $per_page";
		$sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;
		$result = $wpdb->get_results( $sql, 'ARRAY_A' );
		//echo "<pre>";		print_r($result);	echo "</pre>";
		return $result;
	}
	/**
	 * Delete a customer record.
	 *
	 * @param int $id customer id
	 */
	public static function delete_donation( $id ) {
		global $wpdb, $alertme_table; ;
		$wpdb->delete(
			"{$wpdb->prefix}{$alertme_table}",
			[ 'id' => $id ],
			[ '%d' ]
		);
	}
	/**
	 * Returns the count of records in the database.
	 *
	 * @return null|string
	 */
	public static function record_count() {
		global $wpdb, $alertme_table; ;
		$table_name = $wpdb->prefix . $alertme_table; 
		$search = ( isset( $_REQUEST['s'] ) ) ? $_REQUEST['s'] : false;
		$do_search = ( $search ) ? $wpdb->prepare(" AND user_repair_order LIKE '%%%s%%' OR email LIKE '%%%s%%' OR user_car_count LIKE '%%%s%%'", $search, $search, $search) : '';
		$sql = "SELECT COUNT(*) FROM $table_name WHERE id !=  '' AND email_confirm = 1";
		$sql .= $do_search;
		return $wpdb->get_var( $sql );
	}
	/** Text displayed when no customer data is available */
	public function no_items() {
		_e( 'No subscribers currently.', ALERTME_TXT_DOMAIN );
	}
	/**
	 * Render a column when no column specific method exist.
	 *
	 * @param array $item
	 * @param string $column_name
	 *
	 * @return mixed
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			// case 'id':
			// 	return $item[ $column_name ];
			case 'email':
				//return $this->column_name($item);
				return $item[ $column_name ];
			case 'post_id':
				return $this->get_post_name($item);
			case 'email_confirm':
				return (($item[ $column_name ] == 1) ? 'Confirmed' : 'Not Confirm' );
			case 'user_id':
				return (($item[ $column_name ] == 0) ? 'Visitor' : $this->getUserName($item[ $column_name ]) );
			case 'created_at':
				return date('F j, Y', strtotime($item[ $column_name ]));
			default:
				return print_r( $item, true ); //Show the whole array for troubleshooting purposes
		}
	}
	public function getUserName($userID) {
		$user_info = get_userdata($userID);
		return ucwords($user_info->display_name);
	}
	/**
	 * Render the bulk edit checkbox
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['id']
		);
	}
	/**
	 * Method for name column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_name( $item ) {
		$delete_nonce = wp_create_nonce( 'rt_delete_alertme_item' );
		$title = '<strong>' . $item['email'] . '</strong>';
		$actions = [
			'delete' => sprintf( '<a href="?page=%s&action=%s&id=%s&_wpnonce=%s">Delete</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['id'] ), $delete_nonce )
		];
		//$actions = array();
		return $title . $this->row_actions( $actions );
	}
	function get_post_name($item) {
		return get_the_title($item['post_id']);
	}
	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	function get_columns() {
		$columns = [
			'cb'      => '<input type="checkbox" />',
			//'id'    => __( 'ID', ALERTME_TXT_DOMAIN ),
			'email'    => __( 'Email', ALERTME_TXT_DOMAIN ),
			'post_id'   => __( 'Post/Page', ALERTME_TXT_DOMAIN ),
			'email_confirm' => __( 'Email Confirmed', ALERTME_TXT_DOMAIN ),
			'user_id' => __( 'Subscriber', ALERTME_TXT_DOMAIN ),
			'created_at'	=> __( 'Subscribed Date', ALERTME_TXT_DOMAIN ),
		];
		return $columns;
	}
	/**
	 * Columns to make sortable.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'email' => array( 'email', true )
		);
		return $sortable_columns;
	}
	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = [
			'bulk-delete' => 'Delete'
		];
		return $actions;
	}
	/**
	 * Handles data query and filter, sorting, and pagination.
	 */
	public function prepare_items() {
		/** Process bulk action */
		$this->process_bulk_action();
		$this->_column_headers = $this->get_column_info();
		$per_page     = $this->get_items_per_page( 'alert_me_per_page', 20 );
		$current_page = $this->get_pagenum();
		$total_items  = self::record_count();
		$this->set_pagination_args( [
			'total_items' => $total_items, //WE have to calculate the total number of items
			'per_page'    => $per_page //WE have to determine how many items to show on a page
		] );
		$this->items = self::get_customers( $per_page, $current_page );
	}
	public function process_bulk_action() {
		//Detect when a bulk action is being triggered...
		if ( 'delete' === $this->current_action() ) {
			// In our file that handles the request, verify the nonce.
			$nonce = esc_attr( $_REQUEST['_wpnonce'] );
			if ( ! wp_verify_nonce( $nonce, 'rt_delete_alertme_item' ) ) {
				die( 'Go get a life script kiddies' );
			}
			else {
				self::delete_donation( absint( $_GET['id'] ) );
		        //wp_redirect( esc_url( add_query_arg() ) );
		        wp_redirect( $_SERVER['HTTP_REFERER'] );
				exit;
			}
		}
		// If the delete bulk action is triggered
		if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' )
		     || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-delete' )
		) {
			$delete_ids = esc_sql( $_POST['bulk-delete'] );
			// loop over the array of record ids and delete them
			foreach ( $delete_ids as $id ) {
				self::delete_donation( $id );
			}
			wp_redirect( $_SERVER['HTTP_REFERER'] );
			exit;
		}
	}
}
class AlertMe_load_Subscriber_page {
	// class instance
	static $instance;
	// customer WP_List_Table object
	public $customers_obj;
	// class constructor
	public function __construct() {
		add_filter( 'set-screen-option', [ __CLASS__, 'set_screen' ], 10, 3 );
		add_action( 'admin_menu', [ $this, 'plugin_menu' ] );
	}
	public static function set_screen( $status, $option, $value ) {
		return $value;
	}
	public function plugin_menu() {
		$hook = add_submenu_page( 'alert_me_settings', 'Subscribers', 'Subscribers',
    'manage_options', 'alert-me-subscribers', [ $this, 'alert_me_subscribers' ]);
		add_action( "load-$hook", [ $this, 'screen_option' ] );
	}
	/**
	 * Plugin settings page
	 */
	public function alert_me_subscribers() {
		?>
		<div class="wrap">
			<h2><?php echo esc_html__('AlertMe! Subscribers', ALERTME_TXT_DOMAIN); ?></h2>
			<?php
			    $message = '';
			    if ('delete' === $this->customers_obj->current_action()) {
			        $message = '<div class="updated below-h2" id="message"><p>' . sprintf(__('Items deleted: %d', 'cltd_example'), '1' ) . '</p></div>';
			    } elseif ('bulk-delete' === $this->customers_obj->current_action()) {
			        $message = '<div class="updated below-h2" id="message"><p>' . sprintf(__('Items deleted: %d', 'cltd_example'), count($_REQUEST['bulk-delete'])) . '</p></div>';			    	
			    }
			?>
			<?php echo $message; ?>
			<?php if ($message != ''): ?>
				<?php echo 	sprintf( '<a href="%s">Go Back to List</a>',$_SERVER['HTTP_REFERER'] ); ?>
			<?php endif; ?>
			<?php if ($message == ''): ?>
				<form method="post" name="download_report">
					<input type="hidden" name="download_file" value="1">
					<?php wp_nonce_field('alert-me-download-file'); ?>
					<button type="submit" class="button-primary" name="hm_sbp_download" value="1" onclick="jQuery(this).closest('form').attr('target', \'\'); return true;">Download as CSV</button>
				</form>
				<form method="get">
					<p class="search-box">
					<label class="screen-reader-text" for="search_id-search-input">
					search:</label> 
					<input id="search_id-search-input" type="text" name="s" value="<?php echo (( isset($_REQUEST['s'])) ? $_REQUEST['s'] : '');  ?>" /> 
					<input id="search-submit" class="button" type="submit" name="" value="search" />
				  	<input type="hidden" name="page" value="<?= esc_attr($_REQUEST['page']) ?>" />
				  	</p>
				</form>
			<?php endif; ?>
			<div id="poststuff">
				<div id="post-body" class="metabox-holder">
					<div id="post-body-content">
						<div class="meta-box-sortables ui-sortable">
							<form method="post">
								<?php
								$this->customers_obj->prepare_items();
								$this->customers_obj->display(); ?>
							</form>
						</div>
					</div>
				</div>
				<br class="clear">
			</div>
		</div>
	<?php
	}
	/**
	 * Screen options
	 */
	public function screen_option() {
		$option = 'per_page';
		$args   = [
			'label'   => 'AlertMe!',
			'default' => 5,
			'option'  => 'alert_me_per_page'
		];
		add_screen_option( $option, $args );
		$this->customers_obj = new Alert_Me_Subscribers_List();
	}
	/** Singleton instance */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
?>