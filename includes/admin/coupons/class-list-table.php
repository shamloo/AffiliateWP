<?php
/**
 * 'Coupons' Admin List Table
 *
 * @package   AffiliateWP\Admin\Coupons
 * @copyright Copyright (c) 2017, AffiliateWP, LLC
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     2.1
 *
 */

use AffWP\Admin\List_Table;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * AffWP_Coupons_Table class.
 *
 * Renders the Coupons table on the Affiliate-edit screen.
 *
 * @since 2.1
 *
 * @see \AffWP\Admin\List_Table
 */
class AffWP_Coupons_Table extends List_Table {

	/**
	 * Default number of items to show per page
	 *
	 * @access public
	 * @since 2.1
	 * @var    string
	 */
	public $per_page = 30;

	/**
	 * Total number of coupons found.
	 *
	 * @access public
	 * @since 2.1
	 * @var    int
	 */
	public $total_count;

	/**
	 * Number of 'failed' coupons found
	 *
	 * @access public
	 * @since 2.1
	 * @var    string
	 */
	public $failed_count;

	/**
	 * Coupons table constructor.
	 *
	 * @access public
	 * @since 2.1
	 *
	 * @see WP_List_Table::__construct()
	 *
	 * @param array $args Optional. Arbitrary display and query arguments to pass through
	 *                    the list table. Default empty array.
	 */
	public function __construct( $args = array() ) {
		$args = wp_parse_args( $args, array(
			'singular' => 'coupon',
			'plural'   => 'coupons',
		) );

		parent::__construct( $args );

		$this->get_coupon_counts();
	}

	/**
	 * Displays the search field.
	 *
	 * @access public
	 * @since 2.1
	 *
	 * @param string $text     Label for the search box.
	 * @param string $input_id ID of the search box.
	 */
	public function search_box( $text, $input_id ) {
		if ( empty( $_REQUEST['s'] ) && ! $this->has_items() )
			return;

		$input_id = $input_id . '-search-input';

		if ( ! empty( $_REQUEST['orderby'] ) )
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
		if ( ! empty( $_REQUEST['order'] ) )
			echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
		?>
		<p class="search-box">
			<label class="screen-reader-text" for="<?php echo $input_id ?>"><?php echo $text; ?>:</label>
			<input type="search" id="<?php echo $input_id ?>" name="s" value="<?php _admin_search_query(); ?>" />
			<?php submit_button( $text, 'button', false, false, array( 'ID' => 'search-submit' ) ); ?>
		</p>
		<?php
	}

	/**
	 * Retrieves the coupon view types.
	 *
	 * @access public
	 * @since 2.1
	 *
	 * @return array $views All the views available.
	 */
	public function get_views() {
		$base         = affwp_admin_url( 'coupons' );
		$current      = isset( $_GET['status'] ) ? $_GET['status'] : '';
		$total_count  = '&nbsp;<span class="count">(' . $this->total_count    . ')</span>';

		$views = array(
			'all'    => sprintf( '<a href="%s"%s>%s</a>', esc_url( remove_query_arg( 'status', $base ) ), $current === 'all' || $current == '' ? ' class="current"' : '', _x( 'All', 'coupons', 'affiliate-wp') . $total_count )
		);

		return $views;
	}

	/**
	 * Retrieves the coupons table columns.
	 *
	 * @access public
	 * @since 2.1
	 *
	 * @return array $columns Array of all the coupons list table columns.
	 */
	public function get_columns() {
		$columns = array(
			'cb'            => '<input type="checkbox" />',
			'integration'   => __( 'Integration', 'affiliate-wp' ),
			'code'          => __( 'Coupon Code', 'affiliate-wp' ),
			'coupon_id'     => __( 'Coupon ID', 'affiliate-wp' ),
			'referrals'     => __( 'Referrals', 'affiliate-wp' ),
			'actions'       => __( 'Actions', 'affiliate-wp' )
		);

		/**
		 * Filters the coupons list table columns.
		 *
		 * @since 2.1
		 *
		 * @param array $columns List table columns.
		 */
		return apply_filters( 'affwp_coupon_table_columns', $this->prepare_columns( $columns ) );
	}

