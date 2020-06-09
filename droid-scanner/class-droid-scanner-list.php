<?php
/**
 * Create a table to display all entries submitted by the Droid Scanner.
 *
 * @package DroidScanner
 */

// Admin Table for DroidScanner Key Management.
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Create a table to display all entries submitted by the Droid Scanner.
 */
class Droid_Scanner_List extends WP_List_Table {

	/**
	 *  Get things started with the table.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => __( 'Entry', 'droid-scanner' ),
				'plural'   => __( 'Entries', 'droid-scanner' ),
				'ajax'     => false,
			)
		);
	}

	/**
	 *  Text displayed when no entry data is available.
	 */
	public function no_items() {
		esc_attr_e( 'No entries stored yet.', 'droid-scanner' );
	}

	/**
	 *  Format the data for the row item
	 *
	 * @param array $item The row item to display.
	 */
	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="%1$s" value="%2$s" />',
			$this->_args['plural'] . '[]',
			$item->droid_scanner_name
		);
	}

	/**
	 * Get columns and their names.
	 */
	public function get_columns() {
		$columns = array(
			'cb'           => '<input type="checkbox" />',
			'droid_scanner_name'    => __( 'Applicant Name', 'droid-scanner' ),
			'droid_scanner_email' => __( 'Applicant Email', 'droid-scanner' ),
			'droid_scanner_inspire'         => __( 'Applicant Inspiration', 'droid-scanner' ),
		);

		return $columns;
	}

	/**
	 * Get sortable columns and their config.
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'droid_scanner_name' => array( 'droid_scanner_name', true ),
			'droid_scanner_email'      => array( 'droid_scanner_email', false ),
		);

		return $sortable_columns;
	}

	/**
	 * Set the content for each row's column.
	 *
	 * @param array  $item The row item to be displayed.
	 * @param string $column_name The name of the column to put the data in.
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'droid_scanner_name':
				return $item->droid_scanner_name;
			case 'droid_scanner_email':
				return $item->droid_scanner_email;
			case 'droid_scanner_inspire':
				return $item->droid_scanner_inspire;
		}
	}

	/**
	 * Get the data from the database to put into the table.
	 */
	public function prepare_items() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'droid_scanner_entries';
		$order      = ! empty( $_GET['order'] ) ? sanitize_text_field( wp_unslash( $_GET['order'] ) ) : '';
		$orderby    = ! empty( $_GET['orderby'] ) ? sanitize_sql_orderby( wp_unslash( $_GET['orderby'] ) . ' ' . $order ) : 'ASC';

		// Process any bulk actions first.
		$this->process_bulk_action();

		$query = "SELECT * FROM $table_name";

		if ( ! empty( $orderby ) & ! empty( $order ) ) {
			$query .= $wpdb->prepare( ' ORDER BY %s ', array( $orderby ) );
		}

		$totalitems = $wpdb->query( $query ); // WPCS: unprepared SQL OK.

		// First, lets decide how many records per page to show.
		$perpage = 20;

		// Which page is this?
		$paged = ! empty( $_GET['paged'] ) ? intval( $_GET['paged'] ) : '';
		// Page Number.
		if ( empty( $paged ) || ! is_numeric( $paged ) || $paged <= 0 ) {
			$paged = 1;
		}

		// How many pages do we have in total?
		$totalpages = ceil( $totalitems / $perpage );
		// Adjust the query to take pagination into account.
		if ( ! empty( $paged ) && ! empty( $perpage ) ) {
			$offset = ( $paged - 1 ) * $perpage;
			$query .= $wpdb->prepare( ' LIMIT %d,%d', array( (int) $offset, (int) $perpage ) );
		}

		// Register the pagination.
		$this->set_pagination_args(
			array(
				'total_items' => $totalitems,
				'total_pages' => $totalpages,
				'per_page'    => $perpage,
			)
		);

		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items           = $wpdb->get_results( $query ); // WPCS: unprepared SQL OK.
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = array(
			'bulk-delete' => __('Delete', 'droid-scanner'),
		);

		return $actions;
	}

	/**
	 * Do the bulk action submitted.
	 */
	public function process_bulk_action() {


	}
}
