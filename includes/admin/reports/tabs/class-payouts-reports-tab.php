<?php
namespace AffWP\Affiliate\Payout\Admin\Reports;

use AffWP\Admin\Reports;

/**
 * Implements an 'Payouts' tab for the Reports screen.
 *
 * @since 2.1
 *
 * @see \AffWP\Admin\Reports\Tab
 */
class Tab extends Reports\Tab {

	/**
	 * Sets up the Payouts tab for Reports.
	 *
	 * @access public
	 * @since  2.1
	 */
	public function __construct() {
		$this->tab_id   = 'payouts';
		$this->label    = __( 'Payouts', 'affiliate-wp' );
		$this->priority = 5;
		$this->graph    = new \Affiliate_WP_Registrations_Graph;

		parent::__construct();
	}

	/**
	 * Registers the 'Total Earnings Paid' (all time) tile.
	 *
	 * @access public
	 * @since  2.1
	 *
	 * @see register_tile()
	 */
	public function total_paid_all_time_tile() {
		$payouts = affiliate_wp()->affiliates->payouts->get_payouts( array(
			'number' => -1,
			'fields' => 'amount',
		) );

		$this->register_tile( 'total_paid_all_time', array(
			'label'           => __( 'Total Earnings Paid', 'affiliate-wp' ),
			'type'            => 'amount',
			'context'         => 'primary',
			'data'            => array_sum( $payouts ),
			'comparison_data' => __( 'All Time', 'affiliate-wp' ),
		) );
	}

	/**
	 * Registers the 'Total Earnings Paid' date-based tile.
	 *
	 * @access public
	 * @since  2.1
	 *
	 * @see register_tile()
	 */
	public function total_earnings_paid_tile() {
		$payouts = affiliate_wp()->affiliates->payouts->get_payouts( array(
			'number' => -1,
			'fields' => 'amount',
			'date'   => $this->date_query,
		) );

		$this->register_tile( 'total_earnings_paid', array(
			'label' => __( 'Total Earnings Paid', 'affiliate-wp' ),
			'type'  => 'amount',
			'context' => 'secondary',
			'data'    => array_sum( $payouts ),
			'comparison_data' => $this->get_date_comparison_label(),
		) );
	}

	/**
	 * Registers the 'Total Payouts Count' (all time) tile.
	 *
	 * @access public
	 * @since  2.1
	 *
	 * @see register_tile()
	 */
	public function total_payouts_count_tile() {
		$this->register_tile( 'total_payouts_count', array(
			'label'           => __( 'Total Payouts Count', 'affiliate-wp' ),
			'type'            => 'number',
			'context'         => 'tertiary',
			'data'            => affiliate_wp()->affiliates->payouts->count(),
			'comparison_data' => __( 'All Time', 'affiliate-wp' ),
		) );
	}

	/**
	 * Registers the 'Average Payout' date-based tile.
	 *
	 * @access public
	 * @since  2.1
	 *
	 * @see register_tile()
	 */
	public function average_payout_tile() {
		$payouts = affiliate_wp()->affiliates->payouts->get_payouts( array(
			'number' => -1,
			'fields' => 'amount',
			'date'   => $this->date_query,
		) );

		if ( ! $payouts ) {
			$payouts = array( 0 );
		}

		$this->register_tile( 'average_payout_amount', array(
			'label'           => __( 'Average Payout', 'affiliate-wp' ),
			'type'            => 'amount',
			'context'         => 'secondary',
			'data'            => array_sum( $payouts ) / count( $payouts ),
			'comparison_data' => $this->get_date_comparison_label(),
		) );
	}

	/**
	 * Registers the Payouts tab tiles.
	 *
	 * @access public
	 * @since  2.1
	 */
	public function register_tiles() {
		$this->total_paid_all_time_tile();
		$this->total_earnings_paid_tile();
		$this->total_payouts_count_tile();
		$this->average_payout_tile();
	}

	/**
	 * Handles displaying the 'Trends' graph.
	 *
	 * @access public
	 * @since  2.1
	 */
	public function display_trends() {
		$this->graph->set( 'show_controls', false );
		$this->graph->set( 'x_mode',   'time' );
		$this->graph->set( 'currency', false  );
		$this->graph->display();
	}

}
