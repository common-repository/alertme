<?php
class Alert_Me_Statistics_List extends WP_List_Table {
	/** Class constructor */
	public function __construct() {
		parent::__construct( [
			'singular' => __( 'Statistic', ALERTME_TXT_DOMAIN ), //singular name of the listed records
			'plural'   => __( 'Statistics', ALERTME_TXT_DOMAIN ), //plural name of the listed records
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
		$sql = "SELECT `post_id` as page, COUNT(*) as Subscribers FROM $table_name WHERE email_confirm =1 GROUP BY `post_id`";
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
	 * Returns the count of records in the database.
	 *
	 * @return null|string
	 */
	public static function record_count() {
		global $wpdb, $alertme_table; ;
		$table_name = $wpdb->prefix . $alertme_table;
		$sql = "SELECT COUNT(*) FROM (SELECT COUNT(*) FROM $table_name WHERE email_confirm =1 GROUP BY `post_id`) groups";
		return $wpdb->get_var( $sql );
	}
	/** Text displayed when no customer data is available */
	public function no_items() {
		_e( 'No Statistics yet.', ALERTME_TXT_DOMAIN );
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
			case 'page':
				return $this->get_post_name($item);
			case 'Subscribers':
				return $item[ $column_name ];
			default:
				return print_r( $item, true ); //Show the whole array for troubleshooting purposes
		}
	}
	function get_post_name($item) {
		return '<strong>'.get_the_title($item['page']) . '</srong>';
	}
	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	function get_columns() {
		$columns = [
			'page'    => __( 'Page/Post', ALERTME_TXT_DOMAIN ),
			'Subscribers'   => __( 'Subscribers', ALERTME_TXT_DOMAIN )
		];
		return $columns;
	}
	/**
	 * Columns to make sortable.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortable_columns = array();
		return $sortable_columns;
	}
	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = [];
		return $actions;
	}
	/**
	 * Handles data query and filter, sorting, and pagination.
	 */
	public function prepare_items() {
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
}
class AlertMe_load_Statistics_page {
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
		$hook = add_submenu_page( 'alert_me_settings', 'Statistics', 'Statistics',
    'manage_options', 'alert-me-statistics', [ $this, 'alert_me_statistics' ]);
		add_action( "load-$hook", [ $this, 'screen_option' ] );
	}
	/**
	 * Plugin settings page
	 */
	public function alert_me_statistics() {
		global $wpdb, $alertme_table;
		$table_name = $wpdb->prefix . $alertme_table;
		$sql_max = "SELECT `post_id` as page, COUNT(*) as Subscribers FROM $table_name WHERE email_confirm =1 GROUP BY `post_id` ORDER BY Subscribers DESC LIMIT 1";
		$sql_min = "SELECT `post_id` as page, COUNT(*) as Subscribers FROM $table_name WHERE email_confirm =1 GROUP BY `post_id` ORDER BY Subscribers ASC LIMIT 1";
		$result_max = $wpdb->get_row( $sql_max, 'ARRAY_A' );
		$result_min = $wpdb->get_row( $sql_min, 'ARRAY_A' );
		?>
		<div class="wrap">
			<h2><?php echo esc_html__('AlertMe! Subscription Statistics', ALERTME_TXT_DOMAIN); ?></h2>
			<div id="post-body" class="metabox-holder">
                <table class="form-table">
                	<tbody>
                		<tr>
                			<th style="width: 50%;">
                				<div class="postbox">
                					<div class="inside">
                						<div class="alertme_most_least_subscriptions">
                							<p>
                								<?php echo esc_html__('Page with the most Subscriptions', ALERTME_TXT_DOMAIN); ?>
                							</p>
                							<span class="count">
                								<?php echo ((isset($result_max['Subscribers'])) ? $result_max['Subscribers'] : '0' ); ?>
                							</span>
                							<span class="page_name">
                								<?php echo ((isset($result_max['page'])) ? get_the_title($result_max['page']) : '' ); ?>
                							</span>
                						</div>
                					</div>
                				</div>
                			</th>
                			<th style="width: 50%;">
                				<div class="postbox">
                					<div class="inside">
                						<div class="alertme_most_least_subscriptions">
                							<p>
                								<?php echo esc_html__('Page with the least Subscriptions', ALERTME_TXT_DOMAIN); ?>
                							</p>
                							<span class="count">
                								<?php echo ((isset($result_min['Subscribers'])) ? $result_min['Subscribers'] : '0' ); ?>
                							</span>
                							<span class="page_name">
                								<?php echo ((isset($result_min['page'])) ? get_the_title($result_min['page']) : '' ); ?>
                							</span>
                						</div>
                						
                					</div>
                				</div>                				
                			</th>
                		</tr>
                	</tbody>
                </table>
       		</div>
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
		$this->customers_obj = new Alert_Me_Statistics_List();
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