	/**
	 * Retrieves the coupons table's sortable columns.
	 *
	 * @access public
	 * @since 2.1
	 *
	 * @return array Array of all the sortable columns
	 */
	public function get_sortable_columns() {
		return array(
			'coupon_id'     => array( 'coupon_id', false ),
			'affiliate_id'  => array( 'affiliate', false ),
			'coupon_method' => array( 'coupon_method', false ),
			'status'        => array( 'status', false ),
			'date'          => array( 'date', false ),
		);
	}

	/**
	 * Renders the checkbox column.
	 *
	 * @access public
	 * @since 2.1
	 *
	 * @param \AffWP\Affiliate\Coupon $coupon Current coupon object.
	 * @return string Checkbox markup.
	 */
	function column_cb( $coupon ) {
		return '<input type="checkbox" name="coupon_id[]" value="' . absint( $coupon->ID ) . '" />';
	}

	/**
	 * Renders the 'Coupon ID' column
	 *
	 * @access public
	 * @since 2.1
	 *
	 * @param \AffWP\Affiliate\Coupon $coupon Current coupon object.
	 * @return string Coupon ID.
	 */
	public function column_coupon_id( $coupon ) {
		$value = esc_html( $coupon->ID );

		/**
		 * Filters the value of the 'Coupon ID' column in the coupons list table.
		 *
		 * @since 2.1
		 *
		 * @param int                     $value  Coupon ID.
		 * @param \AffWP\Affiliate\Coupon $coupon Current coupon object.
		 */
		return apply_filters( 'affwp_coupon_table_coupon_id', $value, $coupon );
	}

	/**
	 * Renders the 'Affiliate' column.
	 *
	 * @access public
	 * @since 2.1
	 *
	 * @param \AffWP\Affiliate\Coupon $coupon Current coupon object.
	 * @return string Linked affiliate name and ID.
	 */
	function column_affiliate( $coupon ) {
		$url = affwp_admin_url( 'affiliates', array(
			'action'       => 'view_affiliate',
			'affiliate_id' => $coupon->affiliate_id
		) );

		$name      = affiliate_wp()->affiliates->get_affiliate_name( $coupon->affiliate_id );
		$affiliate = affwp_get_affiliate( $coupon->affiliate_id );

		if ( $affiliate && $name ) {
			$value = sprintf( '<a href="%1$s">%2$s</a> (ID: %3$s)',
				esc_url( $url ),
				esc_html( $name ),
				esc_html( $affiliate->ID )
			);
		} else {
			$value = __( '(user deleted)', 'affiliate-wp' );
		}

		/**
		 * Filters the value of the 'Affiliate' column in the coupons list table.
		 *
		 * @since 2.1
		 *
		 * @param mixed                   $value  Column value.
		 * @param \AffWP\Affiliate\Coupon $coupon Current coupon object.
		 */
		return apply_filters( 'affwp_coupon_table_affiliate', $value, $coupon );
	}

	/**
	 * Renders the 'Referrals' column.
	 *
	 * @access public
	 * @since 2.1
	 *
	 * @param \AffWP\Affiliate\Coupon $coupon Current coupon object.
	 * @return string Linked affiliate name and ID.
	 */
	public function column_referrals( $coupon ) {
		$referrals = affiliate_wp()->affiliates->coupons->get_referral_ids( $coupon );
		$links     = array();

		foreach ( $referrals as $referral_id ) {
			$links[] = affwp_admin_link( 'referrals', esc_html( $referral_id ), array( 'action' => 'edit_referral', 'referral_id' => $referral_id ) );
		}

		$value = implode( ', ', $links );

		/**
		 * Filters the value of the 'Referrals' column in the coupons list table.
		 *
		 * @since 2.1
		 *
		 * @param mixed                   $value  Column value.
		 * @param \AffWP\Affiliate\Coupon $coupon Current coupon object.
		 */
		return apply_filters( 'affwp_coupon_table_referrals', $value, $coupon );
	}

	/**
	 * Renders the 'Actions' column.
	 *
	 * @access public
	 * @since 2.1
	 *
	 * @see WP_List_Table::row_actions()
	 *
	 * @param \AffWP\Affiliate\Coupon $coupon Current coupon object.
	 * @return string Action links markup.
	 */
	function column_actions( $coupon ) {

		$base_query_args = array(
			'page'      => 'affiliate-wp-coupons',
			'coupon_id' => $coupon->ID
		);

		// View.
		$row_actions['view'] = $this->get_row_action_link(
			__( 'View', 'affiliate-wp' ),
			array_merge( $base_query_args, array(
				'action'       => 'view_coupon',
				'affwp_notice' => false,
			) )
		);

		if ( strtolower( $coupon->status ) == 'failed' ) {
			// Retry Payment.
			$row_actions['retry'] = $this->get_row_action_link(
				__( 'Retry Payment', 'affiliate-wp' ),
				array_merge( $base_query_args, array(
					'affwp_notice' => 'coupon_retried',
					'action'       => 'retry_payment',
				) ),
				'coupon-nonce'
			);
		}

		/**
		 * Filters the row actions for the coupons list table row.
		 *
		 * @since 2.1
		 *
		 * @param array                   $row_actions Row actions markup.
		 * @param \AffWP\Affiliate\Coupon $coupon      Current coupon object.
		 */
		$row_actions = apply_filters( 'affwp_coupon_row_actions', $row_actions, $coupon );

		return $this->row_actions( $row_actions, true );
	}

	/**
	 * Renders the 'Status' column.
	 *
	 * @access public
	 * @since 2.1
	 *
	 * @param \AffWP\Affiliate\Coupon $coupon Current coupon object.
	 * @return string Coupon status.
	 */
	public function column_status( $coupon ) {
		$value = sprintf( '<span class="affwp-status %1$s"><i></i>%2$s</span>',
			esc_attr( $coupon->status ),
			affwp_get_coupon_status_label( $coupon )
		);

		/**
		 * Filters the value of the 'Status' column in the coupons list table.
		 *
		 * @since 2.1
		 *
		 * @param string                  $value  Coupon status.
		 * @param \AffWP\Affiliate\Coupon $coupon Current coupon object.
		 */
		return apply_filters( 'affwp_referral_table_status', $value, $coupon );
	}

	/**
	 * Renders the default output for a custom column in the coupons list table.
	 *
	 * @access public
	 * @since 2.1
	 *
	 * @param \AffWP\Affiliate\Coupon $coupon      Current coupon object.
	 * @param string                  $column_name The name of the column.
	 * @return string Column name.
	 */
	function column_default( $coupon, $column_name ) {
		$value = isset( $coupon->$column_name ) ? $coupon->$column_name : '';

		/**
		 * Filters the value of the default column in the coupons list table.
		 *
		 * The dynamic portion of the hook name, `$column_name`, refers to the column name.
		 *
		 * @since 2.1
		 *
		 * @param mixed                   $value  Column value.
		 * @param \AffWP\Affiliate\Coupon $coupon Current coupon object.
		 */
		return apply_filters( 'affwp_coupon_table_' . $column_name, $value, $coupon );
	}

	/**
	 * Message to be displayed when there are no items.
	 *
	 * @access public
	 * @since 2.1
	 */
	function no_items() {
		_e( 'No coupons found.', 'affiliate-wp' );
	}

	/**
	 * Retrieves the bulk actions.
	 *
	 * @access public
	 * @since 2.1
	 *
	 * @return array $actions Array of the bulk actions.
	 */
	public function get_bulk_actions() {
		$actions = array(
			'retry_payment' => __( 'Retry Payment', 'affiliate-wp' ),
		);

		/**
		 * Filters the list of bulk actions for the coupons list table.
		 *
		 * @since 2.1
		 *
		 * @param array $actions Bulk actions.
		 */
		return apply_filters( 'affwp_coupon_bulk_actions', $actions );
	}

	/**
	 * Processes the bulk actions.
	 *
	 * @access public
	 * @since 2.1
	 */
	public function process_bulk_action() {
		// @todo Hook up bulk actions.
	}

	/**
	 * Retrieves the coupon counts.
	 *
	 * @access public
	 * @since 2.1
	 */
	public function get_coupon_counts() {
		$this->failed_count = affiliate_wp()->affiliates->coupons->count(
			array_merge( $this->query_args, array( 'status' => 'failed' ) )
		);

		$this->total_count  = $this->paid_count + $this->failed_count;
	}

	/**
	 * Retrieves all the data for all the coupons.
	 *
	 * @access public
	 * @since 2.1
	 *
	 * @return array Array of all the data for the coupons.
	 */
	public function coupons_data() {

		$page    = isset( $_GET['paged'] )   ? absint( $_GET['paged'] )         :           1;
		$owner   = isset( $_GET['owner'] )   ? absint( $_GET['owner'] )         :           0;
		$status  = isset( $_GET['status'] )  ? sanitize_key( $_GET['status'] )  :          '';
		$order   = isset( $_GET['order'] )   ? sanitize_key( $_GET['order'] )   :      'DESC';
		$orderby = isset( $_GET['orderby'] ) ? sanitize_key( $_GET['orderby'] ) : 'coupon_id';

		$is_search = false;

		if ( isset( $_GET['coupon_id'] ) ) {
			$coupon_ids = sanitize_text_field( $_GET['coupon_id'] );
		} else {
			$coupon_ids = 0;
		}

		if ( isset( $_GET['affiliate_id'] ) ) {
			$affiliates = sanitize_text_field( $_GET['affiliate_id'] );
		} else {
			$affiliates = 0;
		}

		if ( isset( $_GET['referrals'] ) ) {
			$referrals = sanitize_text_field( $_GET['referrals'] );
		} else {
			$referrals = array();
		}

		if( ! empty( $_GET['s'] ) ) {

			$is_search = true;

			$search = sanitize_text_field( $_GET['s'] );

			if ( is_numeric( $search ) || preg_match( '/^([0-9]+\,[0-9]+)/', $search, $matches ) ) {
				// Searching for specific coupons.
				if ( ! empty( $matches[0] ) ) {
					$is_search  = false;
					$coupon_ids = array_map( 'absint', explode( ',', $search ) );
				} else {
					$coupon_ids = absint( $search );
				}
			} elseif ( strpos( $search, 'referrals:' ) !== false ) {
				$referrals = trim( str_replace( array( ' ', 'referrals:' ), '', $search ) );
				if ( false !== strpos( $referrals, ',' ) ) {
					$is_search = false;
					$referrals = array_map( 'absint', explode( ',', $referrals ) );
				} else {
					$referrals = absint( $referrals );
				}
			} elseif ( strpos( $search, 'affiliate:' ) !== false ) {
				$affiliates = trim( str_replace( array( ' ', 'affiliate:' ), '', $search ) );
				if ( false !== strpos( $affiliates, ',' ) ) {
					$is_search  = false;
					$affiliates = array_map( 'absint', explode( ',', $affiliates ) );
				} else {
					$affiliates = absint( $affiliates );
				}
			}

		}

		$per_page = $this->get_items_per_page( 'affwp_edit_coupons_per_page', $this->per_page );

		$args = wp_parse_args( $this->query_args, array(
			'number'       => $per_page,
			'offset'       => $per_page * ( $page - 1 ),
			'coupon_id'    => $coupon_ids,
			'referrals'    => $referrals,
			'affiliate_id' => $affiliates,
			'owner'        => $owner,
			'status'       => $status,
			'search'       => $is_search,
			'orderby'      => $orderby,
			'order'        => $order
		) );

		$coupons = affiliate_wp()->affiliates->coupons->get_coupons( $args );

		// Retrieve the "current" total count for pagination purposes.
		$args['number']      = -1;
		$this->current_count = affiliate_wp()->affiliates->coupons->count( $args );

		return $coupons;
	}

	/**
	 * Sets up the final data for the coupons list table.
	 *
	 * @access public
	 * @since 2.1
	 */
	public function prepare_items() {
		$per_page = $this->get_items_per_page( 'affwp_edit_coupons_per_page', $this->per_page );

		$this->get_column_info();

		$this->process_bulk_action();

		$data = $this->coupons_data();

		$current_page = $this->get_pagenum();

		$status = isset( $_GET['status'] ) ? $_GET['status'] : 'any';

		switch( $status ) {
			case 'paid':
				$total_items = $this->paid_count;
				break;
			case 'failed':
				$total_items = $this->failed_count;
				break;
			case 'any':
				$total_items = $this->current_count;
				break;
		}

		$this->items = $data;

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $per_page,
			'total_pages' => ceil( $total_items / $per_page )
		) );
	}
}